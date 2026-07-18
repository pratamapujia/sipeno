<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruFree;
use App\Models\GuruMapel;
use App\Models\SlotJam;
use Illuminate\Http\Request;

class GuruFreeController extends Controller
{
    // Tampilkan grid ketersediaan guru
    public function index(Request $request)
    {
        // 1. Tarik data plotting untuk mendapatkan kombinasi Guru dan Kelas
        // Asumsi relasi 'guru' dan 'kelas' sudah ada di model GuruMapel
        $guruMapels = GuruMapel::with(['guru', 'kelas'])
            ->orderBy('guru_id')
            ->get()
            ->unique(function ($item) {
                // Saring agar kombinasi Guru dan Kelas tidak duplikat
                return $item->guru_id . '_' . $item->kelas_id;
            })
            ->values();

        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // 2. Ambil target dari dropdown (Format value: "guruId_kelasId")
        $selectedTarget = $request->get('target');

        // Jika tidak ada yang dipilih, ambil kombinasi pertama sebagai default
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

        // 3. Tarik data ketersediaan SPESIFIK untuk Guru & Kelas tersebut
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

    // Simpan Perubahan Ketersediaan Guru (massive update)
    public function store(Request $request)
    {
        $request->validate([
            'target' => 'required', // Format: "guruId_kelasId"
        ], [
            'target.required' => 'Pilih kombinasi Guru dan Kelas terlebih dahulu.',
        ]);

        $parts = explode('_', $request->target);
        $guruId = $parts[0];
        $kelasId = $parts[1];

        // Hapus data lama HANYA untuk Guru DAN Kelas yang bersangkutan
        GuruFree::where('guru_id', $guruId)->where('kelas_id', $kelasId)->delete();

        // Jika ada kotak yang dicentang
        if ($request->has('unassigned')) {
            $dataInsert = [];
            foreach ($request->unassigned as $key => $value) {
                [$hari, $slotId] = explode('_', $key);
                $dataInsert[] = [
                    'guru_id' => $guruId,
                    'kelas_id' => $kelasId, // Simpan ID Kelas
                    'day' => $hari,
                    'time_slot_id' => $slotId,
                    'is_available' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            GuruFree::insert($dataInsert);
        }

        return redirect()->route('admin.guruFree.index', ['target' => $request->target])
            ->with('success', 'Batasan waktu mengajar untuk kelas tersebut berhasil diperbarui.');
    }

    public function rekap()
    {
        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Tarik data dengan relasi guru dan kelas
        $tidakTersedia = GuruFree::with(['guru', 'kelas'])->where('is_available', false)->get();

        $rekapData = [];
        foreach ($tidakTersedia as $item) {
            if ($item->guru && $item->kelas) {
                // Format teks rekap: "Nama Guru (Kelas X)"
                $rekapData[$item->day][$item->time_slot_id][] = $item->guru->nama_guru . ' (' . $item->kelas->nama_kelas . ')';
            }
        }

        return view('admin.guruFree.rekap', compact('slot', 'hari', 'rekapData'));
    }
}
