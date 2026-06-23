<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <title>Jadwal Kelas {{ $kelas->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        font-family: Arial, sans-serif;
        padding: 20px;
        color: #000;
        background: #fff;
      }

      .header-kop {
        text-align: center;
        border-bottom: 3px solid #000;
        padding-bottom: 15px;
        margin-bottom: 20px;
      }

      .table-print th,
      .table-print td {
        border: 1px solid #000 !important;
        padding: 10px;
        vertical-align: middle;
      }

      .table-print th {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
      }

      @media print {
        .no-print {
          display: none !important;
        }

        body {
          padding: 0;
        }
      }
    </style>
  </head>

  <body onload="window.print()">

    <div class="d-flex justify-content-end mb-3 no-print">
      <button onclick="window.print()" class="btn btn-primary me-2">Cetak / Simpan PDF</button>
      <button onclick="window.close()" class="btn btn-secondary">Tutup Tab</button>
    </div>

    <div class="header-kop">
      <h2>JADWAL MATA PELAJARAN</h2>
      <h4>Tahun Ajaran: {{ $batch->academicYear->tahun_ajaran ?? '...' }} Semester: {{ $batch->academicYear->semester ?? '...' }}</h4>
    </div>

    <div class="mb-3 d-flex justify-content-between align-items-end">
      <h5 class="mb-0">Kelas: <b>{{ $kelas->kelas }}</b></h5>
      <small class="text-muted">Kode Simulasi: {{ $batch->nama }}</small>
    </div>

    <table class="table table-bordered text-center table-print">
      <thead>
        <tr>
          <th style="width: 15%;">Jam / Waktu</th>
          @foreach ($days as $day)
            <th style="width: 17%;">{{ $day }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach ($slots as $slot)
          <tr style="{{ $slot->is_istirahat ? 'background-color: #e9ecef !important; -webkit-print-color-adjust: exact;' : '' }}">
            <td>
              <b>Jam ke-{{ $slot->slot_number }}</b><br>
              <small>{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</small>
            </td>
            @foreach ($days as $day)
              <td>
                @if ($slot->is_istirahat)
                  <i>Istirahat</i>
                @else
                  @if (isset($jadwalMatrix[$slot->id][$day]))
                    @php $item = $jadwalMatrix[$slot->id][$day]; @endphp
                    <div style="font-weight: bold; margin-bottom: 4px;">{{ $item->mapel->nama_mapel }}</div>
                    <div style="font-size: 0.85em; color: #444;">{{ $item->guru->nama_guru }}</div>
                  @else
                    <span style="color: #ccc;">-</span>
                  @endif
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>

  </body>

</html>
