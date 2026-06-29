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
        $guru = Guru::findOrFail($id);

        $request->validate([
            'nama_guru'     => 'required',
            'nip'           => 'required|unique:teachers,nip,' . $guru->id,
            'jenis_kelamin' => 'required|in:L,P',
            'status'        => 'required',
            'email'         => 'required|email|unique:users,email,' . $guru->users_id,
            'roles'         => 'required|array', // Input berupa array checkbox dari form
        ]);

        DB::transaction(function () use ($request, $guru) {
            // 1. Update data akun login di tabel users
            $user = User::findOrFail($guru->users_id);
            $user->update([
                'name'  => $request->nama_guru,
                'email' => $request->email,
            ]);

            // 2. Sinkronisasi Role Spatie (Kunci Utama Multi-Role)
            // Jika request berisi ['admin', 'guru'], maka user akan memiliki kedua akses tersebut
            $user->syncRoles($request->roles);

            // 3. Update data biodata di tabel teachers
            $guru->update([
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

        // 👇 TAMBAHKAN KODE INI SEMENTARA UNTUK DEBUGGING 👇
        // $data = \Maatwebsite\Excel\Facades\Excel::toArray(new GuruImport, $request->file('file_excel'));
        // dd($data);

        try {
            // 1. "Intip" isi file Excel ke dalam bentuk array terlebih dahulu
            $data = Excel::toArray(new GuruImport, $request->file('file_excel'));

            // 2. Cek apakah file benar-benar memiliki data
            if (empty($data) || empty($data[0])) {
                return redirect()->route('admin.m.guru.index')->with('pesan_error', [
                    'type' => 'danger',
                    'title' => 'File Kosong',
                    'body' => 'File Excel yang Anda unggah tidak memiliki data sama sekali.'
                ]);
            }

            // 3. Ambil daftar nama kolom (header) yang dibaca oleh sistem
            $uploadedHeaders = array_keys($data[0][0]);

            // 4. Definisikan header template yang wajib ada
            $expectedHeaders = ['nip', 'nama_guru', 'email', 'jenis_kelamin', 'status'];

            // 5. Cari tahu apakah ada kolom wajib yang tidak ditemukan di file yang diupload
            $missingHeaders = array_diff($expectedHeaders, $uploadedHeaders);

            // 6. Jika ada kolom yang hilang, batalkan import dan tampilkan alert khusus
            if (!empty($missingHeaders)) {
                $pesanGagal = [
                    'type' => 'danger',
                    'title' => 'Format Excel Tidak Sesuai!',
                    'body' => 'File yang Anda unggah tidak menggunakan format/template yang benar.',
                    'details' => [
                        'Kolom yang tidak ditemukan: <b>' . implode(', ', $missingHeaders) . '</b>',
                        'Pastikan header baris pertama persis: nip, nama_guru, email, jenis_kelamin, status'
                    ],
                ];

                return redirect()->route('admin.m.guru.index')->with('pesan_error', $pesanGagal);
            }

            // 7. Lakukan import data
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
