<script setup>
import { router, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { onMounted, ref } from 'vue';

import { Actions, AutoComplete, Button, Loading, Pagination, Toggle } from '@/Components/Common';
import { Dropdown, Modal } from '@/Components/Jet';
import { PageSearch } from '@/Core/PageSearch';
import { $can } from '@/Core/helpers';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeliveryForm from '@/Pages/Sma/Order/Delivery/Form.vue';
import DeliveryView from '@/Pages/Sma/Order/Delivery/QuickView.vue';
import PaymentForm from '@/Pages/Sma/Order/Payment/Form.vue';
import AddressForm from '@/Pages/Sma/People/Address/Form.vue';
import QuickView from './QuickView.vue';

const page = usePage();
defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'countries', 'custom_fields', 'payment_fields', 'address_fields', 'delivery_fields']);

const view = ref(false);
const current = ref(null);
const payment = ref(false);
const deleted = ref(false);
const address = ref(false);
const delivery = ref(false);
const deleting = ref(false);
const address_added = ref(false);
const view_delivery = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

onMounted(() => {
  if (route().params?.id) {
    viewRow({ id: route().params.id });
  }
  filters.shop = page.props.filters?.shop == 1;
});

function addDelivery(row) {
  current.value = row;
  delivery.value = true;
}

function addPayment(row) {
  current.value = row;
  payment.value = true;
}

function editDelivery() {
  view_delivery.value = false;
  delivery.value = true;
}

function viewDelivery(row) {
  current.value = row;
  view_delivery.value = true;
}

function viewRow(row) {
  current.value = row;
  view.value = true;
}

// async function refreshCustomer() {
//   await axios.post(route('customers.refresh', { customer: current.value.customer_id, with: 'addresses' })).then(res => {
//     current.value.customer = res.data;
//   });
// }

function addressAdded() {
  address_added.value = true;
  setInterval(() => (address_added.value = false), 500);
  address.value = false;
}

function editRow(row) {
  router.visit(route('sales.edit', { sale: row.id }));
}

function addReturnRow(row) {
  router.visit(route('return_orders.create', { sale_id: row.id }));
}

function emailRow(row) {
  router.post(route('email.sale', { sale: row.id }));
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('sales.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}
</script>

<template>
  <Head>
    <title>{{ $t('Sales') }}</title>
  </Head>
  <Header>
    {{ $t('Sales') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-sales')" :href="route('sales.create')">
          {{ $t('Add {x}', { x: $t('Sale') }) }}
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
              <div v-if="$page.props.shop_module" class="mt-4">
                <Toggle id="stock" :label="$t('Only Shop Sales')" @change="searchNow" v-model="filters.shop" />
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
                    @click="sortBy(filters?.sort == 'customer.company:asc' ? 'customer.company:desc' : 'customer.company:asc')"
                  >
                    {{ $t('Customer') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('customer.company:')"
                      :name="filters.sort == 'customer.company:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Items') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'total_tax_amount_count:asc' ? 'total_tax_amount:desc' : 'total_tax_amount:asc')"
                  >
                    {{ $t('Tax') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('total_tax_amount:')"
                      :name="filters.sort == 'total_tax_amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'grand_total:asc' ? 'grand_total:desc' : 'grand_total:asc')"
                  >
                    {{ $t('Total') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('grand_total:')"
                      :name="filters.sort == 'grand_total:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
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
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <span class="whitespace-nowrap">{{ $t('Due Amount') }}</span>
                </th>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
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
                <tr
                  :key="row.id"
                  v-for="row in pagination.data"
                  :class="
                    row.deleted_at || row.return_orders_count > 0
                      ? 'bg-red-50 dark:bg-red-900/40'
                      : row.paid >= row.grand_total - row.rounding
                        ? 'bg-green-50 dark:bg-green-900/40'
                        : row.due_date && dayjs(row.due_date).isBefore(dayjs()) && row.paid < row.grand_total - row.rounding
                          ? 'bg-yellow-50 dark:bg-yellow-900/40'
                          : ''
                  "
                >
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.customer.company || row.customer.name }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ row.total_items }} ({{ $number_qty(row.total_quantity) }})
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.total_tax_amount) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer text-end text-sm font-bold whitespace-nowrap">
                    {{ $currency(row.grand_total) }}
                  </td>
                  <td
                    @click="viewRow(row)"
                    class="text-focus cursor-pointer py-4 ps-4 pe-3 text-end text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $currency(row.paid) }}
                  </td>
                  <td class="text-focus cursor-pointer py-1 text-end text-sm font-medium whitespace-nowrap">
                    <button
                      type="button"
                      @click="addPayment(row)"
                      class="group relative rounded px-3 py-3 hover:bg-gray-100 dark:hover:bg-gray-700/50"
                      v-if="Number(row.paid) < Number(row.grand_total) - Number(row.rounding) && $can('create-payments')"
                    >
                      {{ $currency(Number(row.grand_total) + Number(row.rounding) - Number(row.paid)) }}
                      <Icon
                        name="plus"
                        size="size-5"
                        class="absolute top-0 -left-4 my-3 text-green-500 opacity-0 transition group-hover:opacity-100"
                      />
                    </button>
                    <button v-else type="button" @click="viewRow(row)" class="px-3 py-4">
                      {{ $currency(Number(row.grand_total) + Number(row.rounding) - Number(row.paid)) }}
                    </button>
                  </td>
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $datetime(row.due_date) }}
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
                    :class="
                      row.deleted_at || row.return_orders_count > 0
                        ? 'bg-red-100! dark:bg-red-950!'
                        : row.paid >= row.grand_total - row.rounding
                          ? 'bg-green-100! dark:bg-green-950!'
                          : row.due_date && dayjs(row.due_date).isBefore(dayjs()) && row.paid < row.grand_total - row.rounding
                            ? 'bg-yellow-100! dark:bg-yellow-950!'
                            : ''
                    "
                  >
                    <div class="text-mute flex items-center justify-end gap-4">
                      <!-- <button type="button" class="link" @click="viewRow(row)">
                        <Icon name="eye" size="size-5" />
                      </button> -->
                      <button v-if="$can('email-sales')" type="button" class="link" @click="emailRow(row)">
                        <Icon name="envelope" size="size-5" />
                      </button>
                      <template v-if="row.delivery">
                        <button v-if="$can('read-deliveries')" type="button" class="link" @click="viewDelivery(row)">
                          <Icon name="truck" size="size-5" />
                        </button>
                      </template>
                      <template v-else-if="$can('create-deliveries')">
                        <button type="button" class="link" @click="row.repair_order_id ? () => {} : addDelivery(row)">
                          <Icon name="truck-o" size="size-5" />
                        </button>
                      </template>
                      <button
                        class="link"
                        type="button"
                        :title="$t('Add Return')"
                        @click="addReturnRow(row)"
                        v-if="$can('create-return-orders')"
                      >
                        <Icon name="return" size="size-5" />
                      </button>
                      <Actions
                        :row="row"
                        :deleted="deleted"
                        permission="sales"
                        :deleting="deleting"
                        :record="$t('Sale')"
                        :deleteRow="deleteRow"
                        :editRow="row.repair_order_id ? () => {} : editRow"
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
      <QuickView
        :current="current"
        @close="view = false"
        :custom_fields="custom_fields"
        :editRow="current?.repair_order_id ? () => {} : editRow"
      />
    </Modal>

    <Modal :show="payment" max-width="2xl" @close="payment = false">
      <PaymentForm :sale="current" :customer="current?.customer" :custom_fields="payment_fields" @close="payment = false" />
    </Modal>

    <Modal :show="view_delivery" max-width="2xl" @close="view_delivery = false">
      <DeliveryView :current="current.delivery" :editRow="editDelivery" :custom_fields="delivery_fields" @close="view_delivery = false" />
    </Modal>

    <Modal :show="delivery" max-width="2xl" @close="delivery = false">
      <DeliveryForm
        :sale_id="current?.id"
        :countries="countries"
        @close="delivery = false"
        @address="address = true"
        :current="current?.delivery"
        :customer="current?.customer"
        :get_addresses="address_added"
        :custom_fields="delivery_fields"
      />
    </Modal>

    <Modal :show="address" max-width="2xl" @close="address = false">
      <AddressForm
        @done="addressAdded"
        :countries="countries"
        @close="address = false"
        :customer="current?.customer"
        :custom_fields="address_fields"
      />
    </Modal>
  </div>
</template>
