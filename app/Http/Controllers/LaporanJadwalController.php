<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use Illuminate\Http\Request;

class LaporanJadwalController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil info jadwal yang sedang aktif
        $activeBatch = BatchJadwal::where('status', 'active')->first();
        
        // 2. Ambil data master untuk pilihan dropdown filter
        $gurus = Guru::orderBy('nama_guru', 'asc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        // 3. Tangkap parameter filter dari URL
        $tipe = $request->get('tipe', 'guru'); // default filter berdasarkan guru
        $selected_id = $request->get('id');

        $jadwalPerHari = [];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $selectedEntity = null;
        $totalJam = 0;

        // 4. Jika ada jadwal aktif dan filter dipilih, tarik data jadwal terkait
        if ($activeBatch && $selected_id) {
            $query = Jadwal::with(['mapel', 'kelas', 'guru', 'slotJam'])
                ->where('schedule_batch_id', $activeBatch->id);

            if ($tipe == 'guru') {
                $selectedEntity = Guru::find($selected_id);
                $query->where('guru_id', $selected_id);
            } else {
                $selectedEntity = Kelas::find($selected_id);
                $query->where('kelas_id', $selected_id);
            }

            $semuaJadwal = $query->get();
            $totalJam = $semuaJadwal->count();

            // Kelompokkan jadwal ke dalam struktur hari
            foreach ($days as $day) {
                $jadwalPerHari[$day] = $semuaJadwal->where('day', $day)->sortBy(function ($item) {
                    return $item->slotJam->slot_number;
                });
            }
        }

        return view('kepsek.laporan', compact(
            'activeBatch', 'gurus', 'kelas', 'tipe', 'selected_id', 
            'jadwalPerHari', 'days', 'selectedEntity', 'totalJam'
        ));
    }
}
