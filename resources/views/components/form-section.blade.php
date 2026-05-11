@props(['submit' => null])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title ?? '' }}</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $description ?? '' }}</p>
        </div>
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
                <div class="grid grid-cols-6 gap-6">{{ $form ?? $slot }}</div>
            </div>
            @if (isset($actions))
            <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-700 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                {{ $actions }}
            </div>
            @endif
        </form>
    </div>
</div>
