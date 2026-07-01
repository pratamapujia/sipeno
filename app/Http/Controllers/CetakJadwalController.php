<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class CetakJadwalController extends Controller
{
    public function index()
    {
        $academicYear = TahunAjaran::where('is_active', '1')->first();
        $activeBatch = BatchJadwal::where('status', 'active')->first();
        $guru = Guru::orderBy('nama_guru', 'asc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.laporan.index', compact('activeBatch', 'guru', 'kelas', 'academicYear'));
    }

    public function printGuru(Request $request)
    {
        $academicYear = TahunAjaran::where('is_active', '1')->first();
        $activeBatch = BatchJadwal::where('status', 'active')->firstOrFail();
        $guru = Guru::findOrFail($request->guru_id);
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $semuaJadwal = Jadwal::with(['mapel', 'kelas', 'slotJam'])
            ->where('schedule_batch_id', $activeBatch->id)
            ->where('guru_id', $guru->id)
            ->get();

        $jadwalPerHari = [];
        foreach ($days as $day) {
            $jadwalPerHari[$day] = $semuaJadwal->where('day', $day)->sortBy(function ($item) {
                return $item->slotJam->slot_number;
            });
        }

        return view('admin.laporan.print-guru', compact('activeBatch', 'guru', 'days', 'jadwalPerHari', 'academicYear'));
    }

    // 3. Logika Cetak Per Kelas
    public function printKelas(Request $request)
    {
        $academicYear = TahunAjaran::where('is_active', '1')->first();
        $activeBatch = BatchJadwal::where('status', 'active')->firstOrFail();
        $kelas = Kelas::findOrFail($request->kelas_id);
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $semuaJadwal = Jadwal::with(['mapel', 'guru', 'slotJam'])
            ->where('schedule_batch_id', $activeBatch->id)
            ->where('kelas_id', $kelas->id)
            ->get();

        $jadwalPerHari = [];
        foreach ($days as $day) {
            $jadwalPerHari[$day] = $semuaJadwal->where('day', $day)->sortBy(function ($item) {
                return $item->slotJam->slot_number;
            });
        }

        return view('admin.laporan.print-kelas', compact('activeBatch', 'kelas', 'days', 'jadwalPerHari', 'academicYear'));
    }

    // 4. Logika Cetak Semua Jadwal (Master)
    public function printAll()
    {
        $academicYear = TahunAjaran::where('is_active', '1')->first();
        $activeBatch = BatchJadwal::where('status', 'active')->firstOrFail();
        $semuaKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $jadwalMentah = Jadwal::with(['mapel', 'guru', 'slotJam'])
            ->where('schedule_batch_id', $activeBatch->id)
            ->get();

        $jadwalMaster = [];

        // Kelompokkan jadwal berdasarkan Kelas, lalu berdasarkan Hari
        foreach ($semuaKelas as $k) {
            foreach ($days as $day) {
                $jadwalMaster[$k->nama_kelas][$day] = $jadwalMentah
                    ->where('kelas_id', $k->id)
                    ->where('day', $day)
                    ->sortBy(function ($item) {
                        return $item->slotJam->slot_number;
                    });
            }
        }

        return view('admin.laporan.print-all', compact('activeBatch', 'semuaKelas', 'days', 'jadwalMaster', 'academicYear'));
    }
}
