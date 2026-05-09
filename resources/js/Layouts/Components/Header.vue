<template>
  <header
    class="flex items-start justify-between gap-x-6 border-b border-gray-200 px-4 py-3 sm:px-6 md:items-center lg:px-8 dark:border-gray-700"
  >
    <div>
      <h1 class="text-focus text-base leading-7 font-semibold">
        <slot />
      </h1>

      <p class="text-mute mt-0.5 text-sm"><slot name="subheading" /></p>
    </div>

    <div class="z-1">
      <slot name="menu">
        <!-- <div class="relative">
        <button
          type="button"
          class="flex items-center gap-x-1 text-sm font-medium leading-6 text-white"
          id="sort-menu-button"
          aria-expanded="false"
          aria-haspopup="true"
        >
          Sort by
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
            <path
              fill-rule="evenodd"
              d="M10.53 3.47a.75.75 0 0 0-1.06 0L6.22 6.72a.75.75 0 0 0 1.06 1.06L10 5.06l2.72 2.72a.75.75 0 1 0 1.06-1.06l-3.25-3.25Zm-4.31 9.81 3.25 3.25a.75.75 0 0 0 1.06 0l3.25-3.25a.75.75 0 1 0-1.06-1.06L10 14.94l-2.72-2.72a.75.75 0 0 0-1.06 1.06Z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
        <div
          class="absolute end-0 z-10 mt-2.5 w-40 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-hidden"
          role="menu"
          aria-orientation="vertical"
          aria-labelledby="sort-menu-button"
          tabindex="-1"
        >
          <a href="#" class="block px-3 py-1 text-sm leading-6 text-gray-900" role="menuitem" tabindex="-1" id="sort-menu-item-0">Name</a>
          <a href="#" class="block px-3 py-1 text-sm leading-6 text-gray-900 bg-gray-50" role="menuitem" tabindex="-1" id="sort-menu-item-1"
            >Date updated</a
          >
          <a href="#" class="block px-3 py-1 text-sm leading-6 text-gray-900 bg-gray-50" role="menuitem" tabindex="-1" id="sort-menu-item-2"
            >Environment</a
          >
        </div>
      </div> -->
      </slot>
    </div>
  </header>
</template>
