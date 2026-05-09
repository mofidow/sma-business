<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

import QuickView from './QuickView.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, Modal, SecondaryButton } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, LoadingButton, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'custom_fields']);

const view = ref(false);
const current = ref(null);
const deleted = ref(false);
const deleting = ref(false);
const adjusting = ref(false);
const confirming = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

onMounted(() => {
  if (route().params?.id) {
    viewRow({ id: route().params.id });
  }
});

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function adjust(row) {
  current.value = row;
  confirming.value = true;
}

function adjustNow() {
  adjusting.value = true;
  router.visit(route('stock_counts.adjust', current.value.id), {
    method: 'post',
    onFinish: () => (adjusting.value = false),
    preserveState: page => page.props.flash?.error,
  });
}

function editRow(row) {
  router.visit(route('stock_counts.edit', { stock_count: row.id }));
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('stock_counts.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}
</script>

<template>
  <Head>
    <title>{{ $t('Stock Counts') }}</title>
  </Head>
  <Header>
    {{ $t('Stock Counts') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button :href="route('stock_counts.create')">
          {{ $t('Start Stock Count') }}
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
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'date:asc' ? 'date:desc' : 'date:asc')"
                  >
                    {{ $t('Date') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('date:')"
                      :name="filters.sort == 'date:desc' ? 'c-up' : 'c-down'"
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'type:asc' ? 'type:desc' : 'type:asc')"
                  >
                    {{ $t('Type') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('type:')"
                      :name="filters.sort == 'type:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'details:asc' ? 'details:desc' : 'details:asc')"
                  >
                    {{ $t('Details') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('details:')"
                      :name="filters.sort == 'details:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'created_at:asc' ? 'created_at:desc' : 'created_at:asc')"
                  >
                    {{ $t('Created at') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('created_at:')"
                      :name="filters.sort == 'created_at:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'completed_at:asc' ? 'completed_at:desc' : 'completed_at:asc')"
                  >
                    {{ $t('Completed at') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('completed_at:')"
                      :name="filters.sort == 'completed_at:desc' ? 'c-up' : 'c-down'"
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
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $date(row.date) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.reference }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.type == 'full' ? $t('Full') : $t('Partial') }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    <div class="-my-2 line-clamp-2 min-w-64">
                      {{ row.details || '' }}
                    </div>
                  </td>
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ $datetime(row.completed_at) }}
                  </td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <div class="text-mute flex items-center justify-start gap-4">
                      <button type="button" class="link" @click="viewRow(row)">
                        <Icon name="eye" size="size-5" />
                      </button>
                      <template v-if="!row.completed_at">
                        <a target="_blank" class="link" :href="route('stock_counts.export', row.id)">
                          <Icon name="download-o" size="size-5" />
                        </a>
                      </template>
                      <template v-else>
                        <a target="_blank" class="link" :href="route('stock_counts.download', row.id)">
                          <Icon name="download-o" size="size-5 text-success-500" />
                        </a>
                      </template>
                      <template v-if="row.completed_at">
                        <template v-if="row.adjusted_at">
                          <Icon name="tick" size="size-5 text-success-500" />
                        </template>
                        <template v-else>
                          <button type="button" class="link" @click="adjust(row)">
                            <Icon name="adjust-v" size="size-5" />
                          </button>
                        </template>
                      </template>
                      <Actions
                        :row="row"
                        :deleted="deleted"
                        :deleting="deleting"
                        permission="stock-counts"
                        :record="$t('Stock Count')"
                        :editRow="row.completed_at ? false : editRow"
                        :deleteRow="row.completed_at ? false : deleteRow"
                      />
                    </div>
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

    <Modal :show="view" max-width="3xl" @close="view = false">
      <QuickView :current="current" :fields="custom_fields" @close="view = false" :editRow="current.completed_at ? false : editRow" />
    </Modal>

    <Modal :show="confirming" max-width="lg" @close="confirming = false" :closeable="!adjusting">
      <div class="p-6">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
            <Icon name="disclaimer" class="size-6 text-red-600" />
          </div>
          <div class="mt-3 text-center sm:ms-4 sm:mt-0 sm:text-left">
            <h3 class="text-focus text-base font-semibold" id="modal-title">{{ $t('Adjust Store Stock?') }}</h3>
            <div class="mt-2">
              <div class="text-sm">
                {{ $t('Are you sure you want to perform stock adjustments?') }} {{ $t('All of your data will be permanently changed.') }}
                <div class="mt-4 text-error-500">
                  {{ $t('This action cannot be undone.') }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-center gap-4 md:justify-end">
          <SecondaryButton type="button" @click="confirming = false" :disabled="adjusting">
            {{ $t('Cancel') }}
          </SecondaryButton>
          <LoadingButton class="justify-center md:w-full" @click="adjustNow()" :loading="adjusting" :class="{ 'opacity-25': adjusting }">
            {{ $t('Initiate Adjustments') }}
          </LoadingButton>
        </div>
      </div>
    </Modal>
  </div>
</template>
