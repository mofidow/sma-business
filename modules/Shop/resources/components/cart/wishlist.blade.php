<x-slot name="title">{{ __('Wishlist') }}</x-slot>
<div class="container mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24" x-data="{ open: false }">
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ __('Wishlist') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6 flex items-center gap-4">
        @if ($products->count())
          <div x-data="{ open: false }">
            <button type="button" @click="open = true" class="x-focus btn-danger">
              {{ __('Empty Wishlist') }}
            </button>

            <div x-show="open">
              <x-shop::alpine.modal :backdrop="false">
                <div class="select-none pointer-events-none">
                  <h2 class="font-medium text-prominent">{{ __('Empty :Model!', ['model' => __('Wishlist')]) }}</h2>
                  <p class="mt-2 text-mute">{{ __('Are you sure you want to delete all the records?') }}</p>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                  <button type="button"@click="open = false" class="x-focus btn-primary">
                    {{ __('No, Cancel') }}
                  </button>
                  <button type="button" @click="() => { $wire.removeAll(); open = false; }" class="x-focus btn-danger">
                    {{ __('Yes, Delete') }}
                  </button>
                </div>
              </x-shop::alpine.modal>
            </div>
          </div>
        @endif
        <x-shop::shared.link :to="route('shop.home')">
          {{ __('Go to Home') }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="content">
      @unless ($products->count())
        <div class="">
          <div class="dark:-mx-2 dark:md:mx-0">
            <div class="p-8 text-center">
              <x-shop::shared.icon name="heart" class="w-32 h-32 mx-auto" />
              <h3 class="text-lg font-extrabold my-4">{{ __('No data to display!') }}</h3>
            </div>
          </div>
        </div>
      @else
        <div class="-m-6">
          <div class="flex flex-col divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($products as $wishlist)
              <div wire:key="wli-{{ $wishlist->id }}" class="flex items-center w-full select-none p-4 gap-4">
                <a href="{{ route('shop.product', $wishlist->product->slug) }}" class="grow flex items-center rounded focus-default">
                  @if ($wishlist->product->photo)
                    <img src="{{ $wishlist->product->photo }}" alt="{{ $wishlist->product->name }}"
                      class="w-16 h-16 rounded-md me-4 object-cover" />
                  @endif
                  <div class="grow">
                    <span class="block mb-1">{{ $wishlist->product->name }}</span>
                    <span class="text-gray-500 dark:text-gray-400 line-clamp-2">{{ $wishlist->product->description }}</span>
                  </div>
                </a>
                <div class="flex items-stretch">
                  {{-- @if ($confirming == $wishlist->product->id)
                    <button type="button" wire:click="removeItem('{{ $wishlist->product->id }}')"
                      class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-s-md text-white bg-red-600 hover:bg-red-700 focus-default">
                      {{ __('Sure?') }}
                    </button>
                    <button type="button" wire:click="confirmDelete()"
                      class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-e-md text-white bg-blue-600 hover:bg-blue-700 focus-default">
                      <x-shop::shared.icon name="x" class="w-4 h-4" />
                    </button>
                  @else
                    <button type="button" wire:click="confirmDelete('{{ $wishlist->product->id }}')"
                      class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus-default">
                      {{ __('Delete') }}
                    </button>
                  @endif --}}
                  <button type="button" @click="open = '{{ $wishlist->id }}'"
                    class="x-focus flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                    <x-shop::shared.icon name="trash" class="size-5" />
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endunless


    </x-slot>
    <x-slot name="pagination">
      {{ $products->links() }}
    </x-slot>

  </x-shop::jet.action-section>

  <div x-show="open">
    <x-shop::alpine.modal :backdrop="false">
      <div class="select-none pointer-events-none">
        <h2 class="font-medium text-prominent">{{ __('Delete :Model!', ['model' => __('Wishlist')]) }}</h2>
        <p class="mt-2 text-mute">{{ __('Are you sure you want to delete this record?') }}</p>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button"@click="open = false" class="x-focus btn-primary">
          {{ __('No, Cancel') }}
        </button>
        <button type="button" @click="() => { $wire.removeProduct(open); open = false; }" class="x-focus btn-danger">
          {{ __('Yes, Delete') }}
        </button>
      </div>
    </x-shop::alpine.modal>
  </div>
</div>
