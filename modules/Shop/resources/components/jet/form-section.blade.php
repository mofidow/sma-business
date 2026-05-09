@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
  <x-jet.section-title>
    <x-slot name="title">{{ $title }}</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
  </x-jet.section-title>

  <div class="mt-5 md:mt-0 md:col-span-2">
    @if (isset($submit))
      <form wire:submit="{{ $submit }}">
      @else
        <div>
    @endif
    <div
      class="px-4 pt-5 bg-gray-50 dark:bg-gray-950 sm:px-6 sm:pt-6 {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
      <div class="grid grid-cols-6 gap-6">
        {{ $form }}
      </div>
    </div>

    @if (isset($actions))
      <div class="flex items-center justify-end p-4 bg-gray-50 dark:bg-gray-950 text-end sm:px-6 sm:rounded-bl-md sm:rounded-br-md">
        {{ $actions }}
      </div>
    @endif
    @if (isset($submit))
      </form>
    @else
  </div>
  @endif
</div>
</div>
