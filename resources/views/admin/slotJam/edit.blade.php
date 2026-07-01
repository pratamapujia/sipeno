@extends('layouts.main')

@section('title')
  <title>Edit Jam Pelajaran</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Form Edit Jam Pelajaran</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="{{ route('admin.m.slotJam.index') }}">Jam Pelajaran</a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">
                Form Edit Jam Pelajaran
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
            <h5>Data Jam Pelajaran</h5>
          </div>
          <div class="ms-auto">
            <a href="{{ route('admin.m.slotJam.index') }}" class="btn icon icon-left btn-primary">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.m.slotJam.update', $slot->id) }}" method="POST" class="form form-vertical">
          @csrf
          @method('PUT')
          <div class="form-body">
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label for="slot_number">Urutan Jam Ke- (Angka)</label>
                  <input type="number" id="slot_number" class="form-control @error('slot_number') is-invalid @enderror" name="slot_number" value="{{ old('slot_number', $slot->slot_number) }}"
                    placeholder="Contoh: 1">
                  @error('slot_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label for="start_time">Waktu Mulai</label>
                  <input type="time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time', $slot->start_time) }}">
                  @error('start_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label for="end_time">Waktu Selesai</label>
                  <input type="time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time', $slot->end_time) }}">
                  @error('end_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-12 mt-3 mb-4">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_istirahat" name="is_istirahat" value="1" {{ $slot->is_istirahat ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_istirahat">Tandai sebagai Jam Istirahat (Jadwal tidak akan diplot di jam ini)</label>
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
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
