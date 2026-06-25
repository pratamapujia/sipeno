<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal Kelas {{ $kelas->nama_kelas }}</title>

    {{-- Gunakan stylesheet utama aplikasi Anda --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/shared/iconly.css') }}"> --}}

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

      .print-header p {
        margin-bottom: 0;
        font-size: 12px;
        color: #333;
      }

      .shift-section {
        margin-bottom: 30px;
      }

      .shift-title {
        font-size: 13px;
        font-weight: bold;
        background-color: #f0f0f0 !important;
        padding: 6px 10px;
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
        padding: 6px 4px !important;
        text-align: center;
        vertical-align: middle !important;
      }

      .table-print th {
        background-color: #e5e5e5 !important;
        color: #000 !important;
        font-weight: bold;
        font-size: 11px;
        text-transform: uppercase;
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
        color: #000;
        display: block;
        font-size: 11px;
      }

      .text-guru {
        font-size: 10px;
        color: #444;
        display: block;
        margin-top: 2px;
      }

      .bg-istirahat {
        background-color: #f5f5f5 !important;
        font-style: italic;
        color: #666;
        font-weight: bold;
        letter-spacing: 2px;
      }

      .text-kosong {
        color: #999;
        font-style: italic;
      }

      {{-- Pengaturan tombol cetak manual jika otomatis cetak tidak berjalan --}} .no-print-area {
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

        {{-- Menjaga agar halaman tidak terpotong canggung di tengah tabel --}} .shift-section {
          page-break-inside: avoid;
        }
      }
    </style>
  </head>
  {{-- Mengaktifkan trigger window.print() otomatis saat halaman selesai dimuat --}}

  <body onload="window.print();">

    {{-- Bar Tombol Kontrol Menyerupai Mazer Style (Hanya muncul di browser, hilang saat diprint) --}}
    <div class="no-print no-print-area d-flex justify-content-between align-items-center">
      <span class="text-muted small"><i class="fas fa-info-circle"></i> Tip: Gunakan kertas ukuran A4/F4 dengan mode Portrait.</span>
      <div>
        <button onclick="window.print();" class="btn btn-sm btn-primary me-2">
          <i class="fas fa-print"></i> Cetak Ulang
        </button>
        <button onclick="window.close();" class="btn btn-sm btn-secondary">
          <i class="fas fa-times"></i> Tutup Halaman
        </button>
      </div>
    </div>

    <div class="container-fluid">
      {{-- Kop / Header Dokumen Cetak --}}
      <div class="print-header">
        <h2>Jadwal Pelajaran Kelas {{ $kelas->nama_kelas }}</h2>
        <p>Tahun Ajaran: <b>{{ $academicYears->tahun_ajaran }}</b> | Semester: <b>{{ $academicYears->semester }}</b></p>
        <small class="text-muted">Simulasi Batch: {{ $batch->nama }}</small>
      </div>

      {{-- LOGIKA PEMISAHAN DATA SHIFT --}}
      @php
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

      {{-- Render Per Shift --}}
      @foreach ($shifts as $key => $shift)
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
                    {{-- Kolom Keterangan Waktu --}}
                    <td class="cell-jam {{ $slot->is_istirahat ? 'bg-istirahat' : '' }}">
                      Jam ke-{{ $slot->slot_number }}<br>
                      <span style="font-size: 9px; font-weight: normal;">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</span>
                    </td>

                    {{-- Kolom Jadwal Hari --}}
                    @foreach ($days as $day)
                      @if ($slot->is_istirahat)
                        <td class="bg-istirahat">ISTIRAHAT</td>
                      @else
                        <td class="cell-day">
                          @if (isset($jadwalMatrix[$slot->id][$day]))
                            @php $s = $jadwalMatrix[$slot->id][$day]; @endphp
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

      {{-- Bagian Tanda Tangan / Titimangsa Kurikulum --}}
      <div class="row mt-5 no-print-inside" style="page-break-inside: avoid;">
        <div class="col-8"></div>
        <div class="col-4 text-center">
          <p>Sidoarjo, {{ now()->translatedFormat('d F Y') }}</p>
          <p style="margin-bottom: 60px;">Waka. Urusan Kurikulum</p>
          <p class="fw-bold text-decoration-underline" style="margin-bottom: 0;">_______________________</p>
          <p class="text-muted small">NIP. .........................</p>
        </div>
      </div>

    </div>

  </body>

</html>
