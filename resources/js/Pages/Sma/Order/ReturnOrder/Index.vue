<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

import QuickView from './QuickView.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown, Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'custom_fields']);

const view = ref(false);
const current = ref(null);
const deleted = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

onMounted(() => {
  if (route().params?.id) {
    viewRow({ id: route().params.id });
  }
});

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function editRow(row) {
  router.visit(route('return_orders.edit', { return_order: row.id }));
}

function emailRow(row) {
  router.post(route('email.return_order', { return_order: row.id }));
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('return_orders.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}
</script>

<template>
  <Head>
    <title>{{ $t('Return Orders') }}</title>
  </Head>
  <Header>
    {{ $t('Return Orders') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-return-orders')" :href="route('return_orders.create')">
          {{ $t('Add {x}', { x: $t('Return Order') }) }}
        </Button>
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
                    @click="sortBy(filters?.sort == 'type:asc' ? 'type:desc' : 'type:asc')"
                  >
                    {{ $t('Type') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('type:')"
                      :name="filters.sort == 'type:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'toStore.name:asc' ? 'toStore.name:desc' : 'toStore.name:asc')"
                  >
                    {{ $t('For') }}
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
                    class="flex items-center gap-2 whitespace-nowrap"
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
                    class="flex items-center gap-2 whitespace-nowrap"
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
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id" :class="{ deleted: row.deleted_at }">
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ $t(row.type) }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    <template v-if="row.type == 'Purchase'">
                      {{ row.supplier.company || row.supplier.name }}
                    </template>
                    <template v-if="row.type == 'Sale'">
                      {{ row.customer.company || row.customer.name }}
                    </template>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ row.total_items }} ({{ $number_qty(row.total_quantity) }})
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.total_tax_amount) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.grand_total) }}
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
                  <td
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="{ deleted: row.deleted_at }"
                  >
                    <div class="text-mute flex items-center justify-end gap-4">
                      <!-- <button type="button" class="link" @click="viewRow(row)">
                        <Icon name="eye" size="size-5" />
                      </button> -->
                      <button type="button" class="link" @click="emailRow(row)">
                        <Icon name="envelope" size="size-5" />
                      </button>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        :deleteRow="deleteRow"
                        permission="return-orders"
                        :record="$t('Return Order')"
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

    <Modal :show="view" max-width="3xl" @close="view = false">
      <QuickView :current="current" :fields="custom_fields" @close="view = false" :editRow="editRow" />
    </Modal>
  </div>
</template>
