<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua Jadwal Kelas</title>

    <link rel="stylesheet" href="{{ asset('assets/css/main/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/shared/iconly.css') }}">

    <style>
      body {
        background-color: #fff !important;
        color: #000 !important;
        font-family: 'Arial', sans-serif;
        font-size: 11px;
      }

      .print-header {
        text-align: center;
        margin-bottom: 25px;
        border-bottom: 3px double #000;
        padding-bottom: 10px;
      }

      .print-header h2 {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 5px;
      }

      .shift-section {
        margin-bottom: 25px;
      }

      .shift-title {
        font-size: 12px;
        font-weight: bold;
        background-color: #f0f0f0 !important;
        padding: 5px 10px;
        border: 1px solid #000;
        border-bottom: none;
        margin-bottom: 0;
        display: inline-block;
      }

      .table-print {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
      }

      .table-print th,
      .table-print td {
        border: 1px solid #000 !important;
        padding: 5px 4px !important;
        text-align: center;
        vertical-align: middle !important;
      }

      .table-print th {
        background-color: #e5e5e5 !important;
        font-weight: bold;
      }

      .cell-jam {
        font-weight: bold;
        background-color: #fafafa;
        width: 12%;
      }

      .cell-day {
        width: 17.6%;
      }

      .text-mapel {
        font-weight: bold;
        display: block;
      }

      .text-guru {
        font-size: 10px;
        color: #444;
        display: block;
      }

      .bg-istirahat {
        background-color: #f5f5f5 !important;
        font-style: italic;
        font-weight: bold;
      }

      .no-print-area {
        background: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
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
          padding: 0;
          margin: 0;
        }

        .shift-section {
          page-break-inside: avoid;
        }
      }
    </style>
  </head>

  <body onload="window.print();">

    <div class="no-print no-print-area d-flex justify-content-between align-items-center">
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Mode Cetak Massal: Setiap kelas akan otomatis berpindah ke halaman baru saat dicetak.</span>
      <div>
        <button onclick="window.print();" class="btn btn-sm btn-primary me-2"><i class="fas fa-print"></i> Cetak</button>
        <button onclick="window.close();" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Tutup</button>
      </div>
    </div>

    {{-- LOOPING UTAMA: Mengulang Cetak untuk Setiap Kelas --}}
    @foreach ($kelasList as $index => $kelas)
      @php
        // Ambil matriks jadwal khusus kelas ini
        $matrixId = $kelasMatrix[$kelas->id] ?? [];

        // Pisahkan data shift
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

      {{-- Bungkus per kelas dengan div page-break (kecuali iterasi terakhir agar tidak ada kertas kosong di akhir) --}}
      <div class="container-fluid {{ !$loop->last ? 'page-break' : '' }}">

        {{-- Kop Jadwal Kelas --}}
        <div class="print-header">
          <h2>Jadwal Pelajaran Kelas {{ $kelas->nama_kelas }}</h2>
          <p>Tahun Ajaran: <b>{{ $academicYears->tahun_ajaran }}</b> | Semester: <b>{{ $academicYears->semester }}</b></p>
          <small class="text-muted">Batch Model: {{ $batch->nama }}</small>
        </div>

        {{-- Render Tabel Shift Pagi & Siang --}}
        @foreach ($shifts as $shift)
          @if ($shift['slots']->count() > 0)
            <div class="shift-section">
              <div class="shift-title">{{ $shift['label'] }}</div>

              <table class="table-print">
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
                              <span class="text-muted italic">-</span>
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

        {{-- Tanda Tangan Dokumen --}}
        <div class="row mt-4" style="page-break-inside: avoid;">
          <div class="col-8"></div>
          <div class="col-4 text-center">
            <p>Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 50px;">Waka. Urusan Kurikulum</p>
            <p class="fw-bold text-decoration-underline" style="margin-bottom: 0;">_______________________</p>
            <p class="text-muted small">NIP. .........................</p>
          </div>
        </div>

      </div>
    @endforeach

  </body>

</html>
