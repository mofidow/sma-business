@props(['payment_settings' => []])
<div>
  <div class="mb-6">
    <x-shop::jet.section-title>
      <x-slot name="title">{{ __('Other Payment Methods') }}</x-slot>
      <x-slot name="description">
        {{ __('Please click the button below to make a payment with one of the other available methods.') }}
      </x-slot>
    </x-shop::jet.section-title>
  </div>


</div>
