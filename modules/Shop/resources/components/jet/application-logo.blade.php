@php
  $settings = get_settings(['name', 'logo', 'logo_dark']);
@endphp
<img alt="{{ $settings['name'] ?: 'SMA' }}" src="{{ $settings['logo'] ?: '/img/sma.svg' }}"
  class="dark:hidden h-full max-h-16 min-h-5 max-w-52" />
<img alt="{{ $settings['name'] ?: 'SMA' }}" src="{{ $settings['logo_dark'] ?: '/img/sma-light.svg' }}"
  class="hidden dark:block h-full max-h-16 min-h-5 max-w-52" />
