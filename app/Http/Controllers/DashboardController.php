<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SlotJam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $stats = [
            'total_guru' => Guru::count(),
            'total_mapel' => Mapel::count(),
            'total_kelas' => Kelas::count(),
            'total_slot' => SlotJam::count(),
        ];

        // Ambil hasil simulasi jadwal terbaru
        $latestBatch = BatchJadwal::latest()->first();

        return view('admin.index', compact('stats', 'latestBatch'));
    }

    public function guruDashboard()
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return view('guru.index', [
                'guru' => null,
                'total_jam' => 0,
                'jadwal_sekarang' => collect(),
                'hari_ini' => date('l'),
            ]);
        }

        // Cari batch jadwal yang statusnya sudah active
        $activeBatch = BatchJadwal::where('status', 'active')->first();

        $jadwal_sekarang = collect();
        $total_jam = 0;
        $hari_ini = 'Senin';

        if ($activeBatch) {
            $daftarHari = [
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu',
                'Sunday'    => 'Minggu'
            ];
            $hari_ini = $daftarHari[date('l')] ?? 'Senin';

            // Hitung total beban jam guru tersebut di jadwal aktif
            $total_jam = Jadwal::where('schedule_batch_id', $activeBatch->id)->where('guru_id', $guru->id)->count();

            // Ambil jadwal khusus hari ini untuk guru tersebut
            $jadwal_sekarang = Jadwal::with(['mapel', 'kelas', 'slotJam'])
                ->where('schedule_batch_id', $activeBatch->id)
                ->where('guru_id', $guru->id)
                ->where('day', $hari_ini)->get()->sortBy(function ($query) {
                    return $query->slotJam->slot_number;
                });
        }
        return view('guru.index', compact('guru', 'total_jam', 'jadwal_sekarang', 'hari_ini'));
    }

    public function kepsekDashboard()
    {
        $stats = [
            'total_guru' => Guru::count(),
            'total_kelas' => Kelas::count(),
            'batch_aktif' => BatchJadwal::where('status', 'active')->count(),
            'batch_draft' => BatchJadwal::where('status', 'draft')->count(),
        ];

        // Ambil info batch jadwal yang sedang aktif saat ini
        $activeBatch = BatchJadwal::where('status', 'active')->orderBy('updated_at', 'desc')->first();

        return view('kepsek.index', compact('stats', 'activeBatch'));
    }
}
