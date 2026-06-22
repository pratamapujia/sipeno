<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\TahunAjaran;
use App\Services\GeneticScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('akses-admin');

        $batches = BatchJadwal::orderBy('created_at', 'desc')->get();
        $academicYears = TahunAjaran::where('is_active', true)->get();

        return view('admin.schedule.index', compact('batches', 'academicYears'));
    }

    // Memicu proses pembuatan jadwal otomatis
    public function generate(Request $request)
    {
        Gate::authorize('akses-admin');

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id'
        ]);

        try {
            // Jalankan algoritma lewat service
            $batch = $this->geneticService->generate($request->academic_year_id);

            return redirect()->route('admin.schedule.index')
                ->with('success', "Jadwal berhasil digenerate sebagai draft dengan skor penalti: {$batch->final_fitness_score}");
        } catch (\Exception $e) {
            return redirect()->route('admin.schedule.index')
                ->with('error', 'Gagal memproses jadwal: ' . $e->getMessage());
        }
    }

    // Menyetujui draft jadwal agar aktif digunakan sekolah
    public function activate($id)
    {
        Gate::authorize('akses-admin');

        $batch = BatchJadwal::findOrFail($id);

        // Matikan batch yang aktif sebelumnya, lalu aktifkan yang baru
        BatchJadwal::where('status', 'active')->update(['status' => 'draft']);
        $batch->update(['status' => 'active']);

        return redirect()->route('admin.schedule.index')
            ->with('success', "Jadwal '{$batch->name}' sekarang resmi aktif digunakan.");
    }

    // Menghapus draft simulasi jadwal
    public function destroy($id)
    {
        Gate::authorize('akses-admin');

        $batch = BatchJadwal::findOrFail($id);
        $batch->delete(); // Otomatis menghapus baris tabel `schedules` terkait karena onDelete('cascade')

        return redirect()->route('admin.schedule.index')
            ->with('success', 'Draft jadwal berhasil dihapus.');
    }
}
