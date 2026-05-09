<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
  checked: {
    type: [Array, Boolean],
    default: false,
  },
  value: {
    type: String,
    default: null,
  },
});

const proxyChecked = computed({
  get() {
    return props.checked;
  },

  set(val) {
    emit('update:checked', val);
  },
});
</script>

<template>
  <input
    :value="value"
    type="checkbox"
    v-model="proxyChecked"
    class="rounded-sm border-gray-300 text-primary-600 shadow-xs focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-primary-600 dark:focus:ring-offset-gray-800"
  />
</template>
