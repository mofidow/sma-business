<x-slot name="title">{{ __($page->title) }}</x-slot>
<x-slot name="metaDesc">{{ __($page->description) }}</x-slot>
<x-slot name="ogMetaData">
  <meta name="og:site_name" content="{{ __($page->title) }}" />
  <meta name="og:title" content="{{ __($page->description) }}" />
  <meta name="og:url" content="{{ route('shop.page', ['page' => $page->slug]) }}" />
  <meta name="og:image" content="{{ asset('img/social/' . $page->slug . '.jpg') }}" />
  <meta name="twitter:card" content="summary" />
</x-slot>

<div class="container mx-auto sm:px-6 lg:px-8 my-8">
  <x-shop::jet.section-title>
    <x-slot name="title">
      {{ __($page->title) }}
    </x-slot>

    <x-slot name="description">
      {{ __($page->description) }}
    </x-slot>
  </x-shop::jet.section-title>
  {{-- <div id="page-title" class="pb-6">
    <h2 class="text-3xl font-extrabold mb-2">{{ __($page->title) }}</h2>
    <h4 class="text-lg font-light">{{ __($page->description) }}</h4>
  </div> --}}
  <div class="mt-6 flex items-start w-full gap-6 relative">
    <div class="hidden lg:block w-64 xl:w-72">
      {{-- <div class="w-64 xl:w-72"></div> --}}
      <div class="w-64 xl:w-72 rounded-lg shadow-sm bg-white dark:bg-gray-900">
        <ul class="w-full p-2 space-y-2">
          @foreach ($pages as $p)
            <li class="cursor-default select-none relative">
              @if ($p->id == $page->id)
                <span class="block w-full text-start font-normal bg-gray-100 dark:bg-black rounded-md py-2 px-4">
                  {{ __($p->title) }}
                </span>
              @else
                {{-- <button type="button" wire:click="changePage('{{ $p->id }}')"
                  class="block w-full text-start hover:bg-gray-100 dark:hover:bg-black font-normal focus-default sm:rounded-lg py-2 px-4">
                  {{ __($p->title) }}
                </button> --}}
                <a href="{{ route('shop.page', $p->slug) }}" wire:navigate w.hover
                  class="block w-full text-start hover:bg-gray-100 dark:hover:bg-black font-normal focus-default sm:rounded-lg py-2 px-4">
                  {{ __($p->title) }}
                </a>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </div>
    <div wire:loading class="flex items-center justify-center py-6 w-full">
      <x-shop::shared.loading-circle />
    </div>
    <div wire:loading.remove
      class="w-full sm:max-w-full p-6 bg-white dark:bg-gray-950 shadow-sm overflow-x-scroll lg:overflow-hidden sm:rounded-lg isolate html">
      {{ str($contents)->markdown()->toHtmlString() }}
    </div>
  </div>
</div>

@once
  @push('scripts')
    <script>
      scroll(0, 0);
      document.addEventListener('DOMContentLoaded', function() {
        @this.on('page-changed', function(data) {
          window.history.pushState(data.state, data.title, data.url);
          document.title = data.title + ' - {{ config('app.name') }}';
          document.querySelector('meta[name="description"]').setAttribute("content", data
            .description);
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        });
      });
      window.addEventListener('popstate', function(e) {
        if (e.state) {
          document.location.reload(true);
        }
      });
    </script>
  @endpush
@endonce
