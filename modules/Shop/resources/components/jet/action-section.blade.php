<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
  <x-jet.section-title>
    <x-slot name="title">{{ $title }}</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
  </x-jet.section-title>

  <div class="mt-5 md:mt-0 md:col-span-2 print:w-full print:col-span-full print:mt-0">
    <div class="px-4 py-5 sm:p-6 bg-gray-50 dark:bg-gray-950 sm:rounded-lg">
      {{ $content }}
    </div>

    @if ($pagination ?? false)
      <div class="mt-6 px-4 sm:px-0">
        {{ $pagination }}
      </div>
    @endif
  </div>
</div>
