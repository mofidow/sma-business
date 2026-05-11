<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-primary inline-flex items-center']) }}>
    {{ $slot }}
</button>
