<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import { PageSearch } from '@/Core/PageSearch';
import { Dropdown } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, AutoComplete, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'users']);

const deleted = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('orders.destroy', row.number || row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}
</script>

<template>
  <Head>
    <title>{{ $t('Orders') }}</title>
  </Head>
  <Header>
    {{ $t('Orders') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Dropdown align="right" width="56" :auto-close="false">
          <template #trigger>
            <button class="-m-2 flex items-center rounded-md p-2.5 transition duration-150 ease-in-out">
              <Icon name="funnel-o" size="size-5" />
              <span class="sr-only">{{ $t('Show Filters') }}</span>
            </button>
          </template>

          <template #content>
            <div class="px-4 py-2">
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Trashed')"
                  v-model="filters.trashed"
                  :placeholder="$t('With Trashed')"
                  :suggestions="[
                    { value: 'not', label: $t('Not Trashed') },
                    { value: 'with', label: $t('With Trashed') },
                    { value: 'only', label: $t('Only Trashed') },
                  ]"
                />
              </div>
            </div>
          </template>
        </Dropdown>
      </div>
    </template>
  </Header>

  <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <Loading v-if="searching" circle-size="w-10 h-10" />
    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="my-2 inline-block min-w-full border-b border-gray-200 align-middle dark:border-gray-700">
          <table class="fixed-actions min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'number:asc' ? 'number:desc' : 'number:asc')"
                  >
                    {{ $t('Number') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('number:')"
                      :name="filters.sort == 'number:desc' ? 'c-up' : 'c-down'"
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
                    @click="sortBy(filters?.sort == 'customer.name:asc' ? 'customer.name:desc' : 'customer.name:asc')"
                  >
                    {{ $t('Customer') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('customer.name:')"
                      :name="filters.sort == 'customer.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th v-if="$page.props.settings.restaurant" scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Table') }}
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
                    @click="sortBy(filters?.sort == 'total:asc' ? 'total:desc' : 'total:asc')"
                  >
                    {{ $t('Total') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('total:')"
                      :name="filters.sort == 'total:desc' ? 'c-up' : 'c-down'"
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
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr
                  v-for="row in pagination.data"
                  :key="row.id"
                  :class="
                    row.deleted_at || row.return_orders_count > 0
                      ? 'bg-red-50 dark:bg-red-950'
                      : row.store_id != $page.props.selected_store
                        ? 'bg-yellow-50 dark:bg-yellow-950'
                        : ''
                  "
                >
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.store?.name || '' }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.number }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.customer?.company || row.customer?.name || '' }}
                  </td>
                  <td v-if="$page.props.settings.restaurant" class="px-3 py-4 text-sm whitespace-nowrap">
                    <span v-if="row.table">{{ row.table.name }} ({{ row.hall?.name || '' }})</span>
                    <span v-else-if="row.reference_number" class="text-gray-500 italic">{{ row.reference_number }}</span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ row.total_items }} ({{ $number_qty(row.total_quantity) }})
                  </td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.total) }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.user?.name || '' }}
                  </td>
                  <td
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="{ deleted: row.deleted_at || row.return_orders_count > 0 }"
                  >
                    <div class="text-mute flex items-center justify-end gap-4">
                      <Link v-if="!row.deleted_at" :href="route('pos', { order: row.id })">
                        <Icon name="pos-o" size="size-5" />
                      </Link>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        permission="orders"
                        :deleting="deleting"
                        :record="$t('Order')"
                        :deleteRow="deleteRow"
                      />
                    </div>
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
  </div>
</template>
