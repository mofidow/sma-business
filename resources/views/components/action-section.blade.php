<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title ?? '' }}</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $description ?? '' }}</p>
        </div>
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 sm:p-6 bg-white dark:bg-gray-800 shadow sm:rounded-md">
            {{ $content ?? $slot }}
        </div>
    </div>
</div>
