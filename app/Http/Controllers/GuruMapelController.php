<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

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
            ->where('tahun_ajaran_id', $thnAktif->id)->orderBy('created_at', 'desc')
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
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.plotting.create', compact('guru', 'thnAjaran', 'mapel', 'kelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'tahun_ajaran_id' => 'required|exists:academic_years,id',
            'guru_id' => 'required|exists:teachers,id',
            'mapel_id' => 'required|array',
            'mapel_id.*' => 'exists:subjects,id',
            'kelas_id' => 'required|array',
            'kelas_id.*' => 'exists:classes,id',
        ], [
            'tahun_ajaran_id.required' => 'Tahun Ajaran harus dipilih.',
            'tahun_ajaran_id.exists' => 'Tahun Ajaran tidak ditemukan.',
            'guru_id.required' => 'Guru harus dipilih.',
            'guru_id.exists' => 'Guru tidak ditemukan.',
            'mapel_id.required' => 'Mapel harus dipilih.',
            'mapel_id.*.exists' => 'Mapel tidak ditemukan.',
            'kelas_id.required' => 'Kelas harus dipilih minimal 1 Kelas.',
            'kelas_id.*.exists' => 'Kelas tidak ditemukan.',
        ]);

        if ($validasi->fails()) {
            return redirect()->back()->withErrors($validasi)->withInput();
        }

        $suksesCount = 0;
        $gagalCount = 0;

        foreach ($request->mapel_id as $mapelId) {
            foreach ($request->kelas_id as $kelasId) {
                $isDuplicate = GuruMapel::where([
                    'tahun_ajaran_id' => $request->tahun_ajaran_id,
                    'guru_id'         => $request->guru_id,
                    'mapel_id'        => $mapelId,
                    'kelas_id'        => $kelasId,
                ])->exists();

                if (!$isDuplicate) {
                    GuruMapel::create([
                        'tahun_ajaran_id' => $request->tahun_ajaran_id,
                        'guru_id'         => $request->guru_id,
                        'mapel_id'        => $mapelId,
                        'kelas_id'        => $kelasId,
                    ]);
                    $suksesCount++;
                } else {
                    $gagalCount++;
                }
            }
        }

        if ($gagalCount > 0) {
            $msg = "Berhasil mem-plot {$suksesCount} kombinasi. ({$gagalCount} kombinasi dilewati karena sudah ada).";
        } else {
            $msg = "Berhasil mem-plot {$suksesCount} kombinasi jadwal sekaligus.";
        }

        return redirect()->route('admin.plotting.index')->with('success', $msg);
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
        $plotting = GuruMapel::findOrFail($id);
        $thnAktif = TahunAjaran::where('is_active', true)->first();

        $guru = Guru::all();
        $mapel = Mapel::all();
        $kelas = Kelas::all();

        return view('admin.plotting.edit', compact('plotting', 'thnAktif', 'guru', 'mapel', 'kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'guru_id' => 'required|exists:teachers,id',
            'mapel_id' => 'required|exists:subjects,id',
            'kelas_id' => 'required|exists:classes,id',
        ]);

        $plotting = GuruMapel::findOrFail($id);
        $isDuplicate = GuruMapel::where([
            'tahun_ajaran_id' => $plotting->tahun_ajaran_id,
            'guru_id' => $request->guru_id,
            'mapel_id' => $request->mapel_id,
            'kelas_id' => $request->kelas_id,
        ])->where('id', '!=', $id)->exists();

        if ($isDuplicate) {
            return redirect()->route('admin.plotting.edit', $id)->with('error', 'Kelas tersebut sudah pernah di-plot sebelumnya.');
        }

        $plotting->update([
            'guru_id' => $request->guru_id,
            'mapel_id' => $request->mapel_id,
            'kelas_id' => $request->kelas_id,
        ]);

        return redirect()->route('admin.plotting.index')->with('success', 'Berhasil memperbarui target mengajar Guru.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $plotting = GuruMapel::findOrFail($id);
        $plotting->delete();
        return redirect()->route('admin.plotting.index')->with('success', 'Berhasil menghapus target mengajar Guru.');
    }
}
