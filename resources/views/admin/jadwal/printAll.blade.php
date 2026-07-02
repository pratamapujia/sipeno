<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua Jadwal Kelas</title>

    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">

    <style>
      body {
        font-size: 11px !important;
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
        padding: 5px 4px !important;
        text-align: center;
        vertical-align: middle !important;
        color: #000 !important;
      }

      .table-bw th {
        font-weight: 900 !important;
        text-transform: uppercase;
        background-color: transparent !important;
      }

      .shift-title {
        font-size: 12px;
        font-weight: 900;
        padding: 5px 10px;
        border: 2px solid #000;
        border-bottom: none;
        margin-bottom: 0;
        display: inline-block;
        color: #000;
      }

      .cell-jam {
        font-weight: bold;
        width: 12%;
      }

      .cell-day {
        width: 17.6%;
      }

      .text-mapel {
        font-weight: 900;
        display: block;
        color: #000;
      }

      .text-guru {
        font-size: 10px;
        display: block;
        color: #000;
      }

      .bg-istirahat {
        font-style: italic;
        font-weight: 900;
        color: #000;
        letter-spacing: 2px;
      }

      /* KUNCI UTAMA AMAN CETAK PER HALAMAN */
      .page-break {
        page-break-after: always;
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

        .shift-section {
          page-break-inside: avoid;
        }

        @page {
          margin: 1cm;
        }
      }
    </style>
  </head>

  <body class="bg-white text-dark p-4" onload="window.print();">

    <div class="no-print bg-light p-3 mb-4 rounded d-flex justify-content-between align-items-center border">
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Mode Cetak Massal: Setiap kelas akan otomatis berpindah ke halaman baru saat dicetak.</span>
      <div>
        <button onclick="window.print();" class="btn btn-sm btn-primary fw-bold me-2"><i class="fas fa-print me-1"></i> Cetak</button>
        <button onclick="window.close();" class="btn btn-sm btn-secondary fw-bold"><i class="fas fa-times me-1"></i> Tutup</button>
      </div>
    </div>

    {{-- LOOPING UTAMA: Mengulang Cetak untuk Setiap Kelas --}}
    @foreach ($kelasList as $index => $kelas)
      @php
        $matrixId = $kelasMatrix[$kelas->id] ?? [];
        $shifts = [
            'Pagi' => [
                'label' => 'SHIFT PAGI (Jam ke-1 s/d 12)',
                'slots' => $slots->where('slot_number', '<=', 12),
            ],
            'Siang' => [
                'label' => 'SHIFT SIANG (Jam ke-13 s/d 18)',
                'slots' => $slots->where('slot_number', '>', 12),
            ],
        ];
      @endphp

      <div class="container-fluid px-0 {{ !$loop->last ? 'page-break' : '' }}">

        {{-- KOP SURAT (Muncul di setiap halaman kelas) --}}
        <div class="text-center mb-4">
          <img src="{{ asset('assets/static/images/kop-print.png') }}" alt="Kop Surat SMK PGRI 1 Sidoarjo" class="img-fluid w-100">
        </div>

        {{-- HEADER DOKUMEN --}}
        <div class="text-center mt-2">
          <h4 class="fw-bold text-uppercase text-dark">Jadwal Pelajaran Kelas {{ $kelas->nama_kelas }}</h4>
          <div class="d-flex justify-content-between mt-3 text-start">
            <p><b>Tahun Ajaran:</b> {{ $academicYears->tahun_ajaran }} <br><b>Semester:</b> {{ $academicYears->semester }}</p>
            <p><b>Wali Kelas:</b> {{ $kelas->waliKelas->nama_guru ?? 'Belum diatur' }} <br><b>Status Jadwal:</b> {{ strtoupper($batch->status) }}</p>
          </div>
        </div>

        {{-- Render Tabel Shift Pagi & Siang --}}
        @foreach ($shifts as $shift)
          @if ($shift['slots']->count() > 0)
            <div class="shift-section mb-4">
              <div class="shift-title">{{ $shift['label'] }}</div>

              <table class="table-bw">
                <thead>
                  <tr>
                    <th>Jam / Waktu</th>
                    @foreach ($days as $day)
                      <th>{{ $day }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach ($shift['slots'] as $slot)
                    <tr>
                      <td class="cell-jam {{ $slot->is_istirahat ? 'bg-istirahat' : '' }}">
                        Jam ke-{{ $slot->slot_number }}<br>
                        <span style="font-size: 9px; font-weight: normal;">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</span>
                      </td>

                      @foreach ($days as $day)
                        @if ($slot->is_istirahat)
                          <td class="bg-istirahat">ISTIRAHAT</td>
                        @else
                          <td class="cell-day">
                            @if (isset($matrixId[$slot->id][$day]))
                              @php $s = $matrixId[$slot->id][$day]; @endphp
                              <span class="text-mapel">{{ $s->mapel->nama_mapel }}</span>
                              <span class="text-guru">{{ $s->guru->nama_guru }}</span>
                            @else
                              <span class="text-kosong">-</span>
                            @endif
                          </td>
                        @endif
                      @endforeach
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        @endforeach

        {{-- TANDA TANGAN --}}
        <div class="row mt-4" style="page-break-inside: avoid; color: #000;">
          <div class="col-8"></div>
          <div class="col-4 text-center">
            <p class="mb-1">Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 80px;">Waka Kurikulum</p>
            <p class="fw-bold text-decoration-underline mb-0">{{ Auth::user()->name }}</p>
          </div>
        </div>

      </div>
    @endforeach

  </body>

</html>
