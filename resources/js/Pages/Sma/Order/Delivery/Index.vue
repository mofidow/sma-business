<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import QuickView from './QuickView.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown, Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, AutoComplete, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'custom_fields', 'address_fields']);

const add = ref(false);
const view = ref(false);
const current = ref(null);
const deleted = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function editRow(row) {
  current.value = row;
  view.value = false;
  add.value = true;
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('deliveries.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}

function hideForm() {
  current.value = null;
  add.value = false;
}
</script>

<template>
  <Head>
    <title>{{ $t('Deliveries') }}</title>
  </Head>
  <Header>
    {{ $t('Deliveries') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <!-- <Button type="button" @click="add = true">
          {{ $t('Add {x}', { x: $t('Delivery') }) }}
        </Button> -->
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
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8">
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
                    @click="sortBy(filters?.sort == 'sale.reference:asc' ? 'sale.reference:desc' : 'sale.reference:asc')"
                  >
                    {{ $t('Sale Reference') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('sale.reference:')"
                      :name="filters.sort == 'sale.reference:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'customer:asc' ? 'customer:desc' : 'customer:asc')"
                  >
                    {{ $t('Customer') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('customer:')"
                      :name="filters.sort == 'customer:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <!-- <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'address:asc' ? 'address:desc' : 'address:asc')"
                  > -->
                  {{ $t('Address') }}
                  <!-- <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('address:')"
                      :name="filters.sort == 'address:desc' ? 'c-up' : 'c-down'"
                    />
                  </button> -->
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'delivered_at:asc' ? 'delivered_at:desc' : 'delivered_at:asc')"
                  >
                    {{ $t('Delivered at') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('delivered_at:')"
                      :name="filters.sort == 'delivered_at:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'delivered_by:asc' ? 'delivered_by:desc' : 'delivered_by:asc')"
                  >
                    {{ $t('Delivered by') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('delivered_by:')"
                      :name="filters.sort == 'delivered_by:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'received_by:asc' ? 'received_by:desc' : 'received_by:asc')"
                  >
                    {{ $t('Received by') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('received_by:')"
                      :name="filters.sort == 'received_by:desc' ? 'c-up' : 'c-down'"
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
                      size="size-3"
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
                <tr v-for="row in pagination.data" :key="row.id" :class="row.deleted_at ? 'bg-red-100 dark:bg-red-950' : ''">
                  <td @click="viewRow(row)" class="cursor-pointer py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.sale.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.customer?.company || row.customer?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm">
                    <div class="-my-2 line-clamp-2 min-w-64">
                      {{ $address(row.address) }}
                    </div>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.delivered_at ? $datetime(row.delivered_at) : '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.delivered_by || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.received_by || '' }}</td>
                  <!-- <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm">
                    <div class="-my-2 min-w-64 line-clamp-2">
                      {{ row.details || '' }}
                    </div>
                  </td> -->
                  <td
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="row.deleted_at ? 'deleted' : ''"
                  >
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      :deleting="deleting"
                      :deleteRow="deleteRow"
                      permission="deliveries"
                      :record="$t('Delivery')"
                    />
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

    <Modal :show="view" max-width="2xl" @close="view = false">
      <QuickView :current="current" :fields="custom_fields" @close="view = false" :editRow="editRow" />
    </Modal>

    <Modal :show="add" :backdrop="false" max-width="2xl" :closeable="true" @close="hideForm">
      <Form
        :current="current"
        :customer="current?.customer"
        :address_fields="address_fields"
        :custom_fields="custom_fields"
        @close="hideForm"
        @done="hideForm"
      />
    </Modal>
  </div>
</template>
