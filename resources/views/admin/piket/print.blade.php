<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <title>Cetak Jadwal Guru Piket</title>
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">

    <style>
      body {
        font-size: 13px !important;
        background-color: #fff;
      }

      .table-bw {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
      }

      .table-bw th,
      .table-bw td {
        border: 2px solid #000 !important;
        padding: 10px;
        vertical-align: middle;
        color: #000 !important;
      }

      .table-bw th {
        font-weight: 900 !important;
        text-transform: uppercase;
        text-align: center;
        background-color: transparent !important;
      }

      @media print {
        .no-print {
          display: none !important;
        }

        body {
          padding: 0 !important;
          margin: 0 !important;
        }

        * {
          -webkit-print-color-adjust: exact !important;
          print-color-adjust: exact !important;
        }

        @page {
          margin: 1cm;
        }
      }
    </style>
  </head>

  <body class="bg-white text-dark p-4" onload="window.print();">

    <div class="no-print bg-light p-3 mb-4 rounded d-flex justify-content-between align-items-center border">
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Gunakan ukuran kertas A4/F4.</span>
      <div>
        <button onclick="window.print();" class="btn btn-primary btn-sm fw-bold me-2"><i class="fas fa-print me-1"></i> Cetak</button>
        <button onclick="window.close();" class="btn btn-secondary btn-sm fw-bold"><i class="fas fa-times me-1"></i> Tutup</button>
      </div>
    </div>

    <div class="text-center mb-4">
      <img src="{{ asset('assets/static/images/kop-print.png') }}" alt="Kop Surat" class="img-fluid w-100">
    </div>

    <div class="text-center mb-4 text-black">
      <h4 class="fw-bold text-uppercase mb-1" style="color: #000;">Daftar Tugas Guru Piket</h4>
      <p class="mb-0">Tahun Ajaran: <b>{{ $activeYear->tahun_ajaran }}</b> | Semester: <b>{{ $activeYear->semester }}</b></p>
    </div>

    <table class="table-bw text-center align-middle">
      <thead>
        <tr>
          <th style="width: 30%;">Hari</th>
          <th style="width: 70%;">Nama Guru Yang Bertugas</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($hari as $day)
          @php $pikets = $piketData[$day] ?? collect(); @endphp
          <tr>
            <td class="fw-bold" rowspan="{{ $pikets->count() > 0 ? $pikets->count() : 1 }}">{{ $day }}</td>
            @if ($pikets->count() > 0)
              @foreach ($pikets as $index => $piket)
                @if ($index > 0)
          <tr>
        @endif
        <td class="fw-bold" style="text-align: left; padding-left: 20px;">
          <i class="fas fa-check-square me-2"></i> {{ $piket->guru->nama_guru }}
        </td>
        @if ($index > 0)
          </tr>
        @endif
        @endforeach
      @else
        <td class="fst-italic text-center">-</td>
        @endif
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="row mt-5" style="page-break-inside: avoid; color: #000;">
      <div class="col-8"></div>
      <div class="col-4 text-center">
        <p class="mb-1">Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
        <p style="margin-bottom: 80px;">Waka Kurikulum</p>
        <p class="fw-bold text-decoration-underline mb-0">{{ Auth::user()->name }}</p>
      </div>
    </div>

  </body>

</html>
