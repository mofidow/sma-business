@props(['price' => 0, 'currency' => null])
@php
    $symbol = $currency ?? (get_settings('payment') ? (json_decode(get_settings('payment'), true)['default_currency'] ?? 'USD') : 'USD');
    $formatted = number_format((float) $price, 2);
@endphp
<span {{ $attributes }}>{{ $symbol }} {{ $formatted }}</span>
