@extends('layouts.main')

@section('title')
  <title>Jadwal Mengajar Saya</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title mb-3">
      <div class="row align-items-center">
        <div class="col-12 col-md-8">
          <h3>Jadwal Mengajar Pribadi</h3>
          <p class="text-muted">
            Tahun Ajaran Berjalan
            @if ($activeBatch)
              <span class="badge bg-success ms-2"><i class="fas fa-check-circle me-1"></i> {{ $activeBatch->nama }}</span>
            @else
              <span class="badge bg-warning ms-2">BELUM ADA JADWAL AKTIF</span>
            @endif
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
          @if ($activeBatch)
            <a href="{{ route('guru.jadwal.print') }}" target="_blank" class="btn btn-primary shadow-sm">
              <i class="fas fa-print me-2"></i> Cetak Jadwal
            </a>
          @endif
        </div>
      </div>
    </div>

    <section class="section">
      @if (!$activeBatch)
        <div class="alert alert-light-warning color-warning">
          <i class="bi bi-exclamation-triangle"></i> Mohon maaf, Admin Kurikulum belum merilis jadwal pelajaran yang berstatus <b>Aktif</b> untuk saat ini.
        </div>
      @else
        <div class="row">
          @foreach ($days as $day)
            @php
              $jadwalHariIni = $jadwalPerHari[$day];
            @endphp

            <div class="col-12 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-header bg-light-primary border-bottom py-3">
                  <h5 class="mb-0 text-primary">
                    <i class="fas fa-calendar-day me-2"></i> Hari {{ $day }}
                  </h5>
                </div>
                <div class="card-body p-0">
                  @if ($jadwalHariIni->count() > 0)
                    <div class="table-responsive">
                      <table class="table table-hover table-striped mb-0 text-center align-middle">
                        <thead class="table-dark">
                          <tr>
                            <th style="width: 15%;">Jam Ke-</th>
                            <th style="width: 20%;">Waktu</th>
                            <th style="width: 40%;">Mata Pelajaran</th>
                            <th style="width: 25%;">Kelas</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($jadwalHariIni as $j)
                            <tr>
                              <td class="fw-bold">Jam ke-{{ $j->slotJam->slot_number }}</td>
                              <td><small class="text-muted">{{ substr($j->slotJam->start_time, 0, 5) }} - {{ substr($j->slotJam->end_time, 0, 5) }}</small></td>
                              <td class="fw-semibold text-primary">{{ $j->mapel->nama_mapel }}</td>
                              <td>
                                <span class="badge bg-light-success text-success px-3 py-2 rounded-pill font-bold">
                                  {{ $j->kelas->nama_kelas }}
                                </span>
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  @else
                    <div class="text-center py-5 text-muted">
                      <i class="fas fa-mug-hot fa-3x mb-3 text-light-secondary"></i>
                      <p class="mb-0 font-semibold">Tidak ada jadwal mengajar / Libur</p>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>
  </div>
@endsection
