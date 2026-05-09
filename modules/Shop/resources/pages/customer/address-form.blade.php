<form wire:submit="store" @address-added.window="{{ $property }} = false">
  <h4 class="text-lg font-bold p-4 border-b dark:border-gray-700">
    {{ $this->address?->id ? __('Edit Address') : __('Add New Address') }}
  </h4>
  <div class="p-6 grid grid-cols-6 gap-6 py-6">
    <x-shop::jet.validation-errors class="col-span-6 rounded-md" />

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="name" value="{{ __('Full Name') }}" />
      <x-shop::jet.input id="name" type="text" name="name" wire:model="form.name" autofocus />
      <x-shop::jet.input-error for="form.name" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="company" value="{{ __('Company') }}" />
      <x-shop::jet.input id="company" type="text" name="company" wire:model="form.company" />
      <x-shop::jet.input-error for="form.company" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="phone" value="{{ __('Phone') }}" />
      <x-shop::jet.input id="phone" type="text" name="phone" wire:model="form.phone" />
      <x-shop::jet.input-error for="form.phone" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="email" value="{{ __('Email Address') }}" />
      <x-shop::jet.input id="email" type="email" name="email" wire:model="form.email" />
      <x-shop::jet.input-error for="form.email" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="lot_no" value="{{ __('House/Lot No.') }}" />
      <x-shop::jet.input id="lot_no" type="text" name="lot_no" wire:model="form.lot_no" />
      <x-shop::jet.input-error for="form.lot_no" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="street" value="{{ __('Street') }}" />
      <x-shop::jet.input id="street" type="text" name="street" wire:model="form.street" />
      <x-shop::jet.input-error for="form.street" class="mt-1" />
    </div>

    <div class="col-span-6">
      <x-shop::jet.label for="address_line_1" value="{{ __('Address Line 1') }}" />
      <x-shop::jet.input id="address_line_1" type="text" name="address_line_1" wire:model="form.address_line_1" />
      <x-shop::jet.input-error for="form.address_line_1" class="mt-1" />
    </div>

    <div class="col-span-6">
      <x-shop::jet.label for="address_line_2" value="{{ __('Address Line 2') }}" />
      <x-shop::jet.input id="address_line_2" type="text" name="address_line_2" wire:model="form.address_line_2" />
      <x-shop::jet.input-error for="form.address_line_2" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="city" value="{{ __('City') }}" />
      <x-shop::jet.input id="city" type="text" name="city" wire:model="form.city" />
      <x-shop::jet.input-error for="form.city" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="postal_code" value="{{ __('Postal Code/ZIP') }}" />
      <x-shop::jet.input id="postal_code" type="text" name="postal_code" wire:model="form.postal_code" />
      <x-shop::jet.input-error for="form.postal_code" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="country_id" value="{{ __('Country') }}" />
      <x-shop::jet.select id="country_id" name="country_id" wire:model.live="form.country_id">
        <option value="">{{ __('Select') }}</option>
        @foreach ($countries as $country)
          <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
        @endforeach
      </x-shop::jet.select>
      <x-shop::jet.input-error for="form.country_id" class="mt-1" />
    </div>

    <div class="col-span-6 sm:col-span-3">
      <x-shop::jet.label for="state_id" value="{{ __('State') }}" />
      <x-shop::jet.select id="state_id" name="state_id" wire:model="form.state_id">
        <option value="">{{ __('Select') }}</option>
        @if ($selected_country && !empty($selected_country->states))
          @foreach ($selected_country->states as $state)
            <option value="{{ $state['id'] }}">{{ $state['name'] }}</option>
          @endforeach
        @endif
      </x-shop::jet.select>
      <x-shop::jet.input-error for="form.state_id" class="mt-1" />
    </div>

    <div class="col-span-6">
      <label for="is_default" class="inline-flex items-center">
        <x-shop::jet.checkbox id="is_default" name="is_default" wire:model="form.is_default" />
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Make it default') }}</span>
      </label>
    </div>
  </div>
  <div class="w-full p-4 bg-gray-100 dark:bg-gray-900 text-end border-t dark:border-gray-700">
    <x-shop::jet.secondary-button @click="{{ $property }} = false" wire:loading.attr="disabled">
      {{ __('Cancel') }}
    </x-shop::jet.secondary-button>

    <x-shop::jet.button class="ms-2" wire:loading.attr="disabled">
      {{ __('Save') }}
    </x-shop::jet.button>
  </div>
</form>
