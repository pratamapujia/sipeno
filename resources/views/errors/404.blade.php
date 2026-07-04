<!DOCTYPE html>
<html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>

    {{-- Sesuaikan path asset ini dengan struktur project Anda --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/error.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">
  </head>

  <body>
    <div id="error">
      <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2">
          <div class="text-center">
            <img class="img-error" src="{{ asset('assets/compiled/svg/error-404.svg') }}" alt="Not Found">
            <h1 class="error-title">404</h1>
            <p class='fs-5 text-gray-600'>Maaf, halaman yang Anda cari tidak dapat ditemukan atau telah dihapus.</p>
            @php
              $previousUrl = url()->previous();
              $currentUrl = url()->current();
              $fallbackUrl = Auth::check() ? url('/dashboard') : url('/');
              $targetUrl = $previousUrl !== $currentUrl ? $previousUrl : $fallbackUrl;
            @endphp

            <a href="{{ $targetUrl }}" class="btn btn-lg btn-outline-primary mt-3">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
