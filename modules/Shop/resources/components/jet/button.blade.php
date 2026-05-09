@props(['loading' => true])
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary']) }}>
  {{ $slot }}
  @if ($loading)
    <svg wire:loading viewBox="0 0 38 38" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" class="size-5 stroke-current inline ms-2">
      <g fill="none" fill-rule="evenodd">
        <g transform="translate(1 1)" stroke-width="1.5">
          <circle stroke-opacity=".3" cx="18" cy="18" r="18" />
          <path d="M36 18c0-9.94-8.06-18-18-18">
            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
              repeatCount="indefinite" />
          </path>
        </g>
      </g>
    </svg>
  @endif
</button>
