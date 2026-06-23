@extends('layouts.main')

@section('title')
  <title>Detail Jadwal Kelas</title>
@endsection

@section('main')
  <div class="page-heading">
    <div class="page-title mb-3">
      <div class="row align-items-center">
        <div class="col-12 col-md-8">
          <h3>Pratinjau Jadwal Kelas</h3>
          <p class="text-muted">Simulasi: <b class="text-primary">{{ $batch->nama }}</b>
            @if ($batch->status == 'active')
              <span class="badge bg-success ms-2">AKTIF</span>
            @else
              <span class="badge bg-secondary ms-2">DRAFT</span>
            @endif
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <a href="{{ route('admin.jadwal.index') }}" class="btn btn-light-secondary icon icon-left me-2">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          @if ($selectedKelasId)
            <a href="{{ route('admin.jadwal.print', ['id' => $batch->id, 'kelas_id' => $selectedKelasId]) }}" target="_blank" class="btn btn-primary icon icon-left shadow">
              <i class="fas fa-print"></i> Cetak Jadwal
            </a>
          @endif
        </div>
      </div>
    </div>

    <section class="section">
      <div class="card mb-4">
        <div class="card-body">
          <form action="{{ route('admin.jadwal.show', $batch->id) }}" method="GET" id="form-filter">
            <div class="form-group mb-0">
              <label for="kelas_id" class="form-label font-bold">Tampilkan Jadwal Untuk Kelas:</label>
              <select name="kelas_id" id="kelas_id" class="form-select w-auto min-w-200" onchange="document.getElementById('form-filter').submit();">
                @foreach ($kelasList as $kelas)
                  <option value="{{ $kelas->id }}" {{ $selectedKelasId == $kelas->id ? 'selected' : '' }}>
                    {{ $kelas->kelas }}
                  </option>
                @endforeach
              </select>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="width: 15%;">Jam / Waktu</th>
                  @foreach ($days as $day)
                    <th style="width: 17%;">{{ $day }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach ($slots as $slot)
                  <tr class="{{ $slot->is_istirahat ? 'table-warning' : '' }}">
                    <td>
                      <b class="text-nowrap">Jam ke-{{ $slot->slot_number }}</b><br>
                      <small class="text-muted text-nowrap">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</small>
                    </td>

                    @foreach ($days as $day)
                      <td>
                        @if ($slot->is_istirahat)
                          <span class="text-muted font-italic">ISTIRAHAT</span>
                        @else
                          @if (isset($jadwalMatrix[$slot->id][$day]))
                            @php $item = $jadwalMatrix[$slot->id][$day]; @endphp
                            <div class="d-flex flex-column align-items-center">
                              <span class="badge bg-primary mb-1 text-wrap" style="line-height: 1.4;">{{ $item->mapel->nama_mapel }}</span>
                              <small class="text-muted fw-bold">{{ $item->guru->nama_guru }}</small>
                            </div>
                          @else
                            <span class="text-light-muted">-Kosong-</span>
                          @endif
                        @endif
                      </td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
