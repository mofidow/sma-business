<script setup>
import { ref } from 'vue';
import { vKeyboard } from '@/Pages/Sma/Pos/Components/Keyboard';
import { InputError, InputLabel } from '@/Components/Jet';

const input = ref(null);
const emit = defineEmits(['blur-sm', 'input', 'change', 'focus', 'keyup', 'update:modelValue']);
const props = defineProps({
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
  type: {
    type: String,
    default: 'text',
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  keyboard: {
    type: Boolean,
    default: false,
  },
  step: {
    type: String,
    default: '1',
  },
  rounded: {
    type: Boolean,
    default: false,
  },
  min: [String, Number],
  max: [String, Number],
  size: [String, Number],
  error: String,
  label: String,
  title: String,
  pattern: String,
  action: Function,
  actionText: String,
  modelValue: String,
  placeholder: String,
  selectOnFocus: Boolean,
});

const focused = e => {
  if (props.selectOnFocus) {
    $event.target.select();
  }
  emit('focus', e);
};
</script>
<template>
  <div v-if="label" class="mb-1 flex items-center justify-between">
    <InputLabel :for="id" :value="label" />
    <button v-if="actionText && action" type="button" @click="action()" class="link text-sm">{{ actionText }}</button>
  </div>
  <template v-if="keyboard">
    <input
      :id="id"
      :min="min"
      :max="max"
      v-keyboard
      ref="input"
      :step="step"
      :type="type"
      :title="title"
      @focus="focused"
      :pattern="pattern"
      :value="modelValue"
      :readonly="readonly"
      @blur="e => $emit('blur-sm')"
      @keyup="e => $emit('keyup', e)"
      @change="e => $emit('change', e)"
      @keypress="e => $emit('keypress', e)"
      :placeholder="placeholder || label || ''"
      @input="
        e => {
          $emit('update:modelValue', e.target.value);
          $emit('input', e);
        }
      "
      :class="{
        error: error,
        'rounded-md': !rounded,
        'rounded-full': rounded,
        'px-2 py-1 text-sm': size == 'sm',
        'border-gray-300 dark:border-gray-700': !error,
        'border-gray-300 opacity-60 focus:border-gray-300 focus:ring-0 dark:focus:border-gray-700': readonly,
        'focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-600 dark:focus:ring-primary-600': !readonly,
      }"
      class="block w-full shadow-xs placeholder:text-gray-400 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-600"
    />
  </template>
  <template v-else>
    <input
      :id="id"
      :min="min"
      :max="max"
      ref="input"
      :step="step"
      :type="type"
      :title="title"
      @focus="focused"
      :pattern="pattern"
      :value="modelValue"
      :readonly="readonly"
      @blur="e => $emit('blur-sm')"
      @keyup="e => $emit('keyup', e)"
      @change="e => $emit('change', e)"
      @keypress="e => $emit('keypress', e)"
      :placeholder="placeholder || label || ''"
      @input="
        e => {
          $emit('update:modelValue', e.target.value);
          $emit('input', e);
        }
      "
      :class="{
        error: error,
        'px-2 py-1 text-sm': size == 'sm',
        'border-gray-300 dark:border-gray-700': !error,
        'border-gray-300 opacity-60 focus:border-gray-300 focus:ring-0 dark:focus:border-gray-700': readonly,
        'focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-600 dark:focus:ring-primary-600': !readonly,
      }"
      class="block w-full rounded-md shadow-xs placeholder:text-gray-400 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-600"
    />
  </template>
  <InputError :message="error?.replace(' id', '')" class="mt-2" />
</template>
