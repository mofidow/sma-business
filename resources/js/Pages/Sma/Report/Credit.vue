<script setup>
import { ref } from 'vue';
import { AutoComplete, Pagination } from '@/Components/Common';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps(['totals', 'aging', 'cluster', 'installments', 'stores', 'customers']);

const { filters, searchNow } = PageSearch();

function statusClass(status) {
  return {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    paid:    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  }[status] ?? 'bg-gray-100 text-gray-800';
}
</script>

<template>
  <Head><title>{{ $t('Credit Report') }}</title></Head>

  <Header>
    {{ $t('Credit Report') }}
    <template #subheading>{{ $t('Aging, customer cluster and installment detail') }}</template>
  </Header>

  <!-- Filters -->
  <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
    <div class="flex flex-wrap gap-3">
      <div class="w-44">
        <AutoComplete :json="true" @change="searchNow" :label="$t('Store')" v-model="filters.store_id"
          :placeholder="$t('All Stores')" :suggestions="stores" />
      </div>
      <div class="w-52">
        <AutoComplete :json="true" @change="searchNow" :label="$t('Customer')" v-model="filters.customer_id"
          :placeholder="$t('All Customers')" :suggestions="customers" />
      </div>
      <div class="w-40">
        <AutoComplete :json="true" @change="searchNow" :label="$t('Status')" v-model="filters.credit_status"
          :placeholder="$t('All Statuses')"
          :suggestions="[
            { value: 'pending', label: $t('Pending') },
            { value: 'paid',    label: $t('Paid') },
            { value: 'overdue', label: $t('Overdue') },
          ]" />
      </div>
      <div class="flex items-end gap-2">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('From') }}</label>
          <input type="date" v-model="filters.start_date" @change="searchNow"
            class="rounded border border-gray-300 px-2 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('To') }}</label>
          <input type="date" v-model="filters.end_date" @change="searchNow"
            class="rounded border border-gray-300 px-2 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700" />
        </div>
      </div>
    </div>
  </div>

  <div class="p-4 sm:p-6 space-y-6">

    <!-- Summary totals -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $t('Credit Sales') }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ totals?.count ?? 0 }}</p>
      </div>
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $t('Total Credit') }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ format_number(totals?.total ?? 0) }}</p>
      </div>
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $t('Total Paid') }}</p>
        <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ format_number(totals?.paid ?? 0) }}</p>
      </div>
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $t('Outstanding') }}</p>
        <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ format_number(totals?.outstanding ?? 0) }}</p>
      </div>
    </div>

    <!-- Aging buckets -->
    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 font-semibold text-gray-900 dark:text-white">
        {{ $t('Aging') }}
      </div>
      <div class="grid grid-cols-2 gap-0 sm:grid-cols-4 divide-x divide-gray-200 dark:divide-gray-700">
        <div class="p-4 text-center">
          <p class="text-xs text-gray-500">{{ $t('Current (0-30 days)') }}</p>
          <p class="mt-1 text-xl font-bold text-gray-700 dark:text-gray-200">{{ format_number(aging?.current_30 ?? 0) }}</p>
        </div>
        <div class="p-4 text-center">
          <p class="text-xs text-gray-500">{{ $t('31-60 days') }}</p>
          <p class="mt-1 text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ format_number(aging?.days_31_60 ?? 0) }}</p>
        </div>
        <div class="p-4 text-center">
          <p class="text-xs text-gray-500">{{ $t('61-90 days') }}</p>
          <p class="mt-1 text-xl font-bold text-orange-600 dark:text-orange-400">{{ format_number(aging?.days_61_90 ?? 0) }}</p>
        </div>
        <div class="p-4 text-center">
          <p class="text-xs text-gray-500">{{ $t('Over 90 days') }}</p>
          <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400">{{ format_number(aging?.over_90 ?? 0) }}</p>
        </div>
      </div>
    </div>

    <!-- Customer cluster -->
    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 font-semibold text-gray-900 dark:text-white">
        {{ $t('Customer Cluster') }}
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">{{ $t('Customer') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Sales') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Total Credit') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Total Paid') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Outstanding') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="row in cluster" :key="row.customer_id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900 dark:text-white">{{ row.customer?.name }}</div>
                <div class="text-xs text-gray-500">{{ row.customer?.phone }}</div>
              </td>
              <td class="px-4 py-3 text-center">{{ row.sale_count }}</td>
              <td class="px-4 py-3 text-right font-medium">{{ format_number(row.total_credit) }}</td>
              <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ format_number(row.total_paid) }}</td>
              <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400">{{ format_number(row.outstanding) }}</td>
            </tr>
            <tr v-if="!cluster.length">
              <td colspan="5" class="px-4 py-6 text-center text-gray-400">{{ $t('No records found') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Installment detail -->
    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 font-semibold text-gray-900 dark:text-white">
        {{ $t('Installment Detail') }}
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">{{ $t('Sale') }}</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">{{ $t('Customer') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Amount') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Due Date') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Status') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Aging') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="inst in installments.data" :key="inst.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-4 py-3 font-mono text-xs">{{ inst.sale_reference }}</td>
              <td class="px-4 py-3">
                <div class="font-medium">{{ inst.customer_name }}</div>
                <div class="text-xs text-gray-500">{{ inst.customer_phone }}</div>
              </td>
              <td class="px-4 py-3 text-right font-medium">{{ format_number(inst.amount) }}</td>
              <td class="px-4 py-3 text-center">{{ inst.due_date }}</td>
              <td class="px-4 py-3 text-center">
                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(inst.status)">
                  {{ $t(inst.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-xs"
                :class="inst.days_overdue > 0 ? 'text-red-500 font-medium' : 'text-gray-400'">
                {{ inst.days_overdue > 0 ? inst.days_overdue + ' ' + $t('days') : '—' }}
              </td>
            </tr>
            <tr v-if="!installments.data.length">
              <td colspan="6" class="px-4 py-6 text-center text-gray-400">{{ $t('No records found') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
        <Pagination :pagination="installments" />
      </div>
    </div>

  </div>
</template>
