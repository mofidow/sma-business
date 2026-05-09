<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown, Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaymentForm from '@/Pages/Sma/Order/Payment/Form.vue';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'countries', 'customer_groups', 'price_groups', 'custom_fields', 'payment_fields']);

const add = ref(false);
const view = ref(false);
const current = ref(null);
const payment = ref(false);
const deleted = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function showPaymentModal(row) {
  current.value = row;
  payment.value = true;
}

function editRow(row) {
  current.value = row;
  add.value = true;
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('customers.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}

function hideForm() {
  if (!view.value) {
    current.value = null;
  }
  add.value = false;
}
</script>

<template>
  <Head>
    <title>{{ $t('Customers') }}</title>
  </Head>
  <Header>
    {{ $t('Customers') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-customers')" type="button" @click="add = true">
          {{ $t('Add {x}', { x: $t('Customer') }) }}
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
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold whitespace-nowrap sm:ps-6 lg:ps-8">
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'phone:asc' ? 'phone:desc' : 'phone:asc')"
                  >
                    {{ $t('Phone') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('phone:')"
                      :name="filters.sort == 'phone:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'email:asc' ? 'email:desc' : 'email:asc')"
                  >
                    {{ $t('Email') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('email:')"
                      :name="filters.sort == 'email:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'points:asc' ? 'points:desc' : 'points:asc')"
                  >
                    {{ $t('Points') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('points:')"
                      :name="filters.sort == 'points:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold whitespace-nowrap">
                  {{ $t('Due') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'due_limit:asc' ? 'due_limit:desc' : 'due_limit:asc')"
                  >
                    {{ $t('Due Limit') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('due_limit:')"
                      :name="filters.sort == 'due_limit:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'customer_group.name:asc' ? 'customer_group.name:desc' : 'customer_group.name:asc')"
                  >
                    {{ $t('Customer Group') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('customer_group.name:')"
                      :name="filters.sort == 'customer_group.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'price_group.name:asc' ? 'price_group.name:desc' : 'price_group.name:asc')"
                  >
                    {{ $t('Price Group') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('price_group.name:')"
                      :name="filters.sort == 'price_group.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Address') }}
                </th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td @click="viewRow(row)" class="cursor-pointer py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.company || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.name }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.phone || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.email || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $number(row.points, null, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap">
                    <template v-if="$can('create-payments')">
                      <button type="button" @click="showPaymentModal(row)" class="link">
                        {{ $currency(row.balance) }}
                      </button>
                    </template>
                    <template v-else>
                      {{ $currency(row.balance) }}
                    </template>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ row.due_limit ? $number(row.due_limit) : '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.customer_group?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.price_group?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm">
                    <div class="-my-2 line-clamp-2 max-w-xs min-w-64">
                      {{ $address(row) }}
                    </div>
                  </td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <div class="text-mute flex items-center gap-4">
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        :deleteRow="deleteRow"
                        permission="addresses"
                        :record="$t('Address')"
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

    <Modal :show="add" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="hideForm">
      <Form
        :current="current"
        @close="hideForm"
        @done="hideForm"
        :countries="countries"
        :price_groups="price_groups"
        :customer_groups="customer_groups"
        :custom_fields="custom_fields"
      />
    </Modal>

    <Modal :show="payment" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="payment = false">
      <PaymentForm :current="null" :customer="current" :custom_fields="payment_fields" @close="payment = false" @done="payment = false" />
    </Modal>
  </div>
</template>
