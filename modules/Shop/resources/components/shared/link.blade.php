@props(['to'])
<a href="{{ $to }}" wire:navigate w.hover {{ $attributes->merge(['class' => 'btn-primary']) }}>
  {{ $slot }}
</a>
