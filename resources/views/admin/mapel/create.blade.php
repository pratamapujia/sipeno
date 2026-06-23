@extends('layouts.main')

@section('title')
  <title>Form Tambah Mapel</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Form Tambah Mapel</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('admin.m.mapel.index') }}">Master Mapel</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                Form Tambah Mapel
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
            <h5>Data Mapel</h5>
          </div>
          <div class="ms-auto">
            <a href="{{ route('admin.m.mapel.index') }}" class="btn icon icon-left btn-primary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.m.mapel.store') }}" class="form" method="POST">
          @csrf
          <div class="row">
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="nama_mapel">Nama Mapel</label>
                <input type="text" class="form-control @error('nama_mapel') is-invalid @enderror" name="nama_mapel" placeholder="Masukkan Nama Mapel" value="{{ old('nama_mapel') }}">
                @error('nama_mapel')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="kode_mapel">Kode Mapel</label>
                <input type="text" class="form-control @error('kode_mapel') is-invalid @enderror" name="kode_mapel" placeholder="Masukkan Kode Mapel" value="{{ old('kode_mapel') }}">
                @error('kode_mapel')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="beban_jam">Beban Jam</label>
                <input type="number" class="form-control @error('beban_jam') is-invalid @enderror" name="beban_jam" placeholder="Masukkan Beban Jam" value="{{ old('beban_jam') }}">
                @error('beban_jam')
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
