<x-slot name="title">{{ $page->id ? __('Edit {x}', ['x' => __('Page')]) : __('Create {x}', ['x' => __('Page')]) }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section submit="save">
    <x-slot name="title">
      {{ $page->id ? __('Edit {x}', ['x' => __('Page')]) : __('Create {x}', ['x' => __('Page')]) }}
    </x-slot>

    <x-slot name="description">
      {{ $page->id ? __('Please fill the form to edit record.') : __('Please fill the form to add record.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.pages')">
          {{ __('List {x}', ['x' => __('Pages')]) }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="form">
      {{-- <x-shop::jet.success /> --}}
      <x-shop::jet.validation-errors class="col-span-6 rounded-md" />

      <div class="col-span-6">
        <x-shop::jet.label for="title" value="{{ __('Title') }}" />
        <x-shop::jet.input id="title" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="title" wire:model="form.title"
          autofocus />
        <x-shop::jet.input-error for="form.title" class="mt-2" />
      </div>

      <div class="col-span-6">
        <x-shop::jet.label for="slug" value="{{ __('Slug') }}" />
        <x-shop::jet.input id="slug" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="slug" wire:model="form.slug" />
        <x-shop::jet.input-error for="form.slug" class="mt-2" />
      </div>

      <div class="col-span-6">
        <x-shop::jet.label for="description" value="{{ __('Description') }}" />
        <textarea id="description" rows="2"
          class="mt-1 focus:border-blue-600 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md"
          name="description" wire:model="form.description"></textarea>
        <x-shop::jet.input-error for="form.description" class="mt-2" />
      </div>

      <div class="col-span-6">
        <x-shop::jet.label for="body" value="{{ __('Body') }}" />
        <textarea id="body" rows="10"
          class="mt-1 focus:border-blue-600 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md"
          name="body" wire:model="form.body"></textarea>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          {{ __('You can use markdown here.') }}
        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          {{ __('Available Short Codes:') }}
          <code class="text-xs">
            {!! htmlentities('<!-- [contact-form] -->') !!}
          </code> &
          <code class="text-xs">
            {!! htmlentities('<!-- [map:address here] -->') !!}
          </code>
        </div>
        </p>
        <x-shop::jet.input-error for="form.body" class="mt-2" />
      </div>

      <div class="col-span-6">
        <label for="active" class="inline-flex items-center">
          <x-shop::jet.checkbox id="active" name="active" wire:model="form.active" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
        </label>
      </div>
    </x-slot>

    <x-slot name="actions">
      <x-shop::jet.button type="submit" wire:loading.attr="disabled">
        {{ __('Submit') }}
      </x-shop::jet.button>
    </x-slot>
  </x-shop::jet.form-section>
</div>
