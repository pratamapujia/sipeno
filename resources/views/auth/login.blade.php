<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Penjadwalan Sekolah</title>

    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
      /* Modifikasi untuk membuat form tepat di tengah (Centered) */
      body {
        background-color: #f2f7ff;
        /* Warna latar belakang yang lembut */
      }

      #auth {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
      }

      .auth-card {
        border-radius: 1.5rem;
        border: none;
      }
    </style>
  </head>

  <body>
    <div id="auth">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8 col-lg-6 col-xl-5">

            <div class="card shadow-lg auth-card">
              <div class="card-body p-5">

                <div class="text-center mb-5">
                  <div class="auth-logo mb-3 d-flex flex-column justify-content-center">
                    <h2 style="letter-spacing: 0.5px;">
                      SPRISDA (SISPENO)</h2>
                    </span>
                    <h6 class="text-secondary fw-light" style="letter-spacing: 0.7px; text-transform: uppercase;">
                      Sistem Penjadwalan Online
                    </h6>
                  </div>
                  <h4 class="auth-title fs-3">Masuk</h4>
                  <p class="auth-subtitle fs-6 text-muted">Silakan masuk menggunakan akun yang telah didaftarkan oleh Admin.</p>
                </div>

                <form action="{{ route('login.proses') }}" method="POST">
                  @csrf

                  <div class="form-group position-relative has-icon-left mb-4">
                    <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" placeholder="Masukkan Email" value="{{ old('email') }}" autofocus>
                    <div class="form-control-icon">
                      <i class="fas fa-envelope @error('email') text-danger @enderror"></i>
                    </div>
                    @error('email')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  <div class="form-group position-relative has-icon-left mb-4">
                    <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Kata Sandi">
                    <div class="form-control-icon ">
                      <i class="fas fa-lock @error('password') text-danger @enderror"></i>
                    </div>
                    @error('password')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>

                  <div class="form-check form-check-lg d-flex align-items-center mb-5">
                    <input class="form-check-input me-2" type="checkbox" name="remember" id="flexCheckDefault">
                    <label class="form-check-label text-gray-600 pt-1" for="flexCheckDefault">
                      Ingat Saya
                    </label>
                  </div>

                  <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg">Masuk Sistem</button>
                </form>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      // Konfigurasi default untuk Toast
      const Toast = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
      });

      // Trigger Toast untuk notifikasi sukses (seperti saat berhasil logout)
      @if (session('success'))
        Toast.fire({
          icon: 'success',
          title: "{{ session('success') }}"
        });
      @endif

      // Trigger Toast untuk notifikasi error (salah password / user tidak ditemukan)
      @if ($errors->any())
        Toast.fire({
          icon: 'error',
          title: "{{ $errors->first() }}"
        });
      @endif
    </script>
  </body>

</html>
