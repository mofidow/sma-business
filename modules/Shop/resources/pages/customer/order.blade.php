<x-slot name="title">{{ __('Sale Order') }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:22 lg:py-24 print:p-0 print:max-w-full">
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ __('Sale Order') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6 flex flex-wrap items-center gap-4">
        <x-shop::shared.link :to="route('shop.orders')" class="whitespace-nowrap">
          {{ __('List Orders') }}
        </x-shop::shared.link>
        <x-shop::shared.link :to="route('shop.products')" class="whitespace-nowrap">
          {{ __('Browse Products') }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="content">
      @include('shop::pages.customer.templates.' . ($settings['sale_template'] ?? 'One'))
    </x-slot>
  </x-shop::jet.action-section>
</div>
