<x-slot name="title">{{ __('Addresses') }}</x-slot>
<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24" x-data="{ open: false, edit: false, deleteId: false }">
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ __('Addresses') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6 flex flex-wrap gap-6">
        <div>
          <x-shop::jet.button @click="open = true">
            {{ __('Add New Address') }}
          </x-shop::jet.button>
        </div>
        <div>
          <x-shop::shared.link to="{{ route('shop.billing') }}">
            {{ __('Update Billing Address') }}
          </x-shop::shared.link>
        </div>
        <div x-show="open">
          <x-shop::alpine.modal maxWidth="sm:max-w-2xl">
            <livewire:components.customer.address-form :edit="null" />
          </x-shop::alpine.modal>
        </div>
      </div>
    </x-slot>

    <x-slot name="content">
      @unless ($addresses->count())
        <div class="rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
          <div class="bg-white dark:bg-gray-900 dark:-mx-2 dark:md:mx-0">
            <div class="p-8 text-center">
              <x-shop::shared.icon name="location" class="w-32 h-32 mx-auto" />
              <h3 class="text-lg font-extrabold my-4">{{ __('No data to display!') }}</h3>
              <x-shop::jet.button>
                {{ __('Add New Address') }}
              </x-shop::jet.button>
            </div>
          </div>
        </div>
      @else
        <div class="-m-6">
          <div class="w-full flex flex-col divide-y divide-gray-200 dark:divide-gray-700 sm:rounded-lg overflow-hidden">
            @foreach ($addresses as $address)
              <div wire:key="address-{{ $address->id }}"
                class="leading-relaxed hover:bg-gray-100 dark:hover:bg-gray-800 flex items-start justify-between px-6 py-4">
                <div class="block text-sm font-medium text-gray-700 dark:text-gray-300 sm:ps-2">
                  <div class="font-bold">{{ $address->name }}</div>
                  <div>{{ $address->lot_no }} {{ $address->street }}</div>
                  <div>{{ $address->address_line_1 }}</div>
                  <div>{{ $address->address_line_2 }}</div>
                  <div>
                    {{ $address->city }}
                    {{ $address->postal_code }}
                    {{ $address->state?->name }} {{ $address->country->name }}
                  </div>
                  <div> {{ __('Tel:') }} {{ $address->phone }} @if ($address->email)
                      {{ __('Email:') }}
                      {{ $address->email }}
                    @endif
                  </div>
                </div>
                <div class="flex items-stretch justify-end transition-all duration-500">
                  <button type="button" @click="edit = '{{ $address->id }}'"
                    class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-s-md text-white bg-blue-600 hover:bg-blue-700  focus:z-10">
                    {{ __('Edit') }}
                  </button>
                  <div x-show="edit == '{{ $address->id }}'">
                    <x-shop::alpine.modal maxWidth="sm:max-w-2xl" property="edit" backdrop="false" edit="edit">
                      <livewire:components.customer.address-form :edit="$address->id" property="edit" />
                    </x-shop::alpine.modal>
                  </div>
                  <button type="button" @click="deleteId = '{{ $address->id }}'"
                    class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-e-md text-white bg-yellow-600 hover:bg-yellow-700 focus:z-10">
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
      {{ $addresses->onEachSide(5)->links() }}
    </x-slot>
  </x-shop::jet.action-section>

  <div x-show="deleteId">
    <x-shop::alpine.modal :backdrop="false" property="deleteId">
      <h2 class="font-medium text-prominent">{{ __('Delete :Model!', ['model' => __('Address')]) }}</h2>
      <p class="mt-2 text-mute max-w-xs">{{ __('Are you sure you want to delete this record?') }}</p>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button"@click="deleteId = false" class="x-focus btn-primary">
          {{ __('No, Cancel') }}
        </button>
        <button type="button" @click="() => { $wire.removeCoupon(deleteId); deleteId = false; }" class="x-focus btn-danger">
          {{ __('Yes, Delete') }}
        </button>
      </div>
    </x-shop::alpine.modal>
  </div>
</div>
