<script setup>
import '@vuepic/vue-datepicker/dist/main.css';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import { InputError, InputLabel } from '@/Components/Jet';

const emit = defineEmits(['change', 'update:modelValue']);
defineProps({
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
  centered: {
    type: Boolean,
    default: false,
  },
  monthPicker: {
    type: Boolean,
    default: false,
  },
  clearable: {
    type: Boolean,
    default: true,
  },
  time: {
    type: Boolean,
    default: false,
  },
  showIcon: {
    type: Boolean,
    default: false,
  },
  teleport: {
    type: Boolean,
    default: false,
  },
  error: String,
  label: String,
  format: String,
  action: Function,
  actionText: String,
  modelValue: String,
  placeholder: String,
});

function update(date) {
  emit('update:modelValue', date);
  emit('change', date);
}
</script>

<template>
  <div v-if="label" class="flex items-center justify-between">
    <InputLabel :for="id" :value="label" class="mb-1 flex-1" />
    <button v-if="actionText && action" type="button" @click="action()" class="link text-sm">{{ actionText }}</button>
  </div>
  <VueDatePicker
    :teleport="true"
    :auto-apply="true"
    :readonly="readonly"
    :model-value="modelValue"
    @cleared="e => update(e)"
    :month-picker="monthPicker"
    @update:modelValue="e => update(e)"
    :time-config="{ enableTimePicker: time }"
    :input-attrs="{ id, placeholder, clearable, hideInputIcon: !showIcon }"
    :formats="{ input: format || (monthPicker ? 'MMM yyyy' : time ? 'yyyy-MM-dd HH:mm' : 'yyyy-MM-dd') }"
  >
  </VueDatePicker>
  <InputError :message="error" class="mt-2" />
</template>
