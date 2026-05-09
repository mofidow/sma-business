<div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 lg:px-8">
  <div class="border-b border-gray-200 dark:border-gray-700 pb-10">
    <h1 class="text-4xl font-bold tracking-tight ">
      {{ __('Categories') }}
    </h1>
    <p class="mt-2 text-base text-mute">{{ __('Please check the categories below, you can click any category to load products.') }}</p>
  </div>

  <div
    class="mt-16 mx-auto grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-12 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 sm:gap-y-14 lg:mx-0 lg:max-w-none lg:grid-cols-5">
    @foreach ($categories as $category)
      <a href="{{ route('shop.category', $category->slug) }}" wire:navigate w.hover
        class="col-span-2 min-h-12 text-sm font-bold rounded-md w-full lg:col-span-1 flex flex-col items-center gap-2">
        @if ($category->photo)
          <img class="max-h-16 rounded-lg" src="{{ $category->photo }}" alt="{{ $category->name }}">
        @endif
        {{ $category->name }}
      </a>
    @endforeach
    <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-10">
      {{ $categories->links() }}
    </div>
  </div>
