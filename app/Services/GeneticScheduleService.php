<?php

namespace App\Services;

use App\Models\BatchJadwal;
use App\Models\Guru;
use App\Models\GuruFree;
use App\Models\GuruMapel;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SlotJam;
use App\Models\GuruPiket;
use Illuminate\Support\Facades\DB;

class GeneticScheduleService
{
  private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
  private $popSize = 100;
  private $maxGenerations = 5000;

  public function generate($academicYearId)
  {
    $allActiveSlots = SlotJam::where('is_istirahat', false)->orderBy('slot_number', 'asc')->get();
    if ($allActiveSlots->isEmpty()) {
      throw new \Exception('Data slot jam tidak ditemukan atau kosong.');
    }

    $slotsNormal = $allActiveSlots->pluck('id')->toArray();
    $slotsJumat = $allActiveSlots->filter(function ($slot) {
      return $slot->slot_number <= 6 || ($slot->slot_number >= 11 && $slot->slot_number <= 16);
    })->pluck('id')->toArray();

    $guruMapel = GuruMapel::with('mapel')->where('tahun_ajaran_id', $academicYearId)->get()->toArray();
    if (empty($guruMapel)) {
      throw new \Exception('Data plotting (target mengajar) tidak ditemukan atau kosong.');
    }

    $targetGroupsAssoc = [];
    foreach ($guruMapel as $gm) {
      $tipeMapel = $gm['mapel']['type'] ?? 'teori';

      if ($tipeMapel === 'praktikum') {
        $key = 'PRAKTIKUM_' . $gm['kelas_id'] . '_' . $gm['mapel_id'];
      } else {
        $key = 'TEORI_' . $gm['kelas_id'] . '_' . $gm['mapel_id'] . '_' . $gm['guru_id'];
      }

      if (!isset($targetGroupsAssoc[$key])) {
        $targetGroupsAssoc[$key] = [
          'kelas_id'  => $gm['kelas_id'],
          'mapel_id'  => $gm['mapel_id'],
          'beban_jam' => $gm['mapel']['beban_jam'] ?? 2,
          'gurus'     => []
        ];
      }
      if (!in_array($gm['guru_id'], $targetGroupsAssoc[$key]['gurus'])) {
        $targetGroupsAssoc[$key]['gurus'][] = $gm['guru_id'];
      }
    }
    $targetGroups = array_values($targetGroupsAssoc);

    // Memaksa mesin memprioritaskan mapel ber-JP besar (misal 6 JP) di awal 
    // agar mendapat slot berurutan sebelum sisa slot diisi mapel ber-JP kecil.
    usort($targetGroups, function ($a, $b) {
      return $b['beban_jam'] <=> $a['beban_jam'];
    });
    // ---------------------------------------------------------

    $availabilities = GuruFree::where('is_available', false)
      ->get()
      ->groupBy('guru_id')
      ->toArray();

    $guruPiket = GuruPiket::where('tahun_ajaran_id', $academicYearId)
      ->get()
      ->groupBy('guru_id')
      ->map(function ($items) {
        return $items->pluck('hari')->toArray();
      })->toArray();

    $populasi = [];
    for ($i = 0; $i < $this->popSize; $i++) {
      $populasi[] = $this->generateKromosomAcak($targetGroups, $slotsNormal, $slotsJumat, $guruPiket);
    }

    $generasi = 0;
    $solusiTerbaik = null;

    while ($generasi < $this->maxGenerations) {
      $populasi = $this->hitungPopulasiFitness($populasi, $availabilities, $guruPiket);

      usort($populasi, function ($a, $b) {
        return $b['fitness'] <=> $a['fitness'];
      });

      $solusiTerbaik = $populasi[0];

      if ($solusiTerbaik['fitness'] == 0) {
        break;
      }

      $populasiBaru = [$solusiTerbaik];
      while (count($populasiBaru) < $this->popSize) {
        $p1 = $populasi[rand(0, 9)]['jadwal'];
        $p2 = $populasi[rand(0, 9)]['jadwal'];

        $anak = $this->crossover($p1, $p2);
        $anak = $this->mutasi($anak, $slotsNormal, $slotsJumat, $guruPiket);

        $populasiBaru[] = ['jadwal' => $anak, 'fitness' => -9999];
      }

      $populasi = $populasiBaru;
      $generasi++;
    }

    $detailKonflik = [];
    if ($solusiTerbaik['fitness'] < 0) {
      $detailKonflik = $this->diagnosaKonflik($solusiTerbaik['jadwal'], $availabilities, $guruPiket);
    }

    $batch = DB::transaction(function () use ($solusiTerbaik, $academicYearId, $generasi) {
      $batch = BatchJadwal::create([
        'nama' => 'Simulasi Otomatis - Generasi ' . $generasi . ' (' . now()->format('d/m Y H:i') . ')',
        'status' => 'draft',
        'final_fitness_score' => $solusiTerbaik['fitness']
      ]);

      $dataInsert = [];
      foreach ($solusiTerbaik['jadwal'] as $item) {
        $dataInsert[] = array_merge($item, [
          'schedule_batch_id' => $batch->id,
          'academic_year_id'  => $academicYearId,
          'created_at'        => now(),
          'updated_at'        => now()
        ]);
      }

      Jadwal::insert($dataInsert);
      return $batch;
    });

    if ($solusiTerbaik['fitness'] == 0) {
      return ['status' => 'perfect', 'batch' => $batch, 'conflicts' => []];
    } else {
      return ['status' => 'need_revision', 'batch' => $batch, 'conflicts' => $detailKonflik];
    }
  }

  // --- PEMBARUAN: GENERATE BERBASIS BLOK JP & TIM ---
  private function generateKromosomAcak($targetGroups, $slotsNormal, $slotsJumat, $guruPiket)
  {
    $jadwal = [];
    foreach ($targetGroups as $group) {
      $hariAcak = $this->days[array_rand($this->days)];

      $attempts = 0;
      $isPiket = true;
      while ($isPiket && $attempts < 10) {
        $isPiket = false;
        foreach ($group['gurus'] as $gId) {
          if (isset($guruPiket[$gId]) && in_array($hariAcak, $guruPiket[$gId])) {
            $isPiket = true;
            break;
          }
        }
        if ($isPiket) {
          $hariAcak = $this->days[array_rand($this->days)];
          $attempts++;
        }
      }

      // KUNCI PERBAIKAN: Memecah kolam menjadi Shift Pagi dan Siang agar tidak menabrak batas
      $validPools = [];
      $b = $group['beban_jam'];

      if ($hariAcak === 'Jumat') {
        $pagi = array_slice($slotsJumat, 0, 6); // Array Slot 1-6
        $siang = array_slice($slotsJumat, 6, 6); // Array Slot 11-16
      } else {
        $pagi = array_slice($slotsNormal, 0, 10); // Array Slot 1-10
        $siang = array_slice($slotsNormal, 10, 7); // Array Slot 11-17
      }

      // Hanya gunakan Shift yang sisa kapasitasnya cukup untuk menampung JP mapel ini
      if (count($pagi) >= $b) $validPools[] = $pagi;
      if (count($siang) >= $b) $validPools[] = $siang;

      if (empty($validPools)) {
        // Fallback darurat (jika JP > 10, mesin akan terpaksa menggabungkannya)
        $slotPool = ($hariAcak === 'Jumat') ? $slotsJumat : $slotsNormal;
      } else {
        // Pilih secara acak mau dimasukkan murni ke Pagi atau murni ke Siang
        $slotPool = $validPools[array_rand($validPools)];
      }

      $maxIndex = count($slotPool) - $b;
      if ($maxIndex < 0) $maxIndex = 0;
      $startIndex = rand(0, $maxIndex);

      for ($i = 0; $i < $b; $i++) {
        $assignedSlotId = $slotPool[$startIndex + $i] ?? $slotPool[array_rand($slotPool)];

        foreach ($group['gurus'] as $guruId) {
          $jadwal[] = [
            'guru_id'      => $guruId,
            'mapel_id'     => $group['mapel_id'],
            'kelas_id'     => $group['kelas_id'],
            'day'          => $hariAcak,
            'time_slot_id' => $assignedSlotId
          ];
        }
      }
    }
    return ['jadwal' => $jadwal, 'fitness' => -9999];
  }

  private function hitungPopulasiFitness($populasi, $availabilities, $guruPiket)
  {
    $mapelTypes = Mapel::pluck('type', 'id')->toArray();
    $slotDetails = SlotJam::select('id', 'slot_number', 'is_istirahat')->get()->keyBy('id')->toArray();
    $activeSlots = SlotJam::where('is_istirahat', false)->orderBy('slot_number')->pluck('slot_number')->toArray();

    foreach ($populasi as &$individu) {
      $penalty = 0;
      $checkGuru = [];
      $checkKelas = [];
      $kelasJadwalHari = [];

      // OPTIMASI: Loop sekali saja untuk semua aturan dasar
      foreach ($individu['jadwal'] as $g) {
        // Cek Bentrok Guru & Kelas (Menggunakan akses array lebih cepat daripada query DB)
        $keyGuru = "{$g['day']}_{$g['time_slot_id']}_{$g['guru_id']}";
        if (isset($checkGuru[$keyGuru])) {
          $penalty -= 1000;
        }
        $checkGuru[$keyGuru] = true;

        $keyKelas = "{$g['day']}_{$g['time_slot_id']}_{$g['kelas_id']}";
        // Pengecualian hanya jika praktikum, gunakan logika sederhana
        if (isset($checkKelas[$keyKelas])) {
          $penalty -= 1000;
        }
        $checkKelas[$keyKelas] = true;

        // Cek Ketersediaan Guru (Availabilities)
        if (isset($availabilities[$g['guru_id']])) {
          foreach ($availabilities[$g['guru_id']] as $av) {
            if ($av['day'] === $g['day'] && $av['time_slot_id'] === $g['time_slot_id'] && $av['kelas_id'] == $g['kelas_id']) {
              $penalty -= 500;
            }
          }
        }

        $nomorJam = $slotDetails[$g['time_slot_id']]['slot_number'] ?? 1;
        $tipeMapel = $mapelTypes[$g['mapel_id']] ?? 'teori';

        // Aturan Shift (Ringan)
        if ($g['day'] === 'Jumat') {
          if (($nomorJam > 6 && $nomorJam <= 10) || $nomorJam > 16) $penalty -= 1000;
        } elseif ($tipeMapel === 'teori' && $nomorJam > 10) {
          $penalty -= 500;
        }
      }

      $individu['fitness'] = $penalty;
    }

    return $populasi;
  }

  private function crossover($j1, $j2)
  {
    if (count($j1) <= 2) return $j1;
    $cut = rand(1, count($j1) - 2);
    return array_merge(array_slice($j1, 0, $cut), array_slice($j2, $cut));
  }

  private function mutasi($jadwal, $slotsNormal, $slotsJumat, $guruPiket)
  {
    if (rand(1, 100) <= 20) {
      $randomIndex = array_rand($jadwal);
      $targetKelas = $jadwal[$randomIndex]['kelas_id'];
      $targetMapel = $jadwal[$randomIndex]['mapel_id'];

      $blockIndices = [];
      $gurus = [];
      foreach ($jadwal as $idx => $j) {
        if ($j['kelas_id'] == $targetKelas && $j['mapel_id'] == $targetMapel) {
          $blockIndices[] = $idx;
          $gurus[$j['guru_id']] = true;
        }
      }

      $b = count($blockIndices) / count($gurus); // Dapatkan Beban JP
      $hariMutasi = $this->days[array_rand($this->days)];

      $attempts = 0;
      $isPiket = true;
      while ($isPiket && $attempts < 10) {
        $isPiket = false;
        foreach (array_keys($gurus) as $gId) {
          if (isset($guruPiket[$gId]) && in_array($hariMutasi, $guruPiket[$gId])) {
            $isPiket = true;
            break;
          }
        }
        if ($isPiket) {
          $hariMutasi = $this->days[array_rand($this->days)];
          $attempts++;
        }
      }

      // KUNCI PERBAIKAN KOLAM MUTASI
      $validPools = [];
      if ($hariMutasi === 'Jumat') {
        $pagi = array_slice($slotsJumat, 0, 6);
        $siang = array_slice($slotsJumat, 6, 6);
      } else {
        $pagi = array_slice($slotsNormal, 0, 10);
        $siang = array_slice($slotsNormal, 10, 7);
      }

      if (count($pagi) >= $b) $validPools[] = $pagi;
      if (count($siang) >= $b) $validPools[] = $siang;

      if (empty($validPools)) {
        $slotPool = ($hariMutasi === 'Jumat') ? $slotsJumat : $slotsNormal;
      } else {
        $slotPool = $validPools[array_rand($validPools)];
      }

      $maxIndex = count($slotPool) - $b;
      if ($maxIndex < 0) $maxIndex = 0;
      $startIndex = rand(0, $maxIndex);

      $guruAssignments = [];
      foreach ($blockIndices as $idx) {
        $guruAssignments[$jadwal[$idx]['guru_id']][] = $idx;
      }

      foreach ($guruAssignments as $gid => $indices) {
        for ($i = 0; $i < count($indices); $i++) {
          $idx = $indices[$i];
          $jadwal[$idx]['day'] = $hariMutasi;
          $jadwal[$idx]['time_slot_id'] = $slotPool[$startIndex + $i] ?? $slotPool[array_rand($slotPool)];
        }
      }
    }
    return $jadwal;
  }

  private function diagnosaKonflik($jadwal, $availabilities, $guruPiket)
  {
    $pesan = [];
    $checkGuru = [];
    $checkKelas = [];
    $jadwalTeamTeaching = [];

    $gurus = Guru::pluck('nama_guru', 'id')->toArray();
    $kelas = Kelas::pluck('nama_kelas', 'id')->toArray();
    $slots = SlotJam::pluck('slot_number', 'id')->toArray();
    $mapelTypes = Mapel::pluck('type', 'id')->toArray();
    $mapelNames = Mapel::pluck('nama_mapel', 'id')->toArray();
    $activeSlots = SlotJam::where('is_istirahat', false)->orderBy('slot_number')->pluck('slot_number')->toArray();

    $kelasShiftLog = [];

    foreach ($jadwal as $g) {
      $namaGuru = $gurus[$g['guru_id']] ?? 'Guru Tidak Dikenal';
      $namaKelas = $kelas[$g['kelas_id']] ?? 'Kelas Tidak Dikenal';
      $namaMapel = $mapelNames[$g['mapel_id']] ?? 'Mapel Tidak Dikenal';
      $tipeMapel = $mapelTypes[$g['mapel_id']] ?? 'teori';
      $jamKe = $slots[$g['time_slot_id']] ?? 1;
      $hari = $g['day'];

      if (isset($checkGuru["{$hari}_{$jamKe}_{$g['guru_id']}"])) {
        $kelasSebelumnya = $checkGuru["{$hari}_{$jamKe}_{$g['guru_id']}"];
        $pesan[] = "Guru <b>{$namaGuru}</b> bentrok! Mengajar di <b>Kelas {$kelasSebelumnya}</b> dan <b>Kelas {$namaKelas}</b> bersamaan pada <b>{$hari} Jam ke-{$jamKe}</b>.";
      }
      $checkGuru["{$hari}_{$jamKe}_{$g['guru_id']}"] = $namaKelas;

      if (isset($checkKelas["{$hari}_{$jamKe}_{$g['kelas_id']}"])) {
        $existing = $checkKelas["{$hari}_{$jamKe}_{$g['kelas_id']}"];
        // Pengecualian Diagnosa HANYA untuk Praktikum
        if ($existing['mapel_id'] == $g['mapel_id'] && $existing['tipe'] === 'praktikum' && $tipeMapel === 'praktikum' && $existing['guru_id'] != $g['guru_id']) {
          // Team teaching diizinkan
        } else {
          $guruSebelumnya = $existing['nama_guru'];
          $pesan[] = "<b>Kelas {$namaKelas}</b> bentrok! Diajar oleh <b>{$guruSebelumnya}</b> dan <b>{$namaGuru}</b> bersamaan pada <b>{$hari} Jam ke-{$jamKe}</b>.";
        }
      } else {
        $checkKelas["{$hari}_{$jamKe}_{$g['kelas_id']}"] = [
          'nama_guru' => $namaGuru,
          'mapel_id' => $g['mapel_id'],
          'tipe' => $tipeMapel,
          'guru_id' => $g['guru_id']
        ];
      }

      if (isset($guruPiket[$g['guru_id']]) && in_array($hari, $guruPiket[$g['guru_id']])) {
        $pesan[] = "ATURAN PIKET: <b>{$namaGuru}</b> jadwalnya terpaksa masuk pada hari <b>{$hari}</b> padahal sedang bertugas PIKET.";
      }

      if ($hari === 'Jumat') {
        if (($jamKe > 6 && $jamKe <= 10) || $jamKe > 16) {
          $pesan[] = "ATURAN JUMAT: {$namaMapel} ({$namaKelas}) masuk di zona terlarang (Slot 7-10 atau 17 Jumat).";
        }
        if ($tipeMapel === 'teori' && $jamKe > 6) {
          $pesan[] = "ATURAN SHIFT JUMAT: Mapel Teori <b>{$namaMapel} ({$namaKelas})</b> melewati batas 6 JP (Terplot di Jam ke-{$jamKe}).";
        }
      } else {
        if ($tipeMapel === 'teori' && $jamKe > 10) {
          $pesan[] = "ATURAN SHIFT: Mapel Teori <b>{$namaMapel} ({$namaKelas})</b> terpaksa ditaruh di Jam Siang (Jam ke-{$jamKe}).";
        }
      }

      if (isset($availabilities[$g['guru_id']])) {
        foreach ($availabilities[$g['guru_id']] as $av) {
          if ($av['day'] === $hari && $av['time_slot_id'] === $g['time_slot_id'] && $av['kelas_id'] == $g['kelas_id']) {
            $pesan[] = "Guru <b>{$namaGuru}</b> diplot pada waktu berhalangannya di <b>Kelas {$namaKelas}</b> pada <b>{$hari} Jam ke-{$jamKe}</b>.";
          }
        }
      }

      $shiftTipe = ($jamKe <= 10) ? 'pagi' : 'siang';
      $kelasShiftLog[$g['kelas_id']][$hari][$shiftTipe] = true;

      if ($tipeMapel === 'praktikum') {
        $keyTeam = "{$g['kelas_id']}_{$g['mapel_id']}";
        $jadwalTeamTeaching[$keyTeam][] = $g;
      }
    }

    foreach ($jadwalTeamTeaching as $key => $jadwals) {
      $guruGroups = [];
      foreach ($jadwals as $jwd) {
        $guruGroups[$jwd['guru_id']][] = $jwd;
      }
      if (count($guruGroups) > 1) {
        $baseSlots = null;
        foreach ($guruGroups as $guruId => $slotsArray) {
          $currentSlots = [];
          foreach ($slotsArray as $s) {
            $currentSlots[] = $s['day'] . '_' . $s['time_slot_id'];
          }
          sort($currentSlots);

          if ($baseSlots === null) {
            $baseSlots = $currentSlots;
          } else {
            if ($baseSlots !== $currentSlots) {
              $namaKelas = $kelas[$slotsArray[0]['kelas_id']] ?? 'Kelas';
              $namaMapel = $mapelNames[$slotsArray[0]['mapel_id']] ?? 'Mapel';
              $pesan[] = "TEAM TEACHING: Guru-guru pada Praktikum <b>{$namaMapel} ({$namaKelas})</b> terpencar dan tidak mengajar di waktu yang bersamaan.";
              break;
            }
          }
        }
      }
    }

    foreach ($kelasShiftLog as $kelasId => $hariData) {
      foreach ($hariData as $hari => $shifts) {
        if (isset($shifts['pagi']) && isset($shifts['siang'])) {
          $namaKelas = $kelas[$kelasId] ?? 'Kelas';
          $pesan[] = "KAPASITAS SHIFT: <b>Kelas {$namaKelas}</b> terpaksa masuk Pagi DAN Siang sekaligus pada hari <b>{$hari}</b>.";
        }
      }
    }

    $kelompokDiagnosa = [];
    foreach ($jadwal as $g) {
      $keyGrouping = "{$g['guru_id']}_{$g['mapel_id']}_{$g['kelas_id']}";
      $kelompokDiagnosa[$keyGrouping][] = [
        'day' => $g['day'],
        'jamKe' => $slots[$g['time_slot_id']] ?? 1,
        'namaMapel' => $mapelNames[$g['mapel_id']] ?? 'Mapel',
        'namaKelas' => $kelas[$g['kelas_id']] ?? 'Kelas'
      ];
    }

    foreach ($kelompokDiagnosa as $key => $jadwals) {
      if (count($jadwals) <= 1) continue;
      $hariPertama = $jadwals[0]['day'];
      $jamKeArr = [];
      $namaMapel = $jadwals[0]['namaMapel'];
      $namaKelas = $jadwals[0]['namaKelas'];
      $bedaHari = false;
      foreach ($jadwals as $jwd) {
        if ($jwd['day'] !== $hariPertama) $bedaHari = true;
        $jamKeArr[] = $jwd['jamKe'];
      }
      if ($bedaHari) {
        $pesan[] = "ATURAN BLOK: Mapel <b>{$namaMapel} ({$namaKelas})</b> terpencar di hari yang berbeda.";
      }
      sort($jamKeArr);
      for ($i = 0; $i < count($jamKeArr) - 1; $i++) {
        $idx1 = array_search($jamKeArr[$i], $activeSlots);
        $idx2 = array_search($jamKeArr[$i + 1], $activeSlots);
        if ($idx2 !== false && $idx1 !== false) {
          if (($idx2 - $idx1) !== 1) {
            $pesan[] = "ATURAN BLOK: Jam mengajar <b>{$namaMapel} ({$namaKelas})</b> bolong/tidak berurutan (Terplot di jam {$jamKeArr[$i]} dan {$jamKeArr[$i + 1]}).";
          }
        }
      }
    }
    return array_unique($pesan);
  }
}
