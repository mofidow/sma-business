<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { InputError, InputLabel } from '@/Components/Jet';
import { vKeyboard } from '@/Pages/Sma/Pos/Components/Keyboard';

const input = ref(null);
const emits = defineEmits(['update:modelValue']);
defineProps({
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
  modelValue: String,
  label: String,
  error: String,
  rows: String,
  className: String,
  keyboard: {
    type: Boolean,
    default: false,
  },
});

onMounted(async () => {
  await nextTick();
  input.value.setAttribute('style', `height: ${input.value.scrollHeight + 2}px`);
});

function autoResizeAndEmit(event) {
  event.target.style.height = 'auto';
  event.target.style.height = `${event.target.scrollHeight + 2}px`;
  emits('update:modelValue', event.target.value);
}

// export default {
//   emits: ['update:modelValue'],

//   components: { TecLabel, TecInputError },

//   props: {
//     id: {
//       type: String,
//       default() {
//         return (Math.random() + '').replace('0.', '');
//       },
//     },
//     modelValue: String,
//     label: String,
//     error: String,
//     className: String,
//   },

//   mounted() {
//     this.$nextTick(() => {
//       this.$refs.input.setAttribute('style', `height: ${this.$refs.input.scrollHeight + 2}px`);
//     });
//   },

//   methods: {
//     focus() {
//       this.$refs.input.focus();
//     },
//     select() {
//       this.$refs.input.select();
//     },
//     autoResizeAndEmit(event) {
//       event.target.style.height = 'auto';
//       event.target.style.height = `${event.target.scrollHeight + 2}px`;
//       this.$emit('update:modelValue', event.target.value);
//     },
//   },
// };
</script>

<template>
  <div :class="className || 'col-span-6 sm:col-span-3 xl:col-span-6'">
    <InputLabel :for="id" :value="label" v-if="label" />
    <template v-if="keyboard">
      <textarea
        :id="id"
        v-keyboard
        ref="input"
        :rows="rows"
        v-bind="$attrs"
        :value="modelValue"
        @input="autoResizeAndEmit"
        :class="{ error: error, 'border-gray-300 dark:border-gray-700': !error }"
        class="mt-1 block w-full rounded-md shadow-xs placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-600 dark:focus:border-primary-600 dark:focus:ring-primary-600"
      />
    </template>
    <template v-else>
      <textarea
        :id="id"
        ref="input"
        :rows="rows"
        v-bind="$attrs"
        :value="modelValue"
        @input="autoResizeAndEmit"
        :class="{ error: error, 'border-gray-300 dark:border-gray-700': !error }"
        class="mt-1 block w-full rounded-md shadow-xs placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-600 dark:focus:border-primary-600 dark:focus:ring-primary-600"
      />
    </template>
    <InputError v-if="error" :message="error" class="mt-0" />
  </div>
</template>
