<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\BatchJadwal;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;

class JadwalGuruController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Cegah error jika admin belum menghubungkan profil guru
        if (!$guru) {
            return redirect()->route('guru.dashboard')->withErrors(['Profil guru Anda belum terhubung.']);
        }

        // Cari jadwal yang statusnya sudah 'active' (Telah dirilis Admin)
        $activeBatch = BatchJadwal::where('status', 'active')->first();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jadwalPerHari = [];

        if ($activeBatch) {
            // Ambil semua jadwal milik guru ini pada batch yang aktif
            $semuaJadwal = Jadwal::with(['mapel', 'kelas', 'slotJam'])
                ->where('schedule_batch_id', $activeBatch->id)
                ->where('guru_id', $guru->id)
                ->get();

            // Kelompokkan jadwal berdasarkan hari dan urutkan berdasarkan jam ke-
            foreach ($days as $day) {
                $jadwalPerHari[$day] = $semuaJadwal->where('day', $day)->sortBy(function ($item) {
                    return $item->slotJam->slot_number;
                });
            }
        }

        return view('guru.jadwal', compact('jadwalPerHari', 'activeBatch', 'days', 'guru'));
    }

    // Tambahkan method ini di bawah method index() yang sudah ada
    public function print()
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('guru.dashboard')->withErrors(['Profil guru Anda belum terhubung.']);
        }

        $activeBatch = BatchJadwal::where('status', 'active')->first();

        if (!$activeBatch) {
            return redirect()->back()->with('error', 'Belum ada jadwal aktif yang bisa dicetak.');
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jadwalPerHari = [];

        // Ambil semua jadwal milik guru ini pada batch yang aktif
        $semuaJadwal = Jadwal::with(['mapel', 'kelas', 'slotJam'])
            ->where('schedule_batch_id', $activeBatch->id)
            ->where('guru_id', $guru->id)
            ->get();

        foreach ($days as $day) {
            $jadwalPerHari[$day] = $semuaJadwal->where('day', $day)->sortBy(function ($item) {
                return $item->slotJam->slot_number;
            });
        }

        return view('guru.print', compact('guru', 'activeBatch', 'jadwalPerHari', 'days'));
    }
}
