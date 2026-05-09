@props(['payment_settings' => []])
<div>
  <div class="mb-6">
    <x-shop::jet.section-title>
      <x-slot name="title">{{ __('Pay with PayPal') }}</x-slot>
      <x-slot name="description">
        {{ __('Please click the button below to make pay with PayPal.') }}
      </x-slot>
    </x-shop::jet.section-title>
  </div>

  {{-- <x-shop::jet.button class="w-full justify-center" wire:click="pay" wire:loading.attr="disabled">
    {{ __('Pay with') }}
    <img src="/img/paypal-logo.svg" alt="PayPal" class="ms-2 h-5 dark:invert">
  </x-shop::jet.button> --}}
  <a href="{{ route('shop.paypal.pay', ['payment' => $payment->id]) }}" class="btn-primary w-full justify-center">
    {{ __('Pay with') }}
    <img src="/img/paypal-logo.svg" alt="PayPal" class="ms-2 h-5 dark:invert">
  </a>
</div>
