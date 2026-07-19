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

        // Data yang diambil dari DB HANYA data yang BERHALANGAN (is_available = false)
        $tidakTersedia = [];
        if ($selectedGuruId && $selectedKelasId) {
            $tidakTersedia = GuruFree::where('guru_id', $selectedGuruId)
                ->where('kelas_id', $selectedKelasId)
                ->where('is_available', false)
                ->get()
                ->mapWithKeys(function ($item) {
                    return ["{$item->day}_{$item->time_slot_id}" => true];
                })->toArray();
        }

        return view('admin.guruFree.index', compact('guruMapels', 'slot', 'hari', 'tidakTersedia', 'selectedTarget'));
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

        // 2. Jika ada kotak yang DICENTANG (artinya BERHALANGAN)
        if ($request->has('unassigned')) {
            $dataInsert = [];
            foreach ($request->unassigned as $key => $value) {
                [$hari, $slotId] = explode('_', $key);
                $dataInsert[] = [
                    'guru_id' => $guruId,
                    'kelas_id' => $kelasId,
                    'day' => $hari,
                    'time_slot_id' => $slotId,
                    'is_available' => false, // Simpan sebagai tidak tersedia
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Lakukan Insert Massal
            GuruFree::insert($dataInsert);
        }

        return redirect()->route('admin.guruFree.index', ['target' => $request->target])
            ->with('success', 'Waktu berhalangan mengajar berhasil diperbarui.');
    }

    public function rekap()
    {
        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

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
