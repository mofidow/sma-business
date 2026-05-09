<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Modal } from '@/Components/Jet';
import { Actions, Button, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['stores', 'accounts', 'countries', 'price_groups', 'custom_fields']);

const add = ref(false);
const logo = ref(null);
const current = ref(null);
const deleted = ref(false);
const deleting = ref(false);

function editRow(row) {
  current.value = row;
  add.value = true;
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('stores.destroy', row.id), {
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
    <title>{{ $t('Stores') }}</title>
  </Head>
  <Header>
    {{ $t('Stores') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <Button v-if="$can('create-stores')" type="button" @click="add = true">
        {{ $t('Add {x}', { x: $t('Store') }) }}
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
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8">
                  <span class="sr-only">{{ $t('Logo') }}</span>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Name') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Phone') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Email') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Active') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Account') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Price Group') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">{{ $t('Address') }}</th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="stores && stores.data && stores.data.length">
                <tr v-for="row in stores.data" :key="row.id">
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    <button type="button" v-if="row.logo" @click="logo = row.logo" class="-my-4 flex h-8 w-8 items-center justify-center">
                      <img alt="" :src="row.logo" class="max-h-full max-w-full rounded-sm" />
                    </button>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.name }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.phone }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.email }}</td>
                  <td class="w-16 px-3 py-4 text-sm whitespace-nowrap">
                    <span v-html="$boolean(row.active, true)"></span>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.account?.title || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.price_group?.name || '' }}</td>
                  <td class="px-3 py-4 text-sm">
                    <div class="line-clamp-2 max-w-xs min-w-56">
                      {{ $address(row) }}
                    </div>
                  </td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      permission="stores"
                      :deleting="deleting"
                      :record="$t('Store')"
                      :deleteRow="deleteRow"
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
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="stores.meta" :links="stores.links" />
    </div>

    <Modal :show="add" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="hideForm">
      <Form
        :current="current"
        :parents="parents"
        @close="hideForm"
        @done="hideForm"
        :accounts="accounts"
        :countries="countries"
        :price_groups="price_groups"
        :custom_fields="custom_fields"
      />
    </Modal>
    <Modal :show="logo" :backdrop="true" max-width="2xl" :closeable="false" :transparent="true" @close="() => (logo = null)">
      <div class="flex items-center justify-center">
        <img alt="" :src="logo" class="h-full max-h-screen min-h-24 w-full max-w-full rounded-md" />
      </div>
    </Modal>
  </div>
</template>
