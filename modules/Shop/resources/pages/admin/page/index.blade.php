<x-slot name="title">{{ __('Pages') }}</x-slot>
<div class="container mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24" x-data="{ open: false }">
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ __('Pages') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6">
        <x-shop::shared.link :to="route('shop.pages.create')">
          {{ __('Create {x}', ['x' => __('Page')]) }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="content">
      @unless ($pages->count())
        <div class="">
          <div class="dark:-mx-2 dark:md:mx-0">
            <div class="p-8 text-center">
              <x-shop::shared.icon name="text-file" class="w-32 h-32 mx-auto" />
              <h3 class="text-lg font-extrabold my-4">{{ __('No data to display!') }}</h3>
              <x-shop::shared.link :to="route('shop.pages.create')">
                {{ __('Create {x}', ['x' => __('Page')]) }}
              </x-shop::shared.link>
            </div>
          </div>
        </div>
      @else
        <div class="-m-6">
          <div class="flex flex-col">
            <div class="sm:rounded-lg sm:overflow-hidden ">
              <div class="align-middle inline-block min-w-full">
                <div class="">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Title') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Slug') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {{ __('Active') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                          <span class="sr-only">{{ __('Actions') }}</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                      @foreach ($pages as $page)
                        <tr>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('shop.page', ['page' => $page]) }}" class="link x-focus">
                              {{ __($page->title) }}
                            </a>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            {{ $page->slug }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            @if ($page->active)
                              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ __('Yes') }}
                              </span>
                            @else
                              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ __('No') }}
                              </span>
                            @endif
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                            <div class="flex items-stretch justify-end">
                              <a wire:navigate w.hover href="{{ route('shop.pages.edit', ['page' => $page->id]) }}"
                                class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-s-md text-white bg-blue-600 hover:bg-blue-700 focus-default">
                                {{-- {{ __('Edit') }} --}}
                                <x-shop::shared.icon name="edit" class="size-5" />
                              </a>
                              {{-- @if ($confirming == $page->id) --}}
                              {{-- <button type="button" wire:click="removePage('{{ $page->id }}')"
                                  class="x-focus flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                  {{ __('Sure?') }}
                                </button>
                                <button type="button" wire:click="confirmDelete()"
                                  class="x-focus flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-e-md text-white bg-blue-600 hover:bg-blue-700">
                                  <x-shop::shared.icon name="x" class="w-4 h-4" />
                                </button> --}}
                              {{-- @else --}}
                              {{-- <button type="button" wire:click="confirmDelete('{{ $page->id }}')"
                                class="x-focus flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-e-md text-white bg-yellow-600 hover:bg-yellow-700">
                                {{ __('Delete') }}
                              </button> --}}
                              {{-- @endif --}}
                              <button type="button" @click="open = '{{ $page->id }}'"
                                class="x-focus flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-e-md text-white bg-yellow-600 hover:bg-yellow-700">
                                <x-shop::shared.icon name="trash" class="size-5" />
                              </button>
                            </div>
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
    <x-slot name="pagination">
      {{ $pages->onEachSide(5)->links() }}
    </x-slot>
  </x-shop::jet.action-section>

  <div x-show="open">
    <x-shop::alpine.modal :backdrop="false">
      <h2 class="font-medium text-prominent">{{ __('Delete :Model!', ['model' => __('Page')]) }}</h2>
      <p class="mt-2 text-mute max-w-xs">{{ __('Are you sure you want to delete this record?') }}</p>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button"@click="open = false" class="x-focus btn-primary">
          {{ __('No, Cancel') }}
        </button>
        <button type="button" @click="() => { $wire.removePage(open); open = false; }" class="x-focus btn-danger">
          {{ __('Yes, Delete') }}
        </button>
      </div>
    </x-shop::alpine.modal>
  </div>
</div>
