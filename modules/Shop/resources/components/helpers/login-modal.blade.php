@props(['id' => 'login'])
<x-alpine.modal :name="$id" maxWidth="sm:max-w-md p-6" {{ $attributes }}>
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Login Required') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('Please login to continue.') }}</p>
        <a href="{{ route('shop.login') }}" class="btn-primary w-full text-center block">{{ __('Login') }}</a>
    </div>
</x-alpine.modal>
