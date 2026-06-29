<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal - {{ $guru->nama_guru }}</title>

    {{-- Gunakan stylesheet utama aplikasi Anda --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">

    <style>
      body {
        background-color: #fff !important;
        color: #000 !important;
        font-family: 'Arial', sans-serif;
        font-size: 12px;
      }

      .print-header {
        text-align: center;
        margin-bottom: 25px;
        border-bottom: 3px double #000;
        padding-bottom: 15px;
      }

      .print-header h2 {
        font-size: 20px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 5px;
        color: #000;
      }

      .biodata-section {
        margin-bottom: 20px;
      }

      .biodata-section table {
        width: 50%;
      }

      .biodata-section td {
        padding: 3px 0;
        font-weight: bold;
      }

      .table-print {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
      }

      .table-print th,
      .table-print td {
        border: 1px solid #000 !important;
        padding: 8px 6px !important;
        vertical-align: middle !important;
      }

      .table-print th {
        background-color: #e5e5e5 !important;
        color: #000 !important;
        font-weight: bold;
        text-align: center;
      }

      .cell-hari {
        font-weight: bold;
        background-color: #fafafa;
        text-transform: uppercase;
        width: 15%;
        text-align: center;
      }

      .text-kosong {
        color: #777;
        font-style: italic;
        text-align: center;
      }

      .no-print-area {
        background: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
      }

      @media print {
        .no-print {
          display: none !important;
        }

        body {
          padding: 0;
          margin: 0;
        }
      }
    </style>
  </head>

  <body onload="window.print();">

    <div class="no-print no-print-area d-flex justify-content-between align-items-center">
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Gunakan ukuran kertas A4 dengan mode Portrait.</span>
      <div>
        <button onclick="window.print();" class="btn btn-sm btn-primary me-2"><i class="fas fa-print"></i> Cetak Dokumen</button>
        <button onclick="window.close();" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Tutup</button>
      </div>
    </div>

    <div class="container-fluid px-4 py-2">

      <div class="print-header">
        <h2>Jadwal Mengajar Pendidik</h2>
        <p class="mb-0">Sistem Informasi Penjadwalan Sekolah (SIPENO)</p>
      </div>

      <div class="biodata-section">
        <table>
          <tr>
            <td style="width: 150px;">Nama Guru</td>
            <td style="width: 10px;">:</td>
            <td>{{ $guru->nama_guru }}</td>
          </tr>
          <tr>
            <td>NIP / NUPTK</td>
            <td>:</td>
            <td>{{ $guru->nip ?? '-' }}</td>
          </tr>
          <tr>
            <td>Jadwal Rilis</td>
            <td>:</td>
            <td>{{ $activeBatch->nama }}</td>
          </tr>
        </table>
      </div>

      <table class="table-print">
        <thead>
          <tr>
            <th>Hari</th>
            <th style="width: 15%;">Jam Ke-</th>
            <th style="width: 15%;">Waktu</th>
            <th>Mata Pelajaran</th>
            <th style="width: 20%;">Kelas</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($days as $day)
            @php
              $jadwalHariIni = $jadwalPerHari[$day];
              $rowspan = $jadwalHariIni->count() > 0 ? $jadwalHariIni->count() : 1;
            @endphp

            <tr>
              <td class="cell-hari" rowspan="{{ $rowspan }}">{{ $day }}</td>

              @if ($jadwalHariIni->count() > 0)
                @foreach ($jadwalHariIni as $index => $j)
                  @if ($index > 0)
            <tr>
          @endif

          <td class="text-center font-bold">Jam ke-{{ $j->slotJam->slot_number }}</td>
          <td class="text-center">{{ substr($j->slotJam->start_time, 0, 5) }} - {{ substr($j->slotJam->end_time, 0, 5) }}</td>
          <td>{{ $j->mapel->nama_mapel }}</td>
          <td class="text-center font-bold">{{ $j->kelas->nama_kelas }}</td>

          @if ($index > 0)
            </tr>
          @endif
          @endforeach
        @else
          <td colspan="4" class="text-kosong">Tidak ada jadwal mengajar</td>
          @endif
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="row mt-5" style="page-break-inside: avoid;">
        <div class="col-8"></div>
        <div class="col-4 text-center">
          <p>Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
          <p style="margin-bottom: 60px;">Guru Mata Pelajaran</p>
          <p class="fw-bold text-decoration-underline" style="margin-bottom: 0;">{{ $guru->nama_guru }}</p>
          <p class="text-muted small">NIP. {{ $guru->nip ?? '.........................' }}</p>
        </div>
      </div>

    </div>

  </body>

</html>
