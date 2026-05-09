<script setup>
defineProps({
  loadingClass: {
    type: String,
    default: '',
  },
  circleSize: {
    type: String,
    default: 'h-10 w-10',
  },
  fullScreen: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div
    :class="fullScreen ? 'fixed inset-0 z-10' : 'absolute inset-0 z-1'"
    class="flex h-full w-full items-center justify-center text-gray-950 dark:text-gray-100"
  >
    <div class="absolute inset-0 bg-gray-100 opacity-75 backdrop-blur-xs dark:bg-gray-900"></div>
    <div class="z-10 flex items-center justify-center" :class="loadingClass">
      <svg viewBox="0 0 38 38" :class="circleSize" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" class="inline stroke-current">
        <g fill="none" fill-rule="evenodd">
          <g transform="translate(1 1)" stroke-width="1.5">
            <circle stroke-opacity=".3" cx="18" cy="18" r="18" />
            <path d="M36 18c0-9.94-8.06-18-18-18">
              <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
            </path>
          </g>
        </g>
      </svg>
    </div>
  </div>
</template>
