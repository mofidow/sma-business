<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination']);

const add = ref(false);
const current = ref(null);
const deleted = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

function editRow(row) {
  current.value = row;
  add.value = true;
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('pos.halls.destroy', row.id), {
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
    <title>{{ $t('Restaurant Halls') }}</title>
  </Head>
  <Header>
    {{ $t('Restaurant Halls') }}
    <template #subheading> {{ $t('Manage dining halls and areas') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <template v-if="$can('create-halls')">
          <Button v-if="$page.props.selected_store" type="button" @click="add = true">
            {{ $t('Add {x}', { x: $t('Hall') }) }}
          </Button>
          <Button v-else type="button" @click="() => ($page.props.select_store = true)">
            {{ $t('Add {x}', { x: $t('Hall') }) }}
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
                  :label="$t('Status')"
                  v-model="filters.active"
                  :placeholder="$t('All')"
                  :suggestions="[
                    { value: '1', label: $t('Active') },
                    { value: '0', label: $t('Inactive') },
                  ]"
                />
              </div>
              <div class="mt-4">
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
            <thead class="whitespace-nowrap">
              <tr>
                <th
                  scope="col"
                  class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8"
                  @click="sortBy('name')"
                >
                  <div class="inline-flex">
                    <span>{{ $t('Hall Name') }}</span>
                    <SortIcons sort="name" :filters="filters" />
                  </div>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Description') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">{{ $t('Tables') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold" @click="sortBy('sort_order')">
                  <div class="inline-flex">
                    <span>{{ $t('Order') }}</span>
                    <SortIcons sort="sort_order" :filters="filters" />
                  </div>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold" @click="sortBy('active')">
                  <div class="inline-flex">
                    <span>{{ $t('Status') }}</span>
                    <SortIcons sort="active" :filters="filters" />
                  </div>
                </th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr
                  tabindex="0"
                  :key="row.id"
                  v-for="row in pagination.data"
                  :class="{ deleted: row.deleted_at }"
                  class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                >
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    <div class="flex items-center text-sm font-semibold">
                      {{ row.name }}
                    </div>
                  </td>
                  <td class="cursor-pointer px-3 py-4 text-start text-sm font-semibold whitespace-nowrap">{{ row.description }}</td>
                  <td class="cursor-pointer px-3 py-4 text-center text-sm font-semibold whitespace-nowrap">{{ row.tables_count }}</td>
                  <td class="cursor-pointer px-3 py-4 text-center text-sm font-semibold whitespace-nowrap">{{ row.sort_order }}</td>
                  <td class="cursor-pointer px-3 py-4 text-center text-sm font-semibold whitespace-nowrap">
                    <Badge v-if="row.active" variant="success">{{ $t('Active') }}</Badge>
                    <Badge v-else variant="danger">{{ $t('Inactive') }}</Badge>
                  </td>
                  <td
                    :class="{ deleted: row.deleted_at }"
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                  >
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      permission="halls"
                      :deleting="deleting"
                      :deleteRow="deleteRow"
                      :record="$t('Hall')"
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
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="pagination.meta" :links="pagination.links" />
    </div>
  </div>

  <Modal :show="add" @close="hideForm" maxWidth="xl">
    <Form :current="current" @close="hideForm" @done="searchNow" />
  </Modal>
</template>
