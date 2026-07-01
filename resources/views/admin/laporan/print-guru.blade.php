<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal - {{ $guru->nama_guru }}</title>

    {{-- Memanggil CSS Utama Mazer --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">

    <style>
      body {
        font-size: 13px !important;
        background-color: #fff;
      }

      /* TEMA TABEL HITAM PUTIH (PRINT RESMI) */
      .table-bw {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
      }

      .table-bw th,
      .table-bw td {
        border: 2px solid #000 !important;
        /* Border tebal dan hitam pekat */
        padding: 8px 10px;
        vertical-align: middle;
        color: #000 !important;
        /* Teks hitam murni */
      }

      .table-bw th {
        font-weight: 900 !important;
        /* Header ditebalkan maksimal */
        text-transform: uppercase;
        text-align: center;
        background-color: transparent !important;
      }

      /* Memaksa margin saat di-print */
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
        <button onclick="window.print();" class="btn btn-primary btn-sm fw-bold me-2"><i class="fas fa-print me-1"></i> Cetak Dokumen</button>
        <button onclick="window.close();" class="btn btn-secondary btn-sm fw-bold"><i class="fas fa-times me-1"></i> Tutup</button>
      </div>
    </div>

    {{-- KOP SURAT --}}
    <div class="text-center mb-4">
      <img src="{{ asset('assets/static/images/kop-print.png') }}" alt="Kop Surat SMK PGRI 1 Sidoarjo" class="img-fluid w-100">
    </div>

    <div class="text-center mb-3 text-black">
      <h5 class="fw-bold text-uppercase mb-1" style="color: #000;">JADWAL MENGAJAR {{ $guru->nama_guru }}</h5>
      <p class="small mb-0">Periode Rilis: {{ $activeBatch->nama }}</p>
    </div>

    <div class="table-responsive">
      <table class="table-bw text-center align-middle">
        <thead>
          <tr>
            <th style="width: 15%;">Hari</th>
            <th style="width: 15%;">Jam Ke-</th>
            <th style="width: 20%;">Waktu</th>
            <th style="width: 30%;">Mata Pelajaran</th>
            <th style="width: 20%;">Kelas</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($days as $day)
            @php
              $jadwalHariIni = $jadwalPerHari[$day];
              $rowspan = $jadwalHariIni->count() > 0 ? $jadwalHariIni->count() : 1;
            @endphp

            @if ($jadwalHariIni->count() > 0)
              @foreach ($jadwalHariIni as $j)
                <tr>
                  @if ($loop->first)
                    <td class="fw-bold" rowspan="{{ $rowspan }}">{{ $day }}</td>
                  @endif

                  <td class="fw-bold">Jam ke-{{ $j->slotJam->slot_number }}</td>
                  <td>{{ substr($j->slotJam->start_time, 0, 5) }} - {{ substr($j->slotJam->end_time, 0, 5) }}</td>
                  <td class="fw-bold">{{ $j->mapel->nama_mapel }}</td>
                  <td class="fw-bold">{{ $j->kelas->nama_kelas }}</td>
                </tr>
              @endforeach
            @else
              <tr>
                <td class="fw-bold">{{ $day }}</td>
                <td colspan="4" class="fst-italic text-center">Tidak ada jadwal mengajar</td>
              </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>

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
