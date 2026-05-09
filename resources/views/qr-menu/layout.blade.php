<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="/img/sma-icon.svg" type="image/svg+xml">

  <title>@yield('title', 'Menu')</title>

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
  </script>

  @vite('resources/css/qr.css', '')

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="h-full bg-gray-100 dark:bg-gray-900 font-sans text-gray-800 dark:text-gray-200 antialiased lg:p-2 min-h-dvh ">
  @yield('content')
</body>

</html>
