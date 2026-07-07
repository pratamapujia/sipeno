<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal Kelas {{ $kelas->nama_kelas }}</title>

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
        padding: 8px 10px;
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
          margin: 0.5cm;
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

    <div class="text-center mb-2">
      <img src="{{ asset('assets/static/images/kop-print.png') }}" alt="Kop Surat SMK PGRI 1 Sidoarjo" class="img-fluid w-100">
    </div>

    <div class="text-center mt-2">
      <h4 class="fw-bold text-uppercase text-dark">Jadwal Pelajaran Kelas {{ $kelas->nama_kelas }}</h4>
      <div class="d-flex justify-content-between mt-2 text-start">
        <p><b>Tahun Ajaran:</b> {{ $academicYear->tahun_ajaran }} <br> <b>Semester:</b> {{ $academicYear->semester }}</p>
        <p><b>Wali Kelas:</b> {{ $kelas->waliKelas->nama_guru ?? 'Belum diatur' }}</p>
      </div>
    </div>

    @php

      $waktuSeninPagi = [
          1 => '07:30 - 08:00',
          2 => '08:00 - 08:30',
          3 => '08:30 - 09:00',
          4 => '09:00 - 09:30',
          5 => '10:00 - 10:35',
          6 => '10:35 - 11:10',
          7 => '11:10 - 11:45',
          8 => '12:15 - 12:50',
          9 => '12:50 - 13:25',
          10 => '13:25 - 14:00',
      ];

      $waktuJumatPagi = [
          1 => '07:30 - 08:00',
          2 => '08:00 - 08:30',
          3 => '08:30 - 09:00',
          4 => '09:30 - 10:00',
          5 => '10:00 - 10:30',
          6 => '10:30 - 11:00',
      ];

      $waktuJumatSiang = [
          11 => '13:00 - 13:30',
          12 => '13:30 - 14:00',
          13 => '14:00 - 14:30',
          14 => '14:30 - 15:00',
          15 => '15:00 - 15:30',
          16 => '15:30 - 16:00',
          17 => '16:00 - 16:30',
      ];
    @endphp

    <div class="table-responsive">
      <table class="table-bw text-center align-middle">
        <thead>
          <tr>
            <th style="width: 15%;">Hari</th>
            <th style="width: 15%;">Jam Ke-</th>
            <th style="width: 20%;">Waktu</th>
            <th style="width: 25%;">Mata Pelajaran</th>
            <th style="width: 25%;">Guru Pengajar</th>
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

                  {{-- Kondisi Waktu: Khusus Jumat, Senin Pagi atau Normal --}}
                  <td>
                    @if ($day == 'Jumat')
                      @if ($j->slotJam->slot_number <= 6)
                        {{ $waktuJumatPagi[$j->slotJam->slot_number] ?? '' }}
                      @elseif ($j->slotJam->slot_number >= 11)
                        {{ $waktuJumatSiang[$j->slotJam->slot_number] ?? '' }}
                      @endif
                    @elseif ($day == 'Senin')
                      @if ($j->slotJam->slot_number <= 10)
                        {{ $waktuSeninPagi[$j->slotJam->slot_number] ?? '' }}
                      @endif
                    @else
                      {{ substr($j->slotJam->start_time, 0, 5) }} - {{ substr($j->slotJam->end_time, 0, 5) }}
                    @endif
                  </td>

                  <td class="fw-bold">{{ $j->mapel->nama_mapel }}</td>
                  <td>{{ $j->guru->nama_guru }}</td>
                </tr>
              @endforeach
            @else
              <tr>
                <td class="fw-bold">{{ $day }}</td>
                <td colspan="4" class="fst-italic text-center">Tidak ada jadwal kelas / Libur</td>
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
        <p class="mb-2">Kepala Sekolah</p>
        <img src="{{ asset('assets/static/images/ttd.jpeg') }}" alt="TTD Kepala Sekolah" style="width: 80px; height: 80px; margin: 5px auto; display: block;">
        <p class="fw-bold text-decoration-underline mb-0">Drs. Bahrul Ulum, M.Si</p>
      </div>
    </div>

  </body>

</html>
