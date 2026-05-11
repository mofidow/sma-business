@props(['maxWidth' => '2xl'])

<div {{ $attributes }}>
    @if(isset($title) || isset($content) || isset($footer))
    <div class="fixed inset-0 z-50 overflow-y-auto" style="display:none" x-show="show ?? false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-{{ $maxWidth }} w-full">
                @if(isset($title))<h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $title }}</h2>@endif
                @if(isset($content))<div class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $content }}</div>@endif
                @if(isset($footer))<div class="mt-4 flex justify-end space-x-3">{{ $footer }}</div>@endif
            </div>
        </div>
    </div>
    @endif
</div>
