<x-slot name="title">{{ $currency->id ? __('Edit {x}', ['x' => __('Currency')]) : __('Create {x}', ['x' => __('Currency')]) }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section submit="save">
    <x-slot name="title">
      {{ $currency['id'] ? __('Edit {x}', ['x' => __('Currency')]) : __('Create {x}', ['x' => __('Currency')]) }}
    </x-slot>

    <x-slot name="description">
      {{ $currency['id'] ? __('Please fill the form to edit record.') : __('Please fill the form to add record.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.currencies')">
          {{ __('List {x}', ['x' => __('Currencies')]) }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="form">
      {{-- <x-shop::jet.success /> --}}
      <x-shop::jet.validation-errors class="col-span-6 rounded-md" />

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="currency_id" value="{{ __('Name') }}" />
        <x-shop::jet.select id="currency_id" class="input sm:text-sm" name="currency_id" wire:model="form.currency_id">
          <option value="">{{ __('Select Currency') }}</option>
          @foreach ($currencies as $currency)
            <option value="{{ $currency['id'] }}">{{ $currency['label'] }}</option>
          @endforeach
        </x-shop::jet.select>
        <x-shop::jet.input-error for="form.currency_id" class="mt-2" />
      </div>
      {{-- <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="name" value="{{ __('Name') }}" />
        <x-shop::jet.input id="name" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="name" wire:model="form.name"
          autofocus />
        <x-shop::jet.input-error for="form.name" class="mt-2" />
      </div> --}}

      {{-- <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="code" value="{{ __('Code') }}" />
        <x-shop::jet.input id="code" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="code" wire:model="form.code" />
        <x-shop::jet.input-error for="form.code" class="mt-2" />
      </div> --}}

      {{-- <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="symbol" value="{{ __('Symbol') }}" />
        <x-shop::jet.input id="symbol" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="symbol"
          wire:model="form.symbol" />
        <x-shop::jet.input-error for="form.symbol" class="mt-2" />
      </div> --}}

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="exchange_rate" value="{{ __('Exchange Rate') }}" />
        <x-shop::jet.input id="exchange_rate" class="no-input-arrows block w-full mt-1 py-2 sm:text-sm" type="number" step="0.0001"
          name="exchange_rate" wire:model="form.exchange_rate" />
        @if ($default_currency ?? false)
          <p class="mt-1 text-xs text-mute font-bold">
            {{ __('This should be equal to one (1) {currency}.', ['currency' => $default_currency->name]) }}
          </p>
        @endif
        <x-shop::jet.input-error for="form.exchange_rate" class="mt-1" />
      </div>

      <div class="col-span-6 -mt-3">
        <label for="auto_update" class="inline-flex items-center">
          <x-shop::jet.checkbox id="auto_update" name="auto_update" wire:model="form.auto_update" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Auto Update') }}</span>
        </label>
      </div>

      <div class="col-span-6 -mt-3">
        <label for="show_at_end" class="inline-flex items-center">
          <x-shop::jet.checkbox id="show_at_end" name="show_at_end" wire:model="form.show_at_end" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Show symbol after amount') }}</span>
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
