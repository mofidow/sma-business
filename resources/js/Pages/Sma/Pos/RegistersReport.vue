<script setup>
import { ref } from 'vue';

import { Modal } from '@/Components/Jet';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Customize from '@/Pages/Sma/Report/Form.vue';
import { Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'users']);

const form = ref(false);
const { filters, searching, sortBy } = PageSearch();
</script>

<template>
  <Head>
    <title>{{ $t('{x} Report', { x: $t('Registers') }) }}</title>
  </Head>
  <Header>
    {{ $t('{x} Report', { x: $t('Registers') }) }}
    <template #subheading>
      <div>{{ $t('Please review the data below') }}</div>
      <div
        class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm font-bold text-primary-700 dark:text-primary-400"
        v-if="filters && Object.keys(filters).filter(k => filters[k] && k !== 'sort').length > 0"
      >
        {{ $t('Filters Applied') }}:
        <span v-if="filters.start_date"> {{ $t('From') }}: {{ $date(filters.start_date.toString(), null, null, true) }} </span>
        <span v-if="filters.end_date"> {{ $t('To') }}: {{ $date(filters.end_date.toString(), null, null, true) }} </span>
        <span v-if="filters.store_id && pagination.data[0]?.store"> {{ $t('Store') }}: {{ pagination.data[0]?.store?.name }} </span>
        <span v-if="filters.user_id && pagination.data[0]?.user"> {{ $t('User') }}: {{ pagination.data[0]?.user?.name }} </span>
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'store.name:asc' ? 'store.name:desc' : 'store.name:asc')"
                  >
                    {{ $t('Store') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('store.name:')"
                      :name="filters.sort == 'store.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'openedBy.name:asc' ? 'openedBy.name:desc' : 'openedBy.name:asc')"
                  >
                    {{ $t('Opened by') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('openedBy.name:')"
                      :name="filters.sort == 'openedBy.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'cash_in_hand:asc' ? 'cash_in_hand:desc' : 'cash_in_hand:asc')"
                  >
                    {{ $t('Cash in Hand') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('cash_in_hand:')"
                      :name="filters.sort == 'cash_in_hand:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'cash_amount:asc' ? 'cash_amount:desc' : 'cash_amount:asc')"
                  >
                    {{ $t('Cash Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('cash_amount:')"
                      :name="filters.sort == 'cash_amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'cc_amount:asc' ? 'cc_amount:desc' : 'cc_amount:asc')"
                  >
                    {{ $t('CC Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('cc_amount:')"
                      :name="filters.sort == 'cc_amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'stripe_amount:asc' ? 'stripe_amount:desc' : 'stripe_amount:asc')"
                  >
                    {{ $t('Stripe Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('stripe_amount:')"
                      :name="filters.sort == 'stripe_amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'other_amount:asc' ? 'other_amount:desc' : 'other_amount:asc')"
                  >
                    {{ $t('Other Amount') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('other_amount:')"
                      :name="filters.sort == 'other_amount:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'closedBy.name:asc' ? 'closedBy.name:desc' : 'closedBy.name:asc')"
                  >
                    {{ $t('Closed by') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('closedBy.name:')"
                      :name="filters.sort == 'closedBy.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'closed_at:asc' ? 'closed_at:desc' : 'closed_at:asc')"
                  >
                    {{ $t('Closed at') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('closed_at:')"
                      :name="filters.sort == 'closed_at:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <!-- <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8 w-16">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th> -->
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr
                  v-for="row in pagination.data"
                  :key="row.id"
                  :class="{
                    'bg-green-50 dark:bg-green-950': row.closed_at,
                    'bg-yellow-50 dark:bg-yellow-950': !row.closed_at,
                  }"
                >
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.store?.name || '' }}
                  </td>
                  <td class="px-3 py-4 text-center text-sm whitespace-nowrap">
                    {{ row.user?.name || '' }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.cash_in_hand) }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm font-bold whitespace-nowrap">
                    {{ row.closed_at ? $currency(row.cash_amount || 0) : '' }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm font-bold whitespace-nowrap">
                    {{ row.closed_at ? $currency(row.cc_payments_amount || 0) : '' }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm font-bold whitespace-nowrap">
                    {{ row.closed_at ? $currency(row.stripe_payments_amount || 0) : '' }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm font-bold whitespace-nowrap">
                    {{ row.closed_at ? $currency(row.other_payments_amount || 0) : '' }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.closed_by?.name || '' }}
                  </td>
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.closed_at ? $datetime(row.closed_at) : '' }}
                  </td>
                  <!-- <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6 lg:pe-8 w-16">
                    <button type="button" class="link" @click="showRegister(row)">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-6"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                        />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                      </svg>
                    </button>
                  </td> -->
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
      <Customize :filters="filters" :stores="stores" :users="users" @close="form = false" :fields="['store', 'user']" />
    </Modal>
  </div>
</template>
