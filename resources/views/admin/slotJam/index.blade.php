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
    <section class="section">
      <div class="card">
        <div class="card-header">
          <a href="{{ route('admin.m.slotJam.create') }}" class="btn icon icon-left btn-primary">
            <i class="fas fa-plus"></i> Tambah Jam Pelajaran
          </a>
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
@endsection

@section('script')
  <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
  <script>
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
