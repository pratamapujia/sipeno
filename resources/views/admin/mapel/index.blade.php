@extends('layouts.main')

@section('title')
  <title>Data Master Mata Pelajaran</title>

  {{-- Datatable CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
  <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endsection

@section('main')
  {{-- Alert --}}
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>
  <div class="flash-data" data-gagal="{{ Session::get('error') }}"></div>

  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6">
          <h3>Data Master Mata Pelajaran</h3>
          <p class="text-subtitle text-muted">Kelola data mata pelajaran di sekolah.</p>
        </div>
      </div>
    </div>

    @if (session()->has('pesan_error'))
      @php
        $pesan = session('pesan_error');
      @endphp
      <div class="alert alert-{{ $pesan['type'] }} alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">{{ $pesan['title'] }}</h5>
        <p>{{ $pesan['body'] }}</p>

        {{-- Jika ada detail error, tampilkan sebagai daftar --}}
        @if (isset($pesan['details']))
          <hr>
          <ul class="mb-0">
            @foreach ($pesan['details'] as $detail)
              {{-- Kita gunakan {!! !!} agar tag <b> bisa dirender --}}
              <li>{!! $detail !!}</li>
            @endforeach
          </ul>
        @endif

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <section class="section">
      <div class="card">
        <div class="card-header">
          <a href="{{ route('admin.m.mapel.create') }}" class="btn icon icon-left btn-primary">
            <i class="fas fa-plus"></i> Tambah Data
          </a>
          <button type="button" class="btn icon icon-left btn-success float-end" data-bs-toggle="modal" data-bs-target="#modalImportMapel">
            <i class="fas fa-file-excel"></i> Import Excel
          </button>
        </div>
        <div class="card-body">
          <table class="table table-striped" id="table1">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Mapel</th>
                <th>Type</th>
                <th>Beban Jam</th>
                <th data-sortable="false">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($mapel as $data)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $data->nama_mapel }}</td>
                  <td>
                    <span class="badge bg-{{ $data->type == 'teori' ? 'primary' : 'info' }}">{{ ucfirst($data->type) }}</span>
                  </td>
                  <td>{{ $data->beban_jam }}</td>
                  <td>
                    <a href="{{ route('admin.m.mapel.edit', Hashids::encode($data->id)) }}" class="btn icon icon-left btn-sm btn-warning">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.m.mapel.destroy', $data->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn icon icon-left btn-danger btn-sm btn-hapus" data-nama="Mapel {{ $data->nama_mapel }}">
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
  <div class="modal fade text-left" id="modalImportMapel" tabindex="-1" role="dialog" aria-labelledby="titleModalMapel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success">
          <h5 class="modal-title white" id="titleModalMapel">Import Data Mata Pelajaran</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <i data-feather="x"></i>
          </button>
        </div>
        <form action="{{ route('admin.m.mapel.import') }}" method="POST" enctype="multipart/form-data" id="formImportMapel">
          @csrf
          <div class="modal-body">
            <div class="alert border border-success text-sm">
              <h6>Aturan Pengisian Excel:</h6>
              <ol class="mb-0 ps-3 text-black">
                <li>Header baris pertama wajib: <b>nama_mapel</b>, <b>type</b>, dan <b>beban_jam</b></li>
                <li>Kolom kode_mapel dapat di isi dengan inisial Mapel misal: <b>MTK</b>, <b>BINDO</b>, <b>PWEB</b></li>
                <li>Kolom nama_mapel wajib diisi dengan nama lengkap Mapel. Contoh: <b>Matematika</b>, <b>Bahasa Indonesia</b>, <b>Pemrograman Web</b></li>
                <li>Kolom type wajib diisi dengan: <b>teori</b> atau <b>praktikum</b></li>
                <li>Kolom beban_jam wajib diisi dengan angka bulat (JP per minggu). Contoh: <b>2</b>, <b>4</b></li>
              </ol>
            </div>
            <div class="form-group mt-3">
              <label for="file_excel" class="form-label">Pilih File Excel (.xlsx / .xls)</label>
              <input type="file" id="file_excel" name="file_excel" class="form-control" required accept=".xlsx, .xls">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" id="btnSubmitMapel" class="btn btn-success ml-1">Mulai Import</button>
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
    // Tampilkan loading ketika tombol import diklik
    document.getElementById('formImportMapel').addEventListener('submit', function() {
      const btn = document.getElementById('btnSubmitMapel');
      // Nonaktifkan tombol
      btn.disabled = true;
      // Tampilkan animasi loading
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sedang Memproses...';
    });

    // Alert Delete
    document.querySelectorAll('.btn-hapus').forEach(button => {
      button.addEventListener('click', function() {
        const form = this.closest('form');
        const nama = this.dataset.nama;

        Swal.fire({
          title: "Peringatan!!!",
          // text: "Data ini akan dihapus secara permanen!",
          html: `Data <b class="text-primary">${nama}</b> akan dihapus secara <b class="text-danger">Permanen</b>`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#435ebe",
          cancelButtonColor: "#dc3545",
          confirmButtonText: "Ya, Hapus",
          showLoaderOnConfirm: true,
          preConfirm: () => {
            return new Promise((resolve) => {
              form.submit();
            });
          },
        });
      });
    });
  </script>
@endsection
