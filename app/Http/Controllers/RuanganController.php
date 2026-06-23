<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class RuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ruangan = Ruangan::all();
        return view('admin.ruangan.index', compact('ruangan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ruangan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'nama_ruangan' => 'required|min:3|unique:rooms,nama_ruangan',
        ], [
            'nama_ruangan.required' => 'Ruangan harus diisi',
            'nama_ruangan.min' => 'Ruangan minimal 3 karakter',
            'nama_ruangan.unique' => 'Ruangan sudah ada',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        Ruangan::create($request->all());
        return redirect()->route('admin.m.ruangan.index')->with('success', 'Data Ruangan ' . $request->nama_ruangan . ' berhasil disimpan');
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

        $ruangan = Ruangan::findOrFail($id);
        return view('admin.ruangan.edit', compact('ruangan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'nama_ruangan' => 'required|min:3|unique:rooms,nama_ruangan,' . $id,
        ], [
            'nama_ruangan.required' => 'Ruangan harus diisi',
            'nama_ruangan.min' => 'Ruangan minimal 3 karakter',
            'nama_ruangan.unique' => 'Ruangan sudah ada',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $ruangan = Ruangan::findOrFail($id);
        $ruangan->update($request->all());
        return redirect()->route('admin.m.ruangan.index')->with('success', 'Data Ruangan ' . $request->nama_ruangan . ' berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->delete();
        return redirect()->route('admin.m.ruangan.index')->with('success', 'Data Ruangan ' . $ruangan->nama_ruangan . ' berhasil dihapus');
    }
}
