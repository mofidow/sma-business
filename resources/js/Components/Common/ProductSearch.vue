<script setup>
import { route } from 'ziggy-js';
import { Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { onMounted, ref, watch, nextTick } from 'vue';

import useClickOutside from '@/Core/useClickOutside.js';
import { searchItems } from '@/Core';
import Input from './Input.vue';

const props = defineProps({
  id: {
    type: String,
    default: 'product-search',
  },
  model: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: '',
  },
  rounded: {
    type: Boolean,
    default: false,
  },
});

const emits = defineEmits(['update:modelValue', 'add-item', 'focus']);

const value = ref('');
const result = ref([]);
const adding = ref(false);
const excludeRef = ref(null);
const openSearch = ref(false);
const componentRef = ref(null);
useClickOutside(componentRef, () => (openSearch.value = false), excludeRef, props.keyboard ? 'keyboard-container' : null);

// watch(value, v => {
//   emits('update:modelValue', v);
// });

watch(value, debounce(searchProducts, 500));

onMounted(() => {
  value.value = props.modelValue;
});

async function searchProducts(query) {
  if (query) {
    result.value = await searchItems(query, props.model);

    if (result.value.length == 1) {
      addItem(result.value[0]);
    }
  }
}

const addItem = async p => {
  adding.value = true;
  openSearch.value = false;
  value.value = '';
  // result.value = [];
  emits('add-item', p);

  await nextTick();
  document.getElementById(props.id)?.focus();
  adding.value = false;
};
</script>

<template>
  <div ref="excludeRef" class="relative">
    <div class="relative">
      <Input
        label=""
        :id="id"
        v-model="value"
        :rounded="rounded"
        :keyboard="type == 'pos'"
        @focus="openSearch = true"
        :placeholder="$t('Scan barcode or type to search')"
      />
      <Link
        v-if="type != 'pos'"
        :href="route('products.create')"
        class="absolute end-0 top-1/2 -translate-y-1/2 rounded-r-md border-l border-gray-300 p-2 text-gray-500 hover:bg-gray-200 hover:text-gray-700 focus:ring-2 focus:ring-primary-500 focus:outline-none dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-white"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
      </Link>
    </div>
    <div
      ref="componentRef"
      v-if="openSearch && value && result?.length"
      class="absolute start-0 end-0 top-full z-10 mt-2 max-h-96 min-w-[200px] overflow-y-auto rounded-md bg-white py-1 ring-1 ring-gray-200 dark:bg-gray-700 dark:ring-gray-700"
    >
      <button
        :key="p.id"
        type="button"
        v-for="p of result"
        @click="addItem(p)"
        :disabled="
          adding ||
          (model == 'purchase'
            ? false
            : model == 'adjustment'
              ? type == 'Subtraction' && $page.props.settings.overselling != 1 && p.store_stock?.balance <= 0
              : $page.props.settings.overselling != 1 && p.store_stock?.balance <= 0)
        "
        class="flex w-full flex-wrap items-center justify-between gap-x-8 gap-y-0 px-4 py-1.5 text-start hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-gray-900"
      >
        {{ p.name }}
        <span class="text-sm">
          <span class="text-mute">{{ $t('In Stock') + ':' }}</span>
          <span class="ms-0.5 font-bold">{{ $decimal_qty(p.store_stock?.balance || 0) }}</span>
        </span>
      </button>
    </div>
  </div>
</template>
