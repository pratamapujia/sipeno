<?php

namespace App\Http\Controllers;

use App\Models\SlotJam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class SlotJamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $slot = SlotJam::orderBy('slot_number', 'asc')->get();
        return view('admin.slotJam.index', compact('slot'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.slotJam.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'slot_number' => 'required|numeric',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ], [
            'slot_number.required' => 'Slot harus diisi',
            'slot_number.numeric' => 'Slot harus angka',
            'start_time.required' => 'Jam mulai harus diisi',
            'start_time.date_format' => 'Format jam mulai harus HH:MM',
            'end_time.required' => 'Jam selesai harus diisi',
            'end_time.date_format' => 'Format jam selesai harus HH:MM',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        SlotJam::create([
            'slot_number' => $request->slot_number,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_istirahat' => $request->has('is_istirahat') ? true : false,
        ]);
        return redirect()->route('admin.m.slotJam.index')->with('success', 'Slot Jam ke-' . $request->slot_number . ' berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $hashedId)
    {
        $id = Hashids::decode($hashedId)[0] ?? null;

        if (!$id) {
            abort(404);
        }

        $slot = SlotJam::findOrFail($id);
        return view('admin.slotJam.edit', compact('slot'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'slot_number' => 'required|numeric',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ], [
            'slot_number.required' => 'Slot harus diisi',
            'slot_number.numeric' => 'Slot harus angka',
            'start_time.required' => 'Jam mulai harus diisi',
            'start_time.date_format' => 'Format jam mulai harus HH:MM',
            'end_time.required' => 'Jam selesai harus diisi',
            'end_time.date_format' => 'Format jam selesai harus HH:MM',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $slot = SlotJam::findOrFail($id);
        $slot->update($request->all());
        return redirect()->route('admin.m.slotJam.index')->with('success', 'Slot Jam ke-' . $request->slot_number . ' berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slot = SlotJam::findOrFail($id);
        $slot->delete();
        return redirect()->route('admin.m.slotJam.index')->with('success', 'Slot Jam ke-' . $slot->slot_number . ' berhasil dihapus');
    }
}
