<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, DropdownLink, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'parents']);

const add = ref(false);
const photo = ref(null);
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
  router.delete(route('categories.destroy', row.id), {
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
    <title>{{ $t('Categories') }}</title>
  </Head>
  <Header>
    {{ $t('Categories') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-categories')" type="button" @click="add = true">
          {{ $t('Add {x}', { x: $t('Category') }) }}
        </Button>
        <Dropdown align="right" width="40" :auto-close="false">
          <template #trigger>
            <button class="-m-2 flex items-center rounded-md p-2.5 transition duration-150 ease-in-out">
              <Icon name="v-arrows" size="size-6" />
              <span class="sr-only">{{ $t('Import/Export') }}</span>
            </button>
          </template>

          <template #content>
            <div>
              <!-- Account Management -->
              <div class="block px-4 py-2 text-xs text-gray-400">{{ $t('Import/Export') }}</div>
              <DropdownLink v-if="route().has('categories.import')" :href="route('categories.import')">
                {{ $t('Import {x}', { x: $t('Categories') }) }}
              </DropdownLink>
              <DropdownLink as="a" v-if="route().has('categories.export')" :href="route('categories.export')">
                {{ $t('Export {x}', { x: $t('Categories') }) }}
              </DropdownLink>
            </div>
          </template>
        </Dropdown>
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
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-start text-sm font-semibold sm:ps-6 lg:ps-8">
                  <span class="sr-only">{{ $t('Photo') }}</span>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'name:asc' ? 'name:desc' : 'name:asc')"
                  >
                    {{ $t('Name') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('name:')"
                      :name="filters.sort == 'name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'slug:asc' ? 'slug:desc' : 'slug:asc')"
                  >
                    {{ $t('Slug') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('slug:')"
                      :name="filters.sort == 'slug:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'title:asc' ? 'title:desc' : 'title:asc')"
                  >
                    {{ $t('Title') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('title:')"
                      :name="filters.sort == 'title:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'active:asc' ? 'active:desc' : 'active:asc')"
                  >
                    {{ $t('Active') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('active:')"
                      :name="filters.sort == 'active:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'category.name:asc' ? 'category.name:desc' : 'category.name:asc')"
                  >
                    {{ $t('Parent Category') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('category.name:')"
                      :name="filters.sort == 'category.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    <button
                      type="button"
                      v-if="row.photo"
                      @click="photo = row.photo"
                      class="-my-4 flex h-8 w-8 items-center justify-center"
                    >
                      <img alt="" :src="row.photo" class="max-h-full max-w-full rounded-sm" />
                    </button>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.name }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.slug }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.title }}</td>
                  <td class="w-16 px-3 py-4 text-sm whitespace-nowrap">
                    <span v-html="$boolean(row.active, true)"></span>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.category?.name || '' }}</td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      :deleting="deleting"
                      :deleteRow="deleteRow"
                      permission="categories"
                      :record="$t('Category')"
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

    <Modal :show="add" :backdrop="false" max-width="2xl" :closeable="true" @close="hideForm">
      <Form :current="current" :parents="parents" @close="hideForm" @done="hideForm" />
    </Modal>
    <Modal :show="photo" max-width="2xl" :transparent="true" @close="() => (photo = null)">
      <div class="flex items-center justify-center">
        <img alt="" :src="photo" class="h-full max-h-screen min-h-24 w-full max-w-full rounded-md" />
      </div>
    </Modal>
  </div>
</template>
