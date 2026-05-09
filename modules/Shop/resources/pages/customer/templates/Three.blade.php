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
  <div class="">
    <div class="mb-6 flex justify-between">
      <div>
        <h1 class="text-focus text-2xl font-black">
          <div class="max-h-16 min-h-10 max-w-[250px]">
            @if ($sale->store->logo)
              <img class="max-h-16 min-h-10 max-w-full" src="{{ $sale->store->logo }}" alt="{{ $sale->store->name }}" />
            @else
              @if ($settings['logo'] ?? null)
                <img alt="{{ $settings['name'] }}" src="{{ $settings['logo'] }}"
                  class="max-h-16 min-h-10 max-w-full dark:hidden print:block!" />
              @endif
              @if ($settings['logo_dark'] ?? null)
                <img alt="{{ $settings['name'] }}" src="{{ $settings['logo_dark'] }}"
                  class="hidden max-h-16 min-h-10 max-w-full dark:block print:hidden!" />
              @endif
            @endif
          </div>
        </h1>
        <div>{{ address($sale->store) }}</div>
      </div>
    </div>

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

    <div
      class="text-focus grid grid-cols-4 gap-4 border-t border-b border-gray-300 py-4 text-center text-sm font-semibold uppercase dark:border-gray-700">
      <div class="text-start">
        <span class="font-bold">{{ __('Sale No. {x}', ['x' => '']) }}</span><br />
        <span class="text-lg font-black">{{ $sale->id }}</span>
      </div>
      <div>
        <span class="font-bold">{{ __('Date') }}</span><br />
        <span class="text-lg font-black">{{ $sale->date }}</span><br />
      </div>
      <div>
        <span class="font-bold">{{ __('Payment Status') }}</span><br />
        @if ($sale->paid >= $sale->grand_total)
          <span class="mt-1 inline-block rounded-full bg-green-100 px-3 py-px text-sm font-semibold text-green-800">
            {{ __('Paid') }}
          </span>
        @elseif ($sale->due_date && now()->parse($sale->due_date) < now() && $sale->paid < $sale->grand_total)
          <span class="mt-1 inline-block rounded-full bg-red-100 px-3 py-px text-sm font-semibold text-red-800">
            {{ __('Overdue') }}
          </span>
        @elseif ($sale->due_date && now()->parse($sale->due_date) > now())
          <span class="mt-1 inline-block rounded-full bg-primary-100 px-3 py-px text-sm font-semibold text-primary-800">
            {{ __('Pending') }}
          </span>
        @else
          <span class="mt-1 inline-block rounded-full bg-yellow-100 px-3 py-px text-sm font-semibold text-yellow-800">
            {{ __('Due') }}
          </span>
        @endif
      </div>
      <div class="text-end">
        <span class="font-bold">{{ __('Total Amount') }}</span><br />
        <span class="text-lg font-black">{{ currency_value($sale->grand_total) }}</span>
      </div>
    </div>

    <div class="print:hidden">
      {{-- {{ json_encode($sale->fiscal_service_response) }} --}}
      @if ($settings['fiscal_service_driver'] ?? null)
        @if (!$sale->fiscal_service_response)
          <div class="mt-6 text-yellow-500">
            <p class="mb-2 font-bold">{{ __('Fiscal service processing is pending for this sale.') }}</p>
          </div>
        @elseif ($sale->fiscal_service_response['success'] ?? null)
          {{-- Success --}}
          @php
            $qrcode = new FiscalServicePlugin()->active()->getQRCodeUrl($sale) ?? null;
          @endphp
        @else
          <div class="mt-6 text-red-500">
            <p class="mb-2 font-bold">{{ $sale->fiscal_service_response['message'] }}</p>
          </div>
        @endif
      @endif
    </div>

    <div class="flex items-center justify-between gap-6 border-b border-gray-300 py-2 dark:border-gray-700">
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
        <svg class="barcode" jsbarcode-width="1" jsbarcode-margin="5" jsbarcode-height="70" jsbarcode-fontsize="12" jsbarcode-textmargin="3"
          jsbarcode-format="CODE128" jsbarcode-fontoptions="bold" jsbarcode-displayvalue="false"
          jsbarcode-value="{{ $sale->reference }}" />
      </div>
      <div class="qr-image qrcode h-[80px] overflow-hidden rounded"></div>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-8 text-sm">
      <div>
        <p class="mb-2 font-bold uppercase">{{ __('Billing Address') }}</p>
        <div class="text-sm">{{ address($sale->customer) }}</div>
      </div>
      <div v-if="$sale->address">
        <p class="mb-2 font-bold uppercase">{{ __('Shipping Address') }}</p>
        <div class="text-sm">{{ address($sale->address) }}</div>
      </div>
    </div>

    <div class="rounded-corners mt-6">
      <table class="w-full">
        <thead class="bg-gray-100 text-sm dark:bg-gray-900">
          <tr>
            <th class="w-7 border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">#</th>
            <th class="border border-gray-300 p-2 text-start font-bold uppercase dark:border-gray-700">{{ __('Description') }}</th>
            <th class="w-[120px] border border-gray-300 p-2 text-center font-bold whitespace-nowrap uppercase dark:border-gray-700">
              {{ $settings['show_tax'] == 1 ? __('Price') : __('Unit Price') }}
            </th>
            <th class="w-[80px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">{{ __('Qty') }}</th>
            @if ($settings['show_discount'] == 1)
              <th class="w-[80px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">
                {{ __('Discount') }}
              </th>
            @endif
            @if ($settings['show_tax'] == 1)
              <th class="w-[80px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">
                {{ __('Tax') }}
              </th>
            @endif
            <th class="w-[120px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">{{ __('Total') }}</th>
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
                <td class="border border-gray-300 p-2 dark:border-gray-700"></td>
                <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                  <!-- {{ format_decimal_qty($item->quantity) }} -->
                </td>
                @if ($settings['show_discount'] == 1)
                  <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                  </td>
                @endif
                @if ($settings['show_tax'] == 1)
                  <td class="border border-gray-300 p-2 dark:border-gray-700">
                  </td>
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
                      {{ format_decimal_qty($variation->pivot->quantity) }}{{ $variation->pivot?->unit?->code ?: '' }}
                    </div>
                  </td>
                  @if ($settings['show_discount'] == 1)
                    <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                      {{ format_decimal($variation->pivot->discount_amount) }}
                    </td>
                  @endif
                  @if ($settings['show_tax'] == 1)
                    <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                      {{ format_decimal($variation->pivot->tax_amount) }}
                    </td>
                  @endif
                  <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">
                    {{ format_decimal($variation->pivot->total) }}</td>
                </tr>
              @endforeach
            </tbody>
          @else
            <tbody
              class="divide-y divide-gray-200 border-y border-gray-200 dark:divide-gray-700 dark:border-gray-700 print:divide-gray-400 dark:print:divide-gray-400">
              <tr>
                <td class="w-7 border border-gray-300 p-2 text-end dark:border-gray-700">{{ $loop->iteration }}</td>
                <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                  <div class="flex items-center gap-2">
                    @if ($settings['show_image'] == 1)
                      @if ($item->product->photo)
                        <img class="h-8 w-8 rounded-xs" src="{{ $item->product->photo }}" alt="Product Image" />
                      @else
                        <img class="me-2 h-8 w-8 rounded-xs" src="img/no-image.png" alt="No Image" />
                      @endif
                    @endif
                    <div>
                      {{ $item->product->name }}
                      <div class="text-mute text-sm">
                        {{ $item->product->code }}
                      </div>
                      @if ($item->comment)
                        <div class="text-mute text-xs">{{ $item->comment }}</div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                  {{ format_decimal($item->price) }}
                </td>
                <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                  <div class="flex items-center justify-center">{{ format_decimal_qty($item->quantity) }}{{ $item->unit?->code ?: '' }}
                  </div>
                </td>
                @if ($settings['show_discount'] == 1)
                  <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                    {{ format_decimal($item->discount_amount) }}
                  </td>
                @endif
                @if ($settings['show_tax'] == 1)
                  <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                    {{ format_decimal($item->tax_amount) }}
                  </td>
                @endif
                <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">{{ format_decimal($item->total) }}</td>
              </tr>
            </tbody>
          @endif
        @endforeach

        <tfoot class="break-inside-avoid">
          @if ($settings['show_tax'] == 1)
            <tr>
              <th colspan="3" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">{{ __('Total') }}
              </th>
              <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ format_decimal_qty($sale->items->sum('quantity')) }}
              </th>
              @if ($settings['show_discount'] == 1)
                <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ currency_value($sale->total_discount_amount) }}
                </th>
              @endif
              @if ($settings['show_tax'] == 1)
                <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ currency_value($sale->total_tax_amount) }}
                </th>
              @endif
              <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">{{ currency_value($sale->total) }}
              </th>
            </tr>
          @else
            @if ($settings['show_discount'] == 1 && $sale->total_discount_amount > 0)
              <tr>
                <th colspan="{{ $colspan }}" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ __('Discount') }}</th>
                <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ currency_value($sale->total_discount_amount) }}
                </th>
              </tr>
            @endif
            @if ($sale->total_tax_amount > 0)
              <tr>
                <th colspan="{{ $colspan }}" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ __('Subtotal') }}</th>
                <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ currency_value($sale->subtotal) }}
                </th>
              </tr>
              <tr>
                <th colspan="{{ $colspan }}" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ __('Tax') }}</th>
                <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ currency_value($sale->total_tax_amount) }}
                </th>
              </tr>
            @elseif ($settings['show_zero_taxes'] == 1)
              <tr>
                <th colspan="{{ $colspan }}" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ __('Tax') }}</th>
                <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                  {{ currency_value($sale->total_tax_amount) }}
                </th>
              </tr>
            @endif
            <tr>
              <th colspan="{{ $colspan }}" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ __('Total') }}</th>
              <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ currency_value($sale->grand_total) }}
              </th>
            </tr>
            <tr>
              <th colspan="{{ $colspan }}" class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ __('Paid') }}</th>
              <th class="border border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ currency_value($sale->paid) }}
              </th>
            </tr>
            <tr>
              <th colspan="{{ $colspan }}" class="border-x border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ __('Balance') }}</th>
              <th class="border-x border-gray-300 dark:border-gray-700 p-2 font-bold text-end text-lg">
                {{ currency_value($sale->grand_total - $sale->paid) }}
              </th>
            </tr>
          @endif
        </tfoot>
      </table>
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
