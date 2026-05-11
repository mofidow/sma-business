<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('shop_rtl') ? 'rtl' : 'ltr' }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  @if (demo())
    <link rel="icon" href="/img/sma-icon.svg" type="image/svg+xml" media="(prefers-color-scheme: light)">
    <link rel="icon" href="/img/sma-icon-light.svg" type="image/svg+xml" media="(prefers-color-scheme: dark)">
  @else
    <link rel="icon" href="{{ get_settings('icon') ?? '/img/sma-icon.svg' }}" media="(prefers-color-scheme: light)" />
    <link rel="icon" href="{{ get_settings('icon_dark') ?? '/img/sma-icon-light.svg' }}" media="(prefers-color-scheme: dark)" />
  @endif

  <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'SMA Shop') }}</title>
  <meta name="description" content="{{ $metaDesc ?? '' }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{ $ogMetaData ?? '' }}

  <script>
    window.Locale = '{{ app()->getLocale() }}';
    if (window.self !== window.top) {
      window.top.location.href = '{{ route('shop.home') }}';
    }

    if (localStorage.theme === 'dark') {
      document.documentElement.classList.add('dark');
      document.documentElement.style.colorScheme = 'dark';
    } else {
      document.documentElement.classList.remove('dark');
      document.documentElement.style.colorScheme = 'light';
    }
  </script>

  @vite(['modules/Shop/resources/assets/app.js', 'modules/Shop/resources/assets/app.css'], '')

  @livewireStyles
  @yield('styles')
  {{ $styles ?? '' }}
  {{ str(shop_header_code() ?? '')->toHtmlString() }}
</head>

<body>
  <div class="bg-white dark:bg-gray-900">
    {{ $slot }}

    <x-shop::alpine.notification />
  </div>

  @livewireScripts
  @yield('scripts')
  {{ $scripts ?? '' }}
  {{ str(shop_footer_code() ?? '')->toHtmlString() }}

  @if (demo())
    <script src="//tecdesk.top/assets/demo-reset.js" data-reset-at="2"
      data-purchase-link="https://tecdiary.com/products/stock-manager-advance-with-all-modules"></script>
  @endif
</body>

</html>
