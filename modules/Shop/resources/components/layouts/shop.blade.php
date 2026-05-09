<x-shop::layouts.base>
  <x-slot name="title">{{ $title ?? '' }}</x-slot>
  <x-slot name="styles">{{ $styles ?? '' }}</x-slot>
  <x-slot name="scripts">{{ $scripts ?? '' }}</x-slot>
  <x-slot name="metaDesc">{{ $metaDesc ?? '' }}</x-slot>
  <x-slot name="ogMetaData">{{ $ogMetaData ?? '' }}</x-slot>

  {{-- <x-shop::banner /> --}}
  <x-shop::header />

  <main>
    {{ $slot }}
  </main>

  <x-shop::footer />
</x-shop::layouts.base>
