<x-slot name="title">{{ __('Sale Orders') }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24" x-data="{ open: null }">
  {{-- Delete Order Modal --}}
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ __('Sale Orders') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.products')">
          {{ __('Browse Products') }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="content">
      @unless ($sales && $sales->count())
        <div class="">
          <div class="dark:-mx-2 dark:md:mx-0">
            <div class="p-8 text-center">
              <x-shop::shared.icon name="text-file" class="w-32 h-32 mx-auto" />
              <h3 class="text-lg font-extrabold my-4">{{ __('No data to display!') }}</h3>
              <x-shop::shared.link :to="route('shop.products')">
                {{ __('Browse Products') }}
              </x-shop::shared.link>
            </div>
          </div>
        </div>
      @else
        <div class="-m-6">
          <div class="flex flex-col">
            <div class="sm:rounded-lg overflow-x-auto">
              <div class="align-middle inline-block min-w-full">
                <div class="">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800 whitespace-nowrap">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Date') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Reference') }}
                        </th>
                        {{-- <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Total') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Tax') }}
                        </th> --}}
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Grand Total') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Paid') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                          <span class="sr-only">{{ __('Actions') }}</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                      @foreach ($sales as $sale)
                        <tr @class([
                            'bg-green-300/20' => $sale->paid >= $sale->grand_total,
                            'bg-red-400/20' => $sale->shop && $sale->paid < $sale->grand_total,
                        ])>
                          <td class="px-6 py-4 whitespace-nowrap relative">
                            <a href="{{ route('shop.order', $sale->id) }}" wire:navigate w.hover class="absolute inset-0"></a>
                            {{ $sale->date }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap relative">
                            <a href="{{ route('shop.order', $sale->id) }}" wire:navigate w.hover class="absolute inset-0"></a>
                            {{ $sale->reference }}
                          </td>
                          {{-- <td class="px-6 py-4 whitespace-nowrap text-end" @click="window.location='{{ route('shop.order', $sale->id) }}'"
                          style="cursor: pointer;">
                            {{ currency_value($sale->subtotal, true) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-end" @click="window.location='{{ route('shop.order', $sale->id) }}'"
                          style="cursor: pointer;">
                            {{ currency_value($sale->total_tax_amount, true) }}
                          </td> --}}
                          <td class="px-6 py-4 whitespace-nowrap relative text-end">
                            <a href="{{ route('shop.order', $sale->id) }}" wire:navigate w.hover class="absolute inset-0"></a>
                            {{ currency_value($sale->grand_total, true) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap relative text-end">
                            <a href="{{ route('shop.order', $sale->id) }}" wire:navigate w.hover class="absolute inset-0"></a>
                            {{ currency_value($sale->paid) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                            @if ($sale->shop == 1 && $sale->paid < $sale->grand_total - $sale->rounding)
                              <div class="flex items-stretch justify-end">
                                <button type="button" @click="open = '{{ $sale->id }}'" class="px-3 py-2 hover:text-red-600">
                                  <x-shop::shared.icon name="trash" class="size-5" />
                                </button>
                              </div>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endunless
    </x-slot>
  </x-shop::jet.action-section>

  <div x-show="open">
    <x-shop::alpine.modal :backdrop="false">
      <h2 class="font-medium text-prominent">{{ __('Delete :Model!', ['model' => __('Order')]) }}</h2>
      <p class="mt-2 text-mute max-w-xs">{{ __('Are you sure you want to delete this record?') }}</p>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button"@click="open = false" class="x-focus btn-primary">
          {{ __('No, Cancel') }}
        </button>
        <button type="button" @click="() => { $wire.removeSale(open); open = false; }" class="x-focus btn-danger">
          {{ __('Yes, Delete') }}
        </button>
      </div>
    </x-shop::alpine.modal>
  </div>
</div>
