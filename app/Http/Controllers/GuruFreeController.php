<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruFree;
use App\Models\SlotJam;
use Illuminate\Http\Request;

class GuruFreeController extends Controller
{
    // Tampilkan grid ketersediaan guru
    public function index(Request $request)
    {
        $guru = Guru::orderBy('nama_guru', 'asc')->get();
        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // ambil guru yang sedang di pilih di dropdown, jika tidak ada maka ambil guru pertama
        $selectedGuruId = $request->get('guru_id') ?? ($guru->first()->id ?? null);

        // Ambil data ketersediaan guru yang dipilih yang berstatus BERHALANGAN (is_available = false)
        // Kita kelompokkan berdasarkan format "HARI_SLOTID" untuk mempermudah pengecekan di Blade
        $tidakTersedia = [];
        if ($selectedGuruId) {
            $tidakTersedia = GuruFree::where('guru_id', $selectedGuruId)->where('is_available', false)->get()->mapWithKeys(function ($item) {
                return ["{$item->hari}_{$item->time_slot_id}" => true];
            })->toArray();
        }

        return view('admin.guruFree.index', compact('guru', 'slot', 'hari', 'tidakTersedia', 'selectedGuruId'));
    }

    // Simpan Perubahan Ketersediaan Guru (massive update)
    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:teachers,id',
        ]);

        $guruId = $request->guru_id;

        // Hapus semua data berhalangan lama untuk guru yang dipilih (Reset data)
        GuruFree::where('guru_id', $guruId)->delete();

        // Jika ada jam yang dicentang berhalangan, maka simpan data baru
        if ($request->has('unassigned')) {
            $dataInsert = [];
            foreach ($request->unassigned as $key => $value) {
                // Key berformat "HARI_SLOTID"
                explode('_', $key);
                [$hari, $slotId] = explode('_', $key);
                $dataInsert[] = [
                    'guru_id' => $guruId,
                    'day' => $hari,
                    'time_slot_id' => $slotId,
                    'is_available' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Masukkan data massal
            GuruFree::insert($dataInsert);
        }

        return redirect()->route('admin.guruFree.index', ['guru_id' => $guruId])->with('success', 'Ketersediaan Guru berhasil diperbarui.');
    }

    public function rekap()
    {
        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Ambil semua data guru yang berhalangan
        $tidakTersedia = GuruFree::with('guru')->where('is_available', false)->get();

        // Mengelompokkan data berdasarkan [HARI] [TIME_SLOT_ID]
        $rekapData = [];
        foreach ($tidakTersedia as $item) {
            if ($item->guru) {
                $rekapData[$item->day][$item->time_slot_id][] = $item->guru->nama_guru;
            }
        }

        return view('admin.guruFree.rekap', compact('slot', 'hari', 'rekapData'));
    }
}
