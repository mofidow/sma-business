<script setup>
import dayjs from 'dayjs';
import { ref } from 'vue';

import Customize from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import QuickView from '@/Pages/Sma/Order/Purchase/QuickView.vue';
import { Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'totals', 'stores', 'users']);

const form = ref(false);
const view = ref(false);
const current = ref(null);

const { filters, searching, sortBy } = PageSearch();

function viewRow(row) {
  current.value = row;
  view.value = true;
}
</script>

<template>
  <Head>
    <title>{{ $t('{x} Report', { x: $t('Purchases') }) }}</title>
  </Head>
  <Header>
    {{ $t('{x} Report', { x: $t('Purchases') }) }}
    <template #subheading>
      <div>{{ $t('Please review the data below') }}</div>
      <div
        class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-bold text-primary-700 dark:text-primary-400"
        v-if="filters && Object.keys(filters).filter(k => filters[k] && k !== 'sort').length > 0"
      >
        {{ $t('Filters Applied') }}:
        <span v-if="filters.start_date"> {{ $t('From') }}: {{ $date(filters.start_date.toString(), null, null, true) }} </span>
        <span v-if="filters.end_date"> {{ $t('To') }}: {{ $date(filters.end_date.toString(), null, null, true) }} </span>
        <span v-if="filters.supplier_id && pagination.data[0]?.supplier">
          {{ $t('Supplier') }}: {{ pagination.data[0]?.supplier?.company }}
        </span>
        <span v-if="filters.store_id && pagination.data[0]?.store"> {{ $t('Store') }}: {{ pagination.data[0]?.store?.name }} </span>
        <span v-if="filters.user_id && pagination.data[0]?.user"> {{ $t('User') }}: {{ pagination.data[0]?.user?.name }} </span>
        <span v-if="filters.products && filters.products.length > 0">
          {{ $t('Products Count') }}: {{ filters.products.length }}, {{ $t('Products Ids') }}: {{ filters.products.join(', ') }}
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
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Purchases') }}</dt>
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
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Amount Paid') }}</dt>
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
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'date:asc' ? 'date:desc' : 'date:asc')"
                  >
                    {{ $t('Date') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('date:')"
                      :name="filters.sort == 'date:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'reference:asc' ? 'reference:desc' : 'reference:asc')"
                  >
                    {{ $t('Reference') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('reference:')"
                      :name="filters.sort == 'reference:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'toStore.name:asc' ? 'toStore.name:desc' : 'toStore.name:asc')"
                  >
                    {{ $t('Supplier') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('toStore.name:')"
                      :name="filters.sort == 'toStore.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'total_items:asc' ? 'total_items:desc' : 'total_items:asc')"
                  >
                    {{ $t('Items') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('total_items:')"
                      :name="filters.sort == 'total_items:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="
                      sortBy(filters?.sort == 'total_tax_amount_count:asc' ? 'total_tax_amount_count:desc' : 'total_tax_amount_count:asc')
                    "
                  >
                    {{ $t('Tax') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('total_tax_amount_count:')"
                      :name="filters.sort == 'total_tax_amount_count:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'grand_total_count:asc' ? 'grand_total_count:desc' : 'grand_total_count:asc')"
                  >
                    {{ $t('Total') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('grand_total_count:')"
                      :name="filters.sort == 'grand_total_count:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'paid:asc' ? 'paid:desc' : 'paid:asc')"
                  >
                    {{ $t('Paid') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('paid:')"
                      :name="filters.sort == 'paid:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <span class="whitespace-nowrap">
                    {{ $t('Due Amount') }}
                  </span>
                </th>
                <!-- <th scope="col" class="py-3.5 ps-4 pe-3 text-center text-sm font-semibold text-focus sm:ps-6 lg:ps-8 w-16">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'due_date:asc' ? 'due_date:desc' : 'due_date:asc')"
                  >
                    {{ $t('Due Date') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('due_date:')"
                      :name="filters.sort == 'due_date:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th> -->
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'store.name:asc' ? 'store.name:desc' : 'store.name:asc')"
                  >
                    {{ $t('Store') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('store.name:')"
                      :name="filters.sort == 'store.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'user.name:asc' ? 'user.name:desc' : 'user.name:asc')"
                  >
                    {{ $t('Created by') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('user.name:')"
                      :name="filters.sort == 'user.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'created_at:asc' ? 'created_at:desc' : 'created_at:asc')"
                  >
                    {{ $t('Created at') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('created_at:')"
                      :name="filters.sort == 'created_at:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <!-- <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-focus">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'details:asc' ? 'details:desc' : 'details:asc')"
                  >
                    {{ $t('Details') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('details:')"
                      :name="filters.sort == 'details:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th> -->
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr
                  :key="row.id"
                  v-for="row in pagination.data"
                  :class="{
                    'bg-red-50 dark:bg-red-950': row.deleted_at || row.return_orders_count > 0,
                    'bg-green-50 dark:bg-green-950': row.paid >= row.grand_total,
                    'bg-yellow-50 dark:bg-yellow-950':
                      ((row.due_date && dayjs(row.due_date).isBefore(dayjs())) || !row.due_date) && row.paid < row.grand_total,
                  }"
                >
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.supplier.company || row.supplier.name }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ row.total_items }} ({{ $number_qty(row.total_quantity) }})
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.total_tax_amount) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm font-bold whitespace-nowrap">
                    {{ $currency(row.grand_total) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.paid) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(Number(row.grand_total) - Number(row.paid)) }}
                  </td>
                  <!-- <td
                    @click="viewRow(row)"
                    class="cursor-pointer whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-focus sm:ps-6 lg:ps-8 w-14"
                  >
                    {{ $datetime(row.due_date) }}
                  </td> -->
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.store?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.user?.name || '' }}
                  </td>
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $datetime(row.created_at) }}
                  </td>
                  <!-- <td @click="viewRow(row)" class="cursor-pointer whitespace-nowrap px-3 py-4 text-sm">
                    <div class="-my-2 min-w-64 line-clamp-2">
                      {{ row.details || '' }}
                    </div>
                  </td> -->
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
      <Customize
        :filters="filters"
        :stores="stores"
        :users="users"
        @close="form = false"
        :fields="['products', 'supplier', 'store', 'user']"
      />
    </Modal>

    <Modal :show="view" max-width="3xl" @close="view = false">
      <QuickView :force="true" :current="current" :fields="[]" @close="view = false" />
    </Modal>
  </div>
</template>
