<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';
import { $currency } from '@r/js/Core';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'users']);

const add = ref(false);
const current = ref(null);
const deleting = ref(false);
const deleted = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

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
  router.delete(route('employees.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
};
</script>

<template>
  <Head
    ><title>{{ $t('Employees') }}</title></Head
  >
  <Header>
    {{ $t('Employees') }}
    <template #subheading>{{ $t('Please review the data below') }}</template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-employees')" @click="add = true">
          {{ $t('Add {x}', { x: $t('Employee') }) }}
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
                    @click="sortBy(filters?.sort == 'name:asc' ? 'name:desc' : 'name:asc')"
                  >
                    {{ $t('Name') }}
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Department') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Job Title') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Hire Date') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Salary') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Store') }}</th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination?.data?.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    <div class="flex items-center gap-3">
                      <img v-if="row.user?.profile_photo_url" :src="row.user.profile_photo_url" class="size-8 rounded-full object-cover" />
                      <div>
                        <div>{{ row.user?.name }}</div>
                        <div class="text-mute text-xs">{{ row.user?.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.department || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.job_title || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.hire_date ? $date(row.hire_date) : '' }}</td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap">{{ $currency(row.basic_salary) }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.store?.name }}</td>
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
                      permission="employees"
                      :record="$t('Employee')"
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

    <Modal :show="add" max-width="2xl" @close="hideForm">
      <Form :current="current" :stores="stores" :users="users" @close="hideForm" />
    </Modal>
  </div>
</template>
