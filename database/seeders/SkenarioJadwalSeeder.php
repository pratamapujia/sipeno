<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SlotJam;
use App\Models\TahunAjaran;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkenarioJadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Tahun Ajaran Aktif
        $ta = TahunAjaran::create([
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true
        ]);

        // 2. Buat Slot Waktu yang Cukup (Jam 1 sampai Jam 5)
        $jam1 = SlotJam::create(['slot_number' => '1', 'start_time' => '08:00:00', 'end_time' => '09:00:00', 'is_istirahat' => false]);
        $jam2 = SlotJam::create(['slot_number' => '2', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'is_istirahat' => false]);
        $jam3 = SlotJam::create(['slot_number' => '3', 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'is_istirahat' => false]);
        $jam4 = SlotJam::create(['slot_number' => '4', 'start_time' => '11:00:00', 'end_time' => '12:00:00', 'is_istirahat' => false]);
        $jam5 = SlotJam::create(['slot_number' => '5', 'start_time' => '12:00:00', 'end_time' => '13:00:00', 'is_istirahat' => false]);

        // 3. Buat 3 Data Guru
        $g1 = Guru::create(['nama_guru' => 'Budi Santoso, S.Pd', 'nip' => '19870101', 'jenis_kelamin' => 'L', 'status' => 'Tetap']);
        $g2 = Guru::create(['nama_guru' => 'Siti Aminah, M.Pd', 'nip' => '19890202', 'jenis_kelamin' => 'P', 'status' => 'Tetap']);
        $g3 = Guru::create(['nama_guru' => 'Ahmad Fauzi, S.Kom', 'nip' => null, 'jenis_kelamin' => 'L', 'status' => 'Honorer']);

        // 4. Buat 3 Mata Pelajaran dengan Beban Jam yang Masuk Akal (Misal: 2 JP)
        $m1 = Mapel::create(['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'beban_jam' => 2]);
        $m2 = Mapel::create(['kode_mapel' => 'BI', 'nama_mapel' => 'Bahasa Inggris', 'beban_jam' => 2]);
        $m3 = Mapel::create(['kode_mapel' => 'WEB',  'nama_mapel' => 'Pemrograman Web', 'type' => 'praktikum', 'beban_jam' => 2]);

        // 5. Buat 2 Data Kelas
        $k1 = Kelas::create(['nama_kelas' => '10 RPL', 'tingkat' => '10']);
        $k2 = Kelas::create(['nama_kelas' => '11 RPL', 'tingkat' => '11']);

        // 6. Buat Plotting Target Mengajar yang Harmonis (Tidak Saling Berebutan Extreme)
        // Kelas X-RPL 1 diajar Matematika (Guru Budi) & Web (Guru Ahmad)
        GuruMapel::create(['tahun_ajaran_id' => $ta->id, 'guru_id' => $g1->id, 'mapel_id' => $m1->id, 'kelas_id' => $k1->id]);
        GuruMapel::create(['tahun_ajaran_id' => $ta->id, 'guru_id' => $g3->id, 'mapel_id' => $m3->id, 'kelas_id' => $k1->id]);

        // Kelas XI-RPL 1 diajar Inggris (Guru Siti) & Web (Guru Ahmad)
        GuruMapel::create(['tahun_ajaran_id' => $ta->id, 'guru_id' => $g2->id, 'mapel_id' => $m2->id, 'kelas_id' => $k2->id]);
        GuruMapel::create(['tahun_ajaran_id' => $ta->id, 'guru_id' => $g3->id, 'mapel_id' => $m3->id, 'kelas_id' => $k2->id]);
    }
}
