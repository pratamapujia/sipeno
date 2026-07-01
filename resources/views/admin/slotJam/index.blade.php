@extends('layouts.main')

@section('title')
  <title>Data Jam Pelajaran</title>
  <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
  <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endsection

@section('main')
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>
  <div class="flash-data" data-gagal="{{ Session::get('error') }}"></div>

  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3>Data Jam Pelajaran (Time Slots)</h3>
          <p class="text-subtitle text-muted">Atur urutan jam mengajar dan waktu istirahat sekolah.</p>
        </div>
      </div>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible show fade">
        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Gagal Mengimpor File!</h6>
        <p class="mb-2">Ditemukan kesalahan pada isi data Excel Anda:</p>
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <section class="section">
      <div class="card">
        <div class="card-header">
          <a href="{{ route('admin.m.slotJam.create') }}" class="btn icon icon-left btn-primary">
            <i class="fas fa-plus"></i> Tambah Jam Pelajaran
          </a>
          <button type="button" class="btn icon icon-left btn-success float-end" data-bs-toggle="modal" data-bs-target="#modalImportJam">
            <i class="fas fa-file-excel"></i> Import Excel
          </button>
        </div>
        <div class="card-body">
          <table class="table table-striped" id="table1">
            <thead>
              <tr>
                <th>Jam Ke-</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Status / Jenis</th>
                <th data-sortable="false">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($slot as $data)
                <tr>
                  <td><b>{{ $data->slot_number }}</b></td>
                  <td>{{ substr($data->start_time, 0, 5) }}</td>
                  <td>{{ substr($data->end_time, 0, 5) }}</td>
                  <td>
                    @if ($data->is_istirahat)
                      <span class="badge bg-warning text-dark">Istirahat</span>
                    @else
                      <span class="badge bg-success">Jam Efektif</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.m.slotJam.edit', Hashids::encode($data->id)) }}" class="btn icon icon-left btn-sm btn-warning">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.m.slotJam.destroy', $data->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn icon icon-left btn-danger btn-sm btn-hapus" data-nama="Jam ke-{{ $data->slot_number }}">
                        <i class="fas fa-trash"></i> Hapus
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

  {{-- Modal Import Excel --}}
  <div class="modal fade text-left" id="modalImportJam" tabindex="-1" role="dialog" aria-labelledby="titleModalJam" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success">
          <h5 class="modal-title white" id="titleModalJam">Import Jam Pelajaran</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <i data-feather="x"></i>
          </button>
        </div>
        <form action="{{ route('admin.m.slotJam.import') }}" method="POST" enctype="multipart/form-data" id="formImportJam">
          @csrf
          <div class="modal-body">
            <div class="alert border border-success text-sm">
              <h6>Aturan Pengisian Excel:</h6>
              <ol class="mb-0 ps-3 text-black">
                <li>Header baris pertama wajib: <b>slot_number</b>, <b>start_time</b>, <b>end_time</b>, <b>is_istirahat</b></li>
                <li>Kolom <b>slot_number</b> isi dengan angka urutan jam pelajaran dan istirahat (1, 2, 3, dst). Kolom slot_number <b>Wajib Diisi</b></li>
                <li>Kolom <b>start_time</b> dan <b>end_time</b> isi dengan waktu mulai dan waktu selesai mengajar dalam format HH:MM(07:00) kalau siang tulis dengan (13:00)</li>
                <li>Kolom <b>is_istirahat</b> isi dengan: <b>true</b> untuk istirahat dan <b>biarkan kosong</b> jika bukan jam istirahat</li>
              </ol>
            </div>
            <div class="form-group mt-3">
              <label for="file_excel" class="form-label">Pilih File Excel (.xlsx / .xls)</label>
              <input type="file" id="file_excel" name="file_excel" class="form-control" required accept=".xlsx, .xls">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" id="btnSubmitJam" class="btn btn-success ml-1">Mulai Import</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
  <script>
    // Tampilkan animasi loading ketika tombol import diklik
    document.getElementById('formImportJam').addEventListener('submit', function() {
      const btn = document.getElementById('btnSubmitJam');
      // Nonaktifkan tombol agar tidak bisa diklik dua kali
      btn.disabled = true;
      // Ubah teks tombol menjadi animasi loading bawaan Bootstrap
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sedang Memproses...';
    });

    document.querySelectorAll('.btn-hapus').forEach(button => {
      button.addEventListener('click', function() {
        const form = this.closest('form');
        const nama = this.dataset.nama;
        Swal.fire({
          title: "Peringatan!!!",
          html: `Data <b class="text-primary">${nama}</b> akan dihapus secara <b class="text-danger">Permanen</b>`,
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
