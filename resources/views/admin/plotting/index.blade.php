@extends('layouts.main')

@section('title')
  <title>Plotting</title>

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
        <div class="col-12">
          {{-- if null tahun ajaran --}}
          @if (!$thnAktif)
            <h3>Plotting Guru Tahun : <b class="text-danger">(Belum Diatur)</b> Semester: <b class="text-danger">(Belum Diatur)</b></h3>
          @else
            <h3>Plotting Guru Tahun : <b class="text-primary">{{ $thnAktif->tahun_ajaran }}</b> Semester: <b class="text-primary">{{ $thnAktif->semester }}</b></h3>
          @endif
          <p class="text-subtitle text-muted">plotting guru yang mengajar di setiap kelas dan mata pelajaran.</p>
        </div>
      </div>
    </div>
    <section class="section">
      @if (!$thnAktif)
        <div class="alert alert-light-warning alert-dismissible show fade">
          <i class="fas fa-exclamation-triangle"></i>
          Tahun ajaran aktif belum diatur! Silakan
          <a href="{{ route('admin.m.thnAjaran.index') }}" class="alert-link">
            <u>aktifkan Tahun Ajaran di sini</u>
          </a>
          terlebih dahulu sebelum mengelola plotting guru.
        </div>
      @endif
      <div class="card">
        <div class="card-header">
          <a href="{{ route('admin.plotting.create') }}" class="btn icon icon-left btn-primary">
            <i class="fas fa-plus"></i> Tambah Data
          </a>
        </div>
        <div class="card-body">
          <table class="table table-striped" id="table1">
            <thead>
              <tr>
                <th>No</th>
                <th width="30%">Nama Guru</th>
                <th width="20%">Mata Pelajaran</th>
                <th>Bobot</th>
                <th>Kelas</th>
                <th data-sortable="false">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($plotting as $index => $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->guru->nama_guru ?? 'Guru Tidak Ditemukan' }}</td>
                  <td>{{ $item->mapel->nama_mapel ?? '-' }}</td>
                  <td>{{ $item->mapel->beban_jam ?? '0' }}</td>
                  <td><span class="badge bg-primary">{{ $item->kelas->nama_kelas ?? '-' }}</span></td>
                  <td>
                    <a href="{{ route('admin.plotting.edit', Hashids::encode($item->id)) }}" class="btn icon icon-left btn-sm btn-warning me-1">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.plotting.destroy', $item->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn icon icon-left btn-danger btn-sm btn-hapus" data-nama="Plotting Guru {{ $item->guru->nama_guru }}">
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
@endsection

@section('script')
  <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
  <script>
    // Alert Delete
    document.addEventListener('click', function(e) {
      const button = e.target.closest('.btn-hapus');
      if (button) {
        e.preventDefault();
        const form = button.closest('form');
        const nama = button.dataset.nama;

        Swal.fire({
          title: "Peringatan!!!",
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
      }
    });
  </script>
@endsection
