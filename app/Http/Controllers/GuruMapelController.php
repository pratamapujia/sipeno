<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuruMapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mencari tahun ajaran yang active
        $thnAktif = TahunAjaran::where('is_active', true)->first();

        // Jika tidak ada tahun ajaran aktif, lempar ke view dengan data kosong
        if (!$thnAktif) {
            return view('admin.plotting.index', [
                'thnAktif' => null,
                'plotting' => collect() // Mencegah error pada @foreach
            ]);
        }

        // Mengambil data ploting (sudah menggunakan eager loading yang benar)
        $plotting = GuruMapel::with(['guru', 'mapel', 'kelas'])
            ->where('tahun_ajaran_id', $thnAktif->id)
            ->get();

        return view('admin.plotting.index', compact('plotting', 'thnAktif'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $thnAjaran = TahunAjaran::where('is_active', true)->first();

        if (!$thnAjaran) {
            return redirect()->route('admin.plotting.index')->with('error', 'Silakan aktifkan Tahun Ajaran terlebih dahulu!');
        }

        // Ambil semua data dari tabel master untuk opsi dropdown
        $guru = Guru::orderBy('nama_guru', 'asc')->get();
        $mapel = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelas = Kelas::orderBy('kelas', 'asc')->get();

        return view('admin.plotting.create', compact('guru', 'thnAjaran', 'mapel', 'kelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'guru_id' => 'required|exists:teachers,id',
            'tahun_ajaran_id' => 'required|exists:academic_years,id',
            'kelas_id' => 'required|exists:classes,id',
            'mapel_id' => 'required|exists:subjects,id',
        ], [
            'guru_id.required' => 'Guru harus dipilih.',
            'guru_id.exists' => 'Guru tidak ditemukan.',
            'tahun_ajaran_id.required' => 'Tahun Ajaran harus dipilih.',
            'tahun_ajaran_id.exists' => 'Tahun Ajaran tidak ditemukan.',
            'kelas_id.required' => 'Kelas harus dipilih.',
            'kelas_id.exists' => 'Kelas tidak ditemukan.',
            'mapel_id.required' => 'Mapel harus dipilih.',
            'mapel_id.exists' => 'Mapel tidak ditemukan.',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        // PROTEKSI: Cek apakah kombinasi Guru, Mapel, dan Kelas ini sudah pernah diinput sebelumnya
        $isExist = GuruMapel::where('guru_id', $request->guru_id)
            ->where('mapel_id', $request->mapel_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->exists();

        if ($isExist) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal! Guru tersebut sudah diplot mengampu mata pelajaran di kelas yang sama.');
        }

        GuruMapel::create($request->all());
        return redirect()->route('admin.plotting.index')->with('success', 'Plotting berhasil ditambahkan.');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
