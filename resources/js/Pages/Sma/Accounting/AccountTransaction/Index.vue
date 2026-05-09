<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown, Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'accounts']);

const add = ref(false);
const deleted = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('account-transactions.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}

function hideForm() {
  add.value = false;
}
</script>

<template>
  <Head>
    <title>{{ $t('Transactions') }}</title>
  </Head>
  <Header>
    {{ $t('Transactions') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-account-transactions')" type="button" @click="add = true">
          {{ $t('Add {x}', { x: $t('Transaction') }) }}
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
                    @click="sortBy(filters?.sort == 'created_at:asc' ? 'created_at:desc' : 'created_at:asc')"
                  >
                    {{ $t('Date') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('created_at:')"
                      :name="filters.sort == 'created_at:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'reference:asc' ? 'reference:desc' : 'reference:asc')"
                  >
                    {{ $t('Reference') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('reference:')"
                      :name="filters.sort == 'reference:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Account') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Type') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'amount:asc' ? 'amount:desc' : 'amount:asc')"
                  >
                    {{ $t('Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('amount:')"
                      :name="filters.sort == 'amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Note') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('By') }}</th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $date(row.created_at) }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.account?.title }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                      :class="
                        row.type === 'credit'
                          ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                          : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                      "
                    >
                      {{ $t(row.type.charAt(0).toUpperCase() + row.type.slice(1)) }}
                    </span>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ $number(row.amount) }}</td>
                  <td class="px-3 py-4 text-sm">
                    <div class="line-clamp-2 max-w-xs min-w-56">{{ row.note }}</div>
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.user?.name }}</td>
                  <td class="relative py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <Actions
                      :row="row"
                      :deleted="deleted"
                      :deleting="deleting"
                      permission="account-transactions"
                      :deleteRow="deleteRow"
                      :record="$t('Transaction')"
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
      <Form :accounts="accounts" @close="hideForm" @done="hideForm" />
    </Modal>
  </div>
</template>
