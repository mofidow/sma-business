<script setup>
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';

import { AutoComplete, Loading, Pagination } from '@/Components/Common';
import { Dropdown } from '@/Components/Jet';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'stores', 'users']);

const { filters, searching, searchNow } = PageSearch();

function viewCredit(sale) {
  router.visit(route('credits.show', { credit: sale.id }));
}

function statusClass(status) {
  return {
    pending:  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    partial:  'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    paid:     'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    overdue:  'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  }[status] ?? 'bg-gray-100 text-gray-800';
}

function agingDays(sale) {
  const installments = sale.credit_installments ?? [];
  const unpaid = installments.filter(i => i.status !== 'paid');
  if (!unpaid.length) return 0;
  const oldest = unpaid.reduce((a, b) => dayjs(a.due_date).isBefore(dayjs(b.due_date)) ? a : b);
  return Math.max(0, dayjs().diff(dayjs(oldest.due_date), 'day'));
}
</script>

<template>
  <Head><title>{{ $t('Credit (Deyn)') }}</title></Head>

  <Header>
    {{ $t('Credit (Deyn)') }}
    <template #subheading>{{ $t('Sales with deferred payment plans') }}</template>
    <template #menu>
      <div class="flex items-center gap-2">
      <Link :href="route('sales.index')" class="btn-primary">+ {{ $t('Convert a Sale') }}</Link>
      <Dropdown align="right" width="56" :auto-close="false">
        <template #trigger>
          <button class="-m-2 flex items-center rounded-md p-2.5 transition duration-150 ease-in-out">
            <Icon name="funnel-o" size="size-5" />
          </button>
        </template>
        <template #content>
          <div class="space-y-3 px-4 py-3">
            <AutoComplete :json="true" @change="searchNow" :label="$t('Status')" v-model="filters.credit_status"
              :placeholder="$t('All Statuses')"
              :suggestions="[
                { value: 'pending',  label: $t('Pending') },
                { value: 'partial',  label: $t('Partial') },
                { value: 'paid',     label: $t('Fully Paid') },
                { value: 'overdue',  label: $t('Overdue') },
              ]" />
            <AutoComplete :json="true" @change="searchNow" :label="$t('Store')" v-model="filters.store_id"
              :placeholder="$t('All Stores')" :suggestions="stores" />
            <AutoComplete :json="true" @change="searchNow" :label="$t('User')" v-model="filters.user_id"
              :placeholder="$t('All Users')" :suggestions="users" />
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('From') }}</label>
                <input type="date" v-model="filters.start_date" @change="searchNow"
                  class="mt-1 w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-800" />
              </div>
              <div>
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('To') }}</label>
                <input type="date" v-model="filters.end_date" @change="searchNow"
                  class="mt-1 w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-800" />
              </div>
            </div>
          </div>
        </template>
      </Dropdown>
      </div>
    </template>
  </Header>

  <Loading v-if="searching" />

  <div class="p-4 sm:p-6">
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">{{ $t('Reference') }}</th>
            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">{{ $t('Date') }}</th>
            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">{{ $t('Customer') }}</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">{{ $t('Total') }}</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">{{ $t('Paid') }}</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">{{ $t('Remaining') }}</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">{{ $t('Status') }}</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">{{ $t('Aging') }}</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
          <tr v-for="sale in pagination.data" :key="sale.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <td class="px-4 py-3 font-mono text-xs">{{ sale.reference }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ sale.date }}</td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ sale.customer?.name }}</div>
              <div v-if="sale.customer?.company" class="text-xs text-gray-500">{{ sale.customer.company }}</div>
            </td>
            <td class="px-4 py-3 text-right font-medium">{{ format_number(sale.grand_total) }}</td>
            <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ format_number(sale.paid ?? 0) }}</td>
            <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">
              {{ format_number((sale.grand_total ?? 0) - (sale.paid ?? 0)) }}
            </td>
            <td class="px-4 py-3 text-center">
              <span class="rounded-full px-2 py-0.5 text-xs font-medium capitalize" :class="statusClass(sale.credit_status)">
                {{ $t(sale.credit_status ?? 'pending') }}
              </span>
            </td>
            <td class="px-4 py-3 text-center text-xs" :class="agingDays(sale) > 30 ? 'text-red-600 font-medium' : 'text-gray-500'">
              {{ agingDays(sale) > 0 ? agingDays(sale) + ' ' + $t('days') : '—' }}
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <button @click="viewCredit(sale)"
                  class="rounded px-2 py-1 text-xs text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                  {{ $t('View') }}
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!pagination.data.length">
            <td colspan="9" class="px-4 py-8 text-center text-gray-400">
              {{ $t('No credit sales yet.') }}
              <Link :href="route('sales.index')" class="ms-1 link">{{ $t('Go to Sales to convert a sale to Credit (Deyn).') }}</Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :pagination="pagination" class="mt-4" />
  </div>

</template>
