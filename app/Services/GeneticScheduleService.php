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
  private $popSize = 40; // Jumlah alternatif jadwal dalam 1 populasi
  private $maxGenerations = 100; // Batas perulangan evolusi

  public function generate($academicYearId)
  {
    // Mengambil semua slot (termasuk istirahat untuk pengecekan validasi fitness nanti)
    $slots = SlotJam::where('is_istirahat', false)->pluck('id')->toArray();
    if (empty($slots)) {
      throw new \Exception('Data slot jam tidak ditemukan atau kosong.');
    }

    // Mengambil target/plot mengajar
    $guruMapel = GuruMapel::with('mapel')->where('tahun_ajaran_id', $academicYearId)->get()->toArray();
    if (empty($guruMapel)) {
      throw new \Exception('Data guru mapel tidak ditemukan atau kosong.');
    }

    // Memecah beban mengajar menjadi "Gen tunggal" per jam pelajaran
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

    // Ambil data guru yang berhalangan (blacklist jam)
    $availabilities = GuruFree::where('is_available', false)
      ->get()
      ->groupBy('guru_id')
      ->toArray();

    // Proses algoritma genetika (Inisialisasi Populasi)
    $populasi = [];
    for ($i = 0; $i < $this->popSize; $i++) {
      $populasi[] = $this->generateKromosomAcak($targetMengajar, $slots);
    }

    $generasi = 0;
    $solusiTerbaik = null;

    // Looping Generasi / Evolusi
    while ($generasi < $this->maxGenerations) {
      // Hitung fitness untuk setiap individu di populasi
      $populasi = $this->hitungPopulasiFitness($populasi, $availabilities);

      // Urutkan dari yang fitness terbaik (paling mendekati 0 / minus terkecil di atas)
      usort($populasi, function ($a, $b) {
        return $b['fitness'] <=> $a['fitness'];
      });

      $solusiTerbaik = $populasi[0];

      // Jika ditemukan jadwal sempurna
      if ($solusiTerbaik['fitness'] == 0) {
        break;
      }

      // Seleksi & Crossover untuk membentuk generasi baru
      $populasiBaru = [$solusiTerbaik]; // Elitismo
      while (count($populasiBaru) < $this->popSize) {
        $p1 = $populasi[rand(0, 9)]['jadwal'];
        $p2 = $populasi[rand(0, 9)]['jadwal'];

        $anak = $this->crossover($p1, $p2);
        $anak = $this->mutasi($anak, $slots);

        $populasiBaru[] = ['jadwal' => $anak, 'fitness' => -9999]; // reset fitness awal
      }

      $populasi = $populasiBaru;
      $generasi++;
    }

    // Jika jadwal masih mengandung bentrok setelah evolusi selesai, lepar rincian diagnosa
    if ($solusiTerbaik['fitness'] < 0) {
      $detailKonflik = $this->diagnosaKonflik($solusiTerbaik['jadwal'], $availabilities);
      throw new \Exception('KONFLIK_JSON:' . json_encode($detailKonflik));
    }

    // Simpan Jadwal Terbaik ke Database
    return DB::transaction(function () use ($solusiTerbaik, $academicYearId, $generasi) {
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
    // Ambil data nomor urut sekaligus status istirahat dari database
    $slotDetails = SlotJam::select('id', 'slot_number', 'is_istirahat')->get()->keyBy('id')->toArray();

    foreach ($populasi as &$individu) {
      $penalty = 0;
      $checkGuru = [];
      $checkKelas = [];
      $kelasJadwalHari = [];

      // --- LOOP 1: Validasi Bentrok Dasar, Shift, Istirahat, & Hari Jumat ---
      foreach ($individu['jadwal'] as $g) {
        $keyGuru = "{$g['day']}_{$g['time_slot_id']}_{$g['guru_id']}";
        $keyKelas = "{$g['day']}_{$g['time_slot_id']}_{$g['kelas_id']}";

        // 1. Validasi Bentrok Guru (Penalti Ekstrem -500 agar terhindar dari Duplicate Entry MySQL)
        if (isset($checkGuru[$keyGuru])) {
          $penalty -= 500;
        }
        $checkGuru[$keyGuru] = true;

        // 2. Validasi Bentrok Kelas
        if (isset($checkKelas[$keyKelas])) {
          $penalty -= 500;
        }
        $checkKelas[$keyKelas] = true;

        $tipeMapel = $mapelTypes[$g['mapel_id']] ?? 'teori';
        $nomorJam = $slotDetails[$g['time_slot_id']]['slot_number'] ?? 1;
        $isIstirahat = $slotDetails[$g['time_slot_id']]['is_istirahat'] ?? false;

        // 3. Aturan Perlindungan Jam Istirahat
        if ($isIstirahat) {
          $penalty -= 500;
        }

        // 4. Aturan Khusus Hari Jumat (Maksimal 6 jam pagi, Maksimal 4 jam siang [slot ke-14 jika total 15])
        if ($g['day'] === 'Jumat') {
          if ($nomorJam > 6 && $nomorJam <= 10) {
            $penalty -= 500;
          }
          if ($nomorJam > 14) {
            $penalty -= 500;
          }
        }

        // 5. Aturan Pembagian Zona Shift (Pagi <= 10, Siang > 10)
        if ($tipeMapel === 'teori' && $nomorJam > 10) {
          $penalty -= 50;
        }
        if ($tipeMapel === 'praktikum' && $nomorJam <= 10) {
          $penalty -= 50;
        }

        // Catat riwayat harian kelas untuk pengecekan karantina di LOOP 2
        $kelasJadwalHari[$g['kelas_id']][$g['day']][] = [
          'slot_number' => $nomorJam,
          'type'        => $tipeMapel
        ];

        // 6. Validasi Waktu Berhalangan Guru
        if (isset($availabilities[$g['guru_id']])) {
          foreach ($availabilities[$g['guru_id']] as $av) {
            if ($av['day'] === $g['day'] && $av['time_slot_id'] === $g['time_slot_id']) {
              $penalty -= 15;
            }
          }
        }
      }

      // --- LOOP 2: Validasi Aturan Karantina (Jika Siang Praktik, Pagi Wajib Kosong) ---
      foreach ($kelasJadwalHari as $kelasId => $hariData) {
        foreach ($hariData as $hari => $daftarJadwal) {

          // Cek apakah ada jadwal praktik di siang hari pada hari ini
          $adaPraktikSiang = false;
          foreach ($daftarJadwal as $jwd) {
            if ($jwd['type'] === 'praktikum' && $jwd['slot_number'] > 10) {
              $adaPraktikSiang = true;
              break;
            }
          }

          // Jika ada praktik siang, pastikan rentang pagi hari tersebut steril/kosong
          if ($adaPraktikSiang) {
            foreach ($daftarJadwal as $jwd) {
              $batasPagi = ($hari === 'Jumat') ? 6 : 10;
              if ($jwd['slot_number'] <= $batasPagi) {
                $penalty -= 100; // Penalti berat per jam pelajaran yang melanggar karantina
              }
            }
          }
        }
      }

      // KUNCI PERBAIKAN: Masukkan skor penalti ke key 'fitness' agar terbaca oleh usort()
      $individu['fitness'] = $penalty;
    }

    return $populasi;
  }

  private function crossover($j1, $j2)
  {
    if (count($j1) <= 2) {
      return $j1;
    }
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
    $kelas = Kelas::pluck('nama_kelas', 'id')->toArray(); // disesuaikan dari 'kelas' menjadi 'name'
    $slots = SlotJam::pluck('slot_number', 'id')->toArray();

    foreach ($jadwal as $g) {
      $namaGuru = $gurus[$g['guru_id']] ?? 'Guru Tidak Dikenal';
      $namaKelas = $kelas[$g['kelas_id']] ?? 'Kelas Tidak Dikenal';
      $jamKe = $slots[$g['time_slot_id']] ?? '-';
      $hari = $g['day'];

      $keyGuru = "{$hari}_{$jamKe}_{$g['guru_id']}";
      if (isset($checkGuru[$keyGuru])) {
        $kelasSebelumnya = $checkGuru[$keyGuru];
        $pesan[] = "Guru <b>{$namaGuru}</b> bentrok mengajar di <b>Kelas {$kelasSebelumnya}</b> dan <b>Kelas {$namaKelas}</b> pada <b>{$hari} Jam ke-{$jamKe}</b>.";
      }
      $checkGuru[$keyGuru] = $namaKelas;

      $keyKelas = "{$hari}_{$jamKe}_{$g['kelas_id']}";
      if (isset($checkKelas[$keyKelas])) {
        $guruSebelumnya = $checkKelas[$keyKelas];
        $pesan[] = "<b>Kelas {$namaKelas}</b> bentrok diajar oleh <b>{$guruSebelumnya}</b> dan <b>{$namaGuru}</b> pada <b>{$hari} Jam ke-{$jamKe}</b>.";
      }
      $checkKelas[$keyKelas] = $namaGuru;

      if (isset($availabilities[$g['guru_id']])) {
        foreach ($availabilities[$g['guru_id']] as $av) {
          if ($av['day'] === $hari && $av['time_slot_id'] === $g['time_slot_id']) {
            $pesan[] = "Guru <b>{$namaGuru}</b> diplot pada waktu berhalangannya di <b>{$hari} Jam ke-{$jamKe}</b>.";
          }
        }
      }
    }
    return array_unique($pesan);
  }
}
