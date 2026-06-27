<?php

namespace App\Http\Controllers;

use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
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
            'email' => 'required|email|unique:users,email',
        ], [
            'nip.unique' => 'NIP sudah terdaftar',
            'nama_guru.required' => 'Nama Guru harus diisi',
            'nama_guru.min' => 'Nama Guru minimal 3 karakter',
            'jenis_kelamin.required' => 'Pilih salah satu',
            'status.required' => 'Pilih status guru',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar',
            'email.email' => 'Format email tidak valid',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_guru,
                'email' => $request->email,
                'password' => Hash::make('guru123'),
            ]);

            $user->assignRole('guru');

            Guru::create([
                'users_id' => $user->id,
                'nip' => $request->nip,
                'nama_guru' => $request->nama_guru,
                'jenis_kelamin' => $request->jenis_kelamin,
                'status' => $request->status,
            ]);
        });

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
        $teacher = Guru::findOrFail($id);

        $request->validate([
            'nama_guru'     => 'required',
            'nip'           => 'required|unique:teachers,nip,' . $teacher->id,
            'jenis_kelamin' => 'required|in:L,P',
            'status'        => 'required',
            'email'         => 'required|email|unique:users,email,' . $teacher->users_id,
            'roles'         => 'required|array', // Input berupa array checkbox dari form
        ]);

        DB::transaction(function () use ($request, $teacher) {
            // 1. Update data akun login di tabel users
            $user = User::findOrFail($teacher->users_id);
            $user->update([
                'name'  => $request->nama_guru,
                'email' => $request->email,
            ]);

            // 2. Sinkronisasi Role Spatie (Kunci Utama Multi-Role)
            // Jika request berisi ['admin', 'guru'], maka user akan memiliki kedua akses tersebut
            $user->syncRoles($request->roles);

            // 3. Update data biodata di tabel teachers
            $teacher->update([
                'nama_guru'     => $request->nama_guru,
                'nip'           => $request->nip,
                'jenis_kelamin' => $request->jenis_kelamin,
                'status'        => $request->status,
            ]);
        });
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

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xls,xlsx',
        ]);

        try {
            Excel::import(new GuruImport, $request->file('file_excel'));
            return redirect()->route('admin.m.guru.index')->with('success', 'Data Guru berhasil di-Import');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ke-<b>' . $failure->row() . '</b>: ' . implode(', ', $failure->errors());
            }

            $pesanGagal = [
                'type' => 'danger',
                'title' => 'Gagal Mengimport Data Guru',
                'body' => 'Terdapat beberapa kesalahan pada file Anda:',
                'details' => $errorMessages,
            ];

            return redirect()->route('admin.m.guru.index')->with('pesan_error', $pesanGagal);
        } catch (\Exception $e) {
            $pesanGagal = [
                'type' => 'danger',
                'title' => 'Terjadi Kesalahan!',
                'body' => 'Tidak dapat memproses file: ' . $e->getMessage()
            ];

            return redirect()->route('admin.m.guru.index')->with('pesan_error', $pesanGagal);
        }
    }
}
