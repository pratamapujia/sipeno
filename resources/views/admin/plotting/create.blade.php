@extends('layouts.main')

@section('title')
  <title>Form Tambah Plotting</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Form Tambah Plotting</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('admin.plotting.index') }}">Plotting</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                Form Tambah Plotting
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
            <h5>Data Plotting</h5>
          </div>
          <div class="ms-auto">
            <a href="{{ route('admin.plotting.index') }}" class="btn icon icon-left btn-primary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.plotting.store') }}" class="form" method="POST">
          @csrf
          <input type="hidden" name="tahun_ajaran_id" value="{{ $thnAjaran->id }}">
          <div class="row">
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="guru_id">Plih Nama Guru</label>
                <select name="guru_id" id="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                  <option value="">-- Pilih Guru --</option>
                  @foreach ($guru as $item)
                    <option value="{{ $item->id }}" {{ old('guru_id') == $item->id ? 'selected' : '' }}>
                      {{ $item->nama_guru }} | {{ $item->nip ? '(' . $item->nip . ')' : '' }}
                    </option>
                  @endforeach
                </select>
                @error('guru_id')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="mapel_id">Plih Mata Pelajaran</label>
                <select name="mapel_id" id="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror">
                  <option value="">-- Pilih Mapel --</option>
                  @foreach ($mapel as $item)
                    <option value="{{ $item->id }}" {{ old('mapel_id') == $item->id ? 'selected' : '' }}>
                      {{ $item->nama_mapel }} | {{ $item->kode_mapel ? '(' . $item->kode_mapel . ')' : '' }}
                    </option>
                  @endforeach
                </select>
                @error('mapel_id')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="kelas_id">Plih Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror">
                  <option value="">-- Pilih Kelas --</option>
                  @foreach ($kelas as $item)
                    <option value="{{ $item->id }}" {{ old('kelas_id') == $item->id ? 'selected' : '' }}>
                      Kelas {{ $item->kelas }}
                    </option>
                  @endforeach
                </select>
                @error('kelas_id')
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
