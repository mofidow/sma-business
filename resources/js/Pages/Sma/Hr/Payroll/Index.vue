<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, LoadingButton, Pagination } from '@/Components/Common';
import { axios } from '@/Core';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'employees']);

const add = ref(false);
const markingPaid = ref(null);
const current = ref(null);
const deleting = ref(false);
const deleted = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

const statusClasses = {
  draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  processed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
};

const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const confirmMarkPaid = row => (markingPaid.value = row);

const submitMarkPaid = () => {
  router.post(
    route('payrolls.mark-paid', markingPaid.value.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => (markingPaid.value = null),
    }
  );
};

const editRow = row => {
  axios.get(route('payrolls.show', row.id)).then(({ data }) => {
    current.value = data;
    add.value = true;
  });
};

const hideForm = () => {
  current.value = null;
  add.value = false;
};

const deleteRow = row => {
  deleting.value = true;
  router.delete(route('payrolls.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
};
</script>

<template>
  <Head
    ><title>{{ $t('Payroll') }}</title></Head
  >
  <Header>
    {{ $t('Payroll') }}
    <template #subheading>{{ $t('Please review the data below') }}</template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-payrolls')" @click="add = true">
          {{ $t('Add {x}', { x: $t('Payroll') }) }}
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
                  :suggestions="[
                    { value: 'draft', label: $t('Draft') },
                    { value: 'processed', label: $t('Processed') },
                    { value: 'paid', label: $t('Paid') },
                  ]"
                />
              </div>
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Store')"
                  :suggestions="stores"
                  v-model="filters.store_id"
                  :placeholder="$t('All Stores')"
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
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8">{{ $t('Title') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Period') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Payslips') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Total Amount') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Status') }}</th>
                <th scope="col" class="relative w-24 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination?.data?.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium sm:ps-6 lg:ps-8">{{ row.title }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ monthNames[row.month - 1] }} {{ row.year }}</td>
                  <td class="px-3 py-4 text-center text-sm">{{ row.payslips_count }}</td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap">{{ $currency(row.total_amount) }}</td>
                  <td class="px-3 py-4 text-center text-sm whitespace-nowrap">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="statusClasses[row.status]">
                      {{ $t(row.status) }}
                    </span>
                  </td>
                  <td
                    class="relative w-24 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="row.deleted_at ? 'deleted' : ''"
                  >
                    <div class="flex items-center gap-2">
                      <Link
                        :href="route('payrolls.show', row.id)"
                        class="me-3 text-xs text-primary-600 hover:underline dark:text-primary-400"
                      >
                        <Icon name="eye" />
                      </Link>
                      <button
                        v-if="row.status === 'processed' && $can('update-payrolls')"
                        type="button"
                        class="me-3 text-xs text-primary-600 hover:underline dark:text-primary-400"
                        @click="confirmMarkPaid(row)"
                      >
                        {{ $t('Mark Paid') }}
                      </button>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        :deleteRow="deleteRow"
                        permission="payrolls"
                        :record="$t('Payroll')"
                      />
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="100%">
                  <div class="text-mute py-3.5 ps-4 text-sm font-light">{{ $t('There is no data to display!') }}</div>
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

    <!-- Payroll Form Modal -->
    <Modal :show="add" max-width="4xl" @close="hideForm">
      <Form :current="current" :stores="stores" :employees="employees" @close="hideForm" />
    </Modal>

    <!-- Mark Paid Confirmation -->
    <Modal :show="!!markingPaid" max-width="sm" @close="markingPaid = null">
      <div class="p-6">
        <h2 class="text-focus text-base font-semibold">{{ $t('Mark as Paid') }}</h2>
        <p class="text-mute mt-2 text-sm">{{ $t('Are you sure you want to mark this payroll as paid? This action cannot be undone.') }}</p>
        <div class="mt-5 flex justify-end gap-3">
          <button type="button" class="text-mute text-sm" @click="markingPaid = null">{{ $t('Cancel') }}</button>
          <LoadingButton @click="submitMarkPaid">{{ $t('Mark as Paid') }}</LoadingButton>
        </div>
      </div>
    </Modal>
  </div>
</template>
