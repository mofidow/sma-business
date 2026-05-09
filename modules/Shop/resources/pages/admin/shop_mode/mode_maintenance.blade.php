<x-slot name="title">{{ __('Maintenance Mode') }} - {{ shop_config('shop_name') }}</x-slot>

<div class="relative h-screen bg-gray-50 dark:bg-gray-800 overflow-hidden">
  <div class="hidden sm:block sm:absolute sm:inset-y-0 sm:h-full sm:w-full" aria-hidden="true">
    <div class="relative h-full max-w-7xl mx-auto text-gray-200 dark:text-gray-700">
      <svg class="fill-current absolute end-full transform translate-y-1/4 translate-x-1/4 lg:translate-x-1/2" width="404" height="784"
        fill="none" viewBox="0 0 404 784">
        <defs>
          <pattern id="f210dbf6-a58d-4871-961e-36d5016a0f49" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <rect x="0" y="0" width="4" height="4" fill="currentColor" />
          </pattern>
        </defs>
        <rect width="404" height="784" fill="url(#f210dbf6-a58d-4871-961e-36d5016a0f49)" />
      </svg>
      <svg class="fill-current absolute start-full transform -translate-y-3/4 -translate-x-1/4 md:-translate-y-1/2 lg:-translate-x-1/2"
        width="404" height="784" fill="none" viewBox="0 0 404 784">
        <defs>
          <pattern id="5d0dd344-b041-4d26-bec4-8d33ea57ec9b" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <rect x="0" y="0" width="4" height="4" fill="currentColor" />
          </pattern>
        </defs>
        <rect width="404" height="784" fill="url(#5d0dd344-b041-4d26-bec4-8d33ea57ec9b)" />
      </svg>
    </div>
  </div>

  <div class="relative h-screen flex items-center justify-center py-6">
    <main class="mx-auto max-w-7xl px-4">
      <div class="text-center">
        <h1 class="flex items-center justify-center mb-12 text-2xl text-blue-600 font-extrabold me-5">
          <span class="hidden dark:block rounded h-20 focus-default">
            <img src="{{ storage_url($shop_settings->shop_logo_dark) }}" alt="{{ __($shop_settings->shop_name) }}"
              class="max-h-20 h-full object-cover" />
          </span>
          <span class="block dark:hidden rounded h-20 focus-default">
            <img src="{{ storage_url($shop_settings->shop_logo_light) }}" alt="{{ __($shop_settings->shop_name) }}"
              class="max-h-20 h-full object-cover" />
          </span>
        </h1>

        <h1 class="text-3xl tracking-tight font-extrabold text-gray-900 dark:text-gray-100 sm:text-4xl md:text-5xl">
          <span class="block xl:inline">{{ shop_config('shop_name') }}</span>
          <span class="block text-blue-600 xl:inline">({{ __('Maintenance Mode') }})</span>
        </h1>
        <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
          {{ __('Site is under maintenance, please visit us after few days.') }}
        </p>
      </div>
    </main>
  </div>
</div>
