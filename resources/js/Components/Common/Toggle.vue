<script setup>
const emit = defineEmits(['change', 'update:modelValue']);

const props = defineProps({
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
  modelValue: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  label: String,
  text: String,
  error: String,
});

function toggleValue() {
  emit('update:modelValue', !props.modelValue);
  emit('change', !props.modelValue);
}
</script>

<template>
  <div class="flex items-center">
    <button
      type="button"
      role="switch"
      :disabled="disabled"
      @click="toggleValue"
      :class="modelValue ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
      class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:outline-hidden disabled:cursor-not-allowed!"
    >
      <span
        :class="
          modelValue
            ? $page.props.settings?.rtl_support == '1'
              ? '-translate-x-5 bg-white'
              : 'translate-x-5 bg-white'
            : 'translate-x-0 bg-white dark:bg-gray-500'
        "
        class="pointer-events-none inline-block size-5 transform rounded-full shadow-sm ring-0 transition duration-200 ease-in-out"
      ></span>
    </button>
    <label v-if="label || text" class="ms-3 text-sm" @click="toggleValue">
      <span v-if="label" class="font-medium">{{ label }}</span>
      <span v-if="text" class="text-mute ms-1">{{ text }}</span>
    </label>
  </div>
</template>
