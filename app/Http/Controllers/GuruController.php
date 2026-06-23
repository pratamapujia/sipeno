<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Vinkla\Hashids\Facades\Hashids;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guru = Guru::all();
        return view('admin.guru.index', compact('guru'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.guru.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'nip' => 'unique:teachers,nip',
            'nama_guru' => 'required|min:3',
            'jenis_kelamin' => 'required',
            'status' => 'required',
        ], [
            'nip.unique' => 'NIP sudah terdaftar',
            'nama_guru.required' => 'Nama Guru harus diisi',
            'nama_guru.min' => 'Nama Guru minimal 3 karakter',
            'jenis_kelamin.required' => 'Pilih salah satu',
            'status.required' => 'Pilih status guru',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        Guru::create($request->all());
        return redirect()->route('admin.m.guru.index')->with('success', 'Data Guru ' . $request->nama_guru . ' berhasil disimpan');
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
    public function edit(string $hashedID)
    {
        $id = Hashids::decode($hashedID)[0] ?? null;

        if (!$id) {
            abort(404);
        }

        $guru = Guru::findOrFail($id);
        return view('admin.guru.edit', compact('guru'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'nip' => 'unique:teachers,nip,' . $id,
            'nama_guru' => 'required|min:3',
            'jenis_kelamin' => 'required',
            'status' => 'required',
        ], [
            'nip.unique' => 'NIP sudah terdaftar',
            'nama_guru.required' => 'Nama Guru harus diisi',
            'nama_guru.min' => 'Nama Guru minimal 3 karakter',
            'jenis_kelamin.required' => 'Pilih salah satu',
            'status.required' => 'Pilih status guru',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $guru = Guru::findOrFail($id);
        $guru->update($request->all());
        return redirect()->route('admin.m.guru.index')->with('success', 'Data Guru ' . $guru->nama_guru . ' berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $guru = Guru::findOrFail($id);
        $guru->delete();
        return redirect()->route('admin.m.guru.index')->with('success', 'Data Guru ' . $guru->nama_guru . ' berhasil dihapus');
    }
}
