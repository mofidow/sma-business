<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Modal } from '@/Components/Jet';
import { Actions, Button, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['custom_fields', 'models', 'types']);

const add = ref(false);
const current = ref(null);
const deleted = ref(false);
const deleting = ref(false);

function editRow(row) {
  current.value = row;
  add.value = true;
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('custom_fields.destroy', row.id), {
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
    <title>{{ $t('Custom Fields') }}</title>
  </Head>
  <Header>
    {{ $t('Custom Fields') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <Button v-if="$can('create-custom-fields')" type="button" @click="add = true">
        {{ $t('Add {x}', { x: $t('Custom Field') }) }}
      </Button>
    </template>
  </Header>

  <div class="flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle">
          <table class="fixed-actions min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8">{{ $t('Name') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Models') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Type') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Options') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Order No.') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Required') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Show in details') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Details') }}</th>
                <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="custom_fields && custom_fields.data && custom_fields.data.length">
                <tr v-for="row in custom_fields.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">{{ row.name }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.models?.map(m => $capitalize(m))?.join(', ') || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap capitalize">{{ row.type || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.options?.join(', ') || '' }}</td>
                  <td class="w-16 px-3 py-4 text-center text-sm whitespace-nowrap">{{ row.order_no || '' }}</td>
                  <td class="w-16 px-3 py-4 text-sm whitespace-nowrap">
                    <span v-html="$boolean(row.is_required, true)"></span>
                  </td>
                  <td class="w-16 px-3 py-4 text-sm whitespace-nowrap">
                    <span v-html="$boolean(row.show_on_details_view, true)"></span>
                  </td>
                  <td class="px-3 py-4 text-sm">
                    <div class="line-clamp-2 max-w-xs min-w-56">
                      {{ row.details || '' }}
                    </div>
                  </td>
                  <td class="relative py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      :deleting="deleting"
                      :deleteRow="deleteRow"
                      permission="custom-fields"
                      :record="$t('Custom Field')"
                    />
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
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="custom_fields.meta" :links="custom_fields.links" />
    </div>

    <Modal :show="add" :backdrop="false" max-width="2xl" :closeable="true" @close="hideForm">
      <Form :current="current" :models="models" :types="types" @close="hideForm" @done="hideForm" />
    </Modal>
  </div>
</template>
