<x-slot name="title">{{ __('Update Billing') }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section submit="save">
    <x-slot name="title">
      {{ __('Update Billing') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill the form to edit record.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.addresses')">
          {{ __('List {x}', ['x' => __('Addresses')]) }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="form">
      <x-shop::jet.validation-errors class="col-span-6 rounded-md" />

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="name" value="{{ __('Name') }}" />
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
        <x-shop::jet.label for="telegram_user_id" value="{{ __('Telegram User ID') }}" />
        <x-shop::jet.input id="telegram_user_id" type="text" name="telegram_user_id" wire:model="form.telegram_user_id" />
        <x-shop::jet.input-error for="form.telegram_user_id" class="mt-1" />
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
        <x-shop::jet.select id="country_id" class="input" name="country_id" wire:model.live="form.country_id">
          <option value="">{{ __('Select') }}</option>
          @foreach ($countries as $country)
            <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
          @endforeach
        </x-shop::jet.select>
        <x-shop::jet.input-error for="form.country_id" class="mt-1" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="state_id" value="{{ __('State') }}" />
        <x-shop::jet.select id="state_id" class="input" type="text" name="state_id" wire:model="form.state_id">
          <option value="">{{ __('Select') }}</option>
          @if ($selected_country && !empty($selected_country->states))
            @foreach ($selected_country->states as $state)
              <option value="{{ $state['id'] }}">{{ $state['name'] }}</option>
            @endforeach
          @endif
        </x-shop::jet.select>
        <x-shop::jet.input-error for="form.state_id" class="mt-1" />
      </div>
    </x-slot>

    <x-slot name="actions">
      <x-shop::jet.button type="submit" wire:loading.attr="disabled">
        {{ __('Submit') }}
      </x-shop::jet.button>
    </x-slot>
  </x-shop::jet.form-section>
</div>
