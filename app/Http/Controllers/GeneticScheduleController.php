<?php

namespace App\Http\Controllers;

use App\Models\BatchJadwal;
use App\Models\GuruPiket;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\SlotJam;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Services\GeneticScheduleService;
use Illuminate\Http\Request;

class GeneticScheduleController extends Controller
{
    protected $geneticService;

    public function __construct(GeneticScheduleService $geneticService)
    {
        $this->geneticService = $geneticService;
    }

    public function index()
    {
        $batches = BatchJadwal::orderBy('created_at', 'desc')->get();
        $academicYears = TahunAjaran::where('is_active', true)->first();
        return view('admin.jadwal.index', compact('batches', 'academicYears'));
    }

    public function generate(Request $request)
    {
        $activeYear = TahunAjaran::where('is_active', true)->first();
        if (!$activeYear) return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran Aktif!');

        try {
            $result = $this->geneticService->generate($activeYear->id);
            if ($result['status'] === 'perfect') return redirect()->route('admin.jadwal.index')->with('success', "Jadwal SEMPURNA berhasil dibuat tanpa ada bentrok sama sekali.");
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dibuat sebagai DRAFT')->with('warning_banyak', $result['conflicts']);
        } catch (\Throwable $e) {
            return redirect()->route('admin.jadwal.index')->with('error', 'GAGAL Fatal: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ' Baris: ' . $e->getLine() . ')');
        }
    }

    public function activate($id)
    {
        $batch = BatchJadwal::findOrFail($id);
        BatchJadwal::where('status', 'active')->update(['status' => 'draft']);
        $batch->update(['status' => 'active']);
        return redirect()->route('admin.jadwal.index')->with('success', "Jadwal '{$batch->name}' sekarang resmi aktif digunakan.");
    }

    public function destroy($id)
    {
        $batch = BatchJadwal::findOrFail($id);
        $batch->delete();
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

        return view('admin.jadwal.show', compact('batch', 'kelasList', 'selectedKelasId', 'days', 'slots', 'jadwalMatrix'));
    }

    public function print($id, Request $request)
    {
        $batch = BatchJadwal::findOrFail($id);
        $academicYears = TahunAjaran::where('is_active', true)->first();
        $kelas = Kelas::findOrFail($request->get('kelas_id'));
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = SlotJam::orderBy('slot_number', 'asc')->get();
        $schedules = Jadwal::with(['guru', 'mapel', 'slotJam'])->where('schedule_batch_id', $id)->where('kelas_id', $kelas->id)->get();
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
        $allSchedules = Jadwal::with(['guru', 'mapel', 'slotJam'])->where('schedule_batch_id', $id)->get()->groupBy('kelas_id');
        $kelasMatrix = [];

        foreach ($kelasList as $kelas) {
            $schedulesForKelas = $allSchedules->get($kelas->id) ?? collect();
            $matrix = [];
            $guruTracker = [];
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
        $request->validate(['day' => 'required', 'time_slot_id' => 'required']);
        $jadwal = Jadwal::with('mapel')->findOrFail($id);
        $targetSlot = SlotJam::findOrFail($request->time_slot_id);
        $slotNumber = $targetSlot->slot_number;

        $jadwalPartner = Jadwal::where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('kelas_id', $jadwal->kelas_id)->where('day', $jadwal->getOriginal('day'))
            ->where('time_slot_id', $jadwal->getOriginal('time_slot_id'))->where('mapel_id', $jadwal->mapel_id)->get();

        foreach ($jadwalPartner as $jp) {
            if (GuruPiket::where('tahun_ajaran_id', $jadwal->academic_year_id)->where('guru_id', $jp->guru_id)->where('hari', $request->day)->exists()) {
                return redirect()->back()->with('error', "Gagal! Salah satu guru berstatus PIKET pada hari {$request->day}.");
            }
            if (Jadwal::where('schedule_batch_id', $jadwal->schedule_batch_id)->where('day', $request->day)->where('time_slot_id', $request->time_slot_id)->where('guru_id', $jp->guru_id)->where('id', '!=', $jp->id)->exists()) {
                return redirect()->back()->with('error', "Gagal! Salah satu guru sudah mengajar di kelas lain pada jam tujuan.");
            }
        }

        $cekKelasBentrok = Jadwal::with('mapel')->where('schedule_batch_id', $jadwal->schedule_batch_id)
            ->where('day', $request->day)->where('time_slot_id', $request->time_slot_id)
            ->where('kelas_id', $jadwal->kelas_id)->whereNotIn('id', $jadwalPartner->pluck('id'))->get();

        if ($cekKelasBentrok->isNotEmpty()) {
            $isTeamTeaching = true;
            foreach ($cekKelasBentrok as $jkb) {
                if ($jkb->mapel_id != $jadwal->mapel_id || ($jkb->mapel->type ?? 'teori') != 'praktikum' || ($jadwal->mapel->type ?? 'teori') != 'praktikum') {
                    $isTeamTeaching = false;
                    break;
                }
            }
            if (!$isTeamTeaching) return redirect()->back()->with('error', 'Gagal memindah! Sudah ada mata pelajaran lain di kelas ini pada jam tujuan.');
        }

        if ($request->day === 'Jumat' && (($slotNumber >= 7 && $slotNumber <= 10) || $slotNumber == 17)) return redirect()->back()->with('error', 'Gagal memindah! Menabrak Zona Kosong Jumat.');
        if (($jadwal->mapel->type ?? 'teori') === 'teori' && $slotNumber > 10) return redirect()->back()->with('error', 'Gagal memindah! Mapel Teori wajib di Shift Pagi.');

        $jadwalHariTujuan = Jadwal::with('slotJam')->where('schedule_batch_id', $jadwal->schedule_batch_id)->where('day', $request->day)->where('kelas_id', $jadwal->kelas_id)->whereNotIn('id', $jadwalPartner->pluck('id'))->get();
        $adaPagi = $slotNumber <= 10;
        $adaSiang = $slotNumber >= 11;
        foreach ($jadwalHariTujuan as $jht) {
            if ($jht->slotJam && $jht->slotJam->slot_number <= 10) $adaPagi = true;
            if ($jht->slotJam && $jht->slotJam->slot_number >= 11) $adaSiang = true;
        }
        if ($adaPagi && $adaSiang) return redirect()->back()->with('error', 'Gagal memindah! Kelas akan masuk Pagi dan Siang sekaligus.');

        foreach ($jadwalPartner as $jp) {
            $jp->update(['day' => $request->day, 'time_slot_id' => $request->time_slot_id]);
        }
        return redirect()->back()->with('success', 'Jadwal berhasil dipindahkan secara manual.');
    }

    public function bulkMoveJadwal(Request $request)
    {
        $request->validate(['jadwal_ids' => 'required|string', 'day' => 'required', 'start_time_slot_id' => 'required']);
        $jadwalIds = explode(',', $request->jadwal_ids);
        $count = count($jadwalIds);
        $schedules = Jadwal::with('mapel', 'slotJam')->join('time_slots', 'schedules.time_slot_id', '=', 'time_slots.id')->whereIn('schedules.id', $jadwalIds)->orderBy('time_slots.slot_number', 'asc')->select('schedules.*')->get();

        if ($schedules->isEmpty()) return redirect()->back()->with('error', 'Tidak ada jadwal yang dipilih.');

        $kelasId = $schedules->first()->kelas_id;
        $academicYearId = $schedules->first()->academic_year_id;
        $batchId = $schedules->first()->schedule_batch_id;
        $tipeMapel = $schedules->first()->mapel->type ?? 'teori';
        $startSlot = SlotJam::findOrFail($request->start_time_slot_id);

        $targetSlots = SlotJam::where('is_istirahat', false)->where('slot_number', '>=', $startSlot->slot_number)->orderBy('slot_number', 'asc')->take($count)->get();
        if ($targetSlots->count() < $count) return redirect()->back()->with('error', 'Slot jam tidak mencukupi untuk memindahkan ' . $count . ' JP.');

        $targetSlotIds = $targetSlots->pluck('id')->toArray();
        $targetSlotNumbers = $targetSlots->pluck('slot_number')->toArray();
        $allPartnersToMove = collect();

        foreach ($schedules as $jadwal) {
            $partners = Jadwal::where('schedule_batch_id', $batchId)->where('kelas_id', $kelasId)->where('day', $jadwal->day)->where('time_slot_id', $jadwal->time_slot_id)->where('mapel_id', $jadwal->mapel_id)->get();
            $allPartnersToMove = $allPartnersToMove->merge($partners);
        }

        foreach ($allPartnersToMove as $jp) {
            if (GuruPiket::where('tahun_ajaran_id', $academicYearId)->where('guru_id', $jp->guru_id)->where('hari', $request->day)->exists()) return redirect()->back()->with('error', 'Gagal memindah! Salah satu guru berstatus PIKET.');
        }

        $jadwalKelasBentrokBulk = Jadwal::with('mapel')->where('schedule_batch_id', $batchId)->where('day', $request->day)->whereIn('time_slot_id', $targetSlotIds)->where('kelas_id', $kelasId)->whereNotIn('id', $allPartnersToMove->pluck('id'))->get();
        if ($jadwalKelasBentrokBulk->isNotEmpty()) {
            $isTeamTeaching = true;
            foreach ($jadwalKelasBentrokBulk as $jkb) {
                if ($jkb->mapel_id != $schedules->first()->mapel_id || ($jkb->mapel->type ?? 'teori') != 'praktikum' || $tipeMapel != 'praktikum') {
                    $isTeamTeaching = false;
                    break;
                }
            }
            if (!$isTeamTeaching) return redirect()->back()->with('error', 'Gagal memindah! Sudah ada mata pelajaran lain di kelas pada jam tujuan.');
        }

        foreach ($targetSlotNumbers as $slotNumber) {
            if ($request->day === 'Jumat' && (($slotNumber >= 7 && $slotNumber <= 10) || $slotNumber == 17)) return redirect()->back()->with('error', 'Gagal memindah! Menabrak Zona Kosong Jumat.');
            if ($tipeMapel === 'teori' && $slotNumber > 10) return redirect()->back()->with('error', 'Gagal memindah! Mapel Teori wajib di Shift Pagi.');
        }

        foreach ($schedules as $index => $jadwal) {
            $jadwalPartner = Jadwal::where('schedule_batch_id', $batchId)->where('kelas_id', $kelasId)->where('day', $jadwal->day)->where('time_slot_id', $jadwal->time_slot_id)->where('mapel_id', $jadwal->mapel_id)->get();
            foreach ($jadwalPartner as $jp) {
                $jp->update(['day' => $request->day, 'time_slot_id' => $targetSlotIds[$index]]);
            }
        }
        return redirect()->back()->with('success', $count . ' Jam Pelajaran berhasil dipindahkan.');
    }

    // ==========================================
    // FITUR CLIPBOARD
    // ==========================================
    public function moveToClipboard(Request $request)
    {
        $request->validate(['jadwal_id' => 'required']);
        $jadwal = Jadwal::findOrFail($request->jadwal_id);
        $clipboard = session()->get('jadwal_clipboard', []);
        $clipboard[] = $jadwal->toArray();
        session()->put('jadwal_clipboard', $clipboard);
        $jadwal->delete();
        return redirect()->back()->with('success', '1 Jadwal telah dipindahkan ke Clipboard.');
    }

    public function moveToClipboardBulk(Request $request)
    {
        $request->validate(['jadwal_ids' => 'required']);
        $ids = explode(',', $request->jadwal_ids);
        $clipboard = session()->get('jadwal_clipboard', []);
        $schedules = Jadwal::whereIn('id', $ids)->get();

        foreach ($schedules as $jadwal) {
            $clipboard[] = $jadwal->toArray();
            $jadwal->delete();
        }
        session()->put('jadwal_clipboard', $clipboard);
        return redirect()->back()->with('success', count($schedules) . ' JP berhasil diamankan ke Clipboard.');
    }

    public function restoreFromClipboard(Request $request)
    {
        $index = $request->index;
        $clipboard = session()->get('jadwal_clipboard', []);

        if (isset($clipboard[$index])) {
            $data = $clipboard[$index];
            $bentrok = Jadwal::where('schedule_batch_id', $data['schedule_batch_id'])->where('day', $request->day)->where('time_slot_id', $request->time_slot_id)->where('kelas_id', $data['kelas_id'])->exists();
            if ($bentrok) return redirect()->back()->with('error', 'Gagal dikembalikan! Jam tujuan sudah terisi jadwal lain.');

            Jadwal::create([
                'schedule_batch_id' => $data['schedule_batch_id'],
                'guru_id'           => $data['guru_id'],
                'kelas_id'          => $data['kelas_id'],
                'mapel_id'          => $data['mapel_id'],
                'day'               => $request->day,
                'time_slot_id'      => $request->time_slot_id,
                'academic_year_id'  => $data['academic_year_id'],
            ]);
            unset($clipboard[$index]);
            session()->put('jadwal_clipboard', array_values($clipboard));
            return redirect()->back()->with('success', 'Jadwal berhasil dikembalikan dari Clipboard.');
        }
        return redirect()->back()->with('error', 'Data tidak ditemukan di Clipboard.');
    }

    // METHOD BARU: MEMULIHKAN JADWAL DARI CLIPBOARD SECARA MASSAL (BULK)
    public function restoreFromClipboardBulk(Request $request)
    {
        $request->validate([
            'clipboard_indexes' => 'required|string',
            'day' => 'required',
            'start_time_slot_id' => 'required',
        ]);

        $indexes = explode(',', $request->clipboard_indexes);
        $clipboard = session()->get('jadwal_clipboard', []);

        $itemsToRestore = [];
        $gurus = [];
        foreach ($indexes as $idx) {
            if (isset($clipboard[$idx])) {
                $itemsToRestore[$idx] = $clipboard[$idx];
                $gurus[$clipboard[$idx]['guru_id']] = true; // Catat unik guru untuk hitung Team Teaching
            }
        }

        if (empty($itemsToRestore)) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $firstItem = reset($itemsToRestore);
        $tipeMapel = Mapel::find($firstItem['mapel_id'])->type ?? 'teori';

        // Kalkulasi kebutuhan slot. (Jika ada 2 guru memegang 4 item, berarti panjang slot yg dibutuhkan adalah 2)
        $bebanJam = count($itemsToRestore) / count($gurus);

        $startSlot = SlotJam::findOrFail($request->start_time_slot_id);
        $targetSlots = SlotJam::where('is_istirahat', false)
            ->where('slot_number', '>=', $startSlot->slot_number)
            ->orderBy('slot_number', 'asc')
            ->take($bebanJam)
            ->get();

        if ($targetSlots->count() < $bebanJam) return redirect()->back()->with('error', 'Slot jam tersisa tidak mencukupi.');

        $targetSlotIds = $targetSlots->pluck('id')->toArray();
        $targetSlotNumbers = $targetSlots->pluck('slot_number')->toArray();

        // Cek Zona Terlarang Shift & Jumat
        foreach ($targetSlotNumbers as $slotNumber) {
            if ($request->day === 'Jumat' && (($slotNumber >= 7 && $slotNumber <= 10) || $slotNumber == 17)) {
                return redirect()->back()->with('error', 'Gagal memulihkan! Bertabrakan dengan Zona Kosong Jumat.');
            }
            if ($tipeMapel === 'teori' && $slotNumber > 10) {
                return redirect()->back()->with('error', 'Gagal memulihkan! Mapel Teori wajib di Shift Pagi.');
            }
        }

        // Cek Guru Piket
        foreach (array_keys($gurus) as $guruId) {
            if (GuruPiket::where('tahun_ajaran_id', $firstItem['academic_year_id'])->where('guru_id', $guruId)->where('hari', $request->day)->exists()) {
                return redirect()->back()->with('error', 'Gagal! Salah satu guru berstatus PIKET pada hari tujuan.');
            }
        }

        // Kelompokkan item per guru untuk dicek bentroknya masing-masing
        $guruAssignments = [];
        foreach ($itemsToRestore as $idx => $data) {
            $guruAssignments[$data['guru_id']][] = $data;
        }

        foreach ($guruAssignments as $gid => $items) {
            if (count($items) != count($targetSlotIds)) {
                return redirect()->back()->with('error', 'Peringatan: Pilihan tidak seimbang! Jika ini Team Teaching, pastikan Anda mencentang semua JP milik kedua guru secara adil.');
            }

            foreach ($items as $i => $data) {
                $targetSlotId = $targetSlotIds[$i];

                // Bentrok Guru
                if (Jadwal::where('schedule_batch_id', $data['schedule_batch_id'])->where('day', $request->day)->where('time_slot_id', $targetSlotId)->where('guru_id', $data['guru_id'])->exists()) {
                    return redirect()->back()->with('error', 'Gagal! Guru sudah mengajar di kelas/jam lain pada jam tujuan.');
                }

                // Bentrok Kelas (Kecuali partner team teaching yang sama)
                $cekKelasBentrok = Jadwal::with('mapel')->where('schedule_batch_id', $data['schedule_batch_id'])
                    ->where('day', $request->day)
                    ->where('time_slot_id', $targetSlotId)
                    ->where('kelas_id', $data['kelas_id'])
                    ->get();

                if ($cekKelasBentrok->isNotEmpty()) {
                    $isTeamTeaching = true;
                    foreach ($cekKelasBentrok as $jkb) {
                        $tipeTujuan = $jkb->mapel->type ?? 'teori';
                        if ($jkb->mapel_id != $data['mapel_id'] || $tipeTujuan != 'praktikum' || $tipeMapel != 'praktikum') {
                            $isTeamTeaching = false;
                            break;
                        }
                    }
                    if (!$isTeamTeaching) return redirect()->back()->with('error', 'Gagal! Sudah ada mata pelajaran lain di kelas ini pada jam tujuan.');
                }
            }
        }

        // EKSEKUSI PEMULIHAN
        foreach ($guruAssignments as $gid => $items) {
            foreach ($items as $i => $data) {
                Jadwal::create([
                    'schedule_batch_id' => $data['schedule_batch_id'],
                    'guru_id'           => $data['guru_id'],
                    'kelas_id'          => $data['kelas_id'],
                    'mapel_id'          => $data['mapel_id'],
                    'day'               => $request->day,
                    'time_slot_id'      => $targetSlotIds[$i],
                    'academic_year_id'  => $data['academic_year_id'],
                ]);
            }
        }

        // Hapus item-item ini dari session
        foreach ($indexes as $idx) {
            unset($clipboard[$idx]);
        }

        session()->put('jadwal_clipboard', array_values($clipboard));
        return redirect()->back()->with('success', (count($indexes) / count($gurus)) . ' Jam Pelajaran berhasil dipulihkan secara Massal.');
    }
}
