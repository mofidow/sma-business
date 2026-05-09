<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  dir="{{ (session('rtl_support', cookie('rtl_support', $settings['rtl_support'] ?? '0')) ?? '0') == '1' ? 'rtl' : 'ltr' }}" class="h-full">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="{{ asset('/manifest.webmanifest') }}">
  <link rel="icon" href="/img/sma-icon.svg" type="image/svg+xml" media="(prefers-color-scheme: light)">
  <link rel="icon" href="/img/sma-icon-light.svg" type="image/svg+xml" media="(prefers-color-scheme: dark)">

  <title inertia>{{ config('app.name', 'SMA') }}</title>

  <!-- Fonts -->
  {{-- <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=andika:400,700" rel="stylesheet" /> --}}

  <script>
    if (localStorage.theme === 'dark') {
      document.documentElement.classList.add('dark');
      document.documentElement.style.colorScheme = 'dark';
    } else if (localStorage.theme === 'light') {
      document.documentElement.classList.remove('dark');
      document.documentElement.style.colorScheme = 'light';
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark');
      document.documentElement.style.colorScheme = 'dark';
    } else {
      document.documentElement.classList.remove('dark');
      document.documentElement.style.colorScheme = 'light';
    }
    window.Locale = '{{ app()->getLocale() }}';
  </script>

  <style>
    html {
      background-color: #ffffff;
    }

    html.dark {
      background-color: var(--color-gray-900);
    }

    .app-loading {
      top: 0px;
      left: 0px;
      right: 0px;
      bottom: 0px;
      width: 100%;
      z-index: 40;
      display: flex;
      position: fixed;
      min-height: 100vh;
      align-items: center;
      flex-direction: column;
      justify-content: center;
    }

    .app-loading svg {
      width: 3rem;
      height: 3rem;
      color: var(--color-gray-700);
      animation: spin 1s linear infinite;
    }

    .dark .app-loading svg {
      color: var(--color-gray-200);
    }

    @keyframes spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }
  </style>

  @routes
  @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"], '')
  {{-- @vite('resources/js/app.js', '') --}}
  @inertiaHead
</head>

<body class="h-full font-sans antialiased text-gray-700 dark:text-gray-300">
  <div id="app-loading" class="app-loading bg-gray-50 dark:bg-gray-800">
    <svg width="64" height="64" fill="none" viewBox="0 0 16 16">
      <circle cx="8" cy="8" r="7" stroke-width="2" stroke="currentColor" stroke-opacity="0.25"
        vector-effect="non-scaling-stroke"></circle>
      <path d="M15 8a7.002 7.002 0 00-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" vector-effect="non-scaling-stroke">
      </path>
    </svg>
  </div>
  @inertia

  @if (demo())
    <script src="//tecdesk.top/assets/demo-reset.js" data-reset-at="2"
      data-purchase-link="https://tecdiary.com/products/stock-manager-advance-with-all-modules"></script>
  @endif
</body>

</html>
