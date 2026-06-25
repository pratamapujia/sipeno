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
use Illuminate\Support\Facades\DB;

class GeneticScheduleService
{
  private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
  private $popSize = 40;
  private $maxGenerations = 2500;

  public function generate($academicYearId)
  {
    $slots = SlotJam::where('is_istirahat', false)->pluck('id')->toArray();
    if (empty($slots)) {
      throw new \Exception('Data slot jam tidak ditemukan atau kosong.');
    }

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

    $populasi = [];
    for ($i = 0; $i < $this->popSize; $i++) {
      $populasi[] = $this->generateKromosomAcak($targetMengajar, $slots);
    }

    $generasi = 0;
    $solusiTerbaik = null;

    while ($generasi < $this->maxGenerations) {
      $populasi = $this->hitungPopulasiFitness($populasi, $availabilities);

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
        $anak = $this->mutasi($anak, $slots);

        $populasiBaru[] = ['jadwal' => $anak, 'fitness' => -9999];
      }

      $populasi = $populasiBaru;
      $generasi++;
    }

    $detailKonflik = [];
    if ($solusiTerbaik['fitness'] < 0) {
      $detailKonflik = $this->diagnosaKonflik($solusiTerbaik['jadwal'], $availabilities);
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

  private function generateKromosomAcak($targetMengajar, $slots)
  {
    $jadwal = [];
    foreach ($targetMengajar as $target) {
      $jadwal[] = array_merge($target, [
        'day'          => $this->days[array_rand($this->days)],
        'time_slot_id' => $slots[array_rand($slots)]
      ]);
    }
    return ['jadwal' => $jadwal, 'fitness' => -9999];
  }

  private function hitungPopulasiFitness($populasi, $availabilities)
  {
    $mapelTypes = Mapel::pluck('type', 'id')->toArray();
    $slotDetails = SlotJam::select('id', 'slot_number', 'is_istirahat')->get()->keyBy('id')->toArray();

    // AMBIL DAFTAR JAM EFEKTIF (Tanpa Istirahat) untuk referensi Aturan Blok
    $activeSlots = SlotJam::where('is_istirahat', false)->orderBy('slot_number')->pluck('slot_number')->toArray();

    foreach ($populasi as &$individu) {
      $penalty = 0;
      $checkGuru = [];
      $checkKelas = [];
      $kelasJadwalHari = [];

      // --- LOOP 1: Validasi Bentrok Dasar, Istirahat, Jumat, dan Shift ---
      foreach ($individu['jadwal'] as $g) {
        $keyGuru = "{$g['day']}_{$g['time_slot_id']}_{$g['guru_id']}";
        $keyKelas = "{$g['day']}_{$g['time_slot_id']}_{$g['kelas_id']}";

        if (isset($checkGuru[$keyGuru])) {
          $penalty -= 500;
        }
        $checkGuru[$keyGuru] = true;

        if (isset($checkKelas[$keyKelas])) {
          $penalty -= 500;
        }
        $checkKelas[$keyKelas] = true;

        $tipeMapel = $mapelTypes[$g['mapel_id']] ?? 'teori';
        $nomorJam = $slotDetails[$g['time_slot_id']]['slot_number'] ?? 1;
        $isIstirahat = $slotDetails[$g['time_slot_id']]['is_istirahat'] ?? false;

        // Aturan Istirahat
        if ($isIstirahat) {
          $penalty -= 500;
        }

        // Aturan Khusus Jumat (Pagi max jam 7, Siang 4 JP mulai jam 13 -> max jam 16)
        if ($g['day'] === 'Jumat') {
          if ($nomorJam > 7 && $nomorJam <= 12) {
            $penalty -= 500;
          }
          if ($nomorJam > 16) {
            $penalty -= 500;
          }
        }

        // PERBAIKAN: Penalti Aturan Shift dinaikkan menjadi -500 agar mutlak ditaati
        if ($tipeMapel === 'teori' && $nomorJam > 12) {
          $penalty -= 500;
        }
        if ($tipeMapel === 'praktikum' && $nomorJam <= 12) {
          $penalty -= 500;
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

      // --- LOOP 2: Karantina Hari Praktikum ---
      foreach ($kelasJadwalHari as $kelasId => $hariData) {
        foreach ($hariData as $hari => $daftarJadwal) {
          $adaPraktikSiang = false;
          foreach ($daftarJadwal as $jwd) {
            if ($jwd['type'] === 'praktikum' && $jwd['slot_number'] > 12) {
              $adaPraktikSiang = true;
              break;
            }
          }

          if ($adaPraktikSiang) {
            foreach ($daftarJadwal as $jwd) {
              $batasPagi = ($hari === 'Jumat') ? 7 : 12;
              if ($jwd['slot_number'] <= $batasPagi) {
                $penalty -= 100;
              }
            }
          }
        }
      }

      // --- LOOP 3: ATURAN BLOK (DENGAN LOGIKA MELEWATI JAM ISTIRAHAT) ---
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
          if ($jwd['day'] !== $hariPertama) {
            $penalty -= 200;
          }
          $slotNumbers[] = $jwd['slot_number'];
        }

        sort($slotNumbers);
        for ($i = 0; $i < count($slotNumbers) - 1; $i++) {
          // CARA CERDAS: Cek posisi indeks di array jam aktif, bukan dikurangi angkanya langsung
          $idx1 = array_search($slotNumbers[$i], $activeSlots);
          $idx2 = array_search($slotNumbers[$i + 1], $activeSlots);

          if ($idx2 !== false && $idx1 !== false) {
            if (($idx2 - $idx1) !== 1) {
              $penalty -= 200; // Jika tidak bersebelahan di jam aktif, penalti!
            }
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

  private function mutasi($jadwal, $slots)
  {
    if (rand(1, 100) <= 20) {
      $index = array_rand($jadwal);
      $jadwal[$index]['day'] = $this->days[array_rand($this->days)];
      $jadwal[$index]['time_slot_id'] = $slots[array_rand($slots)];
    }
    return $jadwal;
  }

  private function diagnosaKonflik($jadwal, $availabilities)
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

      // Update Diagnosa Shift
      if ($tipeMapel === 'teori' && $jamKe > 12) {
        $pesan[] = "ATURAN SHIFT: Mapel Teori <b>{$namaMapel} ({$namaKelas})</b> terpaksa ditaruh di Jam Siang (Jam ke-{$jamKe}).";
      }
      if ($tipeMapel === 'praktikum' && $jamKe <= 12) {
        $pesan[] = "ATURAN SHIFT: Mapel Praktikum <b>{$namaMapel} ({$namaKelas})</b> terpaksa ditaruh di Jam Pagi (Jam ke-{$jamKe}).";
      }

      // Update Diagnosa Jumat
      if ($hari === 'Jumat') {
        if ($jamKe > 7 && $jamKe <= 12) {
          $pesan[] = "ATURAN JUMAT: <b>{$namaMapel} ({$namaKelas})</b> melewati batas jam pagi (Terplot di Jam ke-{$jamKe}).";
        }
        if ($jamKe > 16) {
          $pesan[] = "ATURAN JUMAT: <b>{$namaMapel} ({$namaKelas})</b> melewati batas jam siang (Terplot di Jam ke-{$jamKe}).";
        }
      }

      if (isset($availabilities[$g['guru_id']])) {
        foreach ($availabilities[$g['guru_id']] as $av) {
          if ($av['day'] === $hari && $av['time_slot_id'] === $g['time_slot_id']) {
            $pesan[] = "Guru <b>{$namaGuru}</b> diplot pada waktu berhalangannya di <b>{$hari} Jam ke-{$jamKe}</b>.";
          }
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
