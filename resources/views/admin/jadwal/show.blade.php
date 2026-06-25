@extends('layouts.main')

@section('title')
  <title>Detail Jadwal Kelas</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title mb-3">
      <div class="row align-items-center">
        <div class="col-12 col-md-7">
          <h3>Pratinjau Jadwal Kelas</h3>
          <p class="text-muted">Simulasi: <b class="text-primary">{{ $batch->nama }}</b>
            @if ($batch->status == 'active')
              <span class="badge bg-success ms-2">AKTIF</span>
            @else
              <span class="badge bg-secondary ms-2">DRAFT</span>
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

    <section class="section">
      {{-- Filter Kelas --}}
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

      {{-- LOGIKA PEMISAHAN SHIFT --}}
      @php
        $shiftGroups = [
            'Pagi' => [
                'title' => 'Shift Pagi (Jam ke-1 s/d 12)',
                'icon' => 'fas fa-sun text-warning',
                'data' => $slots->where('slot_number', '<=', 12),
            ],
            'Siang' => [
                'title' => 'Shift Siang (Jam ke-13 s/d 18)',
                'icon' => 'fas fa-cloud-sun text-info',
                'data' => $slots->where('slot_number', '>', 12),
            ],
        ];
      @endphp

      {{-- Render Tabel Berdasarkan Grup Shift --}}
      @foreach ($shiftGroups as $shiftKey => $shift)
        @if ($shift['data']->count() > 0)
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-light-{{ $shiftKey == 'Pagi' ? 'warning' : 'info' }} p-3 border-bottom">
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
                    @foreach ($shift['data'] as $slot)
                      <tr class="{{ $slot->is_istirahat ? 'table-warning' : '' }}">
                        <td>
                          <b class="text-nowrap">Jam ke-{{ $slot->slot_number }}</b><br>
                          <small class="text-muted text-nowrap">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</small>
                        </td>

                        @foreach ($days as $day)
                          <td>
                            @if ($slot->is_istirahat)
                              <span class="text-muted font-italic"><i class="fas fa-coffee me-1"></i> ISTIRAHAT</span>
                            @else
                              @if (isset($jadwalMatrix[$slot->id][$day]))
                                @php $s = $jadwalMatrix[$slot->id][$day]; @endphp

                                <div class="card mb-0 shadow-sm border">
                                  <div class="card-body p-2 text-center">
                                    <span class="fw-bold d-block text-primary">{{ $s->mapel->nama_mapel }}</span>
                                    <small class="text-muted d-block">{{ $s->guru->nama_guru }}</small>

                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-sm btn-light-secondary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}">
                                      <i class="fas fa-edit"></i> Pindah
                                    </button>
                                  </div>
                                </div>

                                {{-- Modal Edit Khusus untuk Item Ini --}}
                                <div class="modal fade text-left" id="editModal{{ $s->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                      <div class="modal-header bg-primary">
                                        <h5 class="modal-title white">Pindah Jadwal Manual</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                          <i data-feather="x"></i>
                                        </button>
                                      </div>
                                      <form action="{{ route('admin.jadwal.updateManual', $s->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body text-start">
                                          <div class="alert alert-info">
                                            Memindahkan <b>{{ $s->mapel->nama_mapel }}</b> ({{ $s->guru->nama_guru }}) dari <b>{{ $s->day }} Jam ke-{{ $s->slotJam->slot_number }}</b>.
                                          </div>

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
                                                <option value="{{ $slotItem->id }}" {{ $s->time_slot_id == $slotItem->id ? 'selected' : '' }}>
                                                  Jam ke-{{ $slotItem->slot_number }} ({{ substr($slotItem->start_time, 0, 5) }} - {{ substr($slotItem->end_time, 0, 5) }})
                                                  @if ($slotItem->is_istirahat)
                                                    [ISTIRAHAT]
                                                  @endif
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
                              @else
                                {{-- Jika kosong --}}
                                <div class="text-center text-muted p-3">-Kosong-</div>
                              @endif
                            @endif
                          </td>
                        @endforeach
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif
      @endforeach

    </section>
  </div>
@endsection
