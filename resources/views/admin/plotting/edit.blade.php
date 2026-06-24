@extends('layouts.main')

@section('title')
  <title>Edit Plotting Mengajar</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Edit Target Mengajar</h3>
          <p class="text-subtitle text-muted">Tahun Ajaran: {{ $thnAktif->tahun_ajaran }} (Semester {{ ucfirst($thnAktif->semester) }})</p>
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

            <form action="{{ route('admin.plotting.update', $plotting->id) }}" method="POST" class="form form-vertical">
              @csrf
              @method('PUT')

              <div class="form-body">
                <div class="row">

                  <div class="col-12">
                    <div class="form-group">
                      <label for="guru_id" class="fw-bold mb-1">Pilih Guru</label>
                      <select name="guru_id" id="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                        @foreach ($guru as $guru)
                          <option value="{{ $guru->id }}" {{ old('guru_id', $plotting->guru_id) == $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama_guru }} {{ $guru->nip ? '(' . $guru->nip . ')' : '' }}
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
                      <label for="mapel_id" class="fw-bold mb-1">Mata Pelajaran</label>
                      <select name="mapel_id" id="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror" required>
                        @foreach ($mapel as $mapel)
                          <option value="{{ $mapel->id }}" {{ old('mapel_id', $plotting->mapel_id) == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama_mapel }} ({{ $mapel->beban_jam }} JP)
                          </option>
                        @endforeach
                      </select>
                      @error('mapel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-12 mt-2">
                    <div class="form-group">
                      <label for="kelas_id" class="fw-bold mb-1">Target Kelas</label>
                      <select name="kelas_id" id="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                        @foreach ($kelas as $k)
                          <option value="{{ $k->id }}" {{ old('kelas_id', $plotting->kelas_id) == $k->id ? 'selected' : '' }}>
                            Kelas {{ $k->nama_kelas }}
                          </option>
                        @endforeach
                      </select>
                      @error('kelas_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-12 d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.plotting.index') }}" class="btn btn-light-secondary me-1 mb-1">Kembali</a>
                    <button type="submit" class="btn btn-primary me-1 mb-1">Perbarui Plotting</button>
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
