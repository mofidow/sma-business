<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import QuickView from './QuickView.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown, Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'custom_fields']);

const add = ref(false);
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
  current.value = row;
  view.value = false;
  add.value = true;
}

function emailRow(row) {
  router.post(route('email.payment', { payment: row.id }));
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('payments.destroy', row.id), {
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
    <title>{{ $t('Payments') }}</title>
  </Head>
  <Header>
    {{ $t('Payments') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <template v-if="$can('create-payments')">
          <Button v-if="$page.props.selected_store" type="button" @click="add = true">
            {{ $t('Add {x}', { x: $t('Payment') }) }}
          </Button>
          <Button v-else type="button" @click="() => ($page.props.select_store = true)">
            {{ $t('Add {x}', { x: $t('Payment') }) }}
          </Button>
        </template>
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
                  {{ $t('For') }}
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
                  :class="[
                    row.deleted_at ? 'bg-red-50 dark:bg-red-900/40' : '',
                    row.payment_for == 'Customer' && row.received ? 'bg-green-50 dark:bg-green-900/40' : '',
                    row.payment_for == 'Supplier' && row.received ? 'bg-primary-50 dark:bg-primary-900/40' : '',
                  ]"
                >
                  <td @click="viewRow(row)" class="cursor-pointer py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ $currency(row.amount) }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.method ? $t(row.method) : '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    <template v-if="row.payment_for == 'Customer'">
                      {{ row.customer?.company || row.customer?.name || '' }}
                    </template>
                    <template v-else-if="row.payment_for == 'Supplier'">
                      {{ row.supplier?.company || row.supplier?.name || '' }}
                    </template>
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
                  <td
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="[
                      row.deleted_at ? 'bg-red-100! dark:bg-red-950!' : '',
                      row.payment_for == 'Customer' && row.received ? 'bg-green-100! dark:bg-green-950!' : '',
                      row.payment_for == 'Supplier' && row.received ? 'bg-primary-100! dark:bg-primary-950!' : '',
                    ]"
                  >
                    <div class="text-mute flex items-center justify-end gap-4">
                      <button v-if="$can('email-payments')" type="button" class="link" @click="emailRow(row)">
                        <Icon name="envelope" size="size-5" />
                      </button>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        permission="payments"
                        :deleteRow="deleteRow"
                        :record="$t('Payment')"
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
      <QuickView :current="current" :fields="custom_fields" @close="view = false" :editRow="editRow" />
    </Modal>

    <Modal :show="add" :backdrop="false" max-width="2xl" :closeable="true" @close="hideForm">
      <Form :current="current" :custom_fields="custom_fields" @close="hideForm" @done="hideForm" />
    </Modal>
  </div>
</template>
