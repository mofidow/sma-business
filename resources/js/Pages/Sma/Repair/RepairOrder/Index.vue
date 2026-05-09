<script setup>
import dayjs from 'dayjs';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';
import { Dropdown, Modal } from '@/Components/Jet';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import QuickView from './QuickView.vue';

const { t } = useI18n();
defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'serviceTypes', 'stores', 'customers', 'custom_fields']);

const view = ref(false);
const current = ref(null);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function editRow(row) {
  router.visit(route('repair-orders.edit', { repair_order: row.id }));
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('repair-orders.destroy', row.id), {
    preserveScroll: true,
    onFinish: () => (deleting.value = false),
  });
}

function generateInvoice(row) {
  router.post(route('repair-orders.generate-invoice', { repair_order: row.id }));
}

function viewInvoice(row) {
  router.get(route('sales.index', { id: row.invoice_id }));
}

const statusOptions = [
  { value: 'pending', label: t('Pending') },
  { value: 'in_progress', label: t('In Progress') },
  { value: 'waiting_parts', label: t('Waiting Parts') },
  { value: 'completed', label: t('Completed') },
  { value: 'delivered', label: t('Delivered') },
  { value: 'cancelled', label: t('Cancelled') },
];

const priorityOptions = [
  { value: 'low', label: t('Low') },
  { value: 'normal', label: t('Normal') },
  { value: 'high', label: t('High') },
  { value: 'urgent', label: t('Urgent') },
];

function getStatusLabel(status) {
  return statusOptions.find(option => option.value === status)?.label || status;
}
function getPriorityLabel(priority) {
  return priorityOptions.find(option => option.value === priority)?.label || priority;
}

function getStatusBadge(status) {
  const colors = {
    pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    waiting_parts: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  };
  return colors[status] || colors.pending;
}

function getPriorityBadge(priority) {
  const colors = {
    low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    normal: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
    high: 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
  };
  return colors[priority] || colors.normal;
}
</script>

<template>
  <Head>
    <title>{{ $t('Repair Orders') }}</title>
  </Head>
  <Header>
    {{ $t('Repair Orders') }}
    <template #subheading> {{ $t('Manage all repair orders') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-repair-orders')" :href="route('repair-orders.create')">
          {{ $t('Add {x}', { x: $t('Repair Order') }) }}
        </Button>
        <Dropdown align="right" width="64" :auto-close="false">
          <template #trigger>
            <button class="-m-2 flex items-center rounded-md p-2.5 transition duration-150 ease-in-out">
              <Icon name="funnel-o" size="size-5" />
              <span class="sr-only">{{ $t('Show Filters') }}</span>
            </button>
          </template>

          <template #content>
            <div class="space-y-3 px-4 py-2">
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Status')"
                  v-model="filters.status"
                  :placeholder="$t('All Statuses')"
                  :suggestions="statusOptions"
                />
              </div>
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Priority')"
                  v-model="filters.priority"
                  :placeholder="$t('All Priorities')"
                  :suggestions="priorityOptions"
                />
              </div>
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Service Type')"
                  v-model="filters.service_type_id"
                  :placeholder="$t('All Service Types')"
                  :suggestions="serviceTypes.map(st => ({ value: st.id, label: st.name }))"
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
                <th v-if="!$page.props.selected_store" scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Store') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Customer') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Service Type') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Serial No') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Technician') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Status') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Priority') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'received_date:asc' ? 'received_date:desc' : 'received_date:asc')"
                  >
                    {{ $t('Received') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('received_date:')"
                      :name="filters.sort == 'received_date:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Due Date') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Price') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  {{ $t('Cost') }}
                </th>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr
                  v-for="row in pagination.data"
                  :key="row.id"
                  :class="[
                    {
                      'bg-yellow-50 dark:bg-yellow-900/50':
                        row.due_date &&
                        dayjs(row.due_date).isBefore(dayjs()) &&
                        !['completed', 'delivered', 'cancelled'].includes(row.status),
                    },
                    {
                      'bg-green-50! dark:bg-green-900/50!': ['completed', 'delivered'].includes(row.status),
                    },
                    {
                      'bg-red-50! dark:bg-red-900/50!': row.status === 'cancelled',
                    },
                  ]"
                >
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ row.reference }}
                  </td>
                  <td v-if="!$page.props.selected_store" @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.store?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.customer?.company || row.customer?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.service_type?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.serial_no || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.technician?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    <span :class="getStatusBadge(row.status)" class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold">
                      {{ $t(getStatusLabel(row.status)) }}
                    </span>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    <span :class="getPriorityBadge(row.priority)" class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold">
                      {{ $t(getPriorityLabel(row.priority)) }}
                    </span>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ $date(row.received_date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ row.due_date ? $date(row.due_date) : '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.price) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ row.actual_cost ? $currency(row.actual_cost) : '' }}
                  </td>
                  <td
                    class="text-focus w-16 py-4 ps-4 pe-3 text-center text-sm font-medium sm:ps-6 lg:ps-8"
                    :class="[
                      {
                        'bg-yellow-100! dark:bg-yellow-950!':
                          row.due_date &&
                          dayjs(row.due_date).isBefore(dayjs()) &&
                          !['completed', 'delivered', 'cancelled'].includes(row.status),
                      },
                      {
                        'bg-green-100! dark:bg-green-950!': ['completed', 'delivered'].includes(row.status),
                      },
                      {
                        'bg-red-100! dark:bg-red-950!': row.status === 'cancelled',
                      },
                    ]"
                  >
                    <div class="text-mute flex items-center gap-4">
                      <button type="button" v-if="row.invoice_id" @click="viewInvoice(row)" class="link">
                        <Icon name="bag" class="size-5" />
                      </button>
                      <button
                        type="button"
                        v-else-if="row.status === 'completed' && $can('create-sales')"
                        @click="generateInvoice(row)"
                        class="link"
                      >
                        <Icon name="bag-o" class="size-5" />
                      </button>
                      <div v-else class="size-5"></div>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        :deleteRow="deleteRow"
                        permission="repair-orders"
                        :record="$t('RepairOrder')"
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

    <Modal :show="view" max-width="2xl" @close="view = false">
      <QuickView :record="current" @close="view = false" :custom_fields="custom_fields" />
    </Modal>
  </div>
</template>
