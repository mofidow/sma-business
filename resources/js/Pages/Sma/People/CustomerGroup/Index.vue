<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, Button, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['customer_groups', 'price_groups']);

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
  router.delete(route('customer_groups.destroy', row.id), {
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
    <title>{{ $t('Customer Groups') }}</title>
  </Head>
  <Header>
    {{ $t('Customer Groups') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <Button v-if="$can('create-customer-groups')" type="button" @click="add = true">
        {{ $t('Add {x}', { x: $t('Customer Group') }) }}
      </Button>
    </template>
  </Header>

  <div class="flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="my-2 inline-block min-w-full border-b border-gray-200 align-middle dark:border-gray-700">
          <table class="fixed-actions min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold whitespace-nowrap sm:ps-6 lg:ps-8">
                  {{ $t('Name') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Discount') }}</th>
                <!-- <th scope="col" class="whitespace-nowrap px-3 py-3.5 text-start text-sm font-semibold text-focus">
                  {{ $t('Apply as discount') }}
                </th> -->
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Price Group') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Details') }}</th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="customer_groups && customer_groups.data && customer_groups.data.length">
                <tr v-for="row in customer_groups.data" :key="row.id">
                  <td class="py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">{{ row.name }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ $number(row.discount) }}</td>
                  <!-- <td class="whitespace-nowrap px-3 py-4 text-sm w-16">
                    <span v-html="$boolean(row.apply_as_discount, true)" />
                  </td> -->
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.price_group?.name || '' }}</td>
                  <td class="px-3 py-4 text-sm">
                    <div class="line-clamp-2 max-w-xs min-w-56">
                      {{ row.details }}
                    </div>
                  </td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      :deleting="deleting"
                      :deleteRow="deleteRow"
                      permission="customer-groups"
                      :record="$t('Customer Group')"
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
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="customer_groups.meta" :links="customer_groups.links" />
    </div>

    <Modal :show="add" :backdrop="false" max-width="2xl" :closeable="true" @close="hideForm">
      <Form :current="current" @close="hideForm" @done="hideForm" :price_groups="price_groups" />
    </Modal>
  </div>
</template>
