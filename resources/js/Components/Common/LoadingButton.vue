<script setup>
defineProps({
  loading: Boolean,
  disabled: Boolean,
  loadingClass: String,
  type: {
    type: String,
    default: 'submit',
  },
});
</script>

<template>
  <button :type="type" :disabled="disabled || loading" :class="{ 'opacity-25': loading }" class="btn-primary">
    <slot />
    <svg v-if="loading" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" class="ms-2 h-4 w-4 stroke-current" :class="loadingClass">
      <g fill="none" fill-rule="evenodd">
        <g transform="translate(1 1)" stroke-width="2">
          <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
          <path d="M36 18c0-9.94-8.06-18-18-18">
            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
          </path>
        </g>
      </g>
    </svg>
  </button>
</template>
