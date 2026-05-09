<script setup>
import dayjs from 'dayjs';
import { onMounted, ref } from 'vue';

import { axios } from '@/Core';
import { Loading } from '@/Components/Common';
import { notify } from 'notiwind';

defineProps(['show']);
const emit = defineEmits(['close', 'loadOrder']);

const orders = ref(null);
const loading = ref(false);

onMounted(() => {
  fetchQrOrders();
});

async function fetchQrOrders() {
  loading.value = true;
  axios
    .get(route('pos.qr-orders'))
    .then(res => (orders.value = res.data))
    .catch()
    .finally(() => (loading.value = false));
}

async function acceptOrder(order) {
  loading.value = true;
  axios
    .post(route('pos.qr-orders.accept', { order: order.id }))
    .then(res => {
      if (res.data.success) {
        emit('loadOrder', res.data.order);
        emit('close');
      }
    })
    .catch(err => {
      console.error(err);

      notify({
        group: 'main',
        type: 'error',
        title: 'Error!',
        text: t('Failed to accept the order. Please try again.'),
      });
    })
    .finally(() => (loading.value = false));
}

function statusColor(status) {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    case 'processing':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
  }
}
</script>

<template>
  <div>
    <div class="relative border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
      <div class="absolute inset-e-0 top-0 pe-4 pt-4">
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
      <div class="flex items-center gap-2">
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('QR Code Orders') }}
        </h3>
      </div>
      <p class="mt-1 text-sm">{{ $t('Click an order to accept and load it to print order, bill or get payment.') }}</p>
    </div>

    <div class="relative min-h-40 rounded-b-lg bg-gray-100 p-6 dark:bg-gray-800">
      <div v-if="loading" class="absolute inset-0">
        <Loading loadingClass="size-10" />
      </div>

      <template v-if="orders && orders.data && orders.data.length">
        <p class="-mt-4 mb-6 text-sm">{{ $t('Please ensure to print the order after accepting it.') }}</p>
        <p class="-mt-4 mb-6 text-sm">{{ $t('If you change order or add more items, please hold the order to save changes.') }}</p>
        <ul role="list" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <li
            :key="order.id"
            v-for="order in orders.data"
            class="group relative col-span-1 rounded-lg bg-white shadow-xs hover:bg-gray-50 dark:bg-gray-900"
            :class="{ 'bg-red-100! dark:bg-red-950/50!': order.getting_late }"
          >
            <div v-if="order.getting_late" class="absolute inset-x-12 inset-y-6 animate-ping rounded-lg bg-red-200 dark:bg-red-950"></div>
            <button type="button" @click="acceptOrder(order)" class="relative z-10 flex w-full flex-col p-4 text-start">
              <div class="flex w-full items-start justify-between">
                <div class="flex-1 truncate">
                  <p v-if="order.table" class="truncate text-sm font-bold text-primary-700 dark:text-primary-400">
                    {{ order.table?.name }} ({{ order.hall?.name }})
                  </p>
                  <h3 class="mt-0.5 truncate text-sm font-bold text-gray-900 dark:text-gray-100">#{{ order.number }}</h3>
                  <p v-if="order.customer_name" class="mt-0.5 truncate text-sm text-gray-600 dark:text-gray-400">
                    {{ order.customer_name }}
                  </p>
                </div>
                <span
                  :class="statusColor(order.status)"
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                >
                  {{ order.status }}
                </span>
              </div>

              <div class="mt-2 w-full border-t border-gray-100 pt-2 dark:border-gray-700">
                <p v-if="order.data?.items" class="truncate text-sm">
                  {{ $t('Items') }}: {{ order.data?.items.length }} ({{
                    $number_qty(order.data.items.reduce((total, item) => Number(total) + Number(item.quantity), 0))
                  }})
                </p>
                <!-- <div v-if="order.data?.items" class="mb-1 max-h-20 space-y-0.5 overflow-y-auto">
                  <p v-for="item in order.data.items" :key="item.id" class="truncate text-xs text-gray-500 dark:text-gray-400">
                    {{ item.quantity }}× {{ item.name }}
                  </p>
                </div> -->
                <div class="flex items-center justify-between">
                  <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                    {{ $currency(order.total) }}
                  </span>
                  <span class="text-xs text-gray-400">{{ order.created_at_for_humans || $datetime(order.created_at) }}</span>
                </div>
              </div>

              <p v-if="order.notes" class="mt-1 truncate text-xs text-amber-600 italic dark:text-amber-400">
                {{ order.notes }}
              </p>
            </button>
          </li>
        </ul>
      </template>

      <div v-else-if="!loading" class="text-center">
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
            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $t('No QR Orders') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ $t('There are no pending QR code orders at the moment.') }}
        </p>
      </div>
    </div>
  </div>
</template>
