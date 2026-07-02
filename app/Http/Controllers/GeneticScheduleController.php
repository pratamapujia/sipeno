<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\GuruPiket;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\SlotJam;
use App\Models\TahunAjaran;
use App\Services\GeneticScheduleService;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;

class GeneticScheduleController extends Controller
{
    protected $geneticService;

    // Memasukkan Service melalui Dependency Injection
    public function __construct(GeneticScheduleService $geneticService)
    {
        $this->geneticService = $geneticService;
    }

    // Menampilkan riwayat hasil generate jadwal
    public function index()
    {
        // Amankan route: Hanya admin/kurikulum yang bisa masuk halaman ini
        // Gate::authorize('akses-admin');

        $batches = BatchJadwal::orderBy('created_at', 'desc')->get();
        $academicYears = TahunAjaran::where('is_active', true)->first();

        return view('admin.jadwal.index', compact('batches', 'academicYears'));
    }

    // Memicu proses pembuatan jadwal otomatis
    public function generate(Request $request)
    {
        // Gate::authorize('akses-admin');

        $activeYear = TahunAjaran::where('is_active', true)->first();
        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran Aktif!');
        }

        try {
            // Panggil Service
            $result = $this->geneticService->generate($activeYear->id);

            // Skenario A: Jadwal berhasil dibuat tanpa ada bentrok sama sekali
            if ($result['status'] === 'perfect') {
                return redirect()->route('admin.jadwal.index')->with('success', "Jadwal SEMPURNA berhasil dibuat tanpa ada bentrok sama sekali.");
            }

            // Skenario B: Jadwal berhasil disimpan, tapi ada aturan shift/jumat yang dilanggar
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dibuat sebagai DRAFT')->with('warning_banyak', $result['conflicts']);
        } catch (\Exception $e) {
            return redirect()->route('admin.jadwal.index')->with('error',  'GAGAL Fatal: ', $e->getMessage());
        }
    }

    // Menyetujui draft jadwal agar aktif digunakan sekolah
    public function activate($id)
    {
        // Gate::authorize('akses-admin');

        $batch = BatchJadwal::findOrFail($id);

        // Matikan batch yang aktif sebelumnya, lalu aktifkan yang baru
        BatchJadwal::where('status', 'active')->update(['status' => 'draft']);
        $batch->update(['status' => 'active']);

        return redirect()->route('admin.jadwal.index')
            ->with('success', "Jadwal '{$batch->name}' sekarang resmi aktif digunakan.");
    }

    // Menghapus draft simulasi jadwal
    public function destroy($id)
    {
        // Gate::authorize('akses-admin');

        $batch = BatchJadwal::findOrFail($id);
        $batch->delete(); // Otomatis menghapus baris tabel `schedules` terkait karena onDelete('cascade')

        return redirect()->route('admin.jadwal.index')->with('success', 'Draft jadwal berhasil dihapus.');
    }

    public function show($id, Request $request)
    {
        $batch = BatchJadwal::findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        // Ambil filter kelas dari dropdown, jika tidak ada, ambil kelas pertama
        $selectedKelasId = $request->get('kelas_id') ?? ($kelasList->first()->id ?? null);

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = SlotJam::orderBy('slot_number', 'asc')->get();

        // Ambil data jadwal khusus untuk batch dan kelas yang dipilih
        $schedules = Jadwal::with(['guru', 'mapel', 'slotJam'])
            ->where('schedule_batch_id', $id)
            ->where('kelas_id', $selectedKelasId)
            ->get();

        // Format data menjadi array 2 dimensi (Matriks) agar mudah di-render di tabel
        $jadwalMatrix = [];
        foreach ($schedules as $s) {
            $jadwalMatrix[$s->time_slot_id][$s->day] = $s;
        }

        return view('admin.jadwal.show', compact('batch', 'kelasList', 'selectedKelasId', 'days', 'slots', 'jadwalMatrix'));
    }

    public function print($id, Request $request)
    {
        $batch = BatchJadwal::findOrFail($id);
        $academicYears = TahunAjaran::where('is_active', true)->first();
        $kelas = Kelas::findOrFail($request->get('kelas_id'));

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = SlotJam::orderBy('slot_number', 'asc')->get();

        $schedules = Jadwal::with(['guru', 'mapel', 'slotJam'])
            ->where('schedule_batch_id', $id)
            ->where('kelas_id', $kelas->id)
            ->get();

        $jadwalMatrix = [];
        foreach ($schedules as $s) {
            $jadwalMatrix[$s->time_slot_id][$s->day] = $s;
        }

        // Return ke view khusus cetak (tanpa sidebar Mazer)
        return view('admin.jadwal.print', compact('batch', 'kelas', 'days', 'slots', 'jadwalMatrix', 'academicYears'));
    }

    public function printAll($id)
    {
        $batch = BatchJadwal::findOrFail($id);
        $academicYears = TahunAjaran::where('is_active', true)->first();

        // Ambil semua kelas yang terdaftar
        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = SlotJam::orderBy('slot_number', 'asc')->get();

        // Ambil semua jadwal yang ada pada batch ini, lalu kelompokkan berdasarkan kelas_id
        $allSchedules = Jadwal::with(['guru', 'mapel', 'slotJam'])
            ->where('schedule_batch_id', $id)
            ->get()
            ->groupBy('kelas_id');

        // Susun matriks jadwal untuk setiap kelas
        $kelasMatrix = [];
        foreach ($kelasList as $kelas) {
            $schedulesForKelas = $allSchedules->get($kelas->id) ?? collect();

            $matrix = [];
            foreach ($schedulesForKelas as $s) {
                $matrix[$s->time_slot_id][$s->day] = $s;
            }

            $kelasMatrix[$kelas->id] = $matrix;
        }

        // Return ke view baru khusus cetak massal
        return view('admin.jadwal.printAll', compact('batch', 'kelasList', 'days', 'slots', 'kelasMatrix', 'academicYears'));
    }

    public function updateManual(Request $request, $id)
    {
        $request->validate([
            'day' => 'required',
            'time_slot_id' => 'required',
        ]);

        $jadwal = Jadwal::findOrFail($id);

        // Validasi 1: Cek Guru Piket
        $sedangPiket = GuruPiket::where('tahun_ajaran_id', $jadwal->academic_year_id)
            ->where('guru_id', $jadwal->guru_id)
            ->where('hari', $request->day)
            ->exists();

        if ($sedangPiket) {
            return redirect()->back()->with('error', 'Gagal memindah! Guru tersebut berstatus PIKET pada hari ' . $request->day . '.');
        }

        // Validasi 2: Pastikan GURU tidak sedang mengajar di kelas lain pada jam tujuan
        $cekGuruBentrok = Jadwal::where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('day', $request->day)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('guru_id', $jadwal->guru_id)
            ->where('id', '!=', $jadwal->id)
            ->exists();

        if ($cekGuruBentrok) {
            return redirect()->back()->with('error', 'Gagal memindah! Guru tersebut sudah memiliki jadwal mengajar di kelas lain pada hari dan jam tersebut.');
        }

        // Validasi 3: Pastikan KELAS ini masih kosong pada jam tujuan (Mencegah Database Error)
        $cekKelasBentrok = Jadwal::where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('day', $request->day)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('id', '!=', $jadwal->id)
            ->exists();

        if ($cekKelasBentrok) {
            return redirect()->back()->with('error', 'Gagal memindah! Sudah ada mata pelajaran lain di kelas ini pada jam dan hari tersebut. Harap pindahkan jadwal yang lama terlebih dahulu.');
        }

        // Jika semua aman, lakukan pembaruan (pindah jadwal)
        $jadwal->update([
            'day' => $request->day,
            'time_slot_id' => $request->time_slot_id,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil dipindahkan secara manual.');
    }
}
