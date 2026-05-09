<script setup>
import { route } from 'ziggy-js';
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

import { axios } from '@/Core';
import { Loading } from '@/Components/Common';

const data = ref({});
const loading = ref(true);
const emit = defineEmits(['close']);

onMounted(async () => {
  await axios
    .get(route('alerts'))
    .then(res => (data.value = res.data))
    .finally(() => (loading.value = false));
});

function print() {
  window.print();
}
</script>

<template>
  <div>
    <span class="absolute end-12 top-4 inline-flex items-center gap-x-4 sm:end-14 print:hidden">
      <button type="button" @click="print" class="link -m-2 p-2">
        <Icon name="print-o" class="size-5" />
      </button>
    </span>

    <div class="border-b border-gray-200 px-4 py-4 sm:px-6 dark:border-gray-700 print:hidden">
      <div class="sm:flex sm:items-baseline sm:justify-between">
        <div class="sm:w-0 sm:flex-1">
          <h1 class="text-focus text-base font-semibold">{{ $t('Application Alerts') }}</h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{ $t('Please view the app alerts below') }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="loading">
      <Loading />
    </div>

    <div v-else class="px-6">
      <dl
        v-if="data?.customers > 0"
        class="my-6 grid grid-cols-1 gap-0.5 overflow-hidden rounded-md text-center sm:grid-cols-2 md:grid-cols-3"
      >
        <button
          type="button"
          v-if="data?.customers > 0"
          @click="
            () => {
              emit('close');
              router.visit(route('customers.index', { filters: { overdue: 1 } }));
            }
          "
          class="flex flex-col items-center justify-center rounded-md bg-gray-100 p-4 hover:bg-yellow-100 dark:bg-gray-900 dark:hover:bg-yellow-900"
        >
          <span class="text-sm/6 font-semibold">{{ data.customers == 1 ? $t('Customer') : $t('Customers') }}</span>
          <span class="-mt-1 text-sm/6 font-semibold">{{ $t('reached due limit') }}</span>
          <span class="text-focused order-first text-3xl font-semibold tracking-tight">{{ data.customers }}</span>
        </button>
      </dl>

      <template v-for="store in data.stores" :key="store.id">
        <div class="border-b border-gray-200 pb-2 dark:border-gray-700" :class="data?.customers > 0 ? '' : 'mt-6'">
          <h3 class="text-focus text-base font-bold">{{ store.name }}</h3>
        </div>

        <div class="my-6 grid grid-cols-1 gap-0.5 overflow-hidden rounded-md text-center sm:grid-cols-2 md:grid-cols-3">
          <button
            type="button"
            v-if="store?.reorder_stock > 0"
            @click="
              () => {
                emit('close');
                router.visit(route('products.index', { filters: { reorder: 1, store_id: store.id } }));
              }
            "
            class="flex flex-col items-center justify-center rounded-md bg-gray-100 p-4 hover:bg-yellow-100 dark:bg-gray-900 dark:hover:bg-yellow-900"
          >
            <span class="text-sm/6 font-semibold">{{ $t('Low Stock') }}</span>
            <span class="text-focused order-first text-3xl font-semibold tracking-tight">{{ store?.reorder_stock || 0 }}</span>
          </button>
          <button
            type="button"
            v-if="store?.unpaid_sales > 0"
            @click="
              () => {
                emit('close');
                router.visit(route('sales.report', { filters: { unpaid: 1, store_id: store.id } }));
              }
            "
            class="flex flex-col items-center justify-center rounded-md bg-gray-100 p-4 hover:bg-yellow-100 dark:bg-gray-900 dark:hover:bg-yellow-900"
          >
            <span class="text-sm/6 font-semibold">{{ $t('Unpaid {x}', { x: $t('Sales') }) }}</span>
            <span class="text-focused order-first text-3xl font-semibold tracking-tight">{{ store.unpaid_sales }}</span>
          </button>
          <button
            type="button"
            v-if="store?.due_sales > 0"
            @click="
              () => {
                emit('close');
                router.visit(route('sales.report', { filters: { overdue: 1, store_id: store.id } }));
              }
            "
            class="flex flex-col items-center justify-center rounded-md bg-gray-100 p-4 hover:bg-yellow-100 dark:bg-gray-900 dark:hover:bg-yellow-900"
          >
            <span class="text-sm/6 font-semibold">{{ $t('Overdue {x}', { x: $t('Sales') }) }}</span>
            <span class="text-focused order-first text-3xl font-semibold tracking-tight">{{ store.due_sales }}</span>
          </button>
          <button
            type="button"
            v-if="store?.unreceived_payments > 0"
            @click="
              () => {
                emit('close');
                router.visit(route('payments.report', { filters: { request: 1, store_id: store.id } }));
              }
            "
            class="flex flex-col items-center justify-center rounded-md bg-gray-100 p-4 hover:bg-yellow-100 dark:bg-gray-900 dark:hover:bg-yellow-900"
          >
            <span class="text-sm/6 font-semibold">{{ $t('Payment Requests') }}</span>
            <span class="text-focused order-first text-3xl font-semibold tracking-tight">{{ store.unreceived_payments }}</span>
          </button>
          <button
            type="button"
            v-if="store?.unpaid_purchases > 0"
            @click="
              () => {
                emit('close');
                router.visit(route('purchases.report', { filters: { unpaid: 1, store_id: store.id } }));
              }
            "
            class="flex flex-col items-center justify-center rounded-md bg-gray-100 p-4 hover:bg-yellow-100 dark:bg-gray-900 dark:hover:bg-yellow-900"
          >
            <span class="text-sm/6 font-semibold">{{ $t('Unpaid {x}', { x: $t('Purchases') }) }}</span>
            <span class="text-focused order-first text-3xl font-semibold tracking-tight">{{ store.unpaid_purchases }}</span>
          </button>
        </div>
      </template>
    </div>
  </div>
</template>
