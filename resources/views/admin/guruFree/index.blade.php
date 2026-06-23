@extends('layouts.main')

@section('title')
  <title>Ketersediaan Waktu Guru</title>
@endsection

@section('main')
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>

  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-8 order-md-1 order-last">
          <h3>Atur Waktu Berhalangan Mengajar</h3>
          <p class="text-subtitle text-muted">Centang pada kotak di mana guru yang bersangkutan <b>TIDAK BISA</b> mengajar.</p>
        </div>
        <div class="col-12 col-md-4 order-md-2 order-first text-md-end mb-3">
          <a href="{{ route('admin.guruFree.rekap') }}" class="btn btn-primary">
            <i class="fas fa-chart-line"></i> Lihat Rekap
          </a>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="card mb-4">
        <div class="card-body">
          <form action="{{ route('admin.guruFree.index') }}" method="GET" id="form-pilih-guru">
            <div class="form-group">
              <label for="guru_id" class="form-label font-bold">Pilih Nama Guru:</label>
              <select name="guru_id" id="guru_id" class="form-select" onchange="document.getElementById('form-pilih-guru').submit();">
                @foreach ($guru as $guru)
                  <option value="{{ $guru->id }}" {{ $selectedGuruId == $guru->id ? 'selected' : '' }}>
                    {{ $guru->nama_guru }}
                  </option>
                @endforeach
              </select>
            </div>
          </form>
        </div>
      </div>

      @if ($selectedGuruId)
        <div class="card">
          <div class="card-content">
            <div class="card-body">
              <form action="{{ route('admin.guruFree.store') }}" method="POST">
                @csrf
                <input type="hidden" name="guru_id" value="{{ $selectedGuruId }}">

                <div class="table-responsive">
                  <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Jam Ke / Waktu</th>
                        @foreach ($hari as $day)
                          <th>{{ $day }}</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($slot as $slot)
                        <tr class="{{ $slot->is_istirahat ? 'table-warning' : '' }}">
                          <td class="text-start">
                            <b>Jam ke-{{ $slot->slot_number }}</b> <br>
                            <small class="text-muted">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</small>
                            @if ($slot->is_istirahat)
                              <span class="badge bg-warning text-dark float-end mt-1">Istirahat</span>
                            @endif
                          </td>

                          @foreach ($hari as $day)
                            <td>
                              @if ($slot->is_istirahat)
                                <span class="text-muted font-italic">N/A</span>
                              @else
                                @php $key = "{$day}_{$slot->id}"; @endphp
                                <div class="form-check form-check-sm d-flex justify-content-center">
                                  <input class="form-check-input form-check-danger form-check-glow  border-danger border-2" type="checkbox" style="transform: scale(1.5);"
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
                  <button type="submit" class="btn btn-danger icon icon-left">
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
