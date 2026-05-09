<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

import { Modal, SecondaryButton } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AutoComplete, Button, DateInput, Loading, LoadingButton, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });

const props = defineProps({
  summary: Object,
  pagination: Object,
  stores: Array,
  users: Array,
  filters: Object,
  method: String,
});

const form = ref(false);
const searching = ref(false);
const localFilters = ref({ ...props.filters });

const methodLabels = {
  FIFO: 'FIFO - First In First Out',
  LIFO: 'LIFO - Last In First Out',
  AVCO: 'AVCO - Average Cost',
  EXPF: 'EXPF - Items Expiring Soon',
};

const typeLabels = {
  sale: 'Sale',
  sale_return: 'Sale Return',
  purchase_return: 'Purchase Return',
};

const typeOptions = [
  { value: '', label: 'All Types' },
  { value: 'sale', label: 'Sale' },
  { value: 'sale_return', label: 'Sale Return' },
  { value: 'purchase_return', label: 'Purchase Return' },
];

const pageTotalCost = computed(() => props.pagination?.data?.reduce((sum, entry) => sum + Number(entry.total_cost), 0) || 0);

const pageTotalQuantity = computed(() => props.pagination?.data?.reduce((sum, entry) => sum + Number(entry.quantity), 0) || 0);

const closeForm = () => {
  form.value = false;
};

const applyFilters = () => {
  const formData = Object.entries(localFilters.value).reduce((a, [k, v]) => (v ? ((a[k] = v), a) : a), {});

  router.visit(route('inventory_accounting.report', Object.keys(formData).length ? { filters: formData } : ''), {
    preserveScroll: true,
    onStart: () => (searching.value = true),
    onFinish: () => {
      searching.value = false;
      form.value = false;
    },
  });
};

const resetFilters = () => {
  localFilters.value = {};
  applyFilters();
};

function getReference(row) {
  if (row.type === 'sale' || row.type === 'sale_return') {
    return row.sale_item?.sale?.reference || '-';
  }
  if (row.type === 'purchase_return') {
    return row.return_order_item?.return_order?.reference || '-';
  }
  return '-';
}

function getPurchaseRef(row) {
  return row.purchase_item?.purchase?.reference || (row.purchase_item_id ? '#' + row.purchase_item_id : 'Fallback');
}
</script>

<template>
  <Head>
    <title>{{ $t('Inventory Accounting Report') }}</title>
  </Head>
  <Header>
    {{ $t('Inventory Accounting Report') }}
    <template #subheading>
      <div>
        {{ $t('Cost allocation details using {method}', { method: methodLabels[method] || method }) }}
      </div>
      <div
        class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-bold text-primary-700 dark:text-primary-400"
        v-if="filters && Object.keys(filters).filter(k => filters[k] && k !== 'sort').length > 0"
      >
        {{ $t('Filters Applied') }}:
        <span v-if="filters.start_date"> {{ $t('From') }}: {{ $date(filters.start_date.toString(), null, null, true) }} </span>
        <span v-if="filters.end_date"> {{ $t('To') }}: {{ $date(filters.end_date.toString(), null, null, true) }} </span>
        <span v-if="filters.store_id"> {{ $t('Store') }}: {{ stores.find(s => s.value == filters.store_id)?.label || '' }} </span>
        <span v-if="filters.type"> {{ $t('Type') }}: {{ $t(typeLabels[filters.type] || filters.type) }} </span>
      </div>
    </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button type="button" @click="form = true">
          {{ $t('Customize') }}
        </Button>
      </div>
    </template>
  </Header>

  <!-- Summary Cards -->
  <dl
    v-if="summary"
    class="grid grid-cols-1 gap-px border-b border-gray-200 bg-gray-200 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-700 dark:bg-gray-700"
  >
    <div class="flex flex-wrap items-baseline justify-between bg-white px-4 py-5 sm:px-6 xl:px-8 dark:bg-gray-900">
      <div>
        <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Total COGS') }}</dt>
        <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
          {{ $currency(summary.total_cogs) }}
        </dd>
        <dd class="text-mute text-sm">{{ $number_qty(summary.total_sold_quantity) }} {{ $t('units sold') }}</dd>
      </div>
    </div>
    <div class="flex flex-wrap items-baseline justify-between bg-white px-4 py-5 sm:px-6 xl:px-8 dark:bg-gray-900">
      <div>
        <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Net COGS') }}</dt>
        <dd
          class="w-full flex-none text-3xl leading-10 font-medium tracking-tight"
          :class="summary.net_cogs >= 0 ? 'text-focus' : 'text-red-600 dark:text-red-400'"
        >
          {{ $currency(summary.net_cogs) }}
        </dd>
        <dd class="text-mute text-sm">{{ $t('After returns') }}</dd>
      </div>
    </div>
    <div class="flex flex-wrap items-baseline justify-between bg-white px-4 py-5 sm:px-6 xl:px-8 dark:bg-gray-900">
      <div>
        <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Sale Returns Cost') }}</dt>
        <dd class="w-full flex-none text-3xl leading-10 font-medium tracking-tight text-green-600 dark:text-green-400">
          {{ $currency(summary.total_sale_return_cost) }}
        </dd>
        <dd class="text-mute text-sm">{{ $number_qty(summary.total_returned_quantity) }} {{ $t('units returned') }}</dd>
      </div>
    </div>
    <div class="flex flex-wrap items-baseline justify-between bg-white px-4 py-5 sm:px-6 xl:px-8 dark:bg-gray-900">
      <div>
        <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Fallback Cost') }}</dt>
        <dd class="w-full flex-none text-3xl leading-10 font-medium tracking-tight text-amber-600 dark:text-amber-400">
          {{ $currency(summary.fallback_cost) }}
        </dd>
        <dd class="text-mute text-sm">{{ $t('No purchase lot') }}</dd>
      </div>
    </div>
  </dl>

  <!-- Detailed Summary Section -->
  <div class="border-b border-gray-200 bg-gray-50 px-4 pt-4 pb-5 sm:px-6 lg:px-8 dark:border-gray-700 dark:bg-gray-900/50">
    <h3 class="text-focus mb-2 text-base leading-7 font-semibold">{{ $t('Summary') }}</h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
        <p class="text-mute text-sm">{{ $t('Costing Method') }}</p>
        <p class="text-focus text-xl font-semibold">{{ methodLabels[method] || method }}</p>
      </div>
      <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
        <p class="text-mute text-sm">{{ $t('Total Allocations') }}</p>
        <p class="text-focus text-xl font-semibold">{{ $number(summary?.total_allocations || 0, null, { maximumFractionDigits: 0 }) }}</p>
      </div>
      <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
        <p class="text-mute text-sm">{{ $t('Purchase Return Cost') }}</p>
        <p class="text-xl font-semibold text-red-600 dark:text-red-400">{{ $currency(summary?.total_purchase_return_cost || 0) }}</p>
      </div>
      <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
        <p class="text-mute text-sm">{{ $t('Avg Unit Cost') }}</p>
        <p class="text-focus text-xl font-semibold">
          {{ summary?.total_sold_quantity > 0 ? $currency(summary.total_cogs / summary.total_sold_quantity) : $currency(0) }}
        </p>
      </div>
    </div>
  </div>

  <!-- Allocations Table -->
  <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <Loading v-if="searching" circle-size="w-10 h-10" />

    <div class="py-4">
      <h3 class="text-focus text-base leading-7 font-semibold">{{ $t('Cost Allocations') }}</h3>
      <p class="text-mute text-sm">{{ $t('Individual lot consumption records') }}</p>
    </div>

    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="mt-2 inline-block min-w-full border-b border-gray-200 align-middle dark:border-gray-700">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
              <tr>
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8">
                  {{ $t('Date') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Type') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Product') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Reference') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Purchase Lot') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-end text-sm font-semibold">
                  {{ $t('Quantity') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-end text-sm font-semibold">
                  {{ $t('Unit Cost') }}
                </th>
                <th scope="col" class="text-focus py-3.5 ps-3 pe-4 text-end text-sm font-semibold sm:pe-6 lg:pe-8">
                  {{ $t('Total Cost') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="w-36 py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td class="w-20 px-3 py-2 text-center text-sm whitespace-nowrap">
                    <span
                      class="mx-auto inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                      :class="{
                        'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300': row.type === 'sale',
                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': row.type === 'sale_return',
                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': row.type === 'purchase_return',
                      }"
                    >
                      {{ $t(typeLabels[row.type] || row.type) }}
                    </span>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div>{{ row.product?.name || '-' }}</div>
                    <div v-if="row.variation" class="text-mute text-xs">{{ row.variation.code }}</div>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ getReference(row) }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <template v-if="row.purchase_item">
                      <div>{{ getPurchaseRef(row) }}</div>
                      <div v-if="row.purchase_item.batch_no" class="text-mute text-xs">
                        {{ $t('Batch') }}: {{ row.purchase_item.batch_no }}
                      </div>
                    </template>
                    <span v-else class="text-mute italic">{{ $t('Fallback') }}</span>
                  </td>
                  <td class="w-28 px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $number_qty(row.quantity) }}
                  </td>
                  <td class="w-28 px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.unit_cost) }}
                  </td>
                  <td class="w-36 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    {{ $currency(row.total_cost) }}
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="8" class="py-8 text-center">
                  <p class="text-mute text-sm">{{ $t('No cost allocations found for the selected period') }}</p>
                </td>
              </tr>
            </tbody>
            <tfoot
              v-if="pagination && pagination.data.length"
              class="border-t border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-900/50"
            >
              <tr>
                <td colspan="5" class="py-4 ps-4 pe-3 text-sm font-semibold sm:ps-6 lg:ps-8">
                  {{ $t('Page Totals') }}
                </td>
                <td class="px-3 py-4 text-end text-sm font-semibold">
                  {{ $number_qty(pageTotalQuantity) }}
                </td>
                <td class="px-3 py-4 text-end text-sm font-semibold">&nbsp;</td>
                <td class="py-4 ps-3 pe-4 text-end text-sm font-semibold sm:pe-6 lg:pe-8">
                  {{ $currency(pageTotalCost) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
      <Pagination
        class="mx-4 mt-auto pt-4 pb-2 text-sm sm:mx-6"
        :meta="{ ...pagination.meta, links: pagination.links }"
        :links="{ prev: pagination.links[0].url, next: pagination.links[pagination.links.length - 1].url }"
      />
    </div>
  </div>

  <!-- Filter Modal -->
  <Modal :show="form" @close="closeForm" max-width="lg">
    <form @submit.prevent="applyFilters">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="sm:flex sm:items-baseline sm:justify-between">
          <div class="sm:w-0 sm:flex-1">
            <h1 class="text-focus text-base font-semibold">
              {{ $t('Customize Report') }}
            </h1>
            <p class="text-mute mt-1 truncate text-sm">
              {{ $t('Please select the desired filters below.') }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-6 gap-6 p-6">
        <!-- Store -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="store_id"
            :clearable="true"
            :searchable="false"
            :label="$t('Store')"
            :suggestions="stores"
            v-model="localFilters.store_id"
          />
        </div>

        <!-- Type -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="type"
            :clearable="true"
            :searchable="false"
            :label="$t('Type')"
            :suggestions="typeOptions"
            v-model="localFilters.type"
          />
        </div>

        <!-- Start Date -->
        <div class="col-span-6 sm:col-span-3">
          <DateInput id="start_date" :label="$t('Start Date')" v-model="localFilters.start_date" />
        </div>

        <!-- End Date -->
        <div class="col-span-6 sm:col-span-3">
          <DateInput id="end_date" :label="$t('End Date')" v-model="localFilters.end_date" />
        </div>
      </div>

      <div class="flex flex-row justify-between rounded-b-lg bg-gray-100 px-6 py-4 dark:bg-gray-950">
        <Button type="button" @click="resetFilters" class="text-red-600 hover:text-red-700">
          {{ $t('Reset Filters') }}
        </Button>
        <div class="flex gap-3">
          <SecondaryButton @click="closeForm">{{ $t('Cancel') }}</SecondaryButton>
          <LoadingButton :loading="searching">{{ $t('Apply') }}</LoadingButton>
        </div>
      </div>
    </form>
  </Modal>
</template>
