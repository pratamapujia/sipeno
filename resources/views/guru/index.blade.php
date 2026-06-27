@extends('layouts.main')

@section('title')
  <title>Dashboard Guru</title>
@endsection

@section('main')
  <div class="page-heading">
    <h3>Dashboard Pendidik</h3>
  </div>
  <div class="page-content">
    <section class="row">
      <div class="col-12 col-lg-8">
        <div class="card bg-light-primary border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="text-primary fw-bold">Selamat Datang, {{ $guru ? $guru->nama_guru : Auth::user()->name }}!</h4>
            <p class="text-gray-600 mb-0">Anda terikat sebagai tenaga pengajar aktif. Total beban mengajar Anda pada semester berjalan ini adalah <b>{{ $total_jam }} Jam Pelajaran (JP)</b>.</p>
          </div>
        </div>

        {{-- Jadwal Mengajar Hari Ini --}}
        <div class="card shadow-sm">
          <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-calendar-day text-warning me-2"></i> Agenda Mengajar Hari Ini ({{ date('l') }})</h4>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle text-center mb-0">
                <thead class="table-dark">
                  <tr>
                    <th>Jam Ke-</th>
                    <th>Waktu</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($jadwal_sekarang as $j)
                    <tr>
                      <td class="fw-bold text-primary">Jam ke-{{ $j->slotJam->slot_number }}</td>
                      <td><small class="text-muted">{{ substr($j->slotJam->start_time, 0, 5) }} - {{ substr($j->slotJam->end_time, 0, 5) }}</small></td>
                      <td class="fw-semibold">{{ $j->mapel->nama_mapel }}</td>
                      <td><span class="badge bg-light-info text-info px-3 py-2 rounded-pill font-semibold">{{ $j->kelas->nama_kelas }}</span></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-mug-hot fa-2x text-muted mb-2"></i><br>
                        Tidak ada agenda mengajar untuk Anda pada hari <b>{{ date('l') }}</b>.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- Informasi Akun Samping --}}
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header border-bottom">
            <h4>Profil Kepegawaian</h4>
          </div>
          <div class="card-body pt-3">
            @if ($guru)
              <div class="mb-3">
                <small class="text-muted d-block">Nomor Induk Pegawai (NIP):</small>
                <span class="fw-bold fs-6">{{ $guru->nip ?? '-' }}</span>
              </div>
              <div class="mb-3">
                <small class="text-muted d-block">Status Ikatan Kerja:</small>
                <span class="badge bg-light-success text-success font-semibold">{{ $guru->status }}</span>
              </div>
              <div class="mb-0">
                <small class="text-muted d-block">Jenis Kelamin:</small>
                <span class="fw-semibold">{{ $guru->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
              </div>
            @else
              <div class="alert alert-warning p-2 small mb-0">
                <i class="fas fa-exclamation-circle"></i> Profil guru belum dihubungkan dengan akun login ini oleh Admin.
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
