@extends('layouts.main')

@section('title')
  <title>Tambah Plotting Mengajar</title>
  <link rel="stylesheet" href="{{ asset('assets/extensions/choices.js/public/assets/styles/choices.css') }}">
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Tambah Target Mengajar</h3>
          <p class="text-subtitle text-muted">Tahun Ajaran: {{ $thnAjaran->tahun_ajaran }} (Semester {{ ucfirst($thnAjaran->semester) }})</p>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="card">
        <div class="card-content">
          <div class="card-body">

            @if (session('error'))
              <div class="alert alert-danger alert-dismissible show fade">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <form action="{{ route('admin.plotting.store') }}" method="POST" class="form form-vertical">
              @csrf
              <input type="hidden" name="tahun_ajaran_id" value="{{ $thnAjaran->id }}">
              <div class="form-body">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label for="guru_id" class="fw-bold mb-1">Pilih Guru</label>
                      <select name="guru_id" id="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Guru --</option>
                        @foreach ($guru as $guru)
                          <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama_guru }}
                          </option>
                        @endforeach
                      </select>
                      @error('guru_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-12 mt-2">
                    <div class="form-group">
                      <label for="mapel_id" class="fw-bold mb-1">Mata Pelajaran (Bisa pilih lebih dari satu)</label>

                      <select name="mapel_id[]" id="mapel_id" class="choices form-select multiple-remove @error('mapel_id') is-invalid @enderror" multiple="multiple" required>
                        <optgroup label="Daftar Mata Pelajaran">
                          @foreach ($mapel as $m)
                            <option value="{{ $m->id }}" {{ is_array(old('mapel_id')) && in_array($m->id, old('mapel_id')) ? 'selected' : '' }}>
                              {{ $m->nama_mapel }} ({{ $m->beban_jam }} JP)
                            </option>
                          @endforeach
                        </optgroup>
                      </select>

                      @error('mapel_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-12 mt-3">
                    <div class="form-group">
                      <label class="fw-bold mb-2 d-block">Pilih Target Kelas (Bisa pilih lebih dari satu):</label>
                      @error('kelas_id')
                        <div class="text-danger small mb-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                      @enderror
                      <div class="p-3 border rounded">
                        <div class="row">
                          @foreach ($kelas as $k)
                            <div class="col-md-3 col-sm-6 mb-2">
                              <div class="form-check">
                                <input class="form-check-input @error('kelas_id') is-invalid @enderror" type="checkbox" name="kelas_id[]" value="{{ $k->id }}" id="kelas_{{ $k->id }}"
                                  {{ is_array(old('kelas_id')) && in_array($k->id, old('kelas_id')) ? 'checked' : '' }}>
                                <label class="form-check-input-label fw-semibold text-dark" for="kelas_{{ $k->id }}" style="cursor:pointer;">
                                  Kelas {{ $k->nama_kelas }}
                                </label>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.plotting.index') }}" class="btn btn-light-secondary me-1 mb-1">Kembali</a>
                    <button type="submit" class="btn btn-primary me-1 mb-1">Simpan Plotting</button>
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

@section('script')
  <script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/form-element-select.js') }}"></script>
@endsection
