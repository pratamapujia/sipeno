<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
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
            $batch = $this->geneticService->generate($activeYear->id);
            return redirect()->route('admin.jadwal.index')
                ->with('success', "Jadwal SEMPURNA berhasil dibuat tanpa ada bentrok sama sekali.");
        } catch (\Exception $e) {
            $msg = $e->getMessage();

            // Cek apakah error ini berisi rincian JSON dari diagnosa konflik
            if (strpos($msg, 'KONFLIK_JSON:') === 0) {
                $detailArray = json_decode(substr($msg, 13), true);
                return redirect()->route('admin.jadwal.index')
                    ->with('error_banyak', $detailArray);
            }

            // Jika error biasa (misal database / relasi kosong)
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Gagal: ' . $msg);
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
        $kelasList = Kelas::orderBy('kelas', 'asc')->get();

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
        return view('admin.jadwal.print', compact('batch', 'kelas', 'days', 'slots', 'jadwalMatrix'));
    }
}
