<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';

import Customize from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'users']);

const form = ref(false);
const { filters, searching, sortBy } = PageSearch();

function viewRow(row) {
  router.visit(route('purchases.report', { filters: { supplier_id: row.id } }));
}
</script>

<template>
  <Head>
    <title>{{ $t('{x} Report', { x: $t('Suppliers') }) }}</title>
  </Head>
  <Header>
    {{ $t('{x} Report', { x: $t('Suppliers') }) }}
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
        <span v-if="filters.user_id"> {{ $t('User') }}: {{ pagination.data[0]?.user?.name || filters.user_id }} </span>
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
                    @click="sortBy(filters?.sort == 'company:asc' ? 'company:desc' : 'company:asc')"
                  >
                    {{ $t('Company') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('company:')"
                      :name="filters.sort == 'company:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'phone:asc' ? 'phone:desc' : 'phone:asc')"
                  >
                    {{ $t('Contact') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('phone:')"
                      :name="filters.sort == 'phone:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'purchases:asc' ? 'purchases:desc' : 'purchases:asc')"
                  >
                    {{ $t('Purchases') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('purchases:')"
                      :name="filters.sort == 'purchases:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'amount:asc' ? 'amount:desc' : 'amount:asc')"
                  >
                    {{ $t('Purchases Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('amount:')"
                      :name="filters.sort == 'amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'paid:asc' ? 'paid:desc' : 'paid:asc')"
                  >
                    {{ $t('Paid Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('paid:')"
                      :name="filters.sort == 'paid:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Balance Amount') }}
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td
                    @click="viewRow(row)"
                    class="text-focus cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    <div>{{ row.company }}</div>
                    <div class="text-mute">
                      <span>{{ $t('Name') }}</span
                      >: {{ row.name }}
                    </div>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    <div>{{ row.phone }}</div>
                    <div class="text-mute">{{ row.email }}</div>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ $number(row.purchases || 0, null, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.amount || 0) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(Number(row.paid || 0)) }}
                  </td>
                  <td
                    @click="viewRow(row)"
                    class="text-focus cursor-pointer py-4 ps-4 pe-3 text-end text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $currency(Number(row.amount || 0) - Number(row.paid || 0)) }}
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
      <Customize :users="users" :stores="stores" :filters="filters" @close="form = false" :fields="['store', 'user']" />
    </Modal>
  </div>
</template>
