<!doctyp<!doctype html>
<html {!! language_attributes() !!}>
  <head>
    <meta charset="{{ bloginfo('charset') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

    {{-- WP hooks --}}
    {!! do_action('get_header') !!}
    {!! wp_head() !!}

    @stack('head')
  </head>

  <body {!! body_class() !!}>
    {!! wp_body_open() !!}

    <div id="app">
      @include('sections.header')

      <main id="main" class="main">
        @yield('content')
      </main>

      @hasSection('sidebar')
        <aside class="sidebar">@yield('sidebar')</aside>
      @endif

      @include('sections.footer')
    </div>
    @stack('scripts')
    {!! wp_footer() !!}

    {{-- Vite entry printed with raw Blade echo --}}
    {!! \Illuminate\Support\Facades\Vite::withEntryPoints([
      'resources/js/app.js',
    ])->toHtml() !!}
  </body>
</html>

