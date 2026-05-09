<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, DateInput, Loading, LoadingButton, Pagination } from '@/Components/Common';

const { t } = useI18n();
defineOptions({ layout: AdminLayout });
const props = defineProps(['pagination', 'stores', 'employees']);

const add = ref(false);
const bulk = ref(false);
const current = ref(null);
const deleting = ref(false);
const deleted = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

const statusOptions = [
  { value: 'present', label: t('Present') },
  { value: 'absent', label: t('Absent') },
  { value: 'late', label: t('Late') },
  { value: 'half_day', label: t('Half Day') },
  { value: 'holiday', label: t('Holiday') },
  { value: 'on_leave', label: t('On Leave') },
];

const statusClasses = {
  present: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  absent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  late: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  half_day: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  holiday: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
  on_leave: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
};

// Bulk sheet form
const bulkForm = useForm({
  store_id: null,
  date: new Date().toISOString().slice(0, 10),
  records: [],
});

const bulkEmployees = computed(() => props.employees.filter(e => !bulkForm.store_id || e.store_id == bulkForm.store_id));

const initBulk = () => {
  bulkForm.records = bulkEmployees.value.map(e => ({
    employee_id: e.id,
    name: e.user?.name,
    status: 'present',
    clock_in: '',
    clock_out: '',
    note: '',
  }));
  bulk.value = true;
};

const submitBulk = () => {
  bulkForm.post(route('attendances.bulk'), {
    preserveScroll: true,
    onSuccess: () => (bulk.value = false),
  });
};

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
  router.delete(route('attendances.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
};
</script>

<template>
  <Head
    ><title>{{ $t('Attendance') }}</title></Head
  >
  <Header>
    {{ $t('Attendance') }}
    <template #subheading>{{ $t('Please review the data below') }}</template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-attendances')" variant="secondary" @click="initBulk">
          {{ $t('Monthly Sheet') }}
        </Button>
        <Button v-if="$can('create-attendances')" @click="add = true">
          {{ $t('Add {x}', { x: $t('Attendance') }) }}
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
                  :label="$t('Store')"
                  :suggestions="stores"
                  v-model="filters.store_id"
                  :placeholder="$t('All Stores')"
                />
              </div>
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Status')"
                  v-model="filters.status"
                  :suggestions="statusOptions"
                  :placeholder="$t('All Statuses')"
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Date') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Clock In') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Clock Out') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Hours') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Overtime') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Status') }}</th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
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
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ $date(row.date) }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ $time(row.clock_in || '') || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ $time(row.clock_out || '') || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.hours_worked ? $number(row.hours_worked) : '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.overtime_hours > 0 ? $number(row.overtime_hours) : '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusClasses[row.status]">
                      {{ $t(statusOptions.find(s => s.value === row.status)?.label || row.status) }}
                    </span>
                  </td>
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
                      permission="attendances"
                      :record="$t('Attendance')"
                    />
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

    <!-- Single Attendance Form Modal -->
    <Modal :show="add" max-width="2xl" @close="hideForm">
      <Form :current="current" :stores="stores" :employees="employees" @close="hideForm" />
    </Modal>

    <!-- Bulk Monthly Sheet Modal -->
    <Modal :show="bulk" max-width="4xl" @close="bulk = false" :overflow="true">
      <div class="p-6">
        <h2 class="text-focus text-base font-semibold">{{ $t('Monthly Attendance Sheet') }}</h2>
        <p class="text-mute mt-1 text-sm">{{ $t('Mark attendance for all employees at once') }}</p>

        <div class="mt-4 grid grid-cols-6 gap-4">
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              :label="$t('Store')"
              :suggestions="stores"
              v-model="bulkForm.store_id"
              :error="bulkForm.errors.store_id"
              :placeholder="$t('Select store')"
              @change="
                bulkForm.records = bulkEmployees.map(e => ({
                  employee_id: e.id,
                  name: e.user?.name,
                  status: 'present',
                  clock_in: '',
                  clock_out: '',
                  note: '',
                }))
              "
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <DateInput type="date" :label="$t('Date')" :required="true" v-model="bulkForm.date" :error="bulkForm.errors.date" />
          </div>
        </div>

        <div v-if="bulkForm.records.length" class="mt-6 overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th class="text-focus py-2 ps-2 text-start text-xs font-semibold">{{ $t('Employee') }}</th>
                <th class="text-focus w-36 px-2 py-2 text-start text-xs font-semibold">{{ $t('Status') }}</th>
                <th class="text-focus w-32 px-2 py-2 text-start text-xs font-semibold">{{ $t('Clock In') }}</th>
                <th class="text-focus w-32 px-2 py-2 text-start text-xs font-semibold">{{ $t('Clock Out') }}</th>
                <th class="text-focus px-2 py-2 text-start text-xs font-semibold">{{ $t('Note') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="(record, i) in bulkForm.records" :key="record.employee_id">
                <td class="py-2 ps-2 text-sm font-medium whitespace-nowrap">{{ record.name }}</td>
                <td class="px-2 py-1">
                  <select
                    v-model="record.status"
                    class="input-sm w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700"
                  >
                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ $t(s.label) }}</option>
                  </select>
                </td>
                <td class="px-2 py-1">
                  <input
                    type="time"
                    v-model="record.clock_in"
                    class="input-sm w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700"
                  />
                </td>
                <td class="px-2 py-1">
                  <input
                    type="time"
                    v-model="record.clock_out"
                    class="input-sm w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700"
                  />
                </td>
                <td class="px-2 py-1">
                  <input
                    type="text"
                    v-model="record.note"
                    :placeholder="$t('Note')"
                    class="input-sm w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="text-mute text-sm" @click="bulk = false">{{ $t('Cancel') }}</button>
          <LoadingButton :loading="bulkForm.processing" @click="submitBulk" :disabled="!bulkForm.records.length">
            {{ $t('Save Attendance') }}
          </LoadingButton>
        </div>
      </div>
    </Modal>
  </div>
</template>
