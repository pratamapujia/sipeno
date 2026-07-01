@extends('layouts.main')

@section('title')
  <title>Cetak Jadwal</title>
@endsection

@section('main')
  <div class="page-heading">
    <h3><i class="bi bi-printer text-primary me-2"></i>Cetak Jadwal Aktif</h3>
    <p class="text-muted">Pilih entitas data untuk mencetak dokumen jadwal pelajaran dalam format fisik.</p>
  </div>

  <div class="page-content">
    @if (!$activeBatch)
      <div class="alert alert-warning shadow-sm"><i class="fas fa-exclamation-triangle"></i> Belum ada jadwal yang berstatus <b>Aktif</b>. Anda tidak bisa mencetak jadwal saat ini.</div>
    @else
      <div class="alert alert-success shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> Jadwal Rilis Aktif saat ini: <b>{{ $activeBatch->nama }}</b>
      </div>

      <div class="row">
        {{-- CARD 1: CETAK PER GURU --}}
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header">
              <h5 class="mb-0 text-primary"><i class="fas fa-chalkboard-teacher me-2"></i> Cetak Per Guru</h5>
            </div>
            <div class="card-body pt-4">
              <form action="{{ route('admin.cetak.guru') }}" method="GET" target="_blank">
                <div class="form-group mb-3">
                  <label class="fw-bold mb-2">Pilih Nama Guru</label>
                  <select name="guru_id" class="form-select" required>
                    <option value="">-- Pilih Guru Pengajar --</option>
                    @foreach ($guru as $g)
                      <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                    @endforeach
                  </select>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-print me-1"></i> Cetak Jadwal</button>
              </form>
            </div>
          </div>
        </div>

        {{-- CARD 2: CETAK PER KELAS --}}
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header">
              <h5 class="mb-0 text-primary"><i class="fas fa-chalkboard me-2"></i> Cetak Per Kelas</h5>
            </div>
            <div class="card-body pt-4">
              <form action="{{ route('admin.cetak.kelas') }}" method="GET" target="_blank">
                <div class="form-group mb-3">
                  <label class="fw-bold mb-2">Pilih Ruang Kelas</label>
                  <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Rombongan Belajar --</option>
                    @foreach ($kelas as $k)
                      <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }}</option>
                    @endforeach
                  </select>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-print me-1"></i> Cetak Jadwal</button>
              </form>
            </div>
          </div>
        </div>

        {{-- CARD 3: CETAK MASTER JADWAL --}}
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header">
              <h5 class="mb-0 text-primary"><i class="fas fa-file-alt me-2"></i> Cetak Semua Jadwal</h5>
            </div>
            <div class="card-body pt-4">
              <p class="small mb-4">Cetak seluruh jadwal yang dikelompokkan berdasarkan rombongan belajar</p>
              <a href="{{ route('admin.cetak.semua') }}" target="_blank" class="btn btn-primary fw-bold w-100">
                <i class="fas fa-print me-1"></i> Cetak Jadwal
              </a>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
@endsection
