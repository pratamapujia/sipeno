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
        } catch (\Throwable $e) {
            return redirect()->route('admin.jadwal.index')->with('error', 'GAGAL Fatal: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ' Baris: ' . $e->getLine() . ')');
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
        $selectedKelasId = $request->get('kelas_id') ?? ($kelasList->first()->id ?? null);
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = SlotJam::orderBy('slot_number', 'asc')->get();

        $schedules = Jadwal::with(['guru', 'mapel', 'slotJam'])
            ->where('schedule_batch_id', $id)
            ->where('kelas_id', $selectedKelasId)
            ->get();

        $jadwalMatrix = [];
        $guruTracker = []; // Penampung nama guru terpisah

        foreach ($schedules as $s) {
            $slot = $s->time_slot_id;
            $hari = $s->day;
            $namaGuru = $s->guru->nama_guru;

            if (isset($jadwalMatrix[$slot][$hari])) {
                // Cek apakah nama guru belum ada di dalam tracker jam tersebut
                if (!in_array($namaGuru, $guruTracker[$slot][$hari])) {
                    $guruTracker[$slot][$hari][] = $namaGuru;

                    $existingSchedule = $jadwalMatrix[$slot][$hari];
                    $clonedGuru = clone $existingSchedule->guru;
                    $clonedGuru->nama_guru = implode(' & ', $guruTracker[$slot][$hari]);
                    $existingSchedule->setRelation('guru', $clonedGuru);
                }
            } else {
                // Inisialisasi awal saat jadwal pertama kali masuk kotak
                $guruTracker[$slot][$hari] = [$namaGuru];

                $clonedGuru = clone $s->guru;
                $s->setRelation('guru', $clonedGuru);
                $jadwalMatrix[$slot][$hari] = $s;
            }
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
        $guruTracker = [];

        foreach ($schedules as $s) {
            $slot = $s->time_slot_id;
            $hari = $s->day;
            $namaGuru = $s->guru->nama_guru;

            if (isset($jadwalMatrix[$slot][$hari])) {
                if (!in_array($namaGuru, $guruTracker[$slot][$hari])) {
                    $guruTracker[$slot][$hari][] = $namaGuru;

                    $existingSchedule = $jadwalMatrix[$slot][$hari];
                    $clonedGuru = clone $existingSchedule->guru;
                    $clonedGuru->nama_guru = implode(' & ', $guruTracker[$slot][$hari]);
                    $existingSchedule->setRelation('guru', $clonedGuru);
                }
            } else {
                $guruTracker[$slot][$hari] = [$namaGuru];

                $clonedGuru = clone $s->guru;
                $s->setRelation('guru', $clonedGuru);
                $jadwalMatrix[$slot][$hari] = $s;
            }
        }

        return view('admin.jadwal.print', compact('batch', 'kelas', 'days', 'slots', 'jadwalMatrix', 'academicYears'));
    }

    public function printAll($id)
    {
        $batch = BatchJadwal::findOrFail($id);
        $academicYears = TahunAjaran::where('is_active', true)->first();
        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = SlotJam::orderBy('slot_number', 'asc')->get();

        $allSchedules = Jadwal::with(['guru', 'mapel', 'slotJam'])
            ->where('schedule_batch_id', $id)
            ->get()
            ->groupBy('kelas_id');

        $kelasMatrix = [];
        foreach ($kelasList as $kelas) {
            $schedulesForKelas = $allSchedules->get($kelas->id) ?? collect();

            $matrix = [];
            $guruTracker = []; // Pastikan tracker direset untuk setiap kelas

            foreach ($schedulesForKelas as $s) {
                $slot = $s->time_slot_id;
                $hari = $s->day;
                $namaGuru = $s->guru->nama_guru;

                if (isset($matrix[$slot][$hari])) {
                    if (!in_array($namaGuru, $guruTracker[$slot][$hari])) {
                        $guruTracker[$slot][$hari][] = $namaGuru;

                        $existingSchedule = $matrix[$slot][$hari];
                        $clonedGuru = clone $existingSchedule->guru;
                        $clonedGuru->nama_guru = implode(' & ', $guruTracker[$slot][$hari]);
                        $existingSchedule->setRelation('guru', $clonedGuru);
                    }
                } else {
                    $guruTracker[$slot][$hari] = [$namaGuru];

                    $clonedGuru = clone $s->guru;
                    $s->setRelation('guru', $clonedGuru);
                    $matrix[$slot][$hari] = $s;
                }
            }
            $kelasMatrix[$kelas->id] = $matrix;
        }

        return view('admin.jadwal.printAll', compact('batch', 'kelasList', 'days', 'slots', 'kelasMatrix', 'academicYears'));
    }

    public function updateManual(Request $request, $id)
    {
        $request->validate([
            'day' => 'required',
            'time_slot_id' => 'required',
        ]);

        $jadwal = Jadwal::with('mapel')->findOrFail($id);
        $targetSlot = SlotJam::findOrFail($request->time_slot_id);
        $slotNumber = $targetSlot->slot_number;

        // Cari semua rekan guru Team Teaching pada jam asli
        $jadwalPartner = Jadwal::where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('day', $jadwal->getOriginal('day'))
            ->where('time_slot_id', $jadwal->getOriginal('time_slot_id'))
            ->where('mapel_id', $jadwal->mapel_id)
            ->get();

        // Validasi Piket dan Bentrok Guru (harus dicek untuk semua guru partner)
        foreach ($jadwalPartner as $jp) {
            if (GuruPiket::where('tahun_ajaran_id', $jadwal->academic_year_id)->where('guru_id', $jp->guru_id)->where('hari', $request->day)->exists()) {
                return redirect()->back()->with('error', "Gagal! Salah satu guru berstatus PIKET pada hari {$request->day}.");
            }

            if (Jadwal::where('schedule_batch_id', $jadwal->schedule_batch_id)->where('day', $request->day)->where('time_slot_id', $request->time_slot_id)->where('guru_id', $jp->guru_id)->where('id', '!=', $jp->id)->exists()) {
                return redirect()->back()->with('error', "Gagal! Salah satu guru sudah mengajar di kelas lain pada jam tujuan.");
            }
        }

        // Validasi Bentrok Kelas (Kecuali yang menempati adalah mapel praktikum yang sama)
        $cekKelasBentrok = Jadwal::with('mapel')->where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('day', $request->day)
            ->where('time_slot_id', $request->time_slot_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->whereNotIn('id', $jadwalPartner->pluck('id'))
            ->get();

        if ($cekKelasBentrok->isNotEmpty()) {
            $tipePindah = $jadwal->mapel->type ?? 'teori';
            $isTeamTeaching = true;
            foreach ($cekKelasBentrok as $jkb) {
                $tipeTujuan = $jkb->mapel->type ?? 'teori';
                if ($jkb->mapel_id != $jadwal->mapel_id || $tipeTujuan != 'praktikum' || $tipePindah != 'praktikum') {
                    $isTeamTeaching = false;
                    break;
                }
            }
            if (!$isTeamTeaching) {
                return redirect()->back()->with('error', 'Gagal memindah! Sudah ada mata pelajaran lain di kelas ini pada jam tujuan.');
            }
        }

        if ($request->day === 'Jumat' && (($slotNumber >= 7 && $slotNumber <= 10) || $slotNumber == 17)) {
            return redirect()->back()->with('error', 'Gagal memindah! Menabrak Zona Kosong di hari Jumat.');
        }

        $tipeMapel = $jadwal->mapel->type ?? 'teori';
        if ($tipeMapel === 'teori' && $slotNumber > 10) {
            return redirect()->back()->with('error', 'Gagal memindah! Mapel Teori wajib di Shift Pagi (Jam 1 s/d 10).');
        }

        $jadwalHariTujuan = Jadwal::with('slotJam')
            ->where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('day', $request->day)
            ->where('kelas_id', $jadwal->kelas_id)
            ->whereNotIn('id', $jadwalPartner->pluck('id'))
            ->get();

        $adaPagi = $slotNumber <= 10;
        $adaSiang = $slotNumber >= 11;

        foreach ($jadwalHariTujuan as $jht) {
            if ($jht->slotJam) {
                if ($jht->slotJam->slot_number <= 10) $adaPagi = true;
                if ($jht->slotJam->slot_number >= 11) $adaSiang = true;
            }
        }

        if ($adaPagi && $adaSiang) {
            return redirect()->back()->with('error', 'Gagal memindah! Kelas akan masuk Pagi dan Siang sekaligus.');
        }

        // Eksekusi Pindah Massal Rekan Co-Teaching
        foreach ($jadwalPartner as $jp) {
            $jp->update([
                'day' => $request->day,
                'time_slot_id' => $request->time_slot_id,
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal berhasil dipindahkan secara manual.');
    }

    public function bulkMoveJadwal(Request $request)
    {
        $request->validate([
            'jadwal_ids' => 'required|string',
            'day' => 'required',
            'start_time_slot_id' => 'required',
        ]);

        $jadwalIds = explode(',', $request->jadwal_ids);
        $count = count($jadwalIds);

        $schedules = Jadwal::with('mapel', 'slotJam')
            ->join('time_slots', 'schedules.time_slot_id', '=', 'time_slots.id')
            ->whereIn('schedules.id', $jadwalIds)
            ->orderBy('time_slots.slot_number', 'asc')
            ->select('schedules.*')
            ->get();

        if ($schedules->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal yang dipilih.');
        }

        $kelasId = $schedules->first()->kelas_id;
        $academicYearId = $schedules->first()->academic_year_id;
        $batchId = $schedules->first()->schedule_batch_id;
        $tipeMapel = $schedules->first()->mapel->type ?? 'teori';
        $startSlot = SlotJam::findOrFail($request->start_time_slot_id);

        $targetSlots = SlotJam::where('is_istirahat', false)->where('slot_number', '>=', $startSlot->slot_number)->orderBy('slot_number', 'asc')->take($count)->get();

        if ($targetSlots->count() < $count) {
            return redirect()->back()->with('error', 'Slot jam tidak mencukupi untuk memindahkan ' . $count . ' JP.');
        }

        $targetSlotIds = $targetSlots->pluck('id')->toArray();
        $targetSlotNumbers = $targetSlots->pluck('slot_number')->toArray();

        // Kumpulkan semua jadwal beserta partner Team Teachingnya
        $allPartnersToMove = collect();
        foreach ($schedules as $jadwal) {
            $partners = Jadwal::where('schedule_batch_id', $batchId)
                ->where('kelas_id', $kelasId)
                ->where('day', $jadwal->day)
                ->where('time_slot_id', $jadwal->time_slot_id)
                ->where('mapel_id', $jadwal->mapel_id)
                ->get();
            $allPartnersToMove = $allPartnersToMove->merge($partners);
        }

        foreach ($allPartnersToMove as $jp) {
            if (GuruPiket::where('tahun_ajaran_id', $academicYearId)->where('guru_id', $jp->guru_id)->where('hari', $request->day)->exists()) {
                return redirect()->back()->with('error', 'Gagal memindah! Salah satu guru berstatus PIKET pada hari ' . $request->day . '.');
            }
        }

        // Cek Bentrok Kelas dengan pengecualian praktikum yang sama
        $jadwalKelasBentrokBulk = Jadwal::with('mapel')
            ->where('schedule_batch_id', $batchId)
            ->where('day', $request->day)
            ->whereIn('time_slot_id', $targetSlotIds)
            ->where('kelas_id', $kelasId)
            ->whereNotIn('id', $allPartnersToMove->pluck('id'))
            ->get();

        if ($jadwalKelasBentrokBulk->isNotEmpty()) {
            $isTeamTeaching = true;
            foreach ($jadwalKelasBentrokBulk as $jkb) {
                $tipeMapelTujuan = $jkb->mapel->type ?? 'teori';
                if ($jkb->mapel_id != $schedules->first()->mapel_id || $tipeMapelTujuan != 'praktikum' || $tipeMapel != 'praktikum') {
                    $isTeamTeaching = false;
                    break;
                }
            }
            if (!$isTeamTeaching) {
                return redirect()->back()->with('error', 'Gagal memindah! Sudah ada mata pelajaran lain di kelas tersebut pada rentang jam tujuan.');
            }
        }

        foreach ($targetSlotNumbers as $slotNumber) {
            if ($request->day === 'Jumat' && (($slotNumber >= 7 && $slotNumber <= 10) || $slotNumber == 17)) {
                return redirect()->back()->with('error', 'Gagal memindah! Bertabrakan dengan Zona Kosong Jumat.');
            }
            if ($tipeMapel === 'teori' && $slotNumber > 10) {
                return redirect()->back()->with('error', 'Gagal memindah! Mapel Teori wajib di Shift Pagi.');
            }
        }

        // Eksekusi Pindah
        foreach ($schedules as $index => $jadwal) {
            $jadwalPartner = Jadwal::where('schedule_batch_id', $batchId)
                ->where('kelas_id', $kelasId)
                ->where('day', $jadwal->day)
                ->where('time_slot_id', $jadwal->time_slot_id)
                ->where('mapel_id', $jadwal->mapel_id)
                ->get();

            foreach ($jadwalPartner as $jp) {
                $jp->update([
                    'day' => $request->day,
                    'time_slot_id' => $targetSlotIds[$index]
                ]);
            }
        }

        return redirect()->back()->with('success', $count . ' Jam Pelajaran (berserta semua guru timnya) berhasil dipindahkan.');
    }
}
