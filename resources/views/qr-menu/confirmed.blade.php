@extends('qr-menu.layout')

@section('title', __('Order Confirmed'))

@section('content')
  <div class="mx-auto flex max-w-lg min-h-full flex-col items-center px-6 py-12 text-center">
    @if ($order)
      {{-- Success Icon --}}
      <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
        <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('Order Placed!') }}</h1>
      <p class="mb-2 text-gray-500">
        {{ __('Your order has been submitted.') }}
      </p>
      <p class="mb-6 text-gray-500">
        {{ __('To add more items, please call attendant to your table.') }}
      </p>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-6 py-4">
      <div class="text-sm text-gray-500 dark:text-gray-400">{{ $table->hall->name }}</div>
      <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $table->name }}</div>
    </div>

    @if ($order)
      {{-- Order Details --}}
      <div class="mb-6 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-left">
        <div class="border-b border-gray-100 dark:border-gray-700 px-5 py-3">
          <div class="flex items-center justify-between">
            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">#{{ $order->number }}</span>
            <span
              class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
              {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($order->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
              {{ ucfirst($order->status) }}
            </span>
          </div>
          @if ($order->customer_name)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $order->customer_name }}</p>
          @endif
        </div>

        <div class="divide-y divide-gray-50 dark:divide-gray-700 px-5">
          @foreach ($order->data['items'] ?? [] as $item)
            <div class="flex items-start justify-between py-2.5 gap-2">
              <div class="space-y-2 grow">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</p>
                @if (!empty($item['variations']))
                  @foreach ($item['variations'] as $variation)
                    @if (!empty($variation['meta']))
                      <div>
                        <p class="text-xs text-primary-600">
                          {{ collect($variation['meta'])->map(fn($v, $k) => "$k: $v")->implode(', ') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          ({{ number_format($variation['net_price'] ?? ($variation['price'] ?? 0), 2) }} +
                          {{ number_format($variation['tax_amount'] ?? 0, 2) }})
                          &times; {{ $variation['quantity'] }}
                        </p>
                      </div>
                    @endif
                  @endforeach
                @else
                  <p class="text-xs -mt-2 text-gray-500">
                    ({{ number_format($item['net_price'] ?? ($item['price'] ?? 0), 2) }} + {{ number_format($item['tax_amount'] ?? 0, 2) }})
                    &times; {{ $item['quantity'] }}
                  </p>
                @endif
              </div>
              <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                {{ $menuSettings['currency'] ?? '$' }} {{ number_format($item['total'] ?? 0, 2) }}
              </span>
            </div>
          @endforeach
        </div>

        @if ($order->notes)
          <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Notes') }}: {{ $order->notes }}</p>
          </div>
        @endif

        <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-3">
          <div class="flex items-center justify-between">
            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ __('Total') }}</span>
            <span class="text-lg font-bold text-primary-600">
              {{ $menuSettings['currency'] ?? '$' }} {{ number_format($order->total, 2) }}
            </span>
          </div>
        </div>
      </div>

      {{-- <a href="{{ route('qr-menu.show', [$table->qr_token, 'order' => $order->number]) }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-8 py-3 font-semibold text-white shadow-lg transition hover:bg-primary-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        {{ __('Add More Items') }}
      </a> --}}

      <script>
        localStorage.removeItem('qr_cart_{{ $table->qr_token }}');
        @if ($order->status === 'pending')
          setTimeout(() => window.location.reload(), 30000);
        @endif
      </script>
    @else
      <a href="{{ route('qr-menu.show', $table->qr_token) }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-8 py-3 font-semibold text-white shadow-lg transition hover:bg-primary-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        {{ __('Place an Order') }}
      </a>
    @endif

    @if ($menuSettings['name'])
      <p class="mt-auto pt-8 text-xs text-gray-400">{{ $menuSettings['name'] }}</p>
    @endif
  </div>
@endsection
