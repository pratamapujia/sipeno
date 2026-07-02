<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\Guru;
use App\Models\GuruPiket;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class GuruPiketController extends Controller
{
    public function index()
    {
        $activeYear = TahunAjaran::where('is_active', true)->first();
        $guru = Guru::orderBy('nama_guru', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $piketData = [];
        if ($activeYear) {
            $piketData = GuruPiket::with('guru')
                ->where('tahun_ajaran_id', $activeYear->id)
                ->get()
                ->groupBy('hari');
        }

        return view('admin.piket.index', compact('activeYear', 'guru', 'hari', 'piketData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required',
            'hari' => 'required'
        ], [
            'guru_id.required' => 'Guru harus dipilih.',
            'hari.required' => 'Hari harus dipilih.',
        ]);

        $activeYear = TahunAjaran::where('is_active', true)->firstOrFail();

        // 1. Cek apakah guru sudah terdaftar piket di hari tersebut
        $isPiket = GuruPiket::where('tahun_ajaran_id', $activeYear->id)
            ->where('guru_id', $request->guru_id)
            ->where('hari', $request->hari)
            ->exists();

        if ($isPiket) {
            return back()->with('error', 'Guru tersebut sudah terdaftar sebagai piket pada hari ' . $request->hari . '.');
        }

        // 2. PROTEKSI TAMBAHAN: Cek apakah guru pengganti punya jadwal mengajar (Jika jadwal sudah digenerate & Aktif)
        $activeBatch = BatchJadwal::where('status', 'active')->first();
        if ($activeBatch) {
            $isTeaching = Jadwal::where('schedule_batch_id', $activeBatch->id)
                ->where('guru_id', $request->guru_id)
                ->where('day', $request->hari)
                ->exists();

            if ($isTeaching) {
                return back()->with('error', 'Gagal! Guru tersebut tidak bisa dijadwalkan piket karena memiliki jadwal mengajar di kelas pada hari ' . $request->hari . '.');
            }
        }

        // Simpan Data
        GuruPiket::create([
            'tahun_ajaran_id' => $activeYear->id,
            'guru_id' => $request->guru_id,
            'hari' => $request->hari,
        ]);

        return back()->with('success', 'Jadwal Guru Piket berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'guru_id' => 'required',
            'hari' => 'required'
        ], [
            'guru_id.required' => 'Guru harus dipilih.',
            'hari.required' => 'Hari harus dipilih.',
        ]);

        $piket = GuruPiket::findOrFail($id);
        $activeYear = TahunAjaran::where('is_active', true)->firstOrFail();

        // 1. Cek apakah guru sudah terdaftar piket di hari tersebut (Abaikan ID miliknya sendiri)
        $isPiket = GuruPiket::where('tahun_ajaran_id', $activeYear->id)
            ->where('guru_id', $request->guru_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $id)
            ->exists();

        if ($isPiket) {
            return back()->with('error', 'Guru tersebut sudah terdaftar sebagai piket pada hari ' . $request->hari . '.');
        }

        // 2. PROTEKSI TAMBAHAN: Cek apakah guru pengganti punya jadwal mengajar (Jika jadwal sudah digenerate & Aktif)
        $activeBatch = BatchJadwal::where('status', 'active')->first();
        if ($activeBatch) {
            $isTeaching = Jadwal::where('schedule_batch_id', $activeBatch->id)
                ->where('guru_id', $request->guru_id)
                ->where('day', $request->hari)
                ->exists();

            if ($isTeaching) {
                return back()->with('error', 'Gagal Edit! Guru yang dipilih memiliki jadwal mengajar di kelas pada hari ' . $request->hari . '. Harap sesuaikan kembali.');
            }
        }

        // Update Data
        $piket->update([
            'guru_id' => $request->guru_id,
            'hari' => $request->hari,
        ]);

        return back()->with('success', 'Jadwal Guru Piket berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $piket = GuruPiket::findOrFail($id);
        $piket->delete();

        return back()->with('success', 'Data Piket berhasil dihapus.');
    }

    public function print()
    {
        $activeYear = TahunAjaran::where('is_active', true)->firstOrFail();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $piketData = GuruPiket::with('guru')
            ->where('tahun_ajaran_id', $activeYear->id)
            ->get()
            ->groupBy('hari');

        return view('admin.piket.print', compact('activeYear', 'hari', 'piketData'));
    }
}
