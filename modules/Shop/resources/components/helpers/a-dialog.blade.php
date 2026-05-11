@props(['id' => 'dialog', 'maxWidth' => 'sm:max-w-md'])
<x-alpine.modal :name="$id" maxWidth="{{ $maxWidth }} p-6" {{ $attributes }}>
    {{ $slot }}
</x-alpine.modal>
