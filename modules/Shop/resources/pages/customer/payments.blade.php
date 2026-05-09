<x-slot name="title">{{ __('Orders') }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ __('Orders') }}
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
                        <td class="px-6 py-4 whitespace-nowrap" @click="window.location='{{ route('shop.order', $sale->id) }}'"
                          style="cursor: pointer;">
                          {{ $sale->date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          {{ $sale->reference }}
                        </td>
                        {{-- <td class="px-6 py-4 whitespace-nowrap text-end">
                            {{ currency_value($sale->subtotal, true) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-end">
                            {{ currency_value($sale->total_tax_amount, true) }}
                          </td> --}}
                        <td class="px-6 py-4 whitespace-nowrap text-end">
                          {{ currency_value($sale->grand_total, true) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-end">
                          {{ currency_value($sale->paid) }}
                          {{-- <div class="flex items-center">
                              @if ($sale->paid >= $sale->grand_total)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                  {{ __('Yes') }}
                                </span>
                              @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                  {{ __('No') }}
                                </span>
                              @endif
                            </div> --}}
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
    </x-slot>
  </x-shop::jet.action-section>
</div>
