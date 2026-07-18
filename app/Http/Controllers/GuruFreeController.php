<?php

namespace App\Http\Controllers;

use App\Models\GuruFree;
use App\Models\GuruMapel;
use App\Models\SlotJam;
use Illuminate\Http\Request;

class GuruFreeController extends Controller
{
    // Tampilkan grid ketersediaan guru
    public function index(Request $request)
    {
        // 1. Tarik data plotting untuk kombinasi Guru dan Kelas
        $guruMapels = GuruMapel::with(['guru', 'kelas', 'mapel'])
            ->orderBy('guru_id')
            ->get()
            ->unique(function ($item) {
                return $item->guru_id . '_' . $item->kelas_id;
            })
            ->values();

        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $selectedTarget = $request->get('target');
        if (!$selectedTarget && $guruMapels->isNotEmpty()) {
            $selectedTarget = $guruMapels->first()->guru_id . '_' . $guruMapels->first()->kelas_id;
        }

        $selectedGuruId = null;
        $selectedKelasId = null;

        if ($selectedTarget) {
            $parts = explode('_', $selectedTarget);
            $selectedGuruId = $parts[0] ?? null;
            $selectedKelasId = $parts[1] ?? null;
        }

        $tersedia = [];
        $isInitialized = false;

        if ($selectedGuruId && $selectedKelasId) {
            $existingData = GuruFree::where('guru_id', $selectedGuruId)
                ->where('kelas_id', $selectedKelasId)
                ->get();

            // Cek apakah guru ini sudah pernah di-save jadwalnya (diinisialisasi)
            $isInitialized = $existingData->isNotEmpty();

            // Ambil data khusus yang BISA HADIR (is_available = true) untuk dicentang
            $tersedia = $existingData->where('is_available', true)
                ->mapWithKeys(function ($item) {
                    return ["{$item->day}_{$item->time_slot_id}" => true];
                })->toArray();
        }

        return view('admin.guruFree.index', compact('guruMapels', 'slot', 'hari', 'tersedia', 'selectedTarget', 'isInitialized'));
    }

    // Simpan Perubahan Ketersediaan Guru
    public function store(Request $request)
    {
        $request->validate([
            'target' => 'required',
        ], [
            'target.required' => 'Pilih kombinasi Guru dan Kelas terlebih dahulu.',
        ]);

        $parts = explode('_', $request->target);
        $guruId = $parts[0];
        $kelasId = $parts[1];

        // 1. Bersihkan semua riwayat jadwal untuk Guru & Kelas ini
        GuruFree::where('guru_id', $guruId)->where('kelas_id', $kelasId)->delete();

        // 2. Ambil data kotak yang DICENTANG (Bisa Hadir)
        $availableReq = $request->input('available', []);

        $hariArr = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slotDb = SlotJam::where('is_istirahat', false)->get();
        $dataInsert = [];

        // 3. Simpan KEDUA status ke database secara eksplisit
        foreach ($hariArr as $hari) {
            foreach ($slotDb as $s) {
                $key = "{$hari}_{$s->id}";

                // Jika key ada di request = dicentang = true (Hadir)
                // Jika tidak ada = dibiarkan kosong = false (Berhalangan)
                $isAvail = isset($availableReq[$key]);

                $dataInsert[] = [
                    'guru_id' => $guruId,
                    'kelas_id' => $kelasId,
                    'day' => $hari,
                    'time_slot_id' => $s->id,
                    'is_available' => $isAvail,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 4. Lakukan Insert Massal
        if (!empty($dataInsert)) {
            GuruFree::insert($dataInsert);
        }

        return redirect()->route('admin.guruFree.index', ['target' => $request->target])
            ->with('success', 'Ketersediaan waktu mengajar berhasil diperbarui.');
    }

    public function rekap()
    {
        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Rekap HANYA memunculkan yang is_available = false (Berhalangan)
        $tidakTersedia = GuruFree::with(['guru', 'kelas'])->where('is_available', false)->get();

        $rekapData = [];
        foreach ($tidakTersedia as $item) {
            if ($item->guru && $item->kelas) {
                $rekapData[$item->day][$item->time_slot_id][] = $item->guru->nama_guru . ' (' . $item->kelas->nama_kelas . ')';
            }
        }

        return view('admin.guruFree.rekap', compact('slot', 'hari', 'rekapData'));
    }
}
