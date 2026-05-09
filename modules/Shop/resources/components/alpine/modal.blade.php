@props(['maxWidth' => 'sm:max-w-md p-6', 'backdrop' => true, 'name' => 'modal', 'property' => 'open'])

<div x-show="{{ $property }}" class="modal fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" style="display: none;">
  <div class="flex items-center justify-center min-h-full sm:mx-6">
    <div x-show="{{ $property }}" class="fixed inset-0 transform transition-all"
      @if ($backdrop) @click="{{ $property }} = false" @endif x-transition.opacity>
      <div class="absolute z-0 inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    <div x-show="{{ $property }}"
      class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all z-10 sm:w-full lg:mx-auto {{ $maxWidth }}"
      :aria-hidden="{{ $property }} ? false : true" @if ($property == 'open') x-trap.noscroll.inert="{{ $property }}" @endif
      @keydown.tab.prevent="$focus.wrap().next()" @keydown.shift.tab.prevent="$focus.wrap().previous()"
      x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
      {{ $slot }}
    </div>
  </div>
</div>
