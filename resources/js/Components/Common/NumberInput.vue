<script setup>
import { computed } from 'vue';

const emit = defineEmits(['change', 'remove', 'update:modelValue']);

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null,
  },
  name: {
    type: String,
    default: '',
  },
  min: {
    type: Number,
  },
  max: {
    type: Number,
  },
  delOn: {
    type: Number,
  },
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
});

const value = computed({
  get: () => props.modelValue,
  set: value => {
    emit('update:modelValue', value);
    emit('change', value);
  },
});

function decrement() {
  if (props.min === undefined) {
    value.value = Number(value.value) - 1;
  } else if (props.min === 0 && Number(value.value) > 0) {
    value.value = Number(value.value) - 1;
  } else if (props.min !== 0 && Number(props.min) < Number(value.value)) {
    value.value = Number(value.value) - 1;
  }
}

function increment() {
  if (!props.max || (props.max && Number(props.max) > Number(value.value))) {
    value.value = Number(value.value) + 1;
  }
}
</script>

<template>
  <div class="flex max-w-[7rem] items-center">
    <button
      type="button"
      id="decrement-button"
      @click="$emit('remove')"
      v-if="delOn === 0 && delOn == value"
      data-input-counter-decrement="quantity-input"
      class="h-8 rounded-s-lg border border-gray-300 bg-gray-100 p-2 hover:bg-gray-200 focus:ring-2 focus:ring-gray-100 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-600 dark:focus:ring-gray-700"
    >
      <svg
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        xmlns="http://www.w3.org/2000/svg"
        class="-mx-1 -my-2 h-5 w-5 text-red-600"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
        />
      </svg>
    </button>
    <button
      v-else
      type="button"
      id="decrement-button"
      @click="decrement"
      data-input-counter-decrement="quantity-input"
      class="h-8 rounded-s-lg border border-gray-300 bg-gray-100 p-2 hover:bg-gray-200 focus:ring-2 focus:ring-gray-100 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-600 dark:focus:ring-gray-700"
    >
      <svg
        fill="none"
        aria-hidden="true"
        viewBox="0 0 18 2"
        xmlns="http://www.w3.org/2000/svg"
        class="h-3 w-3 text-gray-900 dark:text-white"
      >
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
      </svg>
    </button>
    <input
      :id="id"
      type="text"
      placeholder="1"
      v-model="value"
      class="block h-8 w-full border border-x-0 border-gray-200 border-gray-300 py-2.5 text-center text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
    />
    <button
      type="button"
      @click="increment"
      id="increment-button"
      data-input-counter-increment="quantity-input"
      class="h-8 rounded-e-lg border border-gray-300 bg-gray-100 p-2 hover:bg-gray-200 focus:ring-2 focus:ring-gray-100 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-600 dark:focus:ring-gray-700"
    >
      <svg
        class="h-3 w-3 text-gray-900 dark:text-white"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 18 18"
      >
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
      </svg>
    </button>
  </div>
</template>
