@php
  $settings = get_settings(['name', 'icon', 'icon_dark']);
@endphp
<img alt="{{ $settings['short_name'] ?: 'SMA' }}" src="{{ $settings['icon'] ?: '/img/sma-icon.svg' }}"
  class="dark:hidden h-full max-h-16 min-h-5" />
<img alt="{{ $settings['short_name'] ?: 'SMA' }}" src="{{ $settings['icon_dark'] ?: '/img/sma-icon-light.svg' }}"
  class="hidden dark:block h-full max-h-16 min-h-5" />
