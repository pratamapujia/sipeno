<?php

namespace App\Http\Controllers;

use App\Imports\MapelImport;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Vinkla\Hashids\Facades\Hashids;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mapel = Mapel::all();
        return view('admin.mapel.index', compact('mapel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mapel.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'nama_mapel' => 'required|min:3',
            'beban_jam' => 'required|numeric',
        ], [
            'nama_mapel.required' => 'Nama mapel harus diisi',
            'nama_mapel.min' => 'Nama mapel minimal 3 karakter',
            'beban_jam.required' => 'Beban jam harus diisi',
            'beban_jam.numeric' => 'Beban jam harus angka',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        Mapel::create([
            'nama_mapel' => $request->nama_mapel,
            'beban_jam' => $request->beban_jam,
            'type' => $request->has('type') ? 'praktikum' : 'teori',
        ]);
        return redirect()->route('admin.m.mapel.index')->with('success', 'Data Mata Pelajaran ' . $request->nama_mapel . ' berhasil disimpan');
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

        $mapel = Mapel::findOrFail($id);
        return view('admin.mapel.edit', compact('mapel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'nama_mapel' => 'required|min:3',
            'beban_jam' => 'required|numeric',
        ], [
            'nama_mapel.required' => 'Nama mapel harus diisi',
            'nama_mapel.min' => 'Nama mapel minimal 3 karakter',
            'beban_jam.required' => 'Beban jam harus diisi',
            'beban_jam.numeric' => 'Beban jam harus angka',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $mapel = Mapel::findOrFail($id);
        $mapel->update([
            'nama_mapel' => $request->nama_mapel,
            'beban_jam' => $request->beban_jam,
            'type' => $request->has('type') ? 'praktikum' : 'teori',
        ]);
        return redirect()->route('admin.m.mapel.index')->with('success', 'Data Mata Pelajaran ' . $request->nama_mapel . ' berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->delete();
        return redirect()->route('admin.m.mapel.index')->with('success', 'Data Mata Pelajaran ' . $mapel->nama_mapel . ' berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xls,xlsx',
        ]);

        try {
            Excel::import(new MapelImport, $request->file('file_excel'));
            return redirect()->route('admin.m.mapel.index')->with('success', 'Data Mapel berhasil di-Import');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ke-<b>' . $failure->row() . '</b>: ' . implode(', ', $failure->errors());
            }

            $pesanGagal = [
                'type' => 'danger',
                'title' => 'Gagal Mengimport Data Mapel',
                'body' => 'Terdapat beberapa kesalahan pada file Anda:',
                'details' => $errorMessages,
            ];

            return redirect()->route('admin.m.mapel.index')->with('pesan_error', $pesanGagal);
        } catch (\Exception $e) {
            $pesanGagal = [
                'type' => 'danger',
                'title' => 'Terjadi Kesalahan!',
                'body' => 'Tidak dapat memproses file: ' . $e->getMessage()
            ];

            return redirect()->route('admin.m.mapel.index')->with('pesan_error', $pesanGagal);
        }
    }
}
