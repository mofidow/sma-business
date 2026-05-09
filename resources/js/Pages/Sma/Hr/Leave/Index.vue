<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, LoadingButton, Pagination, Textarea } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'employees', 'leave_types']);

const add = ref(false);
const current = ref(null);
const deleting = ref(false);
const deleted = ref(false);
const approving = ref(null);
const { filters, searching, searchNow, sortBy } = PageSearch();

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

const approveForm = useForm({ status: '', notes: '' });

const editRow = row => {
  current.value = row;
  add.value = true;
};
const hideForm = () => {
  current.value = null;
  add.value = false;
};
const deleteRow = row => {
  deleting.value = true;
  router.delete(route('leaves.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
};

const openApprove = row => {
  approving.value = row;
  approveForm.reset();
};

const handleApprove = status => {
  approveForm.status = status;
  approveForm.post(route('leaves.approve', approving.value.id), {
    preserveScroll: true,
    onSuccess: () => (approving.value = null),
  });
};
</script>

<template>
  <Head
    ><title>{{ $t('Leaves') }}</title></Head
  >
  <Header>
    {{ $t('Leaves') }}
    <template #subheading>{{ $t('Please review the data below') }}</template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-leaves')" @click="add = true">{{ $t('Add {x}', { x: $t('Leave') }) }}</Button>
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
                    { value: 'pending', label: $t('Pending') },
                    { value: 'approved', label: $t('Approved') },
                    { value: 'rejected', label: $t('Rejected') },
                  ]"
                />
              </div>
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Employee')"
                  v-model="filters.employee_id"
                  :placeholder="$t('All Employees')"
                  :suggestions="employees.map(e => ({ value: e.id, label: e.user?.name }))"
                />
              </div>
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Leave Type')"
                  v-model="filters.leave_type_id"
                  :placeholder="$t('All Types')"
                  :suggestions="leave_types.map(t => ({ value: t.id, label: t.name }))"
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
                  {{ $t('Employee') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Leave Type') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Period') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Days') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Status') }}</th>
                <th scope="col" class="relative w-32 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination?.data?.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.employee?.user?.name }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.leave_type?.name }}
                    <span v-if="row.leave_type && !row.leave_type.is_paid" class="ms-1 text-xs text-red-500">({{ $t('Unpaid') }})</span>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ $date(row.start_date) }} → {{ $date(row.end_date) }}</td>
                  <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ row.days }}</td>
                  <td class="px-3 py-4 text-center text-sm whitespace-nowrap">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusClasses[row.status]">
                      {{ $t(row.status.charAt(0).toUpperCase() + row.status.slice(1)) }}
                    </span>
                  </td>
                  <td
                    class="relative w-32 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="row.deleted_at ? 'deleted' : ''"
                  >
                    <div class="flex items-center justify-end gap-3">
                      <button
                        v-if="row.status === 'pending' && $can('update-leaves')"
                        type="button"
                        class="link text-xs"
                        @click="openApprove(row)"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="size-5"
                        >
                          <path d="M8 21s-4-3-4-9 4-9 4-9" />
                          <path d="M16 3s4 3 4 9-4 9-4 9" />
                          <line x1="15" x2="9" y1="9" y2="15" />
                          <line x1="9" x2="15" y1="9" y2="15" />
                        </svg>
                      </button>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        :deleteRow="deleteRow"
                        permission="leaves"
                        :record="$t('Leave')"
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

    <Modal :show="add" max-width="2xl" @close="hideForm">
      <Form :current="current" :employees="employees" :leave_types="leave_types" @close="hideForm" />
    </Modal>

    <!-- Approve / Reject Modal -->
    <Modal :show="!!approving" max-width="md" @close="approving = null">
      <div v-if="approving" class="p-6">
        <h2 class="text-focus text-base font-semibold">{{ $t('Review Leave Request') }}</h2>
        <p class="text-mute mt-1 text-sm">
          {{ approving.employee?.user?.name }} — {{ approving.leave_type?.name }} ({{ approving.days }} {{ $t('days') }})
        </p>
        <div class="mt-4">
          <Textarea rows="2" :label="$t('Notes (optional)')" v-model="approveForm.notes" />
        </div>
        <div class="mt-4 flex justify-end gap-3">
          <button type="button" class="btn-secondary" @click="approving = null">{{ $t('Cancel') }}</button>
          <LoadingButton type="button" variant="danger" :loading="approveForm.processing" @click="handleApprove('rejected')">{{
            $t('Reject')
          }}</LoadingButton>
          <LoadingButton type="button" :loading="approveForm.processing" @click="handleApprove('approved')">{{
            $t('Approve')
          }}</LoadingButton>
        </div>
      </div>
    </Modal>
  </div>
</template>
