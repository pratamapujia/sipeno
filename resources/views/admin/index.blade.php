@extends('layouts.main')

@section('title')
  <title>Dashboard Admin Kurikulum</title>
@endsection

@section('main')
  <div class="page-heading">
    <h3>Dashboard Kurikulum</h3>
  </div>
  <div class="page-content">
    <section class="row">
      <div class="col-12 col-md-6">
        <div class="row">
          <div class="col-6">
            <div class="card shadow-sm">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                    <div class="stats-icon purple mb-2">
                      <i class="fas fa-users"></i>
                    </div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Total Guru</h6>
                    <h6 class="font-extrabold mb-0">{{ $stats['total_guru'] }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card shadow-sm">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                    <div class="stats-icon blue mb-2">
                      <i class="fas fa-book"></i>
                    </div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Total Mapel</h6>
                    <h6 class="font-extrabold mb-0">{{ $stats['total_mapel'] }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card shadow-sm">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                    <div class="stats-icon green mb-2">
                      <i class="fas fa-chalkboard"></i>
                    </div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Total Kelas</h6>
                    <h6 class="font-extrabold mb-0">{{ $stats['total_kelas'] }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card shadow-sm">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                    <div class="stats-icon red mb-2">
                      <i class="fas fa-clock"></i>
                    </div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Slot Waktu</h6>
                    <h6 class="font-extrabold mb-0">{{ $stats['total_slot'] }} JP</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        {{-- Info Penjadwalan Terakhir --}}
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header">
              <h4>Status Simulasi Algoritma Genetika</h4>
            </div>
            <div class="card-body">
              @if ($latestBatch)
                <div class="alert alert-light-secondary d-flex align-items-center justify-content-between p-3 border rounded">
                  <div>
                    <h6 class="fw-bold text-primary mb-1">{{ $latestBatch->nama }}</h6>
                    <small class="text-muted">Dibuat pada: {{ $latestBatch->created_at->format('d M Y H:i') }} Wib</small>
                  </div>
                  <div class="text-end">
                    <span class="badge {{ $latestBatch->status == 'active' ? 'bg-success' : 'bg-secondary' }} mb-1">
                      {{ strtoupper($latestBatch->status) }}
                    </span>
                    <br>
                    <small class="fw-bold">Fitness Score: <b class="{{ $latestBatch->final_fitness_score == 0 ? 'text-success' : 'text-danger' }}">{{ $latestBatch->final_fitness_score }}</b></small>
                  </div>
                </div>
                <div class="mt-3">
                  <a href="{{ route('admin.jadwal.show', $latestBatch->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i> Buka Pratinjau Jadwal
                  </a>
                </div>
              @else
                <div class="text-center text-muted py-4">
                  <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
                  <p>Belum ada simulasi jadwal otomatis yang dijalankan pada tahun ajaran ini.</p>
                  <a href="{{ route('admin.jadwal.index') }}" class="btn btn-sm btn-outline-primary">Mulai Generate Sekarang</a>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="card shadow-sm">
          <div class="card-header">
            <h4>Panduan Memulai Cepat <i class="fas fa-rocket"></i></h4>

          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <div class="mb-1 fw-bold">
                  <h5>1. Input Data Master</h5>
                </div>
                <p>Mulai dengan mengisi <a href="{{ route('admin.m.guru.index') }}">Guru</a>, <a href="{{ route('admin.m.kelas.index') }}">Kelas</a>, <a href="{{ route('admin.m.mapel.index') }}">Mata
                    Pelajaran</a>, <a href="{{ route('admin.m.slotJam.index') }}">Jam Pelajaran</a>, dan <a href="{{ route('admin.m.thnAjaran.index') }}">Tahun Ajaran</a>. Gunakan fitur Import Excel
                  untuk mempercepat proses </p>
              </li>
              <li class="list-group-item">
                <div class="mb-1 fw-bold">
                  <h5>2. Plotting Guru, Mapel dan Kelas</h5>
                </div>
                <p>Pilih Guru, Mata Pelajaran dan Kelas yang akan dijadwalkan di <a href="{{ route('admin.plotting.index') }}">Plotting Guru</a></p>
              </li>
              <li class="list-group-item">
                <div class="mb-1 fw-bold">
                  <h5>3.Generate Jadwal</h5>
                </div>
                <p>Buka halaman <a href="{{ route('admin.jadwal.index') }}">Generate Jadwal</a> untuk memulai penjadwalan otomatis</p>
              </li>
              <li class="list-group-item">
                <div class="mb-1 fw-bold">
                  <h5>4. Lihat dan Cetak Jadwal</h5>
                </div>
                <p>Buka halaman <a href="{{ route('admin.jadwal.index') }}">Jadwal</a> untuk melihat dan mencetak hasil penjadwalan</p>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
