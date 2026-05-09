<x-slot name="title">{{ __('Sale No.') }}: {{ $sale->number }}</x-slot>
<x-slot name="metaDesc"></x-slot>
<x-slot name="ogMetaData"></x-slot>

<div class="container max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 my-8 text-gray-700 dark:text-gray-300">
  <div class="bg-white dark:bg-gray-900 shadow-md overflow-hidden rounded-md">
    {{-- <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
        {{ __('Sale No.') }}: {{ $sale->number }}
      </h3>
      <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
        {{ __('Sale Id') }}: {{ $sale->id }}
      </p>
    </div> --}}
    <div class="p-6">
      <div class="w-full flex justify-between">
        <div>
          <span class="np hidden dark:block rounded h-20">
            <img src="{{ storage_url($shop_settings->shop_logo_dark) }}" alt="{{ __($shop_settings->shop_name) }}"
              class="max-h-20 h-full object-cover" />
          </span>
          <span class="print block dark:hidden rounded h-20">
            <img src="{{ storage_url($shop_settings->shop_logo_light) }}" alt="{{ __($shop_settings->shop_name) }}"
              class="max-h-20 h-full object-cover" />
          </span>
        </div>
        <div class="grow flex justify-end text-end">
          <div class="text-sm pe-2">
            <ul>
              <li>
                <strong>{{ $settings['name'] }} ({{ $settings['short_name'] }})</strong>
              </li>
              <li>
                <strong>{{ $settings['company'] }}</strong>
              </li>
              <li>{{ $settings['email'] }} <span class="text-muted">|</span> {{ $settings['phone'] }}</li>
              <li>{{ $settings['address'] }}</li>
            </ul>
          </div>
          <div class="flex items-center justify-center">
            <div class="np hidden dark:block">
              <div class="p-1 flex flex-col items-center justify-center">
                {!! generate_barcode($sale->reference, null, 1, 70, 'white') !!}
                {{-- <code style="font-size:11px;">{{ $sale->reference }}</code> --}}
              </div>
            </div>
            <div class="print block dark:hidden">
              <div class="p-1 flex flex-col items-center justify-center">
                {!! generate_barcode($sale->reference, null, 1, 70, 'black') !!}
                {{-- <code style="font-size:11px;">{{ $sale->reference }}</code> --}}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-6 -mx-6 px-6 py-2 text-center uppercase font-extrabold border-b-2 border-t-2 dark:border-gray-700">
        {{ __('Sale') }}
      </div>
      <div class="grid grid-cols-2 gap-6 -mx-6 px-6 py-2 border-b dark:border-gray-700">
        <div class="">
          <div>{{ __('Date') }}: <span class="font-bold">{{ $sale->date->toFormattedDateString() }}</span></div>
          <div>{{ __('Created at') }}: <span class="font-bold">{{ $sale->created_at->toDayDateTimeString() }}</span></div>
        </div>
        <div class="">
          <div>{{ __('reference') }}: <span class="font-bold">{{ $sale->reference }}</span></div>
          <div>{{ __('Id') }}: <span class="font-bold">{{ $sale->id }}</span></div>
        </div>
      </div>
      @unless ($sale->paid)
        @if ($sale->draft)
          <div class="rounded-md bg-yellow-50 dark:bg-yellow-900 p-4 mt-6">
            <div class="flex">
              <div class="shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                  aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ms-3">
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                  {{ __('Please make payment to complete the order.') }}
                </p>
              </div>
            </div>
          </div>
        @endif
      @endunless
      <div class="grid grid-cols-2 gap-6 -mx-6 px-6 pt-4">
        <div class="">
          <span class="text-sm">{{ __('Store') }}:</span>
          <div class="font-extrabold">{{ $sale->location->name }}</div>
          <div>{{ $sale->location->address }} {{ $sale->location->state_name }} {{ $sale->location->country_name }}</div>
          <div>{{ $sale->location->phone }}</div>
          <div>{{ $sale->location->email }}</div>
        </div>
        <div class="">
          <span class="text-sm">{{ __('Bill to') }}:</span>
          @if ($sale->customer && $sale->customer->id != $settings['default_customer'])
            <div class="font-extrabold">{{ $sale->customer->name }}</div>
            <div>{{ $sale->customer->address }} {{ $sale->customer->state_name }} {{ $sale->customer->country_name }}</div>
            <div>{{ $sale->customer->phone }}</div>
            <div>{{ $sale->customer->email }}</div>
          @elseif($sale->address)
            <div class="font-extrabold">{{ $sale->address->first_name }} {{ $sale->address->last_name }}</div>
            <div>{{ $sale->address->address }} {{ $sale->address->state_name }} {{ $sale->address->country_name }}</div>
            <div>{{ $sale->address->phone }}</div>
            <div>{{ $sale->address->email }}</div>
          @else
            <div class="font-extrabold">{{ $sale->customer->name }}</div>
          @endif
        </div>
      </div>

      <div class="flex flex-col mt-6">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="overflow-hidden border dark:border-gray-700 rounded-md">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th scope="col" class="px-4 py-2 text-start text-xs font-medium uppercase tracking-wider">
                      {{ __('Description') }}
                    </th>
                    <th scope="col" class="w-20 px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                      {{ __('Quantity') }}
                    </th>
                    <th scope="col" class="w-20 px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                      {{ __('Price') }}
                    </th>
                    @if ($settings['show_discount'])
                      <td>{{ __('Discount') }}</td>
                    @endif
                    @if ($settings['show_tax'])
                      <td>{{ __('Tax') }}</td>
                    @endif
                    <th scope="col" class="w-24 px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                      {{ __('Subtotal') }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($sale->items as $item)
                    @if ($item->variations->isNotEmpty())
                      <tr>
                        <td class="border-t dark:border-gray-700 px-4 py-2">
                          <strong>{{ __($item->item->name) }} [{{ $item->item->code }}]</strong>
                          @if ($item->item->alt_name)
                            <br /> {{ $item->item->alt_name }}
                          @endif
                          {{-- <div v-if="$item->variation"{{ $item->variation->id }}</div> --}}
                        </td>
                        <td class="border-t dark:border-gray-700"></td>
                        <td class="border-t dark:border-gray-700"></td>
                        @if ($settings['show_discount'])
                          <td class="border-t dark:border-gray-700"></td>
                        @endif
                        @if ($settings['show_tax'])
                          <td class="border-t dark:border-gray-700"></td>
                        @endif
                        <td class="border-t dark:border-gray-700"></td>
                      </tr>
                      @foreach ($item->variations as $variation)
                        <tr>
                          <td class="px-4 pb-2">
                            {{ meta_array_to_string($variation->meta) }}
                          </td>
                          <td class="px-4 pb-2 text-center">
                            {{ formatQuantity($variation->pivot->quantity) }}
                          </td>
                          <td class="px-4 pb-2 text-end">
                            {{ formatNumber($variation->pivot->price) }}
                          </td>
                          @if ($settings['show_discount'])
                            <td class="px-4 pb-2 font-bold text-end">
                              {{ formatNumber($variation->pivot->total_discount_amount) }}
                            </td>
                          @endif
                          @if ($settings['show_tax'])
                            <td class="px-4 pb-2 font-bold text-end">
                              {{ formatNumber($variation->pivot->total_tax_amount) }}
                            </td>
                          @endif
                          <td class="px-4 pb-2 text-end">
                            {{ formatNumber($variation->pivot->total) }}</td>
                        </tr>
                      @endforeach
                    @elseif ($item->portions->isNotEmpty())
                      @foreach ($item->portions as $portion)
                        <tr>
                          <td class="border-t dark:border-gray-700 px-4 py-2">
                            <strong>{{ __($item->item->name) }} [{{ $item->item->code }}]</strong>
                            @if ($item->item->alt_name)
                              <br /> {{ $item->item->alt_name }}
                            @endif
                          </td>
                          <td class="border-t dark:border-gray-700"></td>
                          <td class="border-t dark:border-gray-700"></td>
                          @if ($settings['show_discount'])
                            <td class="border-t dark:border-gray-700"></td>
                          @endif
                          @if ($settings['show_tax'])
                            <td class="border-t dark:border-gray-700"></td>
                          @endif
                          <td class="border-t dark:border-gray-700"></td>
                        </tr>
                        <tr>
                          <td class="px-4 pb-2">
                            {{ __('Portion') }}: <span class="font-bold uppercase">{{ $portion->name }}</span>
                          </td>
                          <td class="px-4 pb-2 font-bold text-center">
                            {{ formatQuantity($portion->pivot->quantity) }}
                          </td>
                          <td class="px-4 pb-2 font-bold text-end">
                            @if ($settings['show_discount'])
                              {{ formatNumber($portion->pivot->net_price) }}
                            @elseif ($settings['show_tax'])
                              {{ formatNumber($portion->pivot->price) }}
                            @else
                              {{ formatNumber($portion->pivot->price - $portion->pivot->discount_amount + $portion->pivot->tax_amount) }}
                            @endif
                            <!-- {{ $portion->pivot->net_price }} -->
                          </td>
                          @if ($settings['show_discount'])
                            <td class="px-4 pb-2 font-bold text-end">
                              {{ formatNumber($portion->pivot->total_discount_amount) }}
                            </td>
                          @endif
                          @if ($settings['show_tax'])
                            <td class="px-4 pb-2 font-bold text-end">
                              {{ formatNumber($portion->pivot->total_tax_amount) }}
                            </td>
                          @endif
                          <td class="px-4 pb-2 font-bold text-end">
                            {{ formatNumber($portion->pivot->total) }}
                          </td>
                        </tr>
                        @if ($portion->portion_items && $portion->portion_items->isNotEmpty())
                          @foreach ($portion->portion_items as $pe)
                            <tr class="text-gray-500 dark:text-gray-400">
                              <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }}">
                                {{ __($pe->item->name) }}
                                @if ($pe->meta)
                                  ({{ meta_array_to_string($pe->meta) }})
                                @endif
                              </td>
                              <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-end">
                                {{ $pe->quantity * $portion->pivot->quantity }}
                              </td>
                              <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-end">
                              </td>
                              @if ($settings['show_discount'])
                                <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-end"></td>
                              @endif
                              @if ($settings['show_tax'])
                                <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-end"></td>
                              @endif
                              <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-end">
                              </td>
                            </tr>
                          @endforeach
                        @endif
                        @if ($portion->essentials && $portion->essentials->isNotEmpty())
                          @foreach ($portion->essentials as $pe)
                            <tr class="text-gray-500 dark:text-gray-400">
                              <td class="px-4 pb-0">
                                {{ __($pe->item->name) }}
                                @if ($pe->meta)
                                  ({{ meta_array_to_string($pe->meta) }})
                                @endif
                              </td>
                              <td class="px-4 pb-0 text-center">
                                {{ formatQuantity($pe->quantity * $portion->pivot->quantity) }}</td>
                              <td class="px-4 pb-0 text-end"></td>
                              @if ($settings['show_discount'])
                                <td class="px-4 pb-0 text-end"></td>
                              @endif
                              @if ($settings['show_tax'])
                                <td class="px-4 pb-0 text-end"></td>
                              @endif
                              <td class="px-4 pb-0 text-end"></td>
                            </tr>
                          @endforeach
                        @endif

                        @foreach ($portion->choosables as $group)
                          @foreach ($group->items as $pcitem)
                            @if (collect($portion->pivot->choosables)->where('id', $group->id)->where('item_id', $pcitem->item_id)->first())
                              <tr class="text-gray-500 dark:text-gray-400">
                                <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}">
                                  {{ __($pcitem->item->name) }} (<strong class="text-xs">{{ __($group->name) }}</strong>)
                                  @if ($group->meta)
                                    ({{ meta_array_to_string($group->meta) }})
                                  @endif
                                </td>
                                <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }} text-center">
                                  {{ formatQuantity($pcitem->quantity * $portion->pivot->quantity) }}
                                </td>
                                <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                                @if ($settings['show_discount'])
                                  <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                                @endif
                                @if ($settings['show_tax'])
                                  <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                                @endif
                                <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                              </tr>
                            @endif
                          @endforeach
                        @endforeach
                      @endforeach
                    @else
                      <tr>
                        <td class="border-t dark:border-gray-700 px-4 py-2">
                          <strong>{{ __($item->item->name) }} [{{ $item->item->code }}]</strong>
                          @if ($item->item->alt_name)
                            <br /> {{ $item->item->alt_name }}
                          @endif
                        </td>
                        <td class="border-t dark:border-gray-700 px-4 py-2 text-center">
                          {{ formatNumber($item->quantity) }}
                        </td>
                        <td class="border-t dark:border-gray-700 px-4 py-2 text-end">
                          {{ formatNumber($item->price) }}
                        </td>
                        @if ($settings['show_discount'])
                          <td class="border-t dark:border-gray-700 px-4 py-2 text-end">
                            {{ formatNumber($item->discount_amount) }}
                          </td>
                        @endif
                        @if ($settings['show_tax'])
                          <td class="border-t dark:border-gray-700 px-4 py-2 text-end">
                            {{ formatNumber($item->tax_amount) }}
                          </td>
                        @endif
                        <td class="border-t dark:border-gray-700 px-4 py-2 text-end">
                          {{ formatNumber($item->subtotal) }}
                        </td>
                      </tr>
                    @endif
                  @endforeach
                </tbody>
                <tfoot class="stripe divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                  <tr>
                    <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                      {{ __('Total') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-end">
                      {{ formatNumber($sale->total) }}
                    </td>
                  </tr>
                  @if ($sale->total_tax_amount)
                    <tr>
                      <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Total Tax Amount') }}
                      </td>
                      <td class="px-4 py-2 font-bold text-end">
                        {{ formatNumber($sale->total_tax_amount) }}
                      </td>
                    </tr>
                  @endif
                  @if ($sale->total_discount_amount)
                    <tr>
                      <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Total Discount Amount') }}
                      </td>
                      <td class="px-4 py-2 font-bold text-end">
                        {{ formatNumber($sale->total_discount_amount) }}
                      </td>
                    </tr>
                  @endif
                  @if ($sale->shipping)
                    <tr>
                      <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Shipping') }}
                      </td>
                      <td class="px-4 py-2 font-bold text-end">
                        {{ formatNumber($sale->shipping) }}
                      </td>
                    </tr>
                  @endif
                  <tr>
                    <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                      {{ __('Grand Total') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-end">
                      {{ formatNumber($sale->grand_total) }}
                    </td>
                  </tr>
                  @if ($sale->payments->isNotEmpty())
                    @foreach ($sale->payments as $payment)
                      <tr>
                        <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium">
                          <span>{{ __('Payment settlement') }}</span><br />
                          <span class="text-sm">({{ __('Date') }}: {{ $payment->created_at->toDayDateTimeString() }},
                            {{ __('Reference') }}:
                            {{ $payment->reference }})</span>
                        </td>
                        <td class="px-4 py-2 font-bold text-end">{{ formatNumber($payment->pivot->amount) }}</td>
                      </tr>
                    @endforeach
                    <tr>
                      <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium">{{ __('Balance Due') }}</td>
                      <td class="px-4 py-2 font-bold text-end">
                        {{ formatNumber($sale->grand_total - $totalPaid) }}</td>
                    </tr>
                  @else
                    <tr>
                      <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium">{{ __('Paid') }}</td>
                      <td class="px-4 py-2 font-bold text-end">{{ formatNumber(0) }}</td>
                    </tr>
                    <tr>
                      <td colspan="{{ $colSpan }}" class="px-4 py-2 font-medium">{{ __('Balance Due') }}</td>
                      <td class="px-4 py-2 font-bold text-end">{{ formatNumber($sale->grand_total) }}</td>
                    </tr>
                  @endif
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      @if ($settings['show_tax_summary'] && $sale->total_tax_amount && $taxSummary->isNotEmpty())
        <div class="flex flex-col mt-6">
          <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
              <div class="overflow-hidden border dark:border-gray-700 rounded-md">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                      <th scope="col" colspan="5" class="px-4 py-2 text-start text-xs font-medium uppercase tracking-wider">
                        {{ __('Tax Summary') }}
                      </th>
                    </tr>
                    <tr>
                      <th scope="col" class="px-4 py-2 text-start text-xs font-medium uppercase tracking-wider">
                        {{ __('Name') }}
                      </th>
                      <th scope="col" class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                        {{ __('Code') }}
                      </th>
                      <th scope="col" class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                        {{ __('Qty/Weight') }}
                      </th>
                      <th scope="col" class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                        {{ __('Tax Excl. amount') }}
                      </th>
                      <th scope="col" class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider">
                        {{ __('Tax Amount') }}
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($taxSummary as $tax)
                      <tr>
                        {{-- <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800' : 'bg-white dark:bg-gray-900' }}"> --}}
                        <td class="px-4 py-2">
                          {{ __($tax['name']) }}
                        </td>
                        <td class="px-4 py-2 text-center">
                          {{ $tax['code'] }}
                        </td>
                        <td class="px-4 py-2 text-center">
                          {{ formatNumber($tax['quantity'] ?? 1) }}
                        </td>
                        <td class="px-4 py-2 text-end">
                          {{ formatNumber($tax['item_net_amount']) }}
                        </td>
                        <td class="px-4 py-2 text-end">
                          {{ formatNumber($tax['amount']) }}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                    <tr class="bg-gray-50 dark:bg-gray-800">
                      <td colspan="4" class="px-4 py-2 text-end font-medium">
                        {{ __('Total Tax Amount') }}
                      </td>
                      <td class="px-4 py-2 font-bold text-end">
                        {{ formatNumber($sale->total_tax_amount) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if ($sale->details)
        <div class="mt-6 px-4 py-3 rounded-md border dark:border-gray-700">
          {{ __($sale->details) }}
        </div>
      @endif

      @if ($sale->attachments->isNotEmpty())
        <div class="mt-6 sm:col-span-2 np ps-0">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Attachments
          </div>
          <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
            <ul class="border border-gray-200 dark:border-gray-700 rounded-md divide-y divide-gray-200 dark:divide-gray-700">
              <li class="ps-3 pe-4 py-3 flex items-center justify-between text-sm">
                <div class="w-0 flex-1 flex items-center">
                  {{-- <PaperClipIcon class="shrink-0 h-5 w-5 text-gray-400" aria-hidden="true" /> --}}
                  <span class="ms-2 flex-1 w-0 truncate">
                    resume_back_end_developer.pdf
                  </span>
                </div>
                <div class="ms-4 shrink-0">
                  <a href="#" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-200">
                    Download
                  </a>
                </div>
              </li>
              <li class="ps-3 pe-4 py-3 flex items-center justify-between text-sm">
                <div class="w-0 flex-1 flex items-center">
                  <span class="ms-2 flex-1 w-0 truncate">
                    coverletter_back_end_developer.pdf
                  </span>
                </div>
                <div class="ms-4 shrink-0">
                  <a href="#" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-200">
                    Download
                  </a>
                </div>
              </li>
            </ul>
          </div>
        </div>
      @endif

      <div class="cgd text-center text-sm mt-6 text-gray-500 dark:text-gray-400 ">
        {{ __('This is a computer-generated document. No signature is required.') }}
      </div>

      <div class="np mt-6 flex items-center justify-center gap-4">
        @unless ($sale->paid)
          <x-shop::elements.button wire:click="makePayment">
            {{ __('Make Payment') }}
          </x-shop::elements.button>
          <x-shop::helpers.a-dialog id="delete-order" maxWidth="sm">
            <x-slot name="trigger">
              <x-shop::elements.danger-button type="button">
                {{ __('Delete') }}
              </x-shop::elements.danger-button>
            </x-slot>

            <x-slot name="title">
              {{ __('Delete Order?') }}
            </x-slot>
            <x-slot name="content">
              {{ __('Are you sure you want to delete order?') }}
            </x-slot>

            <x-slot name="footer">
              <x-shop::elements.danger-button class="ms-2" wire:click="remove" wire:loading.attr="disabled">
                {{ __('Delete') }}
              </x-shop::elements.danger-button>
            </x-slot>
          </x-shop::helpers.a-dialog>
          <x-shop::elements.button type="button" onclick="window.print()">
            {{ __('Print') }}
          </x-shop::elements.button>
        @else
          <x-shop::elements.secondary-button type="button" onclick="window.print()" class="w-full justify-center">
            <x-shop::elements.icon name="printer" class="w-5 h-5 me-2" /> {{ __('Print') }}
          </x-shop::elements.secondary-button>
        @endunless
      </div>
    </div>
  </div>
</div>
