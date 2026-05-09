<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title')</title>

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

  @vite('resources/css/errors.css', '')
</head>

<body class="antialiased">
  <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0" role="main">
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-6 items-center justify-center min-h-screen">
      <div class="grow flex items-center pt-8 sm:justify-start sm:pt-0">
        <h1 class="px-4 text-lg dark:text-gray-300 text-gray-700 border-r border-gray-400 tracking-wider">
          @yield('code')
        </h1>

        <div class="ml-4 text-lg dark:text-gray-300 text-gray-700 uppercase tracking-wider">
          @yield('message')
        </div>
      </div>
      <div class="mt-auto py-6">
        <a href="/"
          class="rounded-md text-sm text-gray-600 underline decoration-dotted underline-offset-8 hover:text-gray-900 hover:decoration-solid dark:text-gray-400 dark:hover:text-gray-100">
          {{ __('Go to Home') }}
        </a>
      </div>
    </div>
  </div>
</body>

</html>
