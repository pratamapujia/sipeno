@extends('layouts.main')

@section('title')
  <title>Detail Jadwal Kelas</title>
@endsection

@section('main')
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>
  <div class="flash-data" data-gagal="{{ Session::get('error') }}"></div>

  <div class="page-heading">
    <div class="page-title mb-3">
      <div class="row align-items-center">
        <div class="col-12 col-md-7">
          <h3>Pratinjau Jadwal Kelas</h3>
          <p class="text-muted">Simulasi: <b class="text-primary">{{ $batch->nama }}</b>
            @if ($batch->status == 'active')
              <span class="badge bg-success ms-2"><i class="fas fa-lock me-1"></i> AKTIF</span>
            @else
              <span class="badge bg-secondary ms-2"><i class="fas fa-edit me-1"></i> DRAFT</span>
            @endif
          </p>
        </div>
        <div class="col-12 col-md-5 text-md-end">
          <a href="{{ route('admin.jadwal.index') }}" class="btn btn-light-secondary icon icon-left me-2">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          <a href="{{ route('admin.jadwal.printAll', $batch->id) }}" target="_blank" class="btn btn-success icon icon-left shadow me-2">
            <i class="fas fa-copy"></i> Cetak Semua Kelas
          </a>
        </div>
      </div>
    </div>

    <section class="section position-relative">
      <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
          <div class="row align-items-center">
            <div class="col-12 col-md-8">
              <form action="{{ route('admin.jadwal.show', $batch->id) }}" method="GET" id="form-filter">
                <div class="d-flex align-items-center">
                  <label for="kelas_id" class="form-label font-bold mb-0 me-3">Tampilkan Jadwal Untuk Kelas:</label>
                  <select name="kelas_id" id="kelas_id" class="form-select w-auto min-w-200" onchange="document.getElementById('form-filter').submit();">
                    @foreach ($kelasList as $kelas)
                      <option value="{{ $kelas->id }}" {{ $selectedKelasId == $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->nama_kelas }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </form>
            </div>
            <div class="col-12 col-md-4">
              @if ($selectedKelasId)
                <a href="{{ route('admin.jadwal.print', ['id' => $batch->id, 'kelas_id' => $selectedKelasId]) }}" target="_blank" class="btn btn-primary icon icon-left shadow float-end">
                  <i class="fas fa-print"></i> Cetak Jadwal
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>

      @php
        $shiftGroups = [
            'Pagi' => [
                'title' => 'Shift Pagi (Slot 1 - 10)',
                'icon' => 'fas fa-sun text-warning',
                'data' => $slots->where('slot_number', '<=', 10),
            ],
            'Siang' => [
                'title' => 'Shift Siang (Slot 11 - 17)',
                'icon' => 'fas fa-cloud-sun text-info',
                'data' => $slots->where('slot_number', '>', 10),
            ],
        ];

        $waktuSeninPagi = [
            1 => '07:30 - 08:00',
            2 => '08:00 - 08:30',
            3 => '08:30 - 09:00',
            4 => '09:00 - 09:30',
            5 => '10:00 - 10:35',
            6 => '10:35 - 11:10',
            7 => '11:10 - 11:45',
            8 => '12:15 - 12:50',
            9 => '12:50 - 13:25',
            10 => '13:25 - 14:00',
        ];
        $waktuJumatPagi = [1 => '07:30 - 08:00', 2 => '08:00 - 08:30', 3 => '08:30 - 09:00', 4 => '09:30 - 10:00', 5 => '10:00 - 10:30', 6 => '10:30 - 11:00'];
        $waktuJumatSiang = [11 => '13:00 - 13:30', 12 => '13:30 - 14:00', 13 => '14:00 - 14:30', 14 => '15:00 - 15:30', 15 => '15:30 - 16:00', 16 => '16:00 - 16:30'];
      @endphp

      @foreach ($shiftGroups as $shiftKey => $shift)
        @if ($shift['data']->count() > 0)
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-light-secondary p-3 border-bottom">
              <h5 class="mb-0"><i class="{{ $shift['icon'] }} me-2"></i> {{ $shift['title'] }}</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th style="width: 15%;">Jam / Waktu</th>
                      @foreach ($days as $day)
                        <th style="width: 17%;">{{ $day }}</th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody>
                    @if ($shiftKey == 'Pagi')
                      <tr class="table-info">
                        <td><b class="text-nowrap text-dark">Jam ke-0</b></td>
                        @foreach ($days as $day)
                          @if ($day == 'Senin')
                            <td class="align-middle"><b class="text-primary" style="letter-spacing: 1px;"><i class="fas fa-flag me-1"></i> UPACARA</b></td>
                          @elseif ($day == 'Jumat')
                            <td class="align-middle"><b class="text-success" style="letter-spacing: 1px;"><i class="fas fa-praying-hands me-1"></i> ISTIGHOSAH</b></td>
                          @else
                            <td class="text-muted" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">-</td>
                          @endif
                        @endforeach
                      </tr>
                    @endif

                    @foreach ($shift['data'] as $slot)
                      <tr>
                        <td>
                          <b class="text-nowrap text-dark">Jam ke-{{ $slot->slot_number }}</b>
                        </td>

                        @foreach ($days as $day)
                          <td>
                            @if ($day == 'Jumat' && (($slot->slot_number >= 7 && $slot->slot_number <= 10) || $slot->slot_number == 17))
                              <div class="text-center text-muted p-3 border rounded" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">
                                <i class="fas fa-ban mb-1 d-block text-secondary"></i> <small>KOSONG</small>
                              </div>
                            @else
                              @if (isset($jadwalMatrix[$slot->id][$day]))
                                @php $s = $jadwalMatrix[$slot->id][$day]; @endphp

                                <div class="card mb-0 shadow-sm border position-relative">
                                  {{-- CHECKBOX UNTUK BULK EDIT (Hanya tampil jika status bukan active) --}}
                                  @if ($batch->status != 'active')
                                    <div class="position-absolute top-0 start-0 p-2">
                                      <input class="form-check-input select-jadwal border-primary" type="checkbox" value="{{ $s->id }}" data-guru="{{ $s->guru_id }}"
                                        data-mapel="{{ $s->mapel_id }}" style="cursor: pointer; transform: scale(1.2);">
                                    </div>
                                  @endif

                                  <div class="card-body p-2 text-center">
                                    @if ($day == 'Senin' && $slot->slot_number <= 10)
                                      <span class="badge bg-light-info text-dark border border-info mb-2 d-block"><i class="far fa-clock me-1"></i>
                                        {{ $waktuSeninPagi[$slot->slot_number] ?? '' }}</span>
                                    @elseif ($day == 'Jumat' && $slot->slot_number <= 6)
                                      <span class="badge bg-light-info text-dark border border-info mb-2 d-block"><i class="far fa-clock me-1"></i>
                                        {{ $waktuJumatPagi[$slot->slot_number] ?? '' }}</span>
                                    @elseif ($day == 'Jumat' && $slot->slot_number >= 11)
                                      <span class="badge bg-light-info text-dark border border-info mb-2 d-block"><i class="far fa-clock me-1"></i>
                                        {{ $waktuJumatSiang[$slot->slot_number] ?? '' }}</span>
                                    @else
                                      <span class="badge bg-light-info text-dark border border-info mb-2 d-block"><i class="far fa-clock me-1"></i>
                                        {{ substr($slot->start_time, 0, 5) . ' - ' . substr($slot->end_time, 0, 5) }}</span>
                                    @endif

                                    <span class="fw-bold d-block text-primary" style="margin-right: 15px;">{{ $s->mapel->nama_mapel }}</span>
                                    <small class="text-dark d-block">{{ ucwords(strtolower($s->guru->nama_guru)) }}</small>

                                    @if ($batch->status != 'active')
                                      <button class="btn btn-sm btn-light-secondary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}">
                                        <i class="fas fa-arrows-alt"></i> Pindah
                                      </button>
                                    @endif
                                  </div>
                                </div>

                                {{-- MODAL PINDAH MANUAL (TETAP ADA) --}}
                                @if ($batch->status != 'active')
                                  <div class="modal fade text-left" id="editModal{{ $s->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                      <div class="modal-content">
                                        <div class="modal-header bg-primary">
                                          <h5 class="modal-title white">Pindah Jadwal Manual</h5>
                                          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i data-feather="x"></i></button>
                                        </div>
                                        <form action="{{ route('admin.jadwal.updateManual', $s->id) }}" method="POST">
                                          @csrf
                                          @method('PUT')
                                          <div class="modal-body text-start">
                                            <div class="alert alert-info">Memindahkan <b>{{ $s->mapel->nama_mapel }}</b> ({{ $s->guru->nama_guru }}) dari <b>{{ $s->day }} Jam
                                                ke-{{ $s->slotJam->slot_number }}</b>.</div>
                                            <div class="form-group">
                                              <label class="fw-bold">Pindah ke Hari:</label>
                                              <select name="day" class="form-select" required>
                                                @foreach ($days as $d)
                                                  <option value="{{ $d }}" {{ $s->day == $d ? 'selected' : '' }}>{{ $d }}</option>
                                                @endforeach
                                              </select>
                                            </div>
                                            <div class="form-group mt-3">
                                              <label class="fw-bold">Pindah ke Jam Ke-:</label>
                                              <select name="time_slot_id" class="form-select" required>
                                                @foreach ($slots as $slotItem)
                                                  <option value="{{ $slotItem->id }}" {{ $s->time_slot_id == $slotItem->id ? 'selected' : '' }}>Jam ke-{{ $slotItem->slot_number }}
                                                    ({{ substr($slotItem->start_time, 0, 5) }} - {{ substr($slotItem->end_time, 0, 5) }})
                                                  </option>
                                                @endforeach
                                              </select>
                                            </div>
                                          </div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary ml-1">Simpan Perubahan</button>
                                          </div>
                                        </form>
                                      </div>
                                    </div>
                                  </div>
                                @endif
                              @else
                                <div class="text-center text-muted p-3">-Kosong-</div>
                              @endif
                            @endif
                          </td>
                        @endforeach
                      </tr>

                      {{-- AREA SISIPAN ISTIRAHAT --}}
                      @if ($slot->slot_number == 3)
                        <tr class="table-warning">
                          <td class="align-middle"><b class="text-dark"><i class="fas fa-coffee me-1"></i> ISTIRAHAT</b></td>
                          @foreach ($days as $day)
                            @if ($day == 'Jumat')
                              <td class="align-middle"><b class="text-dark" style="letter-spacing: 2px;">ISTIRAHAT JUMAT</b></td>
                            @else
                              <td class="text-muted" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">-</td>
                            @endif
                          @endforeach
                        </tr>
                      @endif
                      @if ($slot->slot_number == 4)
                        <tr class="table-warning">
                          <td class="align-middle"><b class="text-dark"><i class="fas fa-coffee me-1"></i> ISTIRAHAT</b></td>
                          @foreach ($days as $day)
                            @if ($day != 'Jumat')
                              <td class="align-middle"><b class="text-dark" style="letter-spacing: 2px;">ISTIRAHAT 1</b></td>
                            @else
                              <td class="text-muted" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">-</td>
                            @endif
                          @endforeach
                        </tr>
                      @endif
                      @if ($slot->slot_number == 7)
                        <tr class="table-warning">
                          <td class="align-middle"><b class="text-dark"><i class="fas fa-utensils me-1"></i> ISTIRAHAT</b></td>
                          @foreach ($days as $day)
                            @if ($day != 'Jumat')
                              <td class="align-middle"><b class="text-dark" style="letter-spacing: 2px;">ISTIRAHAT 2</b></td>
                            @else
                              <td class="text-muted" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">-</td>
                            @endif
                          @endforeach
                        </tr>
                      @endif
                      @if ($slot->slot_number == 13)
                        <tr class="table-warning">
                          <td class="align-middle"><b class="text-dark"><i class="fas fa-coffee me-1"></i> ISTIRAHAT</b></td>
                          @foreach ($days as $day)
                            @if ($day == 'Jumat')
                              <td class="align-middle"><b class="text-dark" style="letter-spacing: 2px;">ISTIRAHAT JUMAT</b></td>
                            @else
                              <td class="text-muted" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">-</td>
                            @endif
                          @endforeach
                        </tr>
                      @endif
                      @if ($slot->slot_number == 14)
                        <tr class="table-warning">
                          <td class="align-middle"><b class="text-dark"><i class="fas fa-coffee me-1"></i> ISTIRAHAT</b></td>
                          @foreach ($days as $day)
                            @if ($day != 'Jumat')
                              <td class="align-middle"><b class="text-dark" style="letter-spacing: 2px;">ISTIRAHAT SIANG</b></td>
                            @else
                              <td class="text-muted" style="background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #e9ecef 10px, #e9ecef 20px);">-</td>
                            @endif
                          @endforeach
                        </tr>
                      @endif
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif
      @endforeach

      {{-- TOMBOL FLOATING (MUNCUL JIKA ADA CHECKBOX TERCENTANG) --}}
      <div id="bulk-edit-container" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg" style="z-index: 1050;">
        <button class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-lg border border-dark" data-bs-toggle="modal" data-bs-target="#bulkMoveModal">
          <i class="fas fa-arrows-alt me-1"></i> Pindah Jadwal Terpilih (<span id="selected-count">0</span> JP)
        </button>
      </div>

      {{-- MODAL BULK MOVE (PINDAH MASSAL) --}}
      <div class="modal fade text-left" id="bulkMoveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-warning">
              <h5 class="modal-title text-dark"><i class="fas fa-arrows-alt me-2"></i> Pindah Jadwal Massal</h5>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i data-feather="x"></i></button>
            </div>
            <form action="{{ route('admin.jadwal.bulkMoveJadwal') }}" method="POST">
              @csrf
              <div class="modal-body text-start">
                <input type="hidden" name="jadwal_ids" id="bulk-jadwal-ids">
                <div class="alert alert-light-warning text-dark border-warning">
                  Anda akan memindahkan <b><span id="modal-count-text">0</span> JP</b> secara berurutan.
                </div>

                <div class="form-group">
                  <label class="fw-bold">Pindah ke Hari:</label>
                  <select name="day" class="form-select" required>
                    @foreach ($days as $d)
                      <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group mt-3">
                  <label class="fw-bold">Pindah Mulai Jam Ke-:</label>
                  <select name="start_time_slot_id" class="form-select" required>
                    @foreach ($slots as $slotItem)
                      <option value="{{ $slotItem->id }}">
                        Jam ke-{{ $slotItem->slot_number }} ({{ substr($slotItem->start_time, 0, 5) }} - {{ substr($slotItem->end_time, 0, 5) }})
                      </option>
                    @endforeach
                  </select>
                  <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Sistem akan otomatis mengurutkan ke bawah. (Contoh: jika memindah 3 JP dan memilih mulai jam ke-4, otomatis
                    akan mengisi jam 4, 5, dan 6).</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning ml-1 fw-bold text-dark">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@section('script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // --- NOTIFIKASI SWEETALERT ---
      const flashBerhasil = document.querySelector('.flash-data').getAttribute('data-berhasil');
      const flashGagal = document.querySelectorAll('.flash-data')[1].getAttribute('data-gagal');

      if (flashBerhasil) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: flashBerhasil,
          confirmButtonColor: '#435ebe'
        });
      }
      if (flashGagal) {
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Konflik!',
          text: flashGagal,
          confirmButtonColor: '#dc3545'
        });
      }

      // --- LOGIKA CHECKBOX BULK EDIT ---
      let firstGuru = null;
      let firstMapel = null;
      const checkboxes = document.querySelectorAll('.select-jadwal');
      const bulkContainer = document.getElementById('bulk-edit-container');
      const countDisplay = document.getElementById('selected-count');
      const modalCountText = document.getElementById('modal-count-text');
      const hiddenInputIds = document.getElementById('bulk-jadwal-ids');

      checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          const checkedBoxes = document.querySelectorAll('.select-jadwal:checked');
          const count = checkedBoxes.length;

          if (count === 0) {
            // Jika tidak ada yang dicentang, buka kunci semua checkbox
            firstGuru = null;
            firstMapel = null;
            checkboxes.forEach(cb => {
              cb.disabled = false;
              cb.parentElement.parentElement.classList.remove('border-warning', 'bg-light-warning');
            });
            bulkContainer.classList.add('d-none');
          } else {
            // Jika ada yang dicentang, rekam data dari centang pertama
            if (count === 1) {
              firstGuru = checkedBoxes[0].getAttribute('data-guru');
              firstMapel = checkedBoxes[0].getAttribute('data-mapel');
            }

            // Validasi: Kunci checkbox yang beda guru atau beda mapel
            checkboxes.forEach(cb => {
              const isMatch = cb.getAttribute('data-guru') === firstGuru && cb.getAttribute('data-mapel') === firstMapel;

              if (!isMatch) {
                cb.disabled = true;
              } else {
                cb.disabled = false;
                // Kosmetik: Tambah warna jika dicentang
                if (cb.checked) {
                  cb.parentElement.parentElement.classList.add('border-warning', 'bg-light-warning');
                } else {
                  cb.parentElement.parentElement.classList.remove('border-warning', 'bg-light-warning');
                }
              }
            });

            // Tampilkan tombol dan perbarui data ID
            countDisplay.innerText = count;
            modalCountText.innerText = count;
            bulkContainer.classList.remove('d-none');

            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            hiddenInputIds.value = ids.join(',');
          }
        });
      });
    });
  </script>
@endsection
