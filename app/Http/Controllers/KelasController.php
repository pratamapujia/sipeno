<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelas = Kelas::all();
        return view('admin.kelas.index', compact('kelas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.kelas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'kelas' => 'required|min:3|unique:classes,kelas',
            'tingkat' => 'required|numeric',
        ], [
            'kelas.required' => 'Kelas harus diisi',
            'kelas.min' => 'Kelas minimal 3 karakter',
            'kelas.unique' => 'Kelas sudah ada',
            'tingkat.required' => 'Tingkat harus diisi',
            'tingkat.numeric' => 'Tingkat harus angka',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        Kelas::create($request->all());
        return redirect()->route('admin.m.kelas.index')->with('success', 'Kelas ' . $request->kelas . ' berhasil ditambahkan');
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

        $kelas = Kelas::findOrFail($id);
        return view('admin.kelas.edit', compact('kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'kelas' => 'required|min:3|unique:classes,kelas,' . $id,
            'tingkat' => 'required|numeric',
        ], [
            'kelas.required' => 'Kelas harus diisi',
            'kelas.min' => 'Kelas minimal 3 karakter',
            'kelas.unique' => 'Kelas sudah ada',
            'tingkat.required' => 'Tingkat harus diisi',
            'tingkat.numeric' => 'Tingkat harus angka',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());
        return redirect()->route('admin.m.kelas.index')->with('success', 'Kelas ' . $request->kelas . ' berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return redirect()->route('admin.m.kelas.index')->with('success', 'Kelas ' . $kelas->kelas . ' berhasil dihapus');
    }
}
