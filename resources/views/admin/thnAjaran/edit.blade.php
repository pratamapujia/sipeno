@extends('layouts.main')

@section('title')
  <title>Form Edit Tahun Ajaran</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Form Edit Tahun Ajaran</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('admin.m.thnAjaran.index') }}">Master Tahun Ajaran</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                Form Edit Tahun Ajaran
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
            <h5>Data Tahun Ajaran</h5>
          </div>
          <div class="ms-auto">
            <a href="{{ route('admin.m.thnAjaran.index') }}" class="btn icon icon-left btn-primary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.m.thnAjaran.update', $thnAjaran->id) }}" class="form" method="POST">
          @csrf
          @method('PUT')
          <div class="row">
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="tahun_ajaran">Tahun Ajaran</label>
                <input type="text" class="form-control @error('tahun_ajaran') is-invalid @enderror" name="tahun_ajaran" placeholder="Contoh: 2022/2023"
                  value="{{ old('tahun_ajaran', $thnAjaran->tahun_ajaran) }}">
                @error('tahun_ajaran')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="semester">Semester</label>
                <select name="semester" id="semester" class="form-select @error('semester') is-invalid @enderror">
                  <option value="">Pilih</option>
                  <option value="Ganjil" {{ old('semester', $thnAjaran->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                  <option value="Genap" {{ old('semester', $thnAjaran->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                </select>
                @error('semester')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="is_active">Status</label>
                <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                  <option value="">Pilih</option>
                  <option value="0" {{ old('is_active', $thnAjaran->is_active) == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                  <option value="1" {{ old('is_active', $thnAjaran->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                </select>
                @error('is_active')
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
