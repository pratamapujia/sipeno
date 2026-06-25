@extends('layouts.main')

@section('title')
  <title>Generate Jadwal Otomatis</title>
  <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
  <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endsection

@section('main')
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>
  {{-- <div class="flash-data" data-gagal="{{ Session::get('error') }}"></div> --}}

  <div class="page-heading">
    <div class="page-title">
      <div class="row align-items-center">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Sistem Penjadwalan Cerdas (Algoritma Genetika)</h3>
          @if (isset($academicYears))
            <p class="text-subtitle text-muted">Tahun Ajaran: <b class="text-primary">{{ $academicYears->tahun_ajaran }} - Semester {{ ucfirst($academicYears->semester) }}</b></p>
          @else
            <p class="text-danger"><b>Peringatan:</b> Belum ada Tahun Ajaran yang diaktifkan.</p>
          @endif
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first text-md-end mb-3">
          @if (isset($academicYears))
            <form action="{{ route('admin.jadwal.generate') }}" method="POST" id="form-generate">
              @csrf
              <button type="button" id="btn-generate" class="btn btn-primary icon icon-left shadow">
                <i class="fas fa-magic"></i> Generate Jadwal Baru
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>

    @if (session('warning_banyak'))
      <div class="alert alert-light-warning alert-dismissible show fade mt-3">
        <h6><i class="fas fa-exclamation-triangle me-2"></i> Simulasi Selesai Dengan Catatan:</h6>
        <p class="small mb-2">Algoritma tidak menemukan kombinasi 100% sempurna. Data tetap disimpan sebagai <b>Draft</b>. Silakan lakukan penyesuaian manual pada rincian berikut:</p>
        <ul class="mb-0 text-sm">
          @foreach (session('warning_banyak') as $warnMsg)
            <li>{!! $warnMsg !!}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session('error_banyak'))
      <div class="alert alert-light-danger color-danger show fade">
        <h5 class="alert-heading"><i class="fas fa-times-circle me-2"></i> Simulasi Gagal: Terdeteksi Jadwal Bentrok</h5>
        <p class="text-sm mb-2">Algoritma tidak dapat menemukan kombinasi jadwal yang sempurna. Silakan periksa pembagian beban mengajar Anda. Berikut adalah rincian bentrok yang terjadi:</p>
        <hr>
        <ul class="mb-0 text-sm">
          @foreach (session('error_banyak') as $pesan)
            <li class="mb-1">{!! $pesan !!}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('error'))
      <div class="alert alert-light-danger color-danger alert-dismissible show fade">
        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Peringatan Algoritma:</strong>
        <span class="ms-1">{{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <section class="section">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Riwayat Generate Jadwal</h4>
        </div>
        <div class="card-body">
          <table class="table table-striped" id="table1">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Simulasi</th>
                <th>Status</th>
                <th>Skor Fitness (Penalti)</th>
                <th>Dibuat Pada</th>
                <th data-sortable="false">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($batches as $batch)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><b>{{ $batch->nama }}</b></td>
                  <td>
                    @if ($batch->status == 'active')
                      <span class="badge bg-success"><i class="fas fa-check-circle"></i> AKTIF</span>
                    @else
                      <span class="badge bg-secondary">Draft</span>
                    @endif
                  </td>
                  <td>
                    @if ($batch->final_fitness_score == 0)
                      <span class="badge bg-success">0 (Sempurna)</span>
                    @else
                      <span class="badge bg-danger">{{ $batch->final_fitness_score }} (Ada Bentrok)</span>
                    @endif
                  </td>
                  <td>{{ $batch->created_at->format('d M Y, H:i') }}</td>
                  <td>
                    <a href="{{ route('admin.jadwal.show', $batch->id) }}" class="btn btn-sm btn-info icon icon-left me-1">
                      <i class="fas fa-eye"></i> Lihat
                    </a>
                    @if ($batch->status != 'active')
                      <form action="{{ route('admin.jadwal.activate', $batch->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success icon icon-left" onclick="return confirm('Jadikan jadwal ini sebagai jadwal utama sekolah?')">
                          <i class="fas fa-check"></i> Gunakan
                        </button>
                      </form>
                    @endif

                    <form action="{{ route('admin.jadwal.destroy', $batch->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn icon btn-danger btn-sm btn-hapus" data-nama="{{ $batch->name }}">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
@endsection

@section('script')
  <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
  <script>
    // Script untuk konfirmasi Generate Jadwal
    document.getElementById('btn-generate').addEventListener('click', function() {
      const form = document.getElementById('form-generate');

      Swal.fire({
        title: "Mulai Generate Jadwal?",
        text: "Proses Algoritma Genetika ini memakan waktu beberapa detik hingga menit tergantung dari banyaknya data. Harap tunggu dan jangan tutup halaman.",
        icon: "info",
        showCancelButton: true,
        confirmButtonColor: "#435ebe",
        cancelButtonColor: "#dc3545",
        confirmButtonText: "Ya, Generate Sekarang",
        cancelButtonText: "Batal",
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: () => {
          // Mengubah teks tombol saat proses berjalan agar user tahu sistem tidak hang
          Swal.getConfirmButton().textContent = 'Sedang Memproses...';
          return new Promise((resolve) => {
            form.submit();
          });
        }
      });
    });

    document.querySelectorAll('.btn-hapus').forEach(button => {
      button.addEventListener('click', function() {
        const form = this.closest('form');
        const nama = this.dataset.nama;
        Swal.fire({
          title: "Hapus Simulasi?",
          html: `Data jadwal <b class="text-primary">${nama}</b> beserta rinciannya akan dihapus permanen.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#435ebe",
          cancelButtonColor: "#dc3545",
          confirmButtonText: "Ya, Hapus",
          preConfirm: () => form.submit()
        });
      });
    });
  </script>
@endsection
