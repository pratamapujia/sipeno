@extends('layouts.main')

@section('title')
  <title>Rekap Berhalangan Mengajar</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-8 order-md-1 order-last">
          <h3>Rekapitulasi Guru Berhalangan Mengajar</h3>
          <p class="text-subtitle text-muted">Memantau seluruh guru yang tidak bisa mengajar berdasarkan hari dan jam pelajaran.</p>
        </div>
        <div class="col-12 col-md-4 order-md-2 order-first text-md-end mb-3">
          <a href="{{ route('admin.guruFree.index') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Atur Per Guru
          </a>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="card">
        <div class="card-content">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="table-dark text-center">
                  <tr>
                    <th style="width: 15%;">Jam Ke / Waktu</th>
                    @foreach ($hari as $day)
                      <th style="width: 17%;">{{ $day }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach ($slot as $slot)
                    <tr class="{{ $slot->is_istirahat ? 'table-warning text-center' : '' }}">
                      <td>
                        <b>Jam ke-{{ $slot->slot_number }}</b> <br>
                        <small class="text-muted">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</small>
                        @if ($slot->is_istirahat)
                          <span class="badge bg-warning text-dark d-block mt-1">Istirahat</span>
                        @endif
                      </td>

                      @foreach ($hari as $day)
                        <td>
                          @if ($slot->is_istirahat)
                            <span class="text-muted italic">N/A</span>
                          @else
                            {{-- Cek apakah ada daftar guru yang berhalangan di hari dan jam ini --}}
                            @if (isset($rekapData[$day][$slot->id]))
                              <div class="d-flex flex-column gap-1">
                                @foreach ($rekapData[$day][$slot->id] as $namaGuru)
                                  <span class="badge bg-light-danger text-danger text-start py-1.5 px-2 font-semibold shadow-sm rounded">
                                    <i class="fas fa-user-times me-1"></i> {{ $namaGuru }}
                                  </span>
                                @endforeach
                              </div>
                            @else
                              <div class="text-center">
                                <span class="badge bg-light-success text-success py-1 px-2 rounded-pill font-normal">
                                  <i class="fas fa-check-circle"></i> Semua Guru Siap
                                </span>
                              </div>
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
      </div>
    </section>
  </div>
@endsection
