<script setup>
import { computed } from 'vue';
import { InputError } from '@/Components/Jet';

const emit = defineEmits(['update:checked']);

const props = defineProps({
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
  checked: {
    type: [Array, Boolean],
    default: false,
  },
  value: {
    type: String,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  label: String,
  error: String,
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
  <div>
    <label :for="id" class="inline-flex items-center font-medium" :class="disabled ? 'opacity-50' : ''">
      <input
        :id="id"
        :value="value"
        type="checkbox"
        :disabled="disabled"
        v-model="proxyChecked"
        :class="{ 'border-red-500': error }"
        class="h-5 w-5 rounded-sm border-gray-300 text-primary-600 shadow-xs focus:rounded-sm focus:ring-primary-200/50 focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-600"
      />
      <span v-if="label" v-html="label" class="ms-2"></span>
    </label>
    <InputError :message="error" />
  </div>
</template>
