<x-slot
  name="title">{{ $shipping_method->id ? __('Edit {x}', ['x' => __('Shipping Method')]) : __('Create {x}', ['x' => __('Shipping Method')]) }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section submit="save">
    <x-slot name="title">
      {{ $shipping_method->id ? __('Edit {x}', ['x' => __('Shipping Method')]) : __('Create {x}', ['x' => __('Shipping Method')]) }}
    </x-slot>

    <x-slot name="description">
      {{ $shipping_method->id ? __('Please fill the form to edit record.') : __('Please fill the form to add record.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.shipping_methods')">
          {{ __('List {x}', ['x' => __('Shipping Methods')]) }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="form">
      {{-- <x-shop::jet.success /> --}}
      <x-shop::jet.validation-errors class="col-span-6 rounded-md" />

      <div class="col-span-6">
        <x-shop::jet.label for="provider_name" value="{{ __('Provider Name') }}" />
        <x-shop::jet.input id="provider_name" class="block w-full mt-1 py-2 sm:text-sm" type="text" provider_name="provider_name"
          wire:model="form.provider_name" autofocus />
        <x-shop::jet.input-error for="form.provider_name" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="rate" value="{{ __('Rate') }}" />
        <x-shop::jet.input id="rate" class="no-input-arrows block w-full mt-1 py-2 sm:text-sm" type="number" step="0.0001"
          name="rate" wire:model="form.rate" />
        <x-shop::jet.input-error for="form.rate" class="mt-1" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="free_order_amount" value="{{ __('Free Shipping for Orders Over [Amount]') }}" />
        <x-shop::jet.input id="free_order_amount" class="no-input-arrows block w-full mt-1 py-2 sm:text-sm" type="number" step="0.01"
          name="free_order_amount" wire:model="form.free_order_amount" />
        <x-shop::jet.input-error for="form.free_order_amount" class="mt-1" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="country" value="{{ __('Country') }}" />
        <x-shop::jet.select id="country" class="input mt-1 text-sm" name="country" wire:model.live="form.country_id">
          <option value="">{{ __('Select') }}</option>
          @forelse ($countries as $country)
            <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
          @empty
          @endforelse
        </x-shop::jet.select>
        <x-shop::jet.input-error for="form.country" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="state" value="{{ __('State') }}" />
        <x-shop::jet.select id="state" class="input mt-1 text-sm" type="text" name="state" wire:model="form.state_id">
          <option value="">{{ __('Select') }}</option>
          @forelse ($states as $state)
            <option value="{{ $state['id'] }}">{{ $state['name'] }}</option>
          @empty
            <option value="">{{ __('No states available') }}</option>
          @endforelse
        </x-shop::jet.select>
        <x-shop::jet.input-error for="form.state" class="mt-2" />
      </div>

      <div class="col-span-6">
        <label for="active" class="inline-flex items-center">
          <x-shop::jet.checkbox id="active" name="active" wire:model="form.active" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
        </label>
      </div>
      <div class="col-span-6 -mt-3">
        <label for="charge_for_weight" class="inline-flex items-center">
          <x-shop::jet.checkbox id="charge_for_weight" name="charge_for_weight" wire:model="form.charge_for_weight" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Charge for weight') }}</span>
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
