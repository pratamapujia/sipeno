@extends('layouts.main')

@section('title')
  <title>Profil Saya | Ubah Password</title>
@endsection

@section('main')
  {{-- Wadah Flash Data untuk SweetAlert --}}
  <div class="flash-data" data-berhasil="{{ Session::get('success') }}"></div>

  <div class="page-heading">
    <div class="page-title mb-4">
      <div class="row align-items-center">
        <div class="col-12 col-md-6">
          <h3>Profil Pengguna</h3>
          <p class="text-muted">Perbarui kata sandi Anda secara berkala untuk menjaga keamanan akun.</p>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="row">
        {{-- Card Info Profil Standar --}}
        <div class="col-12 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body py-4 px-4 text-center">
              <div class="avatar avatar-xl mb-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" alt="Avatar" style="width: 100px; height: 100px;">
              </div>
              <h5 class="font-bold">{{ $user->name }}</h5>
              <p class="text-muted mb-0">{{ $user->email ?? 'Guru / Pengajar' }}</p>
              <span class="badge bg-light-success text-success font-semibold">{{ $guru->status }}</span>
            </div>
          </div>
        </div>

        {{-- Card Form Ubah Password --}}
        <div class="col-12 col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header bg-light border-bottom">
              <h5 class="mb-0"><i class="fas fa-lock me-2 text-primary"></i> Ubah Password</h5>
            </div>
            <div class="card-body pt-4">
              <form action="{{ route('guru.profile.update') }}" method="POST" id="formUpdatePassword">
                @csrf
                @method('PUT')

                {{-- Password Lama --}}
                <div class="form-group mb-4">
                  <label for="current_password" class="form-label fw-bold">Password Saat Ini</label>
                  <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" placeholder="Masukkan password Anda saat ini"
                    required>
                  @error('current_password')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                  @enderror
                </div>

                {{-- Password Baru --}}
                <div class="form-group mb-4">
                  <label for="password" class="form-label fw-bold">Password Baru</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Minimal 8 karakter" required>
                  @error('password')
                    <div class="invalid-feedback"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                  @enderror
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div class="form-group mb-4">
                  <label for="password_confirmation" class="form-label fw-bold">Konfirmasi Password Baru</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password baru" required>
                  <small class="text-muted mt-1 d-block">Pastikan konfirmasi password sama persis dengan password baru.</small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary icon icon-left" id="btnSubmit">
                    <i class="fas fa-save"></i> Simpan Password Baru
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@section('script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Membaca flash data dari Controller
      const flashBerhasil = document.querySelector('.flash-data').getAttribute('data-berhasil');

      // Menampilkan Notifikasi Success
      if (flashBerhasil) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil Memperbarui!',
          text: flashBerhasil,
          confirmButtonColor: '#435ebe'
        });
      }

      // Animasi tombol submit
      const form = document.getElementById('formUpdatePassword');
      if (form) {
        form.addEventListener('submit', function() {
          const btn = document.getElementById('btnSubmit');
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
        });
      }
    });
  </script>
@endsection
