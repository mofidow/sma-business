<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { Attachments, Button, Loading, ViewCustomFields } from '@/Components/Common';

const props = defineProps(['current', 'custom_fields', 'editRow']);

const loading = ref(true);
const stock_count = ref(null);

onMounted(async () => {
  await axios
    .get(route('stock_counts.show', { stock_count: props.current.id, json: true }))
    .then(res => {
      stock_count.value = res.data;
      loading.value = false;
    })
    .catch(() => (loading.value = false));
});

function print() {
  window.print();
}
</script>

<template>
  <div v-if="loading" class="relative h-64">
    <Loading />
  </div>
  <template v-else-if="stock_count">
    <span class="absolute end-12 top-4 inline-flex items-center gap-x-4 sm:end-14 print:hidden">
      <button type="button" @click="print" class="link -m-2 p-2">
        <Icon name="print-o" class="size-5" />
      </button>
      <button v-if="editRow && $can('update-stock-counts')" type="button" @click="() => editRow(stock_count)" class="link -m-2 p-2">
        <Icon name="edit-o" class="size-5" />
      </button>
    </span>

    <div class="border-b border-gray-200 px-4 py-4 sm:px-6 dark:border-gray-700 print:hidden">
      <div class="sm:flex sm:items-baseline sm:justify-between">
        <div class="sm:w-0 sm:flex-1">
          <h1 class="text-focus text-base font-semibold">{{ $t('Stock Count') }} #{{ stock_count?.id }} ({{ stock_count?.reference }})</h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{ $t('Please view the details below') }}
          </p>
        </div>
      </div>
    </div>

    <div class="mt-4 px-6 py-4 print:m-0 print:py-0">
      <div class="mb-1 max-h-16 max-w-[250px]">
        <img v-if="stock_count.store.logo" class="h-16 max-w-full" :src="stock_count.store.logo" :alt="stock_count.store.name" />
        <template v-else>
          <img
            :alt="$page.props.settings?.name"
            :src="$page.props.settings?.logo"
            v-if="$page.props.settings?.logo"
            class="h-16 max-w-full dark:hidden print:block!"
          />
          <img
            :alt="$page.props.settings?.name"
            :src="$page.props.settings?.logo_dark"
            v-if="$page.props.settings?.logo_dark"
            class="hidden h-16 max-w-full dark:block print:hidden!"
          />
        </template>
      </div>
      <div class="mb-8 flex items-start justify-between gap-3">
        <div class="flex w-3/5 flex-col">
          <div class="text-lg font-semibold">{{ stock_count.store.name }}</div>
          <div class="text-sm">{{ $address(stock_count.store) }}</div>
          <div class="text-sm" v-if="stock_count.store.phone">{{ $t('Phone') }}: {{ stock_count.store.phone }}</div>
          <div class="text-sm" v-if="stock_count.store.email">{{ $t('Email') }}: {{ stock_count.store.email }}</div>
        </div>
        <div class="w-2/5">
          <div class="mb-1 text-lg font-extrabold uppercase">
            {{ stock_count.type == 'full' ? $t('Full Stock Count') : $t('Partial Stock Count') }}
          </div>
          <div class="text-sm">{{ $t('Stock Count No. {x}', { x: stock_count?.id }) }}</div>
          <div class="text-sm">{{ $t('Date') }}: {{ $datetime(stock_count.created_at) }}</div>
          <div class="flex gap-1 text-sm">
            {{ $t('Reference') }}:
            <p class="truncate hover:text-clip print:block print:text-clip" dir="rtl">{{ stock_count.reference }}</p>
          </div>
        </div>
      </div>

      <div
        v-if="stock_count.type == 'partial'"
        class="rounded-md border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-950"
      >
        <div v-if="stock_count.brands" class="flex flex-wrap items-center justify-between text-lg">
          {{ $t('Brands') }}: <span class="font-bold">{{ stock_count.brands.map(b => b.name).join(', ') }}</span>
        </div>
        <div v-if="stock_count.categories" class="flex flex-wrap items-center justify-between text-lg">
          {{ $t('Categories') }}: <span class="font-bold">{{ stock_count.categories.map(b => b.name).join(', ') }}</span>
        </div>
      </div>

      <template v-if="stock_count.items && stock_count.items.length">
        <div class="mt-6 overflow-hidden rounded-sm border border-gray-200 dark:border-gray-700">
          <table
            class="w-full table-fixed divide-y divide-gray-200 text-sm dark:divide-gray-700 print:divide-gray-400 dark:print:divide-gray-400"
          >
            <thead>
              <tr>
                <th class="w-10 p-2 text-end font-bold uppercase">#</th>
                <th class="p-2 text-start font-bold uppercase">{{ $t('Product Code') }}</th>
                <th class="p-2 text-start font-bold uppercase">{{ $t('Variation Code') }}</th>
                <th class="w-[100px] p-2 text-center font-bold uppercase">{{ $t('Expected') }}</th>
                <th class="w-[100px] p-2 text-center font-bold uppercase">{{ $t('In Store') }}</th>
                <th class="w-[100px] p-2 text-center font-bold uppercase">{{ $t('Difference') }}</th>
              </tr>
            </thead>

            <tbody
              class="divide-y divide-gray-200 border-y border-gray-200 dark:divide-gray-700 dark:border-gray-700 print:divide-gray-400 dark:print:divide-gray-400"
            >
              <template v-for="(item, index) in stock_count.items" :key="item.id">
                <tr>
                  <td class="w-10 p-2 text-end">{{ index + 1 }}</td>
                  <td class="p-2">{{ item.product_code }}</td>
                  <td class="p-2">{{ item.variation_code }}</td>
                  <td class="p-2 text-end">{{ $number_qty(item.expected_quantity) }}</td>
                  <td class="p-2 text-end">{{ $number_qty(item.in_store_quantity) }}</td>
                  <td class="p-2 text-end">{{ $number_qty(Number(item.expected_quantity) - Number(item.in_store_quantity)) }}</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </template>

      <ViewCustomFields
        :modal="false"
        :fields="custom_fields"
        :title="$t('Custom Fields')"
        :extra_attributes="stock_count.extra_attributes"
      />

      <div v-if="stock_count.details" class="mb-4 pt-8">
        {{ stock_count.details }}
      </div>

      <div v-if="stock_count.attachments && stock_count.attachments.length" class="mt-8 w-full py-2 print:hidden">
        <Attachments :attachments="stock_count.attachments" />
      </div>

      <div v-if="!stock_count.completed_at" class="mt-8 w-full py-2 print:hidden">
        <Button away type="button" :href="route('stock_counts.export', stock_count.id)" class="w-full justify-center">
          {{ $t('Download excel file to complete') }}
        </Button>
        <div class="text-mute mt-6 text-center text-sm">
          {{ $t('Please edit to complete the stock count with checked excel file.') }}
        </div>
      </div>
      <div v-else>
        <div>{{ $t('Completed at') }}: {{ $datetime(stock_count.completed_at) }}</div>
        <div>{{ $t('Completed by') }}: {{ stock_count.completed_by?.name || '' }}</div>
        <div class="mt-6 print:hidden">
          <Button away type="button" :href="route('stock_counts.download', stock_count.id)" class="w-full justify-center">
            {{ $t('Download final excel file') }}
          </Button>
        </div>
      </div>
    </div>
  </template>
  <template v-else>
    <div class="flex min-h-64 items-center justify-center p-6 text-lg font-thin">
      {{ $t('No data found, the record might not belong to the selected store.') }}
    </div>
  </template>
</template>
