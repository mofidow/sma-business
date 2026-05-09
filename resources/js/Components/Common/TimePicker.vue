<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { InputError, InputLabel } from '@/Components/Jet';

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String },
  error: { type: String },
  id: { type: String, default: () => (Math.random() + '').replace('0.', '') },
  name: { type: String },
  placeholder: { type: String, default: 'HH:MM' },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  minuteInterval: { type: Number, default: 5 },
});

const emit = defineEmits(['update:modelValue', 'change']);

const showDropdown = ref(false);
const container = ref(null);
const hourListEl = ref(null);
const minuteListEl = ref(null);

const hours = computed(() => Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0')));

const minutes = computed(() => {
  const list = [];
  for (let i = 0; i < 60; i += props.minuteInterval) {
    list.push(String(i).padStart(2, '0'));
  }
  return list;
});

const selectedHour = computed(() => props.modelValue?.split(':')[0] || '');
const selectedMinute = computed(() => props.modelValue?.split(':')[1]?.slice(0, 2) || '');

function selectHour(h) {
  const m = selectedMinute.value || '00';
  update(`${h}:${m}`);
  scrollToActive();
}

function selectMinute(m) {
  const h = selectedHour.value || '00';
  update(`${h}:${m}`);
}

function update(val) {
  emit('update:modelValue', val);
  emit('change', val);
}

function clearTime() {
  update('');
  showDropdown.value = false;
}

function toggle() {
  if (props.disabled || props.readonly) return;
  showDropdown.value = !showDropdown.value;
  if (showDropdown.value) scrollToActive();
}

function scrollToActive() {
  setTimeout(() => {
    [hourListEl, minuteListEl].forEach(list => {
      if (!list.value) return;
      const active = list.value.querySelector('[data-active="true"]');
      if (active) list.value.scrollTop = active.offsetTop - list.value.offsetTop - 36;
    });
  }, 10);
}

function onClickOutside(e) {
  if (container.value && !container.value.contains(e.target)) showDropdown.value = false;
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
  <div ref="container" class="relative">
    <InputLabel :for="id" :value="label" v-if="label" />

    <div class="relative">
      <input
        :id="id"
        readonly
        type="text"
        :name="name"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @click="toggle"
        :class="[
          'block w-full cursor-pointer rounded-md pr-8 shadow-xs placeholder:text-gray-400 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-600',
          error
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
            : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:focus:border-primary-600 dark:focus:ring-primary-600',
          disabled && 'cursor-not-allowed opacity-60',
        ]"
      />
      <div class="absolute inset-y-0 right-2 flex items-center gap-1">
        <button
          v-if="modelValue"
          type="button"
          class="text-gray-400 hover:text-gray-600 focus:outline-none dark:hover:text-gray-300"
          tabindex="-1"
          @click.stop="clearTime"
        >
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <svg v-else class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
        </svg>
      </div>
    </div>

    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="showDropdown"
        class="absolute z-50 mt-1 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="flex divide-x divide-gray-200 dark:divide-gray-700">
          <div ref="hourListEl" class="h-48 flex-1 overflow-y-auto overscroll-none">
            <p
              class="sticky top-0 z-10 bg-gray-50 py-1 text-center text-xs font-semibold tracking-wider text-gray-400 uppercase dark:bg-gray-700"
            >
              HH
            </p>
            <button
              v-for="h in hours"
              :key="h"
              type="button"
              :data-active="selectedHour === h"
              :class="[
                'w-full py-1.5 text-center text-sm transition-colors',
                selectedHour === h
                  ? 'bg-primary-500 font-semibold text-white'
                  : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
              ]"
              @click="selectHour(h)"
            >
              {{ h }}
            </button>
          </div>

          <div ref="minuteListEl" class="h-48 flex-1 overflow-y-auto overscroll-none">
            <p
              class="sticky top-0 z-10 bg-gray-50 py-1 text-center text-xs font-semibold tracking-wider text-gray-400 uppercase dark:bg-gray-700"
            >
              MM
            </p>
            <button
              v-for="m in minutes"
              :key="m"
              type="button"
              :data-active="selectedMinute === m"
              :class="[
                'w-full py-1.5 text-center text-sm transition-colors',
                selectedMinute === m
                  ? 'bg-primary-500 font-semibold text-white'
                  : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
              ]"
              @click="selectMinute(m)"
            >
              {{ m }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <InputError :message="error?.replace(' id', '')" class="mt-2" />
  </div>
</template>
