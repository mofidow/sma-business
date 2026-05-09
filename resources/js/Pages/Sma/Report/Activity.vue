<script setup>
import { ref } from 'vue';

import Customize from './Form.vue';
import { PageSearch } from '@/Core/PageSearch';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'users']);

const form = ref(false);
const view = ref(false);
const current = ref(null);
const { filters, searching, sortBy } = PageSearch();

function viewRow(row) {
  current.value = row;
  view.value = true;
}
</script>

<template>
  <Head>
    <title>{{ $t('{x} Report', { x: $t('Activities') }) }}</title>
  </Head>
  <Header>
    {{ $t('{x} Report', { x: $t('Activities') }) }}
    <template #subheading>
      <div>{{ $t('Please review the data below') }}</div>
      <div
        class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-bold text-primary-700 dark:text-primary-400"
        v-if="filters && Object.keys(filters).filter(k => filters[k] && k !== 'sort').length > 0"
      >
        {{ $t('Filters Applied') }}:
        <span v-if="filters.start_date"> {{ $t('From') }}: {{ $date(filters.start_date.toString(), null, null, true) }} </span>
        <span v-if="filters.end_date"> {{ $t('To') }}: {{ $date(filters.end_date.toString(), null, null, true) }} </span>
        <span v-if="filters.store_id">
          {{ $t('Store') }}: {{ pagination.data[0]?.store?.name || $page.props.stores.find(s => s.value == filters.store_id)?.label || '' }}
        </span>
        <span v-if="filters.user_id && pagination.data[0]?.causer"> {{ $t('User') }}: {{ pagination.data[0]?.causer?.name }} </span>
      </div>
    </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button type="button" @click="form = true">
          {{ $t('Customize') }}
        </Button>
      </div>
    </template>
  </Header>

  <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <Loading v-if="searching" circle-size="w-10 h-10" />
    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="my-2 inline-block min-w-full border-b border-gray-200 align-middle dark:border-gray-700">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'log_name:asc' ? 'log_name:desc' : 'log_name:asc')"
                  >
                    {{ $t('Log Name') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('log_name:')"
                      :name="filters.sort == 'log_name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'description:asc' ? 'description:desc' : 'description:asc')"
                  >
                    {{ $t('Description') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('description:')"
                      :name="filters.sort == 'description:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'subject_type:asc' ? 'subject_type:desc' : 'subject_type:asc')"
                  >
                    {{ $t('Subject') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('subject_type:')"
                      :name="filters.sort == 'subject_type:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'causer.name:asc' ? 'causer.name:desc' : 'causer.name:asc')"
                  >
                    {{ $t('Causer') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('causer.name:')"
                      :name="filters.sort == 'causer.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <!-- <th scope="col" class="whitespace-nowrap py-3.5 ps-3 pe-4 text-end text-sm font-medium sm:pe-6 lg:pe-8 w-16">
                  {{ $t('Properties') }}
                </th> -->
                <th scope="col" class="w-16 py-3.5 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
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
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td
                    @click="viewRow(row)"
                    class="text-focus w-14 cursor-pointer py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8"
                  >
                    {{ row.log_name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.description || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.subject_type?.split('\\').pop() || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.causer?.name || '' }}
                  </td>
                  <!-- <td
                    @click="viewRow(row)"
                    class="cursor-pointer whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6 lg:pe-8 w-16"
                  >
                    <pre>{{ JSON.stringify(row.properties, null, 2) }}</pre>
                  </td> -->
                  <td
                    @click="viewRow(row)"
                    class="w-16 cursor-pointer py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                  >
                    {{ $datetime(row.created_at) }}
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

    <Modal :show="form" max-width="2xl" @close="form = false" :overflow="true">
      <Customize :filters="filters" :stores="stores" :users="users" @close="form = false" :fields="['user']" />
    </Modal>

    <Modal :show="view" max-width="2xl" @close="view = false" :overflow="true">
      <div class="border-b border-gray-200 px-6 py-4 font-bold dark:border-gray-700">
        {{ $t('Activity Details') }}
      </div>
      <div class="text-sm">
        <dl class="divide-y divide-gray-100 dark:divide-white/5">
          <div v-if="current.causer" class="px-4 py-2 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $t('User') }}</dt>
            <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-3 sm:mt-0 dark:text-gray-300">
              {{ current.causer?.name }}
            </dd>
          </div>
          <div class="px-4 py-2 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $t('Action') }}</dt>
            <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-3 sm:mt-0 dark:text-gray-300">{{ current.description }}</dd>
          </div>
          <div v-if="current.subject && Object.keys(current.subject).length" class="px-4 py-2 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $t('Data') }}</dt>
            <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-3 sm:mt-0 dark:text-gray-300">
              <ul>
                <li v-for="key in Object.keys(current.subject)" :key="key" class="font-mono text-sm">
                  <template v-if="current.subject[key]">
                    {{ key }}:
                    <span class="font-bold">{{
                      !isNaN(current.subject[key]) && !isNaN(parseFloat(current.subject[key]))
                        ? Number(current.subject[key])
                        : current.subject[key]
                    }}</span>
                  </template>
                </li>
              </ul>
            </dd>
          </div>
        </dl>
        <!-- <pre class="font-mono whitespace-pre-wrap">{{ JSON.stringify(current, null, 2) }}</pre> -->
      </div>
    </Modal>
  </div>
</template>
