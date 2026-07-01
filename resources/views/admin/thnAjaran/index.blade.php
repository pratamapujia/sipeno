@extends('layouts.main')

@section('title')
  <title>Data Master Tahun Ajaran</title>

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
          <h3>Data Master Tahun Ajaran</h3>
        </div>
      </div>
    </div>
    <section class="section">
      <div class="card">
        <div class="card-header">
          <a href="{{ route('admin.m.thnAjaran.create') }}" class="btn icon icon-left btn-primary">
            <i class="fas fa-plus"></i> Tambah Data
          </a>
        </div>
        <div class="card-body">
          <table class="table table-striped" id="table1">
            <thead>
              <tr>
                <th>No</th>
                <th>Tahun Ajaran</th>
                <th>Semester</th>
                <th>Status</th>
                <th data-sortable="false">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($thnAjaran as $data)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $data->tahun_ajaran }}</td>
                  <td>{{ $data->semester }}</td>
                  <td>
                    <span class="badge {{ $data->is_active == '0' ? 'bg-danger' : 'bg-success' }}">
                      {{ $data->is_active == '0' ? 'Tidak Aktif' : 'Aktif' }}
                    </span>
                  </td>
                  <td>
                    <a href="{{ route('admin.m.thnAjaran.edit', Hashids::encode($data->id)) }}" class="btn icon icon-left btn-sm btn-warning">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.m.thnAjaran.destroy', $data->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn icon icon-left btn-danger btn-sm btn-hapus" data-nama="Tahun Ajaran {{ $data->tahun_ajaran }}">
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
