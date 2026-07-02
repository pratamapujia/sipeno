@extends('layouts.main')

@section('title')
  <title>Form Tambah Kelas</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Form Tambah Kelas</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('admin.m.kelas.index') }}">Master Kelas</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                Form Tambah Kelas
              </li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <div class="page-content">
    <div class="flash-data" data-gagal="{{ Session::get('error') }}"></div>
    <div class="card">
      <div class="card-header">
        <div class="media d-flex align-items-center">
          <div class="me-3">
            <h5>Data Kelas</h5>
          </div>
          <div class="ms-auto">
            <a href="{{ route('admin.m.kelas.index') }}" class="btn icon icon-left btn-primary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.m.kelas.store') }}" class="form" method="POST">
          @csrf
          <div class="row">
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label for="wali_kelas_id" class="fw-bold mb-1">Pilih Wali Kelas</label>
                <select name="wali_kelas_id" id="wali_kelas_id" class="form-select @error('wali_kelas_id') is-invalid @enderror">
                  <option value="">-- Pilih Wali Kelas --</option>
                  @foreach ($guru as $g)
                    <option value="{{ $g->id }}" {{ old('wali_kelas_id') == $g->id ? 'selected' : '' }}>
                      {{ $g->nama_guru }}
                    </option>
                  @endforeach
                </select>
                @error('wali_kelas_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="nama_kelas">Nama Kelas</label>
                <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" name="nama_kelas" placeholder="Masukkan Nama Kelas" value="{{ old('nama_kelas') }}">
                @error('nama_kelas')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="tingkat">Tingkat</label>
                <input type="number" class="form-control @error('tingkat') is-invalid @enderror" name="tingkat" placeholder="Masukkan Tingkat" value="{{ old('tingkat') }}">
                @error('tingkat')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-12">
              <div class="row">
                <div class="col-6 mt-2">
                  <button class="btn btn-primary icon icon-left btn-block">
                    <i class="fas fa-save"></i> Simpan
                  </button>
                </div>
                <div class="col-6 mt-2">
                  <button type="reset" class="btn btn-secondary icon icon-left btn-block">
                    <i class="fas fa-undo"></i> Reset
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
