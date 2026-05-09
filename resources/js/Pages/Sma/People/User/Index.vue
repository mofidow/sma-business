<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import View from './View.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown, Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'custom_fields', 'roles', 'stores']);

const add = ref(false);
const view = ref(false);
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
  router.delete(route('users.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}

function hideForm() {
  if (!view.value) {
    current.value = null;
  }
  add.value = false;
}
</script>

<template>
  <Head>
    <title>{{ $t('Users') }}</title>
  </Head>
  <Header>
    {{ $t('Users') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-users')" type="button" @click="add = true">
          {{ $t('Add {x}', { x: $t('User') }) }}
        </Button>
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
            <div class="px-4 py-2">
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Roles')"
                  v-model="filters.role"
                  :placeholder="$t('With Roles')"
                  :suggestions="[
                    { value: '', label: $t('All') },
                    { value: 'employee', label: $t('All Employees') },
                    { value: 'non-employee', label: $t('All Non Employees') },
                    ...roles.map(role => ({ value: role.name, label: role.name })),
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
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold whitespace-nowrap sm:ps-6 lg:ps-8">
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'phone:asc' ? 'phone:desc' : 'phone:asc')"
                  >
                    {{ $t('Phone') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('phone:')"
                      :name="filters.sort == 'phone:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'username:asc' ? 'username:desc' : 'username:asc')"
                  >
                    {{ $t('Username') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('username:')"
                      :name="filters.sort == 'username:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'email:asc' ? 'email:desc' : 'email:asc')"
                  >
                    {{ $t('Email') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('email:')"
                      :name="filters.sort == 'email:desc' ? 'c-up' : 'c-down'"
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Roles') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'store.name:asc' ? 'store.name:desc' : 'store.name:asc')"
                  >
                    {{ $t('Default Store') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('store.name:')"
                      :name="filters.sort == 'store.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <!-- <th scope="col" class="whitespace-nowrap px-3 py-3.5 text-start text-sm font-semibold text-focus">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'view_all:asc' ? 'view_all:desc' : 'view_all:asc')"
                  >
                    {{ $t('View all') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('view_all:')"
                      :name="filters.sort == 'view_all:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="whitespace-nowrap px-3 py-3.5 text-start text-sm font-semibold text-focus">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'edit_all:asc' ? 'edit_all:desc' : 'edit_all:asc')"
                  >
                    {{ $t('Edit all') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('edit_all:')"
                      :name="filters.sort == 'edit_all:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="whitespace-nowrap px-3 py-3.5 text-start text-sm font-semibold text-focus">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'bulk_actions:asc' ? 'bulk_actions:desc' : 'bulk_actions:asc')"
                  >
                    {{ $t('Bulk Action') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('bulk_actions:')"
                      :name="filters.sort == 'bulk_actions:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th> -->
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'employee:asc' ? 'employee:desc' : 'employee:asc')"
                  >
                    {{ $t('Employee') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('employee:')"
                      :name="filters.sort == 'employee:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'can_be_impersonated:asc' ? 'can_be_impersonated:desc' : 'can_be_impersonated:asc')"
                  >
                    {{ $t('Impersonate') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('can_be_impersonated:')"
                      :name="filters.sort == 'can_be_impersonated:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'created_at:asc' ? 'created_at:desc' : 'created_at:asc')"
                  >
                    {{ $t('Created at') }}
                    <Icon
                      size="size-3"
                      v-if="filters?.sort?.startsWith('created_at:')"
                      :name="filters.sort == 'created_at:desc' ? 'c-up' : 'c-down'"
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
                <tr v-for="row in pagination.data" :key="row.id" :class="{ 'bg-red-50 dark:bg-red-900/50': row.deleted_at }">
                  <td class="py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    <div class="flex items-center gap-x-2">
                      {{ row.name }}
                      <Icon name="trash" size="size-4 text-red-500/50" v-if="row.deleted_at" />
                    </div>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.phone || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.username || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.email || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center justify-center" v-html="$boolean(row.active, true)" />
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.roles?.map(r => r.name).join(', ') }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.store?.name }}</td>
                  <!-- <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span v-html="$boolean(row.view_all, true)"></span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span v-html="$boolean(row.edit_all, true)"></span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span v-html="$boolean(row.bulk_actions, true)"></span>
                  </td> -->
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center justify-center" v-html="$boolean(row.employee, true)" />
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center justify-center" v-html="$boolean(row.can_be_impersonated, true)" />
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                    :class="{ 'bg-red-100! dark:bg-red-950!': row.deleted_at }"
                  >
                    <Actions
                      :row="row"
                      :editRow="editRow"
                      :deleted="deleted"
                      permission="users"
                      :deleting="deleting"
                      :record="$t('User')"
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
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="pagination.meta" :links="pagination.links" />
    </div>

    <Modal :show="view" :backdrop="false" max-width="xl" :closeable="true" @close="view = false">
      <View :current="current" @close="view = false" :editRow="editRow" />
    </Modal>

    <Modal :show="add" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="hideForm">
      <Form :current="current" @close="hideForm" @done="hideForm" :roles="roles" :stores="stores" :custom_fields="custom_fields" />
    </Modal>
  </div>
</template>
