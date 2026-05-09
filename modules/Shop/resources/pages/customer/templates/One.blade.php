<div>
  @php
    $colspan = 4;

    if ($settings['show_discount'] == 1) {
        $colspan++;
    }
    if ($settings['show_tax'] == 1) {
        $colspan++;
    }
  @endphp
  <div class="-m-6">
    <div class="flex flex-col">
      <div class="sm:rounded-lg overflow-x-auto">
        <div class="align-middle inline-block min-w-full">
          <div class="mt-4 px-6 py-4 print:py-0 print:m-0">
            <div class="max-h-16 max-w-[250px] mb-1">
              @if ($sale->store->logo)
                <img class="max-w-full h-16" src="{{ $sale->store->logo }}" alt="{{ $sale->store->name }}" />
              @else
                <img alt="{{ $settings['name'] }}" src="{{ $settings['logo'] }}" class="max-w-full h-16 dark:hidden" />
                @if ($settings['logo_dark'])
                  <img alt="{{ $settings['name'] }}" src="{{ $settings['logo_dark'] }}" class="max-w-full h-16 hidden dark:block" />
                @endif
              @endif
            </div>
            <div class="flex items-start justify-between gap-3 mb-8">
              <div class="flex flex-col w-3/5">
                {{-- <div class="font-semibold text-lg">{{ $sale->store->name }}</div> --}}
                <div class="text-sm">{{ address($sale->store) }}</div>
              </div>
              <div class="w-2/5">
                <div class="font-extrabold uppercase text-lg mb-1">{{ __('Sale Order') }}</div>
                <div class="text-sm">{{ __('Sale No. {x}', ['x' => $sale->id]) }}</div>
                <div class="text-sm">{{ __('Date') }}: {{ $sale->date }}</div>
                <div class="text-sm">{{ __('Created at') }}: {{ $sale->created_at }}</div>
                <div class="text-sm flex gap-1">
                  {{ __('Reference') }}:
                  <p class="truncate hover:text-clip print:text-clip print:block" dir="rtl">{{ $sale->reference }}</p>
                </div>
                @if ($sale->payment_method)
                  <div class="text-sm">{{ __('Payment Method') }}: <span>{{ $sale->payment_method }}</span></div>
                @endif
                @if ($sale->due_date)
                  <div class="text-sm">{{ __('Due Date') }}: {{ $sale->due_date }}</div>
                @endif
              </div>
            </div>

            @if ($sale->return_orders_count > 0)
              <div class="col-span-full pb-8">
                <a href="{{ route('return_orders.index', ['filters' => ['sale_id' => $sale->id]]) }}"
                  class="block rounded-md bg-red-50 dark:bg-red-800 p-4 print:border print:border-red-700">
                  <div class="flex">
                    <div class="shrink-0">
                      <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                          d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                          clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="ms-3">
                      <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ __('This sale has {x}', [
                            'x' => $sale->return_orders_count == 1 ? '1 ' . __('return order') : $sale->return_orders_count . ' ' . __('return orders'),
                        ]) }}
                      </h3>
                    </div>
                  </div>
                </a>
              </div>
            @endif

            @if ($sale->shop && $sale->paid < $sale->grand_total - $sale->rounding && $sale->directPendingPayments?->isNotEmpty())
              <div class="col-span-full pb-8 print:hidden">
                <a href="{{ $sale->directPendingPayments->first() ? route('shop.payment', $sale->directPendingPayments->first()->id) : '#' }}"
                  class="block rounded-md bg-yellow-50 dark:bg-yellow-800 p-4 print:border print:border-yellow-700 border border-yellow-700">
                  <div class="flex">
                    <div class="shrink-0">
                      <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                          d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                          clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="ms-3">
                      <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('Payment is due for this sale, please make payment to complete your order.') }}
                      </h3>
                    </div>
                  </div>
                </a>
              </div>
            @endif

            <div class="print:hidden">
              {{-- {{ json_encode($sale->fiscal_service_response) }} --}}
              @if ($settings['fiscal_service_driver'] ?? null)
                @if (!$sale->fiscal_service_response)
                  <div class="text-yellow-500">
                    <p class="mb-2 font-bold">{{ __('Fiscal service processing is pending for this sale.') }}</p>
                  </div>
                @elseif ($sale->fiscal_service_response['success'] ?? null)
                  {{-- Success --}}
                  @php
                    $qrcode = new FiscalServicePlugin()->active()->getQRCodeUrl($sale) ?? null;
                  @endphp
                @else
                  <div class="text-red-500">
                    <p class="mb-2 font-bold">{{ $sale->fiscal_service_response['message'] }}</p>
                  </div>
                @endif
              @endif
            </div>

            <div class="flex items-center justify-center gap-6 pb-8">
              <script>
                document.addEventListener('livewire:navigated', () => {
                  // Barcode
                  const barcodeElement = document.querySelector('.barcode');
                  if (barcodeElement) {
                    JsBarcode(barcodeElement).init();
                  }

                  // QR Code
                  const qrImageElement = document.querySelector('.qr-image');
                  if (qrImageElement) {
                    QRCode.toDataURL("{{ $qrcode ?? $sale->signedRoute() }}", {
                      type: 'svg'
                    }, (err, url) => {
                      if (!err) {
                        qrImageElement.innerHTML = `<img src="${url}" alt="QR Code" class="h-[80px] w-[80px] rounded" />`;
                      }
                    });
                  }
                });
              </script>
              <div class="bc-image h-[80px] overflow-hidden rounded">
                <svg class="barcode" jsbarcode-width="1" jsbarcode-margin="5" jsbarcode-height="70" jsbarcode-fontsize="12"
                  jsbarcode-textmargin="3" jsbarcode-format="CODE128" jsbarcode-fontoptions="bold" jsbarcode-displayvalue="false"
                  jsbarcode-value="{{ $sale->reference }}" />
              </div>
              <div class="qr-image qrcode h-[80px] overflow-hidden rounded"></div>
            </div>

            <div class="pb-8 w-full flex gap-6">
              <div class="w-1/2">
                <h2 class="text-xs font-bold mb-1">{{ __('Sell To') }}</h2>
                <div class="text-sm">{{ address($sale->customer, true) }}</div>
              </div>
              @if ($sale->address)
                <div class="w-1/2">
                  <h2 class="text-xs font-bold mb-1">{{ __('Ship To') }}</h2>
                  <div class="text-sm">{{ address($sale->address, true) }}</div>
                </div>
              @endif
            </div>

            <div class="overflow-x-auto">
              <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400 dark:print:divide-gray-400">
                <thead>
                  <tr>
                    <th class="font-bold text-center uppercase p-2 w-7">#</th>
                    <th class="font-bold text-start uppercase p-2">{{ __('Description') }}</th>
                    <th class="font-bold text-center uppercase p-2 w-[120px] whitespace-nowrap">
                      {{ $settings['show_tax'] == 1 ? __('Price') : __('Unit Price') }}
                    </th>
                    <th class="font-bold text-center uppercase p-2 w-[80px]">{{ __('Qty') }}</th>
                    @if ($settings['show_discount'] == 1)
                      <th class="font-bold text-center uppercase p-2 w-[80px]">
                        {{ __('Discount') }}
                      </th>
                    @endif
                    @if ($settings['show_tax'] == 1)
                      <th class="font-bold text-center uppercase p-2 w-[80px]">{{ __('Tax') }}</th>
                    @endif
                    <th class="font-bold text-center uppercase p-2 w-[120px]">{{ __('Total') }}</th>
                  </tr>
                </thead>

                @foreach ($sale->items as $item)
                  @if ($item->variations->isNotEmpty())
                    <tbody>
                      <tr>
                        <td class="p-2 w-7 text-end">{{ $loop->iteration }}</td>
                        <td class="p-2">
                          <div class="flex items-center gap-2">
                            @if ($settings['show_image'] == 1)
                              @if ($item->product->photo)
                                <img class="w-8 h-8 rounded-xs self-start" src="{{ $item->product->photo }}" alt="Product Image" />
                              @else
                                <img class="w-8 h-8 rounded-xs self-start" src="img/no-image.png" alt="No Image" />
                              @endif
                            @endif
                            <div>
                              {{ $item->product->name }}
                              @if ($item->comment)
                                <div class="text-xs">{{ $item->comment }}</div>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td class="p-2"></td>
                        <td class="p-2 text-center">
                          <!-- {{ format_decimal_qty($item->quantity) }} -->
                        </td>
                        @if ($settings['show_discount'] == 1)
                          <td class="p-2 text-end"></td>
                        @endif
                        @if ($settings['show_tax'] == 1)
                          <td class="p-2"></td>
                        @endif
                        <td class="p-2"></td>
                      </tr>

                      @foreach ($item->variations as $variation)
                        <tr>
                          <td class="w-7"></td>
                          <td class="p-2">
                            <div class="flex items-center gap-2">
                              @if ($settings['show_image'] == 1)
                                <div class="w-8 h-8 rounded-xs self-start"></div>
                              @endif
                              <div>
                                {{ meta_array_to_string($variation->meta) }}
                              </div>
                            </div>
                          </td>
                          <td class="p-2 text-end">
                            {{ format_decimal($variation->pivot->price) }}
                            <!-- {{ $settings['show_tax'] == 1 ? format_decimal($variation->pivot->price) : format_decimal($variation->pivot->unit_price) }} -->
                          </td>
                          <td class="p-2 text-center">
                            {{ format_decimal_qty($variation->pivot->quantity) }}{{ $variation->pivot?->unit?->code ?? '' }}
                          </td>
                          @if ($settings['show_discount'] == 1)
                            <td class="p-2 text-end">
                              {{ format_decimal($variation->pivot->discount_amount) }}
                            </td>
                          @endif
                          @if ($settings['show_tax'] == 1)
                            <td class="p-2 text-end">{{ format_decimal($variation->pivot->tax_amount) }}</td>
                          @endif
                          <td class="p-2 text-end font-bold">{{ format_decimal($variation->pivot->total) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  @else
                    <tbody
                      class="divide-y divide-gray-200 dark:divide-gray-700 border-y border-gray-200 dark:border-gray-700 print:divide-gray-400 dark:print:divide-gray-400">
                      <tr>
                        <td class="p-2 w-7 text-end">{{ $loop->iteration }}</td>
                        <td class="p-2">
                          <div class="flex items-center gap-2">
                            @if ($settings['show_image'] == 1)
                              @if ($item->product->photo)
                                <img class="w-8 h-8 rounded-xs self-start" src="{{ $item->product->photo }}" alt="Product Image" />
                              @else
                                <img class="w-8 h-8 rounded-xs self-start" src="img/no-image.png" alt="No Image" />
                              @endif
                            @endif

                            <div>
                              {{ $item->product->name }}
                              @if ($item->comment)
                                <div class="text-xs">{{ $item->comment }}</div>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td class="p-2 text-end">
                          {{ format_decimal($item->price) }}
                          <!-- {{ $settings['show_tax'] == 1 ? format_decimal($item->price) : format_decimal($item->unit_price) }} -->
                        </td>
                        <td class="p-2 text-center">
                          {{ format_decimal_qty($item->quantity) }}{{ $item->unit?->code ?? '' }}
                        </td>
                        @if ($settings['show_discount'] == 1)
                          <td class="p-2 text-end">{{ format_decimal($item->discount_amount) }}</td>
                        @endif
                        @if ($settings['show_tax'] == 1)
                          <td class="p-2 text-end">{{ format_decimal($item->tax_amount) }}</td>
                        @endif
                        <td class="p-2 text-end font-bold">{{ format_decimal($item->total) }}</td>
                      </tr>
                    </tbody>
                  @endif
                @endforeach
                <tfoot class="divide-y divide-gray-200 dark:divide-gray-700">
                  @if ($settings['show_tax'] == 1)
                    <tr>
                      <th colspan="3" class="p-2 font-bold text-end text-lg">{{ __('Total') }}</th>
                      <th class="p-2 font-bold text-end text-lg">
                        {{ format_decimal_qty($sale->items->sum('quantity')) }}
                      </th>
                      @if ($settings['show_discount'] == 1)
                        <th class="p-2 font-bold text-end text-lg">
                          {{ currency_value($sale->total_discount_amount) }}
                        </th>
                      @endif
                      @if ($settings['show_tax'] == 1)
                        <th class="p-2 font-bold text-end text-lg">
                          {{ currency_value($sale->total_tax_amount) }}
                        </th>
                      @endif
                      <th class="p-2 font-bold text-end text-lg">{{ currency_value($sale->total) }}</th>
                    </tr>
                  @else
                    @if ($settings['show_discount'] == 1 && $sale->total_discount_amount > 0)
                      <tr>
                        <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Discount') }}</th>
                        <th class="p-2 font-bold text-end text-lg">
                          {{ currency_value($sale->total_discount_amount) }}
                        </th>
                      </tr>
                    @endif
                    @if ($sale->total_tax_amount > 0)
                      <tr>
                        <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Subtotal') }}</th>
                        <th class="p-2 font-bold text-end text-lg">
                          {{ currency_value($sale->subtotal) }}
                        </th>
                      </tr>
                      <tr>
                        <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Tax') }}</th>
                        <th class="p-2 font-bold text-end text-lg">
                          {{ currency_value($sale->total_tax_amount) }}
                        </th>
                      </tr>
                    @elseif ($settings['show_zero_taxes'] == 1)
                      <tr>
                        <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Tax') }}</th>
                        <th class="p-2 font-bold text-end text-lg">
                          {{ currency_value($sale->total_tax_amount) }}
                        </th>
                      </tr>
                    @endif
                    <tr>
                      <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Total') }}</th>
                      <th class="p-2 font-bold text-end text-lg">
                        {{ currency_value($sale->grand_total) }}
                      </th>
                    </tr>
                    <tr>
                      <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Paid') }}</th>
                      <th class="p-2 font-bold text-end text-lg">
                        {{ currency_value($sale->paid) }}
                      </th>
                    </tr>
                    <tr>
                      <th colspan="{{ $colspan }}" class="p-2 font-bold text-end text-lg">{{ __('Balance') }}</th>
                      <th class="p-2 font-bold text-end text-lg">
                        {{ currency_value($sale->grand_total - $sale->paid) }}
                      </th>
                    </tr>
                  @endif
                </tfoot>
              </table>
            </div>

            {{-- <ViewCustomFields :modal="false" :fields="custom_fields" :title="__('Custom Fields')" :extra_attributes="$sale->extra_attributes" /> --}}
            @if ($sale->details)
              <div class="pt-8 mb-4">
                {{ $sale->details }}
              </div>
            @endif

            {{-- @if ($sale->attachments)
              <div class="mt-8 py-2 w-full print:hidden">
                <Attachments :attachments="$sale->attachments" />
              </div>
            @endif --}}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
