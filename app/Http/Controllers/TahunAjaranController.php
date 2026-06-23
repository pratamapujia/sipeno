<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $thnAjaran = TahunAjaran::all()->sortByDesc('created_at');
        return view('admin.thnAjaran.index', compact('thnAjaran'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.thnAjaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'semester' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun Ajaran harus diisi, contoh: 2023/2024',
            'semester.required' => 'Pilih semester',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        TahunAjaran::create($request->all());
        return redirect()->route('admin.m.thnAjaran.index')->with('success', 'Data Tahun Ajaran ' . $request->tahun_ajaran . ' berhasil disimpan');
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

        $thnAjaran = TahunAjaran::findOrFail($id);
        return view('admin.thnAjaran.edit', compact('thnAjaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'semester' => 'required',
            'is_active' => 'required|boolean',

        ], [
            'tahun_ajaran.required' => 'Tahun Ajaran harus diisi, contoh: 2023/2024',
            'semester.required' => 'Pilih semester',
            'is_active.required' => 'Pilih status',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $thnAjaran = TahunAjaran::findOrFail($id);
        $thnAjaran->update($request->all());
        return redirect()->route('admin.m.thnAjaran.index')->with('success', 'Data Tahun Ajaran ' . $request->tahun_ajaran . ' berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $thnAjaran = TahunAjaran::findOrFail($id);
        // Cek apakah tahun ajaran berstatus aktif
        if ($thnAjaran->is_active) {
            return redirect()->back()->withInput()->with('error', 'Tahun ajaran yang sedang aktif dilarang untuk dihapus.');
        }
        $thnAjaran->delete();
        return redirect()->route('admin.m.thnAjaran.index')->with('success', 'Data Tahun Ajaran ' . $thnAjaran->tahun_ajaran . ' berhasil dihapus');
    }
}
