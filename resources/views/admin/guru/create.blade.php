@extends('layouts.main')

@section('title')
  <title>Form Tambah Guru</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Form Tambah Guru</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('guru.index') }}">Master Guru</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                Form Tambah Guru
              </li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <div class="page-content">
    <div class="flash-data" data-gagal="{{ Session::get('gagal') }}"></div>
    <div class="card">
      <div class="card-header">
        <div class="media d-flex align-items-center">
          <div class="me-3">
            <h5>Data Guru</h5>
          </div>
          <div class="ms-auto">
            <a href="{{ route('guru.index') }}" class="btn icon icon-left btn-primary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('guru.store') }}" class="form" method="POST">
          @csrf
          <div class="row">
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="nip">NIP</label>
                <input type="text" class="form-control @error('nip') is-invalid @enderror" name="nip" placeholder="Masukkan NIP" value="{{ old('nip') }}">
                @error('nip')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="nama_guru">Nama Guru</label>
                <input type="text" class="form-control @error('nama_guru') is-invalid @enderror" name="nama_guru" placeholder="Masukkan Nama" value="{{ old('nama_guru') }}">
                @error('nama_guru')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="jenis_kelamin">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                  <option value="">Pilih</option>
                  <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki Laki</option>
                  <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label class="form-label" for="status">Status Guru</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                  <option value="">Pilih</option>
                  <option value="Tetap" {{ old('status') == 'Tetap' ? 'selected' : '' }}>Tetap</option>
                  <option value="Honorer" {{ old('status') == 'Honorer' ? 'selected' : '' }}>Honorer</option>s
                </select>
                @error('status')
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
