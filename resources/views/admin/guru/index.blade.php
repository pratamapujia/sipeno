@extends('layouts.main')

@section('title')
  <title>Data Master Guru</title>

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
          <h3>Data Master Guru</h3>
          <p class="text-subtitle text-muted">Kelola data guru di sekolah.</p>
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
          <a href="{{ route('admin.m.guru.create') }}" class="btn icon icon-left btn-primary">
            <i class="fas fa-plus"></i> Tambah Data
          </a>
          <button type="button" class="btn icon icon-left btn-success float-end" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
            <i class="fas fa-file-excel"></i> Import Excel
          </button>
        </div>
        <div class="card-body">
          <table class="table table-striped" id="table1">
            <thead>
              <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Guru</th>
                <th>Jenis Kelamin</th>
                <th>Status</th>
                <th data-sortable="false">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($guru as $data)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $data->nip == null ? '-' : $data->nip }}</td>
                  <td>{{ $data->nama_guru }}</td>
                  <td>{{ $data->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                  <td>
                    <span class="badge {{ $data->status == 'Tetap' ? 'bg-primary' : 'bg-info' }}">{{ $data->status }}</span>
                  </td>
                  <td>
                    <a href="{{ route('admin.m.guru.edit', Hashids::encode($data->id)) }}" class="btn icon icon-left btn-sm btn-warning">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.m.guru.destroy', $data->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn icon icon-left btn-danger btn-sm btn-hapus" data-nama="Guru {{ $data->nama_guru }}">
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
  <div class="modal fade text-left" id="modalImportGuru" tabindex="-1" role="dialog" aria-labelledby="titleModalGuru" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success">
          <h5 class="modal-title white" id="titleModalGuru">Import Data Master Guru</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <i data-feather="x"></i>
          </button>
        </div>
        <form action="{{ route('admin.m.guru.import') }}" method="POST" enctype="multipart/form-data" id="formImportGuru">
          @csrf
          <div class="modal-body">
            <div class="alert border border-success text-sm">
              <h6>Aturan Pengisian Excel:</h6>
              <ol class="mb-0 ps-3 text-black">
                <li>Header baris pertama wajib: <b>nip</b>, <b>nama_guru</b>,<b>email</b>, <b>jenis_kelamin</b>, <b>status</b></li>
                <li>Kolom nip bersifat <b>Opsional</b></li>
                <li>Kolom email digunakan untuk membuat akun guru. Kolom email wajib diisi</li>
                <li>Kolom jenis_kelamin hanya boleh diisi: <b>L</b> atau <b>P</b></li>
                <li>Kolom status hanya boleh diisi: <b>Tetap</b> atau <b>Honorer</b></li>
              </ol>
            </div>
            <div class="form-group mt-3">
              <label for="file_excel" class="form-label">Pilih File Excel (.xlsx / .xls)</label>
              <input type="file" id="file_excel" name="file_excel" class="form-control" required accept=".xlsx, .xls">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" id="btnSubmitGuru" class="btn btn-success ml-1">Mulai Import</button>
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
    document.getElementById('formImportGuru').addEventListener('submit', function() {
      const btn = document.getElementById('btnSubmitGuru');
      // Nonaktifkan tombol agar tidak bisa diklik dua kali
      btn.disabled = true;
      // Ubah teks tombol menjadi animasi loading bawaan Bootstrap
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
