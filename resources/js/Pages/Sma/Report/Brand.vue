<script setup>
import { ref } from 'vue';

import Customize from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'brands']);

const form = ref(false);
const { filters, searching, sortBy } = PageSearch();
</script>

<template>
  <Head>
    <title>{{ $t('{x} Report', { x: $t('Brands') }) }}</title>
  </Head>
  <Header>
    {{ $t('{x} Report', { x: $t('Brands') }) }}
    <template #subheading>
      <div>{{ $t('Please review the data below') }}</div>
      <div
        class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-bold text-primary-700 dark:text-primary-400"
        v-if="filters && Object.keys(filters).filter(k => filters[k] && k !== 'sort').length > 0"
      >
        {{ $t('Filters Applied') }}:
        <span v-if="filters.start_date"> {{ $t('From') }}: {{ $date(filters.start_date.toString(), null, null, true) }} </span>
        <span v-if="filters.end_date"> {{ $t('To') }}: {{ $date(filters.end_date.toString(), null, null, true) }} </span>
        <span v-if="filters.store_id">
          {{ $t('Store') }}:
          {{ pagination.data[0]?.store?.name || $page.props.stores.find(s => s.value == filters.store_id)?.label || filters.store_id }}
        </span>
        <span v-if="filters.brand_id"> {{ $t('Brand ID') }}: {{ filters.brand_id }} </span>
        <span v-if="filters.brands && filters.brands.length > 0">
          {{ $t('Brands Count') }}: {{ filters.brands.length }}, {{ $t('Brands Ids') }}: {{ filters.brands.join(', ') }}
        </span>
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

  <dl
    v-if="totals"
    class="grid grid-cols-1 gap-px border-b border-gray-200 bg-gray-200 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-700 dark:bg-gray-700"
  >
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Brands') }}</dt>
      <!-- <dd class="text-xs font-medium">+4.75%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $number(totals?.count || 0, null, { maximumFractionDigits: 0 }) }}
      </dd>
    </div>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Amount') }}</dt>
      <!-- <dd class="text-xs font-medium text-rose-600">+54.02%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(totals?.total || 0) }}
      </dd>
    </div>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Received Amount') }}</dt>
      <!-- <dd class="text-xs font-medium text-rose-600">+10.18%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(totals?.paid || 0) }}
      </dd>
    </div>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Tax') }}</dt>
      <!-- <dd class="text-xs font-medium">-1.39%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(totals?.tax || 0) }}
      </dd>
    </div>
  </dl>

  <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <Loading v-if="searching" circle-size="w-10 h-10" />
    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="my-2 inline-block min-w-full border-b border-gray-200 align-middle dark:border-gray-700">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'name:asc' ? 'name:desc' : 'name:asc')"
                  >
                    {{ $t('Name') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('name:')"
                      :name="filters.sort == 'name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="
                      sortBy(
                        filters?.sort == 'purchase_items_sum_total:asc' ? 'purchase_items_sum_total:desc' : 'purchase_items_sum_total:asc'
                      )
                    "
                  >
                    {{ $t('Purchased') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('purchase_items_sum_total:')"
                      :name="filters.sort == 'purchase_items_sum_total:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'sale_items_sum_total:asc' ? 'sale_items_sum_total:desc' : 'sale_items_sum_total:asc')"
                  >
                    {{ $t('Sold') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('sale_items_sum_total:')"
                      :name="filters.sort == 'sale_items_sum_total:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Profit') }}
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.name }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                      <div>({{ $number_qty(row.purchase_items_sum_quantity || 0) }})</div>
                      <strong>{{ $currency(row.purchase_items_sum_total || 0) }}</strong>
                    </div>
                  </td>
                  <td class="px-3 py-4 text-center text-sm whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                      <div>({{ $number_qty(row.sale_items_sum_quantity || 0) }})</div>
                      <strong>{{ $currency(row.sale_items_sum_total || 0) }}</strong>
                    </div>
                  </td>
                  <td class="text-focus py-4 ps-4 pe-3 text-end text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $currency((row.total_sale_amount || 0) - (row.total_cost || 0)) }}
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="100%">
                  <div class="text-mute py-3.5 ps-4 pe-3 text-sm font-light whitespace-nowrap sm:ps-2 lg:ps-4">
                    {{ $t('There is no data to display!') }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="pagination.meta" :links="pagination.links" />
    </div>

    <Modal :show="form" max-width="2xl" @close="form = false" :overflow="true">
      <Customize :users="users" :stores="stores" :filters="filters" @close="form = false" :brands="brands" :fields="['brands', 'store']" />
    </Modal>
  </div>
</template>
