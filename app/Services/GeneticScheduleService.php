<?php

namespace App\Services;

use App\Models\BatchJadwal;
use App\Models\GuruFree;
use App\Models\GuruMapel;
use App\Models\Jadwal;
use App\Models\SlotJam;
use Illuminate\Support\Facades\DB;

class GeneticScheduleService
{
  private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
  private $popSize = 30; // Jumlah alternatif jadwal dalam 1 populasi
  private $maxGenerations = 200; // Batas perulangan evolusi

  public function generate($idTahunAjaran)
  {
    // 1. Ambil data master ke memori (Array) untuk kecepatan proses
    $slots = SlotJam::where('is_istirahat', false)->pluck('id')->toArray();

    // Ambil aturan beban mengajar (misal: Guru A, Mapel X, Kelas Y, harus 3 jam)
    $guruMapel = GuruMapel::where('academic_year_id', $idTahunAjaran)->get()->toArray();

    // Transformasikan beban mengajar menjadi "Gen tunggal" per jam pelajaran
    $targetMengajar = [];
    foreach ($guruMapel as $gm) {
      for ($i = 0; $i < $gm['subject']['beban_jam'] ?? 2; $i++) { // default 2 jam jika tidak diset
        $targetMengajar[] = [
          'teachers_id'   => $gm['teachers_id'],
          'subjects_id'   => $gm['subjects_id'],
          'classes_id' => $gm['classes_id'],
        ];
      }
    }

    // Ambil data ketidakhadiran guru (blacklist jam)
    $availabilities = GuruFree::where('is_available', false)
      ->get()
      ->groupBy('teacher_id')
      ->toArray();

    // 2. Inisialisasi Populasi Awal (Generate acak sebanyak $popSize)
    $populasi = [];
    for ($i = 0; $i < $this->popSize; $i++) {
      $populasi[] = $this->generateKromosomAcak($targetMengajar, $slots);
    }

    // 3. Proses Evolusi (Looping Generasi)
    $generasi = 0;
    $solusiTerbaik = null;

    while ($generasi < $this->maxGenerations) {
      // Hitung fitness untuk setiap individu di populasi
      $populasi = $this->hitungPopulasiFitness($populasi, $availabilities);

      // Urutkan dari yang fitness terbaik (paling mendekati 0)
      usort($populasi, function ($a, $b) {
        return $b['fitness'] <=> $a['fitness']; // Nilai minus terkecil berada di atas
      });

      $solusiTerbaik = $populasi[0];

      // Jika ditemukan jadwal sempurna (tidak ada bentrok sama sekali)
      if ($solusiTerbaik['fitness'] === 0) {
        break;
      }

      // Seleksi & Crossover untuk membentuk generasi baru
      $populasiBaru = [$solusiTerbaik]; // Elitismo: Amankan yang terbaik ke generasi selanjutnya
      while (count($populasiBaru) < $this->popSize) {
        $p1 = $populasi[rand(0, 5)]; // Ambil acak dari top 5
        $p2 = $populasi[rand(0, 5)];

        $anak = $this->crossover($p1['jadwal'], $p2['jadwal']);
        $anak = $this->mutasi($anak, $slots);

        $populasiBaru[] = ['jadwal' => $anak, 'fitness' => 0];
      }

      $populasi = $populasiBaru;
      $generasi++;
    }

    // 4. Simpan Jadwal Terbaik ke Database sebagai DRAFT
    return DB::transaction(function () use ($solusiTerbaik, $idTahunAjaran, $generasi) {
      $batch = BatchJadwal::create([
        'nama' => 'Simulasi Otomatis - Generasi ' . $generasi,
        'status' => 'draft',
        'final_fitness_score' => $solusiTerbaik['fitness']
      ]);

      $dataInsert = [];
      foreach ($solusiTerbaik['jadwal'] as $item) {
        $dataInsert[] = [
          'schedule_batch_id' => $batch->id,
          'academic_year_id'  => $idTahunAjaran,
          'day'               => $item['day'],
          'time_slot_id'      => $item['time_slot_id'],
          'classes_id'      => $item['classes_id'],
          'teacher_id'        => $item['teacher_id'],
          'subject_id'        => $item['subject_id'],
          'created_at'        => now(),
          'updated_at'        => now()
        ];
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
        $keyGuru = "{$g['day']}_{$g['time_slot_id']}_{$g['teacher_id']}";
        $keyKelas = "{$g['day']}_{$g['time_slot_id']}_{$g['classes_id']}";

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
        if (isset($availabilities[$g['teacher_id']])) {
          foreach ($availabilities[$g['teacher_id']] as $av) {
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
    $cut = rand(1, count($j1) - 2);
    return array_merge(array_slice($j1, 0, $cut), array_slice($j2, $cut));
  }

  private function mutasi($jadwal, $slots)
  {
    // Kemungkinan mutasi sebesar 10%
    if (rand(1, 100) <= 10) {
      $index = array_rand($jadwal);
      $jadwal[$index]['day'] = $this->days[array_rand($this->days)];
      $jadwal[$index]['time_slot_id'] = $slots[array_rand($slots)];
    }
    return $jadwal;
  }
}
