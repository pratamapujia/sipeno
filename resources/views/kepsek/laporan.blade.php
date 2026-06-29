@extends('layouts.main')

@section('title')
  <title>Pemantauan Jadwal Akademik</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title mb-3">
      <h3><i class="bi bi-file-earmark-bar-graph-fill text-primary"></i> Laporan & Pemantauan Jadwal</h3>
      <p class="text-muted">Fasilitas monitoring distribusi jam mengajar guru dan jadwal kelas secara real-time.</p>
    </div>

    <section class="section">
      {{-- CARD WIDGET FILTER --}}
      <div class="card shadow-sm">
        <div class="card-header bg-light-primary py-3">
          <h5 class="card-title mb-0 fs-6 text-primary"><i class="bi bi-funnel-fill"></i> Panel Instrumen Pemantauan</h5>
        </div>
        <div class="card-body pt-4">
          <form action="{{ route('kepsek.pemantauan') }}" method="GET" id="filterForm">
            <div class="row align-items-end g-3">

              <div class="col-md-3">
                <label class="form-label fw-bold small">1. Pilih Kategori Pemantauan</label>
                <select name="tipe" id="tipeSelect" class="form-select" onchange="toggleFilterInput()">
                  <option value="guru" {{ $tipe == 'guru' ? 'selected' : '' }}>Berdasarkan Guru Pengajar</option>
                  <option value="kelas" {{ $tipe == 'kelas' ? 'selected' : '' }}>Berdasarkan Rombongan Belajar (Kelas)</option>
                </select>
              </div>

              <div class="col-md-6" id="wrapperGuru" style="{{ $tipe == 'kelas' ? 'display:none;' : '' }}">
                <label class="form-label fw-bold small">2. Nama Guru</label>
                <select id="selectGuru" class="form-select" {{ $tipe == 'guru' ? 'name=id required' : '' }}>
                  <option value="">-- Pilih Guru --</option>
                  @foreach ($gurus as $g)
                    <option value="{{ $g->id }}" {{ $tipe == 'guru' && $selected_id == $g->id ? 'selected' : '' }}>
                      {{ $g->nama_guru }} (NIP: {{ $g->nip ?? '-' }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6" id="wrapperKelas" style="{{ $tipe == 'guru' ? 'display:none;' : '' }}">
                <label class="form-label fw-bold small">2. Pilih Ruang Kelas</label>
                <select id="selectKelas" class="form-select" {{ $tipe == 'kelas' ? 'name=id required' : '' }}>
                  <option value="">-- Pilih Kelas --</option>
                  @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $tipe == 'kelas' && $selected_id == $k->id ? 'selected' : '' }}>
                      Kelas {{ $k->nama_kelas }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="bi bi-search"></i> Tampilkan Data</button>
              </div>

            </div>
          </form>
        </div>
      </div>

      {{-- KONDISI OPERASIONAL DATA BLOCK --}}
      @if (!$activeBatch)
        <div class="alert alert-light-warning border rounded p-4 text-center">
          <i class="bi bi-exclamation-triangle-fill fs-3 text-warning mb-2 d-block"></i>
          <h5>Sistem Belum Memiliki Jadwal Aktif</h5>
          <p class="mb-0 text-sm">Bagian kurikulum belum merilis atau mengaktifkan paket jadwal pelajaran pada semester ini.</p>
        </div>
      @elseif(!$selected_id)
        <div class="alert alert-light-info border rounded p-4 text-center">
          <i class="bi bi-info-circle-fill fs-3 text-info mb-2 d-block"></i>
          <h5>Siap Melakukan Pemantauan</h5>
          <p class="mb-0 text-sm">Silakan tentukan kategori pemantauan dan pilih entitas data pada panel di atas untuk meninjau laporan jadwal.</p>
        </div>
      @else
        {{-- HEADER RESUME INFORMASI --}}
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <p class="fw-bold text-success">
                <i class="fas fa-calendar-day me-1"></i> JADWAL AKTIF : {{ $activeBatch->nama }}
              </p>
              {{-- <span class="badge bg-success mb-2"><i class="bi bi-calendar-check me-1"></i> JADWAL AKTIF : {{ $activeBatch->nama }}</span> --}}
              <h4 class="text-primary fw-bold mb-0">
                @if ($tipe == 'guru')
                  <i class="bi bi-person-badge-fill"></i> Jurnal Mengajar: {{ $selectedEntity->nama_guru }}
                @else
                  <i class="bi bi-house-door-fill"></i> Distribusi Jadwal: Kelas {{ $selectedEntity->nama_kelas }}
                @endif
              </h4>
            </div>
            <div class="text-md-end px-4 py-2">
              <small class="text-muted d-block text-uppercase fw-bold text-xs" style="letter-spacing: 1px;">Total Durasi Beban</small>
              <h4 class="mb-0 text-dark font-extrabold">{{ $totalJam }} <span class="fs-6 fw-normal text-muted">Jam Pelajaran (JP)</span></h4>
            </div>
          </div>
        </div>

        {{-- GRID MATRIKS JADWAL MINGGUAN --}}
        <div class="row">
          @foreach ($days as $day)
            @php $jadwalHariIni = $jadwalPerHari[$day] ?? collect(); @endphp
            <div class="col-12 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-header bg-light border-bottom py-3 d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 text-dark fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i> Hari {{ $day }}</h6>
                  <span class="badge bg-light-secondary text-dark small fw-bold">{{ $jadwalHariIni->count() }} JP Sesi</span>
                </div>
                <div class="card-body p-0">
                  @if ($jadwalHariIni->count() > 0)
                    <div class="table-responsive">
                      <table class="table table-hover table-striped mb-0 text-center align-middle small">
                        <thead class="table-dark">
                          <tr>
                            <th style="width: 12%;">Jam Ke-</th>
                            <th style="width: 18%;">Alokasi Waktu</th>
                            @if ($tipe == 'kelas')
                              <th>Dosen / Guru Pengajar</th>
                            @endif
                            <th>Mata Pelajaran</th>
                            @if ($tipe == 'guru')
                              <th style="width: 20%;">Target Kelas</th>
                            @endif
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($jadwalHariIni as $j)
                            <tr>
                              <td class="fw-bold text-primary">JP-{{ $j->slotJam->slot_number }}</td>
                              <td><small class="text-muted">{{ substr($j->slotJam->start_time, 0, 5) }} - {{ substr($j->slotJam->end_time, 0, 5) }} Wib</small></td>
                              @if ($tipe == 'kelas')
                                <td class="text-start fw-semibold"><i class="bi bi-person me-1"></i> {{ $j->guru->nama_guru }}</td>
                              @endif
                              <td class="text-start fw-bold">{{ $j->mapel->nama_mapel }}</td>
                              @if ($tipe == 'guru')
                                <td><span class="badge bg-success px-3 py-2 font-bold">{{ $j->kelas->nama_kelas }}</span></td>
                              @endif
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  @else
                    <div class="text-center py-4 text-muted">
                      <small class="text-italic"><i class="bi bi-moon-stars me-1"></i> Tidak ada aktivitas tatap muka kelas / Kosong</small>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>
  </div>

  {{-- JAVASCRIPT TOGGLE FILTER LAYER MURE --}}
  <script>
    function toggleFilterInput() {
      const tipe = document.getElementById('tipeSelect').value;
      const wrapperGuru = document.getElementById('wrapperGuru');
      const wrapperKelas = document.getElementById('wrapperKelas');
      const selectGuru = document.getElementById('selectGuru');
      const selectKelas = document.getElementById('selectKelas');

      if (tipe === 'guru') {
        // 1. Tampilkan wrapper Guru, sembunyikan wrapper Kelas
        wrapperGuru.style.display = 'block';
        wrapperKelas.style.display = 'none';

        // 2. Aktifkan atribut name dan required pada dropdown GURU
        selectGuru.setAttribute('name', 'id');
        selectGuru.setAttribute('required', 'required');

        // 3. Hapus atribut name dan required pada dropdown KELAS agar tidak ikut terkirim ke URL
        selectKelas.removeAttribute('name');
        selectKelas.removeAttribute('required');
        selectKelas.value = ""; // Reset pilihan kelas
      } else {
        // 1. Tampilkan wrapper Kelas, sembunyikan wrapper Guru
        wrapperGuru.style.display = 'none';
        wrapperKelas.style.display = 'block';

        // 2. Aktifkan atribut name dan required pada dropdown KELAS
        selectKelas.setAttribute('name', 'id');
        selectKelas.setAttribute('required', 'required');

        // 3. Hapus atribut name dan required pada dropdown GURU agar tidak ikut terkirim ke URL
        selectGuru.removeAttribute('name');
        selectGuru.removeAttribute('required');
        selectGuru.value = ""; // Reset pilihan guru
      }
    }
  </script>
@endsection
