<script setup>
import { onMounted, ref } from 'vue';

import { axios } from '@/Core';
import { Loading } from '@/Components/Common';

defineProps(['show']);
const emit = defineEmits(['close', 'loadOrder', 'delete-order']);

const orders = ref(null);
const loading = ref(false);

onMounted(() => {
  showOpenOrders();
});

async function showOpenOrders() {
  loading.value = true;
  axios
    .get(route('pos.orders'))
    .then(res => (orders.value = res.data))
    .catch()
    .finally(() => (loading.value = false));
}
</script>

<template>
  <div>
    <div class="relative border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
      <div class="absolute end-0 top-0 pe-4 pt-4">
        <button
          type="button"
          @click="emit('close')"
          class="rounded-md hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden"
        >
          <span class="sr-only">{{ $t('Close') }}</span>
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div>
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Open Orders (on hold)') }}
        </h3>
        <p class="mt-1 text-sm">{{ $t('Please click the order below to load or trash icon to delete.') }}</p>
      </div>
    </div>

    <div class="rounded-b-lg bg-gray-100 p-6 dark:bg-gray-800">
      <div v-if="loading" class="h-40">
        <Loading loadingClass="w-10 h-20" />
      </div>

      <template v-if="orders && orders.data && orders.data.length">
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <li
            :key="oi"
            v-for="(openOrder, oi) in orders.data"
            class="group relative col-span-1 rounded-lg bg-white shadow-xs hover:bg-gray-50 dark:bg-gray-900"
          >
            <button
              type="button"
              @click="emit('loadOrder', openOrder)"
              class="flex w-full items-start justify-between space-x-4 p-4 text-start"
            >
              <div class="flex-1 truncate">
                <p
                  v-if="$page.props.settings?.restaurant == 1 && openOrder.table"
                  class="truncate text-sm font-bold text-primary-700 dark:text-primary-400"
                >
                  {{ openOrder.table?.name }} ({{ openOrder.hall?.name }})
                </p>
                <p class="mt-1 truncate text-sm font-medium">
                  {{ $t('Ref') }}: <span class="font-bold">{{ openOrder.reference }}</span>
                </p>
                <h3 class="mt-0.5 truncate text-sm font-bold text-gray-900 dark:text-gray-100">#{{ openOrder.number }}</h3>
                <p class="mt-0.5 truncate text-sm font-medium">{{ openOrder.customer?.company || openOrder.customer?.name }}</p>

                <p class="truncate text-sm">
                  {{ $t('Items') }}: {{ openOrder.data.total_items }} ({{ $number_qty(openOrder.data.total_quantity) }})
                </p>
                <p class="truncate text-sm">{{ $t('Total') }}: {{ $currency(openOrder.data.total) }}</p>
              </div>
              <img
                alt=""
                v-if="!$can('delete-sales')"
                :src="openOrder.user.profile_photo_url"
                class="-mt-px h-10 w-10 rounded-full bg-gray-300 group-hover:hidden"
              />
            </button>
            <div v-if="$can('delete-orders')" class="absolute end-4 top-4 h-10 w-10 shrink-0 rounded-full">
              <img alt="" :src="openOrder.user.profile_photo_url" class="-mt-px h-10 w-10 rounded-full bg-gray-300 group-hover:hidden" />
              <button
                type="button"
                @click="emit('delete-order', openOrder.number)"
                class="-mt-px hidden h-10 w-10 items-center justify-center rounded-full bg-red-200 group-hover:flex"
              >
                <Icon name="trash" class="-mt-px h-6 w-6 text-red-600" />
              </button>
            </div>
          </li>
        </ul>
      </template>
      <div v-else class="text-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="mx-auto h-12 w-12 text-gray-400"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"
          />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $t('No Suspended Orders') }}</h3>
        <p class="mt-1 text-sm">{{ $t('There is no suspended order to display.') }}</p>
      </div>
    </div>
  </div>
</template>
