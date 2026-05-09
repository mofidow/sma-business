<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Modal } from '@/Components/Jet';
import { Actions, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination']);

const add = ref(false);
const current = ref(null);
const deleting = ref(false);
const deleted = ref(false);
const { filters, searching, sortBy } = PageSearch();

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
  router.delete(route('leave-types.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
};
</script>

<template>
  <Head
    ><title>{{ $t('Leave Types') }}</title></Head
  >
  <Header>
    {{ $t('Leave Types') }}
    <template #subheading>{{ $t('Please review the data below') }}</template>
    <template #menu>
      <Button v-if="$can('settings')" @click="add = true">
        {{ $t('Add {x}', { x: $t('Leave Type') }) }}
      </Button>
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
                  {{ $t('Name') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Days/Year') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Paid') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Carry Forward') }}</th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination?.data?.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">{{ row.name }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.days_per_year }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <span :class="row.is_paid ? 'text-green-600' : 'text-red-500'">{{ row.is_paid ? $t('Yes') : $t('No') }}</span>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <span :class="row.carry_forward ? 'text-green-600' : 'text-gray-400'">{{
                      row.carry_forward ? $t('Yes') : $t('No')
                    }}</span>
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
                      permission="settings"
                      :record="$t('Leave Type')"
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
      <Form :current="current" @close="hideForm" />
    </Modal>
  </div>
</template>
