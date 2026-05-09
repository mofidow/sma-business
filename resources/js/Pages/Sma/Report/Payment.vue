<script setup>
import Customize from './Form.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { PageSearch } from '@/Core/PageSearch';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import QuickView from '@/Pages/Sma/Order/Payment/QuickView.vue';
import { Button, Loading, Pagination } from '@/Components/Common';

const page = usePage();
defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'received', 'sent', 'stores', 'users']);

const form = ref(false);
const view = ref(false);
const current = ref(null);
const { filters, searching, sortBy } = PageSearch();

const defaultMethods = ['Cash', 'Credit Card', 'Gift Card', 'Card Terminal', 'Stripe Terminal', 'Others'];
const paymentMethods = computed(() => {
  const methods = page.props.settings?.payment?.static_payment_methods;
  return [...defaultMethods, ...(methods || [])].map(m => ({ value: m, label: m }));
});

function viewRow(row) {
  current.value = row;
  view.value = true;
}
</script>

<template>
  <Head>
    <title>{{ $t('{x} Report', { x: $t('Payments') }) }}</title>
  </Head>
  <Header>
    {{ $t('{x} Report', { x: $t('Payments') }) }}
    <template #subheading>
      <div>{{ $t('Please review the data below') }}</div>
      <div
        class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-bold text-primary-700 dark:text-primary-400"
        v-if="filters && Object.keys(filters).filter(k => filters[k] && k !== 'sort').length > 0"
      >
        {{ $t('Filters Applied') }}:
        <span v-if="filters.start_date"> {{ $t('From') }}: {{ $date(filters.start_date.toString(), null, null, true) }} </span>
        <span v-if="filters.end_date"> {{ $t('To') }}: {{ $date(filters.end_date.toString(), null, null, true) }} </span>
        <span v-if="filters.method"> {{ $t('Method') }}: {{ filters.method }} </span>
        <span v-if="filters.customer_id && pagination.data[0]?.customer">
          {{ $t('Customer') }}: {{ pagination.data[0]?.customer?.company || pagination.data[0]?.customer?.name }}
        </span>
        <span v-if="filters.supplier_id && pagination.data[0]?.supplier">
          {{ $t('Supplier') }}: {{ pagination.data[0]?.supplier?.name }}
        </span>
        <span v-if="filters.store_id && pagination.data[0]?.store"> {{ $t('Store') }}: {{ pagination.data[0]?.store?.name }} </span>
        <span v-if="filters.user_id && pagination.data[0]?.user"> {{ $t('User') }}: {{ pagination.data[0]?.user?.name }} </span>
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
    v-if="received || sent"
    class="grid grid-cols-1 gap-px border-b border-gray-200 bg-gray-200 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-700 dark:bg-gray-700"
  >
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Payment Received') }}</dt>
      <!-- <dd class="text-xs font-medium">+4.75%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(received?.total || 0) }}
        <span class="text-base font-normal">({{ $number(received?.count || 0, null, { maximumFractionDigits: 0 }) }})</span>
      </dd>
    </div>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Customers Due') }}</dt>
      <!-- <dd class="text-xs font-medium text-rose-600">+54.02%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(received?.due || 0) }}
      </dd>
    </div>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Payments Sent') }}</dt>
      <!-- <dd class="text-xs font-medium text-rose-600">+10.18%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(sent?.total || 0) }}
        <span class="text-base font-normal">({{ $number(sent?.count || 0, null, { maximumFractionDigits: 0 }) }})</span>
      </dd>
    </div>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-6 sm:px-6 xl:px-8 dark:bg-gray-900">
      <dt class="text-mute text-sm leading-6 font-medium">{{ $t('Suppliers Due') }}</dt>
      <!-- <dd class="text-xs font-medium">-1.39%</dd> -->
      <dd class="text-focus w-full flex-none text-3xl leading-10 font-medium tracking-tight">
        {{ $currency(sent?.due || 0) }}
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
                    @click="sortBy(filters?.sort == 'amount:asc' ? 'amount:desc' : 'amount:asc')"
                  >
                    {{ $t('Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('amount:')"
                      :name="filters.sort == 'amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'method:asc' ? 'method:desc' : 'method:asc')"
                  >
                    {{ $t('Method') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('method:')"
                      :name="filters.sort == 'method:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <!-- <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'customer.company:asc' ? 'customer.company:desc' : 'customer.company:asc')"
                  > -->
                  {{ $t('For') }}
                  <!-- <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('customer.company:')"
                      :name="filters.sort == 'customer.company:desc' ? 'c-up' : 'c-down'"
                    />
                  </button> -->
                </th>
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
                    @click="sortBy(filters?.sort == 'method_data:asc' ? 'method_data:desc' : 'method_data:asc')"
                  >
                    {{ $t('Data') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('method_data:')"
                      :name="filters.sort == 'method_data:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th> -->
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
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr
                  v-for="row in pagination.data"
                  :key="row.id"
                  :class="[
                    row.deleted_at ? 'bg-red-50 dark:bg-red-950' : '',
                    row.received == 1 && row.payment_for == 'Customer' ? 'bg-green-50 dark:bg-green-950' : '',
                    row.received == 1 && row.payment_for == 'Supplier' ? 'bg-yellow-50 dark:bg-yellow-950' : '',
                  ]"
                >
                  <td @click="viewRow(row)" class="cursor-pointer py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ $currency(row.amount) }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row?.method ? $t(row.method) : '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    <template v-if="row.payment_for == 'Customer'">
                      <span class="text-mute">{{ $t('Received from') }}</span
                      >: {{ row.customer.company || row.customer.name || '' }}
                    </template>
                    <template v-else-if="row.payment_for == 'Supplier'">
                      <span class="text-mute">{{ $t('Sent to') }}</span
                      >: {{ row.supplier.company || row.supplier.name || '' }}
                    </template>
                  </td>
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
                  <!-- <td @click="viewRow(row)" class="cursor-pointer capitalize whitespace-nowrap px-3 py-4 text-sm">
                    {{
                      row.method_data
                        ? Object.keys(row.method_data)
                            .map(k => $t(k.replaceAll('_', ' ')) + ': ' + row.method_data[k])
                            .join(', ')
                        : ''
                    }}
                  </td> -->
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
        :users="users"
        :stores="stores"
        :filters="filters"
        @close="form = false"
        :paymentMethods="paymentMethods"
        :fields="['customer', 'supplier', 'store', 'user', 'payment_methods']"
      />
    </Modal>

    <Modal :show="view" max-width="3xl" @close="view = false">
      <QuickView :force="true" :current="current" :fields="[]" @close="view = false" />
    </Modal>
  </div>
</template>
