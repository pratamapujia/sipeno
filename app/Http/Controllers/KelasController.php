<?php

namespace App\Http\Controllers;

use App\Imports\KelasImport;
use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
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
        $guru = Guru::all();
        return view('admin.kelas.create', compact('guru'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'nama_kelas' => 'required|min:3|unique:classes,nama_kelas',
            'tingkat' => 'required|numeric',
            'wali_kelas_id' => 'nullable|exists:teachers,id',
        ], [
            'nama_kelas.required' => 'Kelas harus diisi',
            'nama_kelas.min' => 'Kelas minimal 3 karakter',
            'nama_kelas.unique' => 'Kelas sudah ada',
            'tingkat.required' => 'Tingkat harus diisi',
            'tingkat.numeric' => 'Tingkat harus angka',
            'wali_kelas_id.exists' => 'Wali kelas yang dipilih tidak valid',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        Kelas::create($request->all());
        return redirect()->route('admin.m.kelas.index')->with('success', 'Kelas ' . $request->nama_kelas . ' berhasil ditambahkan');
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
        $guru = Guru::all();
        return view('admin.kelas.edit', compact('kelas', 'guru'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validasi = Validator::make($request->all(), [
            'nama_kelas' => 'required|min:3|unique:classes,nama_kelas,' . $id,
            'tingkat' => 'required|numeric',
            'wali_kelas_id' => 'nullable|exists:teachers,id',
        ], [
            'nama_kelas.required' => 'Kelas harus diisi',
            'nama_kelas.min' => 'Kelas minimal 3 karakter',
            'nama_kelas.unique' => 'Kelas sudah ada',
            'tingkat.required' => 'Tingkat harus diisi',
            'tingkat.numeric' => 'Tingkat harus angka',
            'wali_kelas_id.exists' => 'Wali kelas yang dipilih tidak valid',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());
        return redirect()->route('admin.m.kelas.index')->with('success', 'Kelas ' . $request->nama_kelas . ' berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return redirect()->route('admin.m.kelas.index')->with('success', 'Kelas ' . $kelas->nama_kelas . ' berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xls,xlsx',
        ]);

        try {
            Excel::import(new KelasImport, $request->file('file_excel'));
            return redirect()->route('admin.m.kelas.index')->with('success', 'Data Kelas berhasil di-Import');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ke-<b>' . $failure->row() . '</b>: ' . implode(', ', $failure->errors());
            }

            $pesanGagal = [
                'type' => 'danger',
                'title' => 'Gagal Mengimport Data Kelas',
                'body' => 'Terdapat beberapa kesalahan pada file Anda:',
                'details' => $errorMessages,
            ];

            return redirect()->route('admin.m.kelas.index')->with('pesan_error', $pesanGagal);
        } catch (\Exception $e) {
            $pesanGagal = [
                'type' => 'danger',
                'title' => 'Terjadi Kesalahan!',
                'body' => 'Tidak dapat memproses file: ' . $e->getMessage()
            ];

            return redirect()->route('admin.m.kelas.index')->with('pesan_error', $pesanGagal);
        }
    }
}
