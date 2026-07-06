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
    $allActiveSlots = SlotJam::where('is_istirahat', false)->get();
    if ($allActiveSlots->isEmpty()) {
      throw new \Exception('Data slot jam tidak ditemukan atau kosong.');
    }

    // --- STRATEGI DUA KOLAM (TWO POOLS) ---
    // 1. Kolam Normal (Senin - Kamis): Menggunakan semua slot 1 s/d 17
    $slotsNormal = $allActiveSlots->pluck('id')->toArray();

    // 2. Kolam Jumat: Hanya slot 1-6 (Pagi) DAN 11-16 (Siang). Slot 7-10 dibuang dari pengacakan Jumat.
    $slotsJumat = $allActiveSlots->filter(function ($slot) {
      return $slot->slot_number <= 6 || ($slot->slot_number >= 11 && $slot->slot_number <= 16);
    })->pluck('id')->toArray();

    $guruMapel = GuruMapel::with('mapel')->where('tahun_ajaran_id', $academicYearId)->get()->toArray();
    if (empty($guruMapel)) {
      throw new \Exception('Data plotting (target mengajar) tidak ditemukan atau kosong.');
    }

    $targetMengajar = [];
    foreach ($guruMapel as $gm) {
      $bebanJam = $gm['mapel']['beban_jam'] ?? 2;
      for ($i = 0; $i < $bebanJam; $i++) {
        $targetMengajar[] = [
          'guru_id'   => $gm['guru_id'],
          'mapel_id'  => $gm['mapel_id'],
          'kelas_id'  => $gm['kelas_id'],
        ];
      }
    }

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
      // Melempar dua kolam ke fungsi kromosom
      $populasi[] = $this->generateKromosomAcak($targetMengajar, $slotsNormal, $slotsJumat, $guruPiket);
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
        // Melempar dua kolam ke fungsi mutasi
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
      return [
        'status' => 'perfect',
        'batch' => $batch,
        'conflicts' => []
      ];
    } else {
      return [
        'status' => 'need_revision',
        'batch' => $batch,
        'conflicts' => $detailKonflik
      ];
    }
  }

  // --- PEMBARUAN: Memilih slot berdasarkan Hari ---
  private function generateKromosomAcak($targetMengajar, $slotsNormal, $slotsJumat, $guruPiket)
  {
    $jadwal = [];
    foreach ($targetMengajar as $target) {
      $hariAcak = $this->days[array_rand($this->days)];

      $attempts = 0;
      while (isset($guruPiket[$target['guru_id']]) && in_array($hariAcak, $guruPiket[$target['guru_id']]) && $attempts < 10) {
        $hariAcak = $this->days[array_rand($this->days)];
        $attempts++;
      }

      // Memilih kolam khusus jika hari Jumat
      $slotPool = ($hariAcak === 'Jumat') ? $slotsJumat : $slotsNormal;

      $jadwal[] = array_merge($target, [
        'day'          => $hariAcak,
        'time_slot_id' => $slotPool[array_rand($slotPool)]
      ]);
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

      foreach ($individu['jadwal'] as $g) {
        $keyGuru = "{$g['day']}_{$g['time_slot_id']}_{$g['guru_id']}";
        $keyKelas = "{$g['day']}_{$g['time_slot_id']}_{$g['kelas_id']}";

        if (isset($checkGuru[$keyGuru])) $penalty -= 500;
        $checkGuru[$keyGuru] = true;

        if (isset($checkKelas[$keyKelas])) $penalty -= 500;
        $checkKelas[$keyKelas] = true;

        $tipeMapel = $mapelTypes[$g['mapel_id']] ?? 'teori';
        $nomorJam = $slotDetails[$g['time_slot_id']]['slot_number'] ?? 1;
        $isIstirahat = $slotDetails[$g['time_slot_id']]['is_istirahat'] ?? false;

        if ($isIstirahat) $penalty -= 500;

        // --- ATURAN SHIFT (NORMAL VS JUMAT) ---
        if ($g['day'] === 'Jumat') {
          // Zona Mati Jumat (Slot 7, 8, 9, 10 dilarang ada jadwal)
          if (($nomorJam > 6 && $nomorJam <= 10) || $nomorJam > 16) {
            $penalty -= 1000;
          }
          // Teori maksimal jam 6, Praktikum harus >= 11
          if ($tipeMapel === 'teori' && $nomorJam > 6) $penalty -= 500;
          if ($tipeMapel === 'praktikum' && $nomorJam <= 10) $penalty -= 500;
        } else {
          // Aturan Shift Senin - Kamis (Teori <= 10, Praktikum >= 11)
          if ($tipeMapel === 'teori' && $nomorJam > 10) $penalty -= 500;
          if ($tipeMapel === 'praktikum' && $nomorJam <= 10) $penalty -= 500;
        }

        if (isset($guruPiket[$g['guru_id']]) && in_array($g['day'], $guruPiket[$g['guru_id']])) {
          $penalty -= 1000;
        }

        $kelasJadwalHari[$g['kelas_id']][$g['day']][] = [
          'slot_number' => $nomorJam,
          'type'        => $tipeMapel
        ];

        if (isset($availabilities[$g['guru_id']])) {
          foreach ($availabilities[$g['guru_id']] as $av) {
            if ($av['day'] === $g['day'] && $av['time_slot_id'] === $g['time_slot_id']) {
              $penalty -= 15;
            }
          }
        }
      }

      // --- ATURAN SHIFT EKSKLUSIF HARIAN ---
      foreach ($kelasJadwalHari as $kelasId => $hariData) {
        foreach ($hariData as $hari => $daftarJadwal) {
          $adaPagi = false;
          $adaSiang = false;

          foreach ($daftarJadwal as $jwd) {
            if ($jwd['slot_number'] <= 10) $adaPagi = true;
            if ($jwd['slot_number'] >= 11) $adaSiang = true;
          }

          // Karantina Penuh: Jika kelas masuk pagi & siang sekaligus dalam 1 hari = Hancurkan Jadwal!
          if ($adaPagi && $adaSiang) {
            $penalty -= 1000;
          }
        }
      }

      // Blok Berurutan
      $kelompokBlock = [];
      foreach ($individu['jadwal'] as $g) {
        $keyBlok = "{$g['guru_id']}_{$g['mapel_id']}_{$g['kelas_id']}";
        $kelompokBlock[$keyBlok][] = [
          'day' => $g['day'],
          'slot_number' => $slotDetails[$g['time_slot_id']]['slot_number'] ?? 1,
        ];
      }

      foreach ($kelompokBlock as $key => $jadwals) {
        if (count($jadwals) <= 1) continue;

        $hariPertama = $jadwals[0]['day'];
        $slotNumbers = [];

        foreach ($jadwals as $jwd) {
          if ($jwd['day'] !== $hariPertama) $penalty -= 200;
          $slotNumbers[] = $jwd['slot_number'];
        }

        sort($slotNumbers);
        for ($i = 0; $i < count($slotNumbers) - 1; $i++) {
          $idx1 = array_search($slotNumbers[$i], $activeSlots);
          $idx2 = array_search($slotNumbers[$i + 1], $activeSlots);

          if ($idx2 !== false && $idx1 !== false) {
            if (($idx2 - $idx1) !== 1) $penalty -= 200;
          }
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

  // --- PEMBARUAN: Memilih slot mutasi berdasarkan Hari ---
  private function mutasi($jadwal, $slotsNormal, $slotsJumat, $guruPiket)
  {
    if (rand(1, 100) <= 20) {
      $index = array_rand($jadwal);
      $hariMutasi = $this->days[array_rand($this->days)];

      $attempts = 0;
      while (isset($guruPiket[$jadwal[$index]['guru_id']]) && in_array($hariMutasi, $guruPiket[$jadwal[$index]['guru_id']]) && $attempts < 10) {
        $hariMutasi = $this->days[array_rand($this->days)];
        $attempts++;
      }

      $slotPool = ($hariMutasi === 'Jumat') ? $slotsJumat : $slotsNormal;

      $jadwal[$index]['day'] = $hariMutasi;
      $jadwal[$index]['time_slot_id'] = $slotPool[array_rand($slotPool)];
    }
    return $jadwal;
  }

  private function diagnosaKonflik($jadwal, $availabilities, $guruPiket)
  {
    $pesan = [];
    $checkGuru = [];
    $checkKelas = [];

    $gurus = Guru::pluck('nama_guru', 'id')->toArray();
    $kelas = Kelas::pluck('nama_kelas', 'id')->toArray();
    $slots = SlotJam::pluck('slot_number', 'id')->toArray();
    $mapelTypes = Mapel::pluck('type', 'id')->toArray();
    $mapelNames = Mapel::pluck('nama_mapel', 'id')->toArray();
    $activeSlots = SlotJam::where('is_istirahat', false)->orderBy('slot_number')->pluck('slot_number')->toArray();

    $kelasShiftLog = []; // Log untuk merekam kehadiran kelas (Pagi/Siang)

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
        $guruSebelumnya = $checkKelas["{$hari}_{$jamKe}_{$g['kelas_id']}"];
        $pesan[] = "<b>Kelas {$namaKelas}</b> bentrok! Diajar oleh <b>{$guruSebelumnya}</b> dan <b>{$namaGuru}</b> bersamaan pada <b>{$hari} Jam ke-{$jamKe}</b>.";
      }
      $checkKelas["{$hari}_{$jamKe}_{$g['kelas_id']}"] = $namaGuru;

      if (isset($guruPiket[$g['guru_id']]) && in_array($hari, $guruPiket[$g['guru_id']])) {
        $pesan[] = "ATURAN PIKET: <b>{$namaGuru}</b> jadwalnya terpaksa masuk pada hari <b>{$hari}</b> padahal sedang bertugas PIKET.";
      }

      // --- Diagnosa Shift & Zona Jumat ---
      if ($hari === 'Jumat') {
        if (($jamKe > 6 && $jamKe <= 10) || $jamKe > 16) {
          $pesan[] = "ATURAN JUMAT: {$namaMapel} ({$namaKelas}) masuk di zona terlarang (Slot 7-10 atau 17 Jumat).";
        }
        if ($tipeMapel === 'teori' && $jamKe > 6) {
          $pesan[] = "ATURAN SHIFT JUMAT: Mapel Teori <b>{$namaMapel} ({$namaKelas})</b> melewati batas 6 JP (Terplot di Jam ke-{$jamKe}).";
        }
        if ($tipeMapel === 'praktikum' && $jamKe <= 10) {
          $pesan[] = "ATURAN SHIFT JUMAT: Mapel Praktikum <b>{$namaMapel} ({$namaKelas})</b> terpaksa ditaruh di Jam Pagi (Jam ke-{$jamKe}).";
        }
      } else {
        if ($tipeMapel === 'teori' && $jamKe > 10) {
          $pesan[] = "ATURAN SHIFT: Mapel Teori <b>{$namaMapel} ({$namaKelas})</b> terpaksa ditaruh di Jam Siang (Jam ke-{$jamKe}).";
        }
        if ($tipeMapel === 'praktikum' && $jamKe <= 10) {
          $pesan[] = "ATURAN SHIFT: Mapel Praktikum <b>{$namaMapel} ({$namaKelas})</b> terpaksa ditaruh di Jam Pagi (Jam ke-{$jamKe}).";
        }
      }

      if (isset($availabilities[$g['guru_id']])) {
        foreach ($availabilities[$g['guru_id']] as $av) {
          if ($av['day'] === $hari && $av['time_slot_id'] === $g['time_slot_id']) {
            $pesan[] = "Guru <b>{$namaGuru}</b> diplot pada waktu berhalangannya di <b>{$hari} Jam ke-{$jamKe}</b>.";
          }
        }
      }

      // Merekam Log Shift Harian
      $shiftTipe = ($jamKe <= 10) ? 'pagi' : 'siang';
      $kelasShiftLog[$g['kelas_id']][$hari][$shiftTipe] = true;
    }

    // Diagnosa Shift Eksklusif
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
