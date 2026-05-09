<x-slot name="title">{{ $coupon->id ? __('Edit {x}', ['x' => __('Coupon')]) : __('Create {x}', ['x' => __('Coupon')]) }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section submit="save">
    <x-slot name="title">
      {{ $coupon['id'] ? __('Edit {x}', ['x' => __('Coupon')]) : __('Create {x}', ['x' => __('Coupon')]) }}
    </x-slot>

    <x-slot name="description">
      {{ $coupon['id'] ? __('Please fill the form to edit record.') : __('Please fill the form to add record.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.coupons')">
          {{ __('List {x}', ['x' => __('Coupons')]) }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="form">
      {{-- <x-shop::jet.success /> --}}
      <x-shop::jet.validation-errors class="col-span-6 rounded-md" />

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="code" value="{{ __('Code') }}" />
        <x-shop::jet.input id="code" type="text" name="code" wire:model.live.defer="form.code" />
        <x-shop::jet.input-error for="form.code" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="discount" value="{{ __('Discount') }}" />
        <x-shop::jet.input id="discount" type="number" name="discount" wire:model.live.defer="form.discount" />
        <x-shop::jet.input-error for="form.discount" class="mt-1" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="allowed" value="{{ __('Allowed') }}" />
        <x-shop::jet.input id="allowed" type="number" name="allowed" wire:model.live.defer="form.allowed" />
        <x-shop::jet.input-error for="form.allowed" class="mt-1" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <x-shop::jet.label for="expiry_date" value="{{ __('Expiry Date') }}" />
        <x-shop::jet.input-date id="expiry_date" type="date" name="expiry_date" wire:model.live.defer="form.expiry_date" />
        <x-shop::jet.input-error for="form.expiry_date" class="mt-2" />
      </div>

      <div class="col-span-6">
        <label for="active" class="inline-flex items-center">
          <x-shop::jet.checkbox id="active" name="active" wire:model.live.defer="form.active" />
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
