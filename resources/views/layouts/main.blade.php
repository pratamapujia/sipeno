<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @yield('title')

    <link rel="stylesheet" href="{{ asset('assets/extensions/sweetalert2/sweetalert2.min.css') }}" />
    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/app.css') }}">

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="{{ asset('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">
  </head>

  <body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <div id="app">

      @include('layouts.menu')

      <div id="main" class='layout-navbar navbar-fixed'>

        @include('layouts.header')

        <div id="main-content">
          @yield('main')
        </div>

        @include('layouts.footer')
      </div>
    </div>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>
    <script src="{{ asset('assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/extensions/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/static/js/my.js') }}"></script>
    @yield('script')


  </body>

</html>
