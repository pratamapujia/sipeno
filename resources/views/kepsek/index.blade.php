@extends('layouts.main')

@section('title')
  <title>Dashboard Kepala Sekolah</title>
@endsection

@section('main')
  <div class="page-heading">
    <h3>Pemantauan Kelembagaan</h3>
  </div>
  <div class="page-content">
    <section class="row">
      <div class="col-12 col-lg-9">
        {{-- Row Info Makro Lembaga --}}
        <div class="row">
          <div class="col-6 col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
              <div class="card-body p-3">
                <small class="text-muted font-semibold d-block">Fakultas/Guru</small>
                <h4 class="font-extrabold mb-0 mt-1">{{ $stats['total_guru'] }} Orang</h4>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
              <div class="card-body p-3">
                <small class="text-muted font-semibold d-block">Rombongan Belajar</small>
                <h4 class="font-extrabold mb-0 mt-1">{{ $stats['total_kelas'] }} Kelas</h4>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
              <div class="card-body p-3">
                <small class="text-muted font-semibold d-block">Jadwal Rilis Aktif</small>
                <h4 class="font-extrabold mb-0 mt-1">{{ $stats['batch_aktif'] }} Dokumen</h4>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card shadow-sm border-start border-secondary border-4">
              <div class="card-body p-3">
                <small class="text-muted font-semibold d-block">Simulasi Draft</small>
                <h4 class="font-extrabold mb-0 mt-1">{{ $stats['batch_draft'] }} Rancangan</h4>
              </div>
            </div>
          </div>
        </div>

        {{-- Laporan Operasional Jadwal --}}
        <div class="card shadow-sm">
          <div class="card-header bg-transparent border-bottom">
            <h4>Kondisi Operasional Akademik</h4>
          </div>
          <div class="card-body pt-3">
            @if ($activeBatch)
              <div class="p-3 bg-light rounded border mb-0">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <span class="badge bg-success mb-2">JADWAL UTAMA AKTIF</span>
                    <h5 class="fw-bold mb-1">{{ $activeBatch->nama }}</h5>
                    <p class="text-muted text-xs mb-0">Terakhir disinkronisasi oleh Kurikulum pada: {{ $activeBatch->updated_at->format('d/m/Y H:i') }} Wib</p>
                  </div>
                  <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('kepsek.pemantauan') }}" class="btn btn-sm btn-outline-primary shadow-sm">
                      <i class="fas fa-search me-1"></i> Audit Distribusi Jam
                    </a>
                  </div>
                </div>
              </div>
            @else
              <div class="alert alert-light-warning border rounded p-3 mb-0">
                <i class="fas fa-info-circle me-1 text-warning"></i> <b>Perhatian:</b> Belum ada paket jadwal pelajaran yang secara resmi diaktifkan untuk tahun ajaran berjalan ini oleh bagian
                kurikulum.
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Identitas Kepala Sekolah --}}
      <div class="col-12 col-lg-3">
        <div class="card shadow-sm">
          <div class="card-body text-center py-4">
            <div class="avatar avatar-2xl bg-dark text-white font-bold mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%;">
              <i class="fas fa-user-tie fa-2x"></i>
            </div>
            <h5 class="font-bold mb-1">{{ Auth::user()->name }}</h5>
            <small class="text-muted d-block mb-3">Kepala Sekolah Utama</small>
            <span class="badge bg-light-primary text-primary px-3 py-2 font-semibold text-xs rounded-pill">Hak Akses: Monitoring</span>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
