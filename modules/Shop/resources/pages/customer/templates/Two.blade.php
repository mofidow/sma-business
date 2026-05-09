<div>
  <div class="-m-6 print:m-0">
    <div class="flex flex-col gap-6 p-6 print:p-0">
      <div class="flex items-end justify-between gap-x-8">
        <div class="w-2/3">
          <div class="max-h-16 max-w-[250px]">
            @if ($sale->store->logo)
              <img class="h-16 max-w-full" src="{{ $sale->store->logo }}" alt="{{ $sale->store->name }}" />
            @else
              @if ($settings['logo'] ?? null)
                <img alt="{{ $settings['name'] }}" src="{{ $settings['logo'] }}" class="h-16 max-w-full dark:hidden print:block!" />
              @endif
              @if ($settings['logo_dark'] ?? null)
                <img alt="{{ $settings['name'] }}" src="{{ $settings['logo_dark'] }}"
                  class="hidden h-16 max-w-full dark:block print:hidden!" />
              @endif
            @endif
          </div>
          <div>
            {{-- <div class="text-lg font-semibold">{{ $sale->store->name }}</div> --}}
            <div class="text-sm">{{ address($sale->store) }}</div>
          </div>
        </div>
        <div class="w-1/3 whitespace-nowrap">
          <h1 class="text-focus text-3xl font-bold uppercase">{{ __('Sale') }}</h1>
          <div class="mt-1 text-sm">
            <div class="text-sm">{{ __('Sale No. {x}', ['x' => $sale->id]) }}</div>
            <div class="text-sm">{{ __('Date') }}: {{ $sale->date }}</div>
            <div class="text-sm">{{ __('Created at') }}: {{ $sale->created_at }}</div>
            <div class="flex gap-1 text-sm">
              {{ __('Reference') }}:
              <p class="truncate hover:text-clip print:block print:text-clip" dir="rtl">{{ $sale->reference }}</p>
            </div>
            @if ($sale->due_date)
              <div class="text-sm">{{ __('Due Date') }}: {{ $sale->due_date }}</div>
            @endif
          </div>
        </div>
      </div>

      @if ($sale->shop && $sale->paid < $sale->grand_total - $sale->rounding && $sale->directPendingPayments?->isNotEmpty())
        <div class="col-span-full print:hidden">
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
              <p class="font-bold">{{ __('Fiscal service processing is pending for this sale.') }}</p>
            </div>
          @elseif ($sale->fiscal_service_response['success'] ?? null)
            {{-- Success --}}
            @php
              $qrcode = new FiscalServicePlugin()->active()->getQRCodeUrl($sale) ?? null;
            @endphp
          @else
            <div class="text-red-500">
              <p class="font-bold">{{ $sale->fiscal_service_response['message'] }}</p>
            </div>
          @endif
        @endif
      </div>

      <div class="flex items-center justify-center gap-6">
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

      <div class="flex justify-between text-sm">
        <div class="max-w-xs">
          <div class="mb-2 font-bold">{{ __('Sell To') }}:</div>
          <div class="text-sm flex flex-col items-start justify-start">{{ address($sale->customer) }}</div>
        </div>
        @if ($sale->address)
          <div class="max-w-xs text-end">
            <div class="mb-2 font-bold">{{ __('Ship To') }}:</div>
            <div class="text-sm flex flex-col items-end justify-end">{{ address($sale->address) }}</div>
          </div>
        @endif
      </div>

      <div>
        <div class="rounded-corners">
          <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100 dark:bg-gray-900">
              <tr>
                <th class="border border-gray-300 p-3 text-start font-semibold dark:border-gray-700">{{ __('Code') }}</th>
                <th class="border border-gray-300 p-3 text-start font-semibold dark:border-gray-700">{{ __('Name') }}</th>
                <th class="border border-gray-300 p-3 text-end font-semibold dark:border-gray-700">{{ __('Price') }}</th>
                <th class="border border-gray-300 p-3 text-center font-semibold dark:border-gray-700">{{ __('Qty') }}</th>
                @if ($settings['show_discount'] == 1)
                  <th class="w-[80px] border border-gray-300 p-3 text-center font-semibold dark:border-gray-700">
                    {{ __('Discount') }}
                  </th>
                @endif
                @if ($settings['show_tax'] == 1)
                  <th class="w-[80px] border border-gray-300 p-3 text-center font-semibold dark:border-gray-700">{{ __('Tax') }}</th>
                @endif
                <th class="border border-gray-300 p-3 text-end font-semibold dark:border-gray-700">{{ __('Total') }}</th>
              </tr>
            </thead>

            @foreach ($sale->items as $item)
              @if ($item->variations->isNotEmpty())
                <tbody>
                  <tr>
                    <td class="w-7 border border-gray-300 p-2 text-end dark:border-gray-700">{{ $loop->iteration }}</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                      <div class="flex items-center gap-2">
                        @if ($settings['show_image'] == 1)
                          @if ($item->product->photo)
                            <img class="h-8 w-8 self-start rounded-xs" src="{{ $item->product->photo }}" alt="Product Image" />
                          @else
                            <img class="me-2 h-8 w-8 self-start rounded-xs" src="img/no-image.png" alt="No Image" />
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
                    <td class="border border-gray-300 p-2 dark:border-gray-700"></td>
                    <td class="border border-gray-300 p-2 text-end dark:border-gray-700"></td>
                    @if ($settings['show_discount'] == 1)
                      <td class="p-2 text-end"></td>
                    @endif
                    @if ($settings['show_tax'] == 1)
                      <td class="p-2"></td>
                    @endif
                    <td class="border border-gray-300 p-2 dark:border-gray-700"></td>
                  </tr>

                  @foreach ($item->variations as $variation)
                    <tr>
                      <td class="w-7 border border-gray-300 dark:border-gray-700"></td>
                      <td class="border border-gray-300 p-2 dark:border-gray-700">{{ meta_array_to_string($variation->meta) }}</td>
                      <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ format_decimal($variation->pivot->price) }}
                      </td>
                      <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                        <div class="flex items-center justify-center">
                          {{ format_decimal_qty($variation->pivot->quantity) }}{{ $variation->pivot->unit->code ?? '' }}
                        </div>
                      </td>
                      @if ($settings['show_discount'] == 1)
                        <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                          {{ format_decimal($variation->pivot->discount_amount) }}
                        </td>
                      @endif
                      @if ($settings['show_tax'] == 1)
                        <td class="p-2 text-end">{{ format_decimal($variation->pivot->tax_amount) }}</td>
                      @endif
                      <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">
                        {{ format_decimal($variation->pivot->total) }}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              @else
                <tbody
                  class="divide-y divide-gray-200 border-y border-gray-200 dark:divide-gray-700 dark:border-gray-700 print:divide-gray-400 dark:print:divide-gray-400">
                  <tr>
                    <td class="w-7 border border-gray-300 p-2 text-end dark:border-gray-700">
                      <!-- {{ $loop->iteration }}  -->
                      {{ $item->product->code }}
                    </td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                      <div class="flex items-center gap-2">
                        @if ($settings['show_image'] == 1)
                          @if ($item->product->photo)
                            <img class="h-8 w-8 self-start rounded-xs" src="$item->product->photo" alt="Product Image" />
                          @else
                            <img class="me-2 h-8 w-8 self-start rounded-xs" src="img/no-image.png" alt="No Image" />
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
                    <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                      {{ format_decimal($item->price) }}
                    </td>
                    <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                      <div class="flex items-center justify-center">
                        {{ format_decimal_qty($item->quantity) }}{{ $item->unit?->code ?? '' }}</div>
                    </td>
                    @if ($settings['show_discount'] == 1)
                      <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">
                        {{ format_decimal($item->discount_amount) }}
                      </td>
                    @endif
                    @if ($settings['show_tax'] == 1)
                      <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">
                        {{ format_decimal($item->tax_amount) }}
                      </td>
                    @endif
                    <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">{{ format_decimal($item->total) }}
                    </td>
                  </tr>
                </tbody>
              @endif
            @endforeach
          </table>
        </div>

        <div class="mt-6 flex break-inside-avoid justify-between gap-x-6 text-sm">
          <div class="max-w-sm">
            <!-- <div class="font-semibold mb-1">Payment info:</div>
          <p>Credit Card - 236************928<br />Amount: $1732</p> -->
          </div>
          <div class="max-w-xs text-end">
            <div class="flex gap-x-6">
              <div class="grow font-semibold">{{ __('Subtotal') }}</div>
              <div class="w-24 font-bold">{{ currency_value($sale->subtotal) }}</div>
            </div>
            <div class="mt-1 flex gap-x-6">
              <div class="grow font-semibold">{{ __('Tax') }}</div>
              <div class="w-24">{{ currency_value($sale->total_tax_amount) }}</div>
            </div>
            @if ($sale->total_discount_amount > 0)
              <div class="mt-1 flex gap-x-6">
                <div class="grow font-semibold">{{ __('Discount') }}</div>
                <div class="w-24">{{ currency_value($sale->total_discount_amount) }}</div>
              </div>
            @endif
            <div class="mt-3 flex gap-x-6 border-t border-gray-400 pt-3 text-lg font-bold dark:border-gray-600">
              <div class="grow">{{ __('Grand Total') }}</div>
              <div class="w-24">{{ currency_value($sale->grand_total) }}</div>
            </div>
            <div class="mt-1 flex gap-x-6">
              <div class="grow font-semibold">{{ __('Paid') }}</div>
              <div class="w-24 font-bold">{{ currency_value($sale->paid) }}</div>
            </div>
            <div class="mt-1 flex gap-x-6">
              <div class="grow font-semibold">{{ __('Balance') }}</div>
              <div class="w-24 font-bold">{{ currency_value($sale->grand_total - $sale->paid) }}</div>
            </div>
          </div>
        </div>
      </div>

      {{-- <ViewCustomFields :modal="false" :fields="custom_fields" :title="__('Custom Fields')"
        :extra_attributes="$sale->extra_attributes" /> --}}

      @if ($sale->details)
        <div class="mt-6 rounded-md border border-gray-300 p-4 text-sm dark:border-gray-700">
          <!-- <div class="font-semibold mb-2">Terms & Conditions:</div> -->
          {{ $sale->details }}
        </div>
      @endif

      {{-- @if ($sale->attachments)
        <div class="mt-8 w-full py-2 print:hidden">
          <Attachments :attachments="$sale->attachments" />
        </div>
      @endif --}}
    </div>
  </div>
</div>
