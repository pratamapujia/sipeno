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
        padding: 6px 4px !important;
        text-align: center;
        vertical-align: middle !important;
        color: #000 !important;
      }

      .table-bw th {
        font-weight: 900 !important;
        text-transform: uppercase;
        font-size: 11px;
      }

      .shift-title {
        font-size: 13px;
        font-weight: 900;
        padding: 6px 10px;
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
        color: #000;
        display: block;
        font-size: 11px;
      }

      .text-guru {
        font-size: 10px;
        color: #000;
        display: block;
        margin-top: 2px;
      }

      .bg-kosong {
        background: repeating-linear-gradient(45deg, #fff, #fff 5px, #e0e0e0 5px, #e0e0e0 10px) !important;
        font-style: italic;
        font-weight: bold;
      }

      .bg-istirahat-row td {
        background-color: #f0f0f0 !important;
        font-weight: 900;
        font-style: italic;
        letter-spacing: 1px;
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
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Tip: Gunakan kertas ukuran A4/F4 dengan mode Portrait.</span>
      <div>
        <button onclick="window.print();" class="btn btn-sm btn-primary fw-bold me-2"><i class="fas fa-print me-1"></i> Cetak Ulang</button>
        <button onclick="window.close();" class="btn btn-sm btn-secondary fw-bold"><i class="fas fa-times me-1"></i> Tutup</button>
      </div>
    </div>

    <div class="container-fluid px-0">
      <div class="text-center mb-4">
        <img src="{{ asset('assets/static/images/kop-print.png') }}" alt="Kop Surat" class="img-fluid w-100">
      </div>

      <div class="text-center mt-2">
        <h4 class="fw-bold text-uppercase text-dark">Jadwal Pelajaran Kelas {{ $kelas->nama_kelas }}</h4>
        <div class="d-flex justify-content-between mt-3 text-start">
          <p><b>Tahun Ajaran:</b> {{ $academicYears->tahun_ajaran }} <br><b>Semester:</b> {{ $academicYears->semester }}</p>
          <p><b>Wali Kelas:</b> {{ $kelas->waliKelas->nama_guru ?? 'Belum diatur' }} <br><b>Status Jadwal:</b> {{ strtoupper($batch->status) }}</p>
        </div>
      </div>

      @php
        $shifts = [
            'Pagi' => [
                'label' => 'SHIFT PAGI (Mapel Teori | Slot 1 s/d 10)',
                'slots' => $slots->where('slot_number', '<=', 10),
            ],
            'Siang' => [
                'label' => 'SHIFT SIANG (Mapel Praktikum | Slot 11 s/d 17)',
                'slots' => $slots->where('slot_number', '>', 10),
            ],
        ];
        $waktuJumat = [1 => '07:00 - 07:30', 2 => '07:30 - 08:00', 3 => '08:00 - 08:30', 4 => '08:45 - 09:15', 5 => '09:15 - 09:45', 6 => '09:45 - 10:15'];
      @endphp

      @foreach ($shifts as $key => $shift)
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
                    <td class="cell-jam">
                      Jam ke-{{ $slot->slot_number }}<br>
                      <span style="font-size: 9px; font-weight: normal;">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</span>
                    </td>

                    @foreach ($days as $day)
                      @if ($day == 'Jumat' && $slot->slot_number >= 7 && $slot->slot_number <= 10)
                        <td class="bg-kosong">-</td>
                      @else
                        <td class="cell-day">
                          @if (isset($jadwalMatrix[$slot->id][$day]))
                            @php $s = $jadwalMatrix[$slot->id][$day]; @endphp
                            @if ($day == 'Jumat' && $slot->slot_number <= 6)
                              <span style="display:block; font-size:8px; border-bottom: 1px solid #000; margin-bottom:3px; font-weight:bold;">{{ $waktuJumat[$slot->slot_number] ?? '' }}</span>
                            @endif
                            <span class="text-mapel">{{ $s->mapel->nama_mapel }}</span>
                            <span class="text-guru">{{ $s->guru->nama_guru }}</span>
                          @else
                            <small class="text-muted">-</small>
                          @endif
                        </td>
                      @endif
                    @endforeach
                  </tr>

                  {{-- SISIPAN: ISTIRAHAT JUMAT --}}
                  @if ($slot->slot_number == 3)
                    <tr class="bg-istirahat-row">
                      <td>ISTIRAHAT</td>
                      @foreach ($days as $day)
                        @if ($day == 'Jumat')
                          <td>ISTIRAHAT JUMAT</td>
                        @else
                          <td class="bg-kosong">-</td>
                        @endif
                      @endforeach
                    </tr>
                  @endif

                  {{-- SISIPAN: ISTIRAHAT 1 SENIN-KAMIS --}}
                  @if ($slot->slot_number == 4)
                    <tr class="bg-istirahat-row">
                      <td>ISTIRAHAT</td>
                      @foreach ($days as $day)
                        @if ($day != 'Jumat')
                          <td>ISTIRAHAT 1</td>
                        @else
                          <td class="bg-kosong">-</td>
                        @endif
                      @endforeach
                    </tr>
                  @endif

                  {{-- SISIPAN: ISTIRAHAT 2 SENIN-KAMIS --}}
                  @if ($slot->slot_number == 7)
                    <tr class="bg-istirahat-row">
                      <td>ISTIRAHAT</td>
                      @foreach ($days as $day)
                        @if ($day != 'Jumat')
                          <td>ISTIRAHAT 2</td>
                        @else
                          <td class="bg-kosong">-</td>
                        @endif
                      @endforeach
                    </tr>
                  @endif
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      @endforeach

      <div class="row mt-5 no-print-inside" style="page-break-inside: avoid; color: #000;">
        <div class="col-6 text-center">
          <p class="mb-1">Mengetahui,</p>
          <p style="margin-bottom: 80px;">Wali Kelas {{ $kelas->nama_kelas }}</p>
          <p class="fw-bold text-decoration-underline mb-0">{{ $kelas->waliKelas->nama_guru ?? '_______________________' }}</p>
        </div>
        <div class="col-6 text-center">
          <p class="mb-1">Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
          <p style="margin-bottom: 80px;">Waka Kurikulum</p>
          <p class="fw-bold text-decoration-underline mb-0">{{ Auth::user()->name }}</p>
        </div>
      </div>
    </div>
  </body>

</html>
