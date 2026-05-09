<script setup>
import { onMounted, ref, watch } from 'vue';
import { InputError } from '@/Components/Jet';

const value = ref('value1');
const emit = defineEmits(['update:checked']);

const props = defineProps({
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
  modelValue: {
    type: [String, Number, Boolean],
    default: false,
  },
  options: {
    type: Array,
    default: () => [],
  },
  label: String,
  error: String,
});

onMounted(() => {
  value.value = props.modelValue;
});

watch(value, newVal => {
  emit('update:checked', newVal);
  emit('update:modelValue', newVal);
});
</script>

<template>
  <div class="flex gap-x-8 gap-y-3">
    <label v-if="label" class="text-mute mb-1 block text-sm font-bold">
      {{ label }}
    </label>
    <div v-for="option in options" :key="option.value">
      <label :for="option.key" class="text-mute inline-flex items-center text-sm font-medium">
        <input
          type="radio"
          v-model="value"
          :id="option.key"
          :name="option.key"
          :value="option.value"
          :class="{ 'border-red-500': error }"
          class="h-5 w-5 rounded-full border-gray-300 text-primary-600 shadow-xs focus:rounded-full focus:ring-primary-400/90 focus:ring-offset-0 dark:border-gray-700 dark:bg-gray-600"
        />
        <span v-if="option.label" v-html="option.label" class="ms-2"></span>
      </label>
      <InputError :message="error" />
    </div>
  </div>
</template>
