@extends('layouts.main')

@section('title')
  <title>Ketersediaan Waktu Guru</title>
  <link rel="stylesheet" href="{{ asset('assets/extensions/choices.js/public/assets/styles/choices.css') }}">
@endsection

@section('main')
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>

  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-8 order-md-1 order-last">
          <h3>Atur Waktu Berhalangan Mengajar</h3>
          <p class="text-subtitle text-muted">
            <b>Centang</b> kotak pada jam di mana guru tersebut <b>BERHALANGAN / TIDAK BISA HADIR</b>.<br>
            <span class="text-primary"><i class="fas fa-info-circle"></i> Kotak yang dibiarkan kosong akan dianggap sebagai waktu luang (tersedia) untuk mengajar.</span>
          </p>
        </div>
        <div class="col-12 col-md-4 order-md-2 order-first text-md-end mb-3">
          <a href="{{ route('admin.guruFree.rekap') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-chart-line"></i> Lihat Rekap Berhalangan
          </a>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <form action="{{ route('admin.guruFree.index') }}" method="GET" id="form-pilih-guru">
            <div class="form-group">
              <label for="target" class="form-label font-bold text-primary"><i class="fas fa-filter me-1"></i> Pilih Guru & Kelas:</label>
              <select name="target" id="target" class="choices form-select shadow-sm" style="border: 1px solid #435ebe;" onchange="document.getElementById('form-pilih-guru').submit();">
                @if ($guruMapels->isEmpty())
                  <option value="" disabled selected>-- Belum ada data Plotting Mapel --</option>
                @endif

                @foreach ($guruMapels as $gm)
                  @php $targetVal = $gm->guru_id . '_' . $gm->kelas_id; @endphp
                  <option value="{{ $targetVal }}" {{ $selectedTarget == $targetVal ? 'selected' : '' }}>
                    {{ $gm->guru->nama_guru ?? 'Guru Hapus' }} | {{ $gm->kelas->nama_kelas ?? 'Kelas Hapus' }} | {{ $gm->mapel->nama_mapel ?? 'Mapel Hapus' }}
                  </option>
                @endforeach
              </select>
            </div>
          </form>
        </div>
      </div>

      @if ($selectedTarget)
        <div class="card shadow-sm border-top border-danger border-3">
          <div class="card-content">
            <div class="card-body">
              <form action="{{ route('admin.guruFree.store') }}" method="POST">
                @csrf
                <input type="hidden" name="target" value="{{ $selectedTarget }}">

                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="mb-0 text-danger"><i class="fas fa-calendar-times me-2"></i> Jadwal Berhalangan</h5>
                  <div>
                    <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="checkAll(true)">
                      <i class="fas fa-check-double"></i> Centang Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(false)">
                      <i class="fas fa-eraser"></i> Kosongkan Semua
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="align-middle">Jam Ke / Waktu</th>
                        @foreach ($hari as $day)
                          <th class="align-middle text-center">
                            {{ $day }}<br>
                            {{-- Checkbox Master Per Hari --}}
                            <div class="form-check form-check-sm d-flex justify-content-center mt-2 mb-0" title="Centang berhalangan sehari penuh pada hari {{ $day }}">
                              <input class="form-check-input check-all-day border-secondary" type="checkbox" data-day="{{ $day }}" style="cursor: pointer; transform: scale(1.2);">
                            </div>
                          </th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($slot as $slot)
                        <tr class="{{ $slot->is_istirahat ? 'table-warning' : '' }}">
                          <td class="text-start">
                            <b class="text-dark">Jam ke-{{ $slot->slot_number }}</b> <br>
                            <small class="text-muted">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</small>
                            @if ($slot->is_istirahat)
                              <span class="badge bg-warning text-dark float-end mt-1">Istirahat</span>
                            @endif
                          </td>

                          @foreach ($hari as $day)
                            <td>
                              @if ($slot->is_istirahat)
                                <span class="text-muted font-italic">-</span>
                              @else
                                @php
                                  $key = "{$day}_{$slot->id}";
                                @endphp

                                <div class="form-check form-check-sm d-flex justify-content-center">
                                  <input class="form-check-input check-jadwal day-{{ $day }} border-danger border-2" type="checkbox" style="transform: scale(1.5); cursor: pointer;"
                                    name="unassigned[{{ $key }}]" value="1" {{ isset($tidakTersedia[$key]) ? 'checked' : '' }}>
                                </div>
                              @endif
                            </td>
                          @endforeach
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-danger icon icon-left fw-bold shadow-sm px-4">
                    <i class="fas fa-save"></i> Simpan Batasan Waktu
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endif
    </section>
  </div>
@endsection

@section('script')
  <script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/form-element-select.js') }}"></script>
  <script>
    // 1. Fungsi untuk mencentang/mengosongkan seluruh tabel
    function checkAll(state) {
      let checkboxes = document.querySelectorAll('.check-jadwal');
      checkboxes.forEach(function(checkbox) {
        checkbox.checked = state;
      });

      // Sinkronisasi juga ke checkbox header harian
      let dayCheckboxes = document.querySelectorAll('.check-all-day');
      dayCheckboxes.forEach(function(cb) {
        cb.checked = state;
      });
    }

    // 2. Fungsi untuk mencentang/mengosongkan berdasarkan Hari
    document.addEventListener('DOMContentLoaded', function() {
      let masterDayCheckboxes = document.querySelectorAll('.check-all-day');

      masterDayCheckboxes.forEach(function(master) {
        master.addEventListener('change', function() {
          let day = this.getAttribute('data-day');
          let isChecked = this.checked;

          // Cari semua checkbox yang memiliki class sesuai nama hari tersebut
          let targetCheckboxes = document.querySelectorAll('.day-' + day);
          targetCheckboxes.forEach(function(cb) {
            cb.checked = isChecked;
          });
        });
      });
    });
  </script>
@endsection
