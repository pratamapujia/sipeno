<?php

namespace App\Services;

use App\Models\BatchJadwal;
use App\Models\Guru;
use App\Models\GuruFree;
use App\Models\GuruMapel;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\SlotJam;
use Illuminate\Support\Facades\DB;

class GeneticScheduleService
{
  private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
  private $popSize = 40; // Jumlah alternatif jadwal dalam 1 populasi
  private $maxGenerations = 100; // Batas perulangan evolusi

  public function generate($academicYearId)
  {
    // Mengambil jam efektif mengajar
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
      for ($i = 0; $i < $gm['mapel']['beban_jam'] ?? 2; $i++) { // default 2 jam jika tidak diset
        $targetMengajar[] = [
          'guru_id'   => $gm['guru_id'],
          'mapel_id'   => $gm['mapel_id'],
          'kelas_id' => $gm['kelas_id'],
        ];
      }
    }

    // Ambil data guru yang berhalangan (blacklist jam)
    $availabilities = GuruFree::where('is_available', false)
      ->get()
      ->groupBy('guru_id')
      ->toArray();

    // Proses algoritma genetika
    $populasi = [];
    for ($i = 0; $i < $this->popSize; $i++) {
      $populasi[] = $this->generateKromosomAcak($targetMengajar, $slots);
    }

    //Proses Evolusi (Looping Generasi)
    $generasi = 0;
    $solusiTerbaik = null;

    // Looping Generasi
    while ($generasi < $this->maxGenerations) {
      // Hitung fitness untuk setiap individu di populasi
      $populasi = $this->hitungPopulasiFitness($populasi, $availabilities);

      // Urutkan dari yang fitness terbaik (paling mendekati 0)
      usort($populasi, function ($a, $b) {
        return $b['fitness'] <=> $a['fitness']; // Nilai minus terkecil berada di atas
      });

      $solusiTerbaik = $populasi[0];

      // Jika ditemukan jadwal sempurna (tidak ada bentrok sama sekali)
      if ($solusiTerbaik['fitness'] == 0) {
        break;
      }

      // Seleksi & Crossover untuk membentuk generasi baru
      $populasiBaru = [$solusiTerbaik]; // Elitismo: Amankan yang terbaik ke generasi selanjutnya
      while (count($populasiBaru) < $this->popSize) {
        $p1 = $populasi[rand(0, 9)]['jadwal']; // Ambil acak dari top 5
        $p2 = $populasi[rand(0, 9)]['jadwal'];

        $anak = $this->crossover($p1, $p2);
        $anak = $this->mutasi($anak, $slots);

        $populasiBaru[] = ['jadwal' => $anak, 'fitness' => 0];
      }

      $populasi = $populasiBaru;
      $generasi++;
    }

    // 4. Simpan Jadwal Terbaik ke Database sebagai DRAFT
    if ($solusiTerbaik['fitness'] < 0) {
      $detailKonflik = $this->diagnosaKonflik($solusiTerbaik['jadwal'], $availabilities);

      // Melempar error dalam bentuk JSON agar mudah ditangkap oleh Controller
      throw new \Exception('KONFLIK_JSON:' . json_encode($detailKonflik));
    }
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

      // Bulk insert ke database
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
    return ['jadwal' => $jadwal, 'fitness' => 0];
  }

  private function hitungPopulasiFitness($populasi, $availabilities)
  {
    foreach ($populasi as &$individu) {
      $penalty = 0;
      $checkGuru = [];
      $checkKelas = [];

      foreach ($individu['jadwal'] as $g) {
        $keyGuru = "{$g['day']}_{$g['time_slot_id']}_{$g['guru_id']}";
        $keyKelas = "{$g['day']}_{$g['time_slot_id']}_{$g['kelas_id']}";

        // Aturan 1: Guru tidak boleh bentrok di jam yang sama
        if (isset($checkGuru[$keyGuru])) {
          $penalty -= 10;
        }
        $checkGuru[$keyGuru] = true;

        // Aturan 2: Kelas tidak boleh bentrok menerima 2 mapel di jam yang sama
        if (isset($checkKelas[$keyKelas])) {
          $penalty -= 10;
        }
        $checkKelas[$keyKelas] = true;

        // Aturan 3: Cek ketersediaan guru (apakah guru berhalangan mengajar di hari/jam itu)
        if (isset($availabilities[$g['guru_id']])) {
          foreach ($availabilities[$g['guru_id']] as $av) {
            if ($av['day'] === $g['day'] && $av['time_slot_id'] === $g['time_slot_id']) {
              $penalty -= 5;
            }
          }
        }
      }
      $individu['fitness'] = $penalty; // Nilai 0 berarti sempurna (tidak ada bentrok)
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
    // Kemungkinan mutasi sebesar 20%
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

    // Ambil nama-nama master untuk ditampilkan ke layar
    $gurus = Guru::pluck('nama_guru', 'id')->toArray();
    $kelas = Kelas::pluck('kelas', 'id')->toArray();
    $slots = SlotJam::pluck('slot_number', 'id')->toArray();

    foreach ($jadwal as $g) {
      $namaGuru = $gurus[$g['guru_id']] ?? 'Guru Tidak Dikenal';
      $namaKelas = $kelas[$g['kelas_id']] ?? 'Kelas Tidak Dikenal';
      $jamKe = $slots[$g['time_slot_id']] ?? '-';
      $hari = $g['day'];

      // 1. Cek Bentrok Guru (Guru mengajar di 2 kelas bersamaan)
      $keyGuru = "{$hari}_{$jamKe}_{$g['guru_id']}";
      if (isset($checkGuru[$keyGuru])) {
        $kelasSebelumnya = $checkGuru[$keyGuru];
        $pesan[] = "Guru <b>{$namaGuru}</b> bentrok mengajar di <b>Kelas {$kelasSebelumnya}</b> dan <b>Kelas {$namaKelas}</b> pada <b>{$hari} Jam ke-{$jamKe}</b>.";
      }
      $checkGuru[$keyGuru] = $namaKelas;

      // 2. Cek Bentrok Kelas (Satu kelas diajar 2 guru berbeda bersamaan)
      $keyKelas = "{$hari}_{$jamKe}_{$g['kelas_id']}";
      if (isset($checkKelas[$keyKelas])) {
        $guruSebelumnya = $checkKelas[$keyKelas];
        $pesan[] = "<b>Kelas {$namaKelas}</b> bentrok diajar oleh <b>{$guruSebelumnya}</b> dan <b>{$namaGuru}</b> pada <b>{$hari} Jam ke-{$jamKe}</b>.";
      }
      $checkKelas[$keyKelas] = $namaGuru;

      // 3. Cek Jam Berhalangan (Guru dijadwalkan di waktu dia tidak bisa)
      if (isset($availabilities[$g['guru_id']])) {
        foreach ($availabilities[$g['guru_id']] as $av) {
          if ($av['day'] === $hari && $av['time_slot_id'] === $g['time_slot_id']) {
            $pesan[] = "Guru <b>{$namaGuru}</b> diplot pada waktu berhalangannya di <b>{$hari} Jam ke-{$jamKe}</b>.";
          }
        }
      }
    }

    return array_unique($pesan); // Hapus pesan duplikat
  }
}
