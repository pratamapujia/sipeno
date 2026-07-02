@extends('layouts.main')

@section('title')
  <title>Manajemen Guru Piket</title>

  {{-- Datatable CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
  <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endsection

@section('main')
  {{-- Wadah Alert SweetAlert --}}
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>
  <div class="flash-data" data-gagal="{{ Session::get('error') }}"></div>

  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-12 col-md-6">
          <h3>Manajemen Guru Piket</h3>
          <p class="text-subtitle text-muted">Atur penugasan guru piket per hari. Guru piket tidak boleh memiliki jadwal mengajar pada hari tugasnya.</p>
        </div>
      </div>
    </div>

    <section class="section">
      @if (!$activeYear)
        <div class="alert alert-warning shadow-sm"><i class="fas fa-exclamation-triangle"></i> Belum ada Tahun Ajaran yang berstatus <b>Aktif</b>.</div>
      @else
        <div class="alert alert-success shadow-sm mb-4">
          <i class="fas fa-check-circle me-2"></i> Tahun Ajaran Aktif saat ini: <b>{{ $activeYear->tahun_ajaran }} - {{ $activeYear->semester }}</b>
        </div>

        <div class="card shadow-sm">
          <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <button type="button" class="btn icon icon-left btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPiket">
              <i class="fas fa-plus"></i> Tambah Guru Piket
            </button>
            <a href="{{ route('admin.piket.print') }}" target="_blank" class="btn icon icon-left btn-dark">
              <i class="fas fa-print"></i> Cetak Jadwal
            </a>
          </div>
          <div class="card-body pt-4">
            <table class="table table-striped" id="table1">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Hari Tugas</th>
                  <th>Nama Guru Piket</th>
                  <th data-sortable="false" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @php $no = 1; @endphp
                @foreach ($hari as $day)
                  @php $pikets = $piketData[$day] ?? collect(); @endphp
                  @foreach ($pikets as $piket)
                    <tr>
                      <td>{{ $no++ }}</td>
                      <td class="fw-bold">{{ $day }}</td>
                      <td>{{ $piket->guru->nama_guru }}</td>
                      <td class="text-center">
                        <button type="button" class="btn icon icon-left btn-warning btn-sm btn-edit" data-id="{{ $piket->id }}" data-hari="{{ $day }}" data-guru="{{ $piket->guru_id }}">
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        <form action="{{ route('admin.piket.destroy', $piket->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="btn icon icon-left btn-danger btn-sm btn-hapus" data-nama="{{ $piket->guru->nama_guru }} ({{ $day }})">
                            <i class="fas fa-trash"></i> Hapus
                          </button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </section>
  </div>

  {{-- Modal Tambah Guru Piket --}}
  @if ($activeYear)
    <div class="modal fade text-left" id="modalTambahPiket" tabindex="-1" role="dialog" aria-labelledby="titleModalPiket" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <h5 class="modal-title white" id="titleModalPiket"><i class="fas fa-user-plus me-2"></i> Tambah Guru Piket</h5>
            <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
              <i data-feather="x"></i>
            </button>
          </div>
          <form action="{{ route('admin.piket.store') }}" method="POST" id="formTambahPiket">
            @csrf
            <div class="modal-body">
              <div class="alert alert-light-primary text-sm border-primary">
                <i class="fas fa-info-circle me-1"></i> Pastikan guru yang dipilih tidak memiliki jadwal mengajar di kelas pada hari yang sama.
              </div>

              <div class="form-group mb-3">
                <label for="hari" class="form-label fw-bold">Pilih Hari</label>
                <select name="hari" id="hari" class="form-select" required>
                  <option value="">-- Pilih Hari Tugas --</option>
                  @foreach ($hari as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="guru_id" class="form-label fw-bold">Pilih Guru</label>
                <select name="guru_id" id="guru_id" class="form-select" required>
                  <option value="">-- Cari dan Pilih Guru --</option>
                  @foreach ($guru as $g)
                    <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" id="btnSubmitPiket" class="btn btn-primary ml-1"><i class="fas fa-save me-1"></i> Simpan Penugasan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Modal Edit Guru Piket --}}
    <div class="modal fade text-left" id="modalEditPiket" tabindex="-1" role="dialog" aria-labelledby="titleModalEdit" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title white" id="titleModalEdit"><i class="fas fa-edit me-2"></i> Edit Guru Piket</h5>
            <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
              <i data-feather="x"></i>
            </button>
          </div>
          <form action="" method="POST" id="formEditPiket">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <div class="form-group mb-3">
                <label for="edit_hari" class="form-label fw-bold">Pilih Hari</label>
                <select name="hari" id="edit_hari" class="form-select" required>
                  <option value="">-- Pilih Hari Tugas --</option>
                  @foreach ($hari as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="edit_guru_id" class="form-label fw-bold">Pilih Guru</label>
                <select name="guru_id" id="edit_guru_id" class="form-select" required>
                  <option value="">-- Cari dan Pilih Guru --</option>
                  @foreach ($guru as $g)
                    <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" id="btnSubmitEdit" class="btn btn-warning ml-1"><i class="fas fa-save me-1"></i> Perbarui</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

@endsection

@section('script')
  <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Membaca flash data untuk SweetAlert
      const flashBerhasil = document.querySelector('.flash-data').getAttribute('data-berhasil');
      const flashGagal = document.querySelectorAll('.flash-data')[1].getAttribute('data-gagal');

      if (flashBerhasil) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: flashBerhasil,
          confirmButtonColor: '#435ebe'
        });
      }

      if (flashGagal) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: flashGagal,
          confirmButtonColor: '#dc3545'
        });
      }
    });

    // Handle Modal Edit (Melempar data ke dalam modal saat tombol edit ditekan)
    document.addEventListener('click', function(e) {
      const btnEdit = e.target.closest('.btn-edit');
      if (btnEdit) {
        const id = btnEdit.getAttribute('data-id');
        const hari = btnEdit.getAttribute('data-hari');
        const guruId = btnEdit.getAttribute('data-guru');

        // Atur URL form action secara dinamis
        const formEdit = document.getElementById('formEditPiket');
        formEdit.action = `{{ url('admin/piket') }}/${id}`;

        // Masukkan nilai ke dalam form select
        document.getElementById('edit_hari').value = hari;
        document.getElementById('edit_guru_id').value = guruId;

        // Tampilkan Modal Edit
        var myModal = new bootstrap.Modal(document.getElementById('modalEditPiket'));
        myModal.show();
      }
    });

    // Animasi Loading saat Submit Form Tambah
    const formTambah = document.getElementById('formTambahPiket');
    if (formTambah) {
      formTambah.addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitPiket');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
      });
    }

    // Animasi Loading saat Submit Form Edit
    const formEdit = document.getElementById('formEditPiket');
    if (formEdit) {
      formEdit.addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitEdit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memperbarui...';
      });
    }

    // Alert Delete dengan SweetAlert
    document.addEventListener('click', function(e) {
      const button = e.target.closest('.btn-hapus');
      if (button) {
        e.preventDefault();
        const form = button.closest('form');
        const nama = button.dataset.nama;

        Swal.fire({
          title: "Hapus Penugasan?",
          html: `Jadwal piket untuk <b class="text-primary">${nama}</b> akan dihapus secara permanen.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#dc3545",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Ya, Hapus",
          cancelButtonText: "Batal",
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
