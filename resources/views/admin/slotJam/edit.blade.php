@extends('layouts.main')

@section('title')
  <title>Edit Jam Pelajaran</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Edit Data Jam Pelajaran</h3>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="card">
        <div class="card-content">
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

                  <div class="col-12 d-flex justify-content-end">
                    <a href="{{ route('admin.m.slotJam.index') }}" class="btn btn-light-secondary me-1 mb-1">Kembali</a>
                    <button type="submit" class="btn btn-primary me-1 mb-1">Simpan Data</button>
                  </div>

                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
