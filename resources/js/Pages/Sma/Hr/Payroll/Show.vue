<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { LoadingButton } from '@/Components/Common';

const { t } = useI18n();
defineOptions({ layout: AdminLayout });
const props = defineProps(['payroll']);

const marking = ref(false);

const statusClasses = {
  draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  processed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
};

const monthNames = [
  t('January'),
  t('February'),
  t('March'),
  t('April'),
  t('May'),
  t('June'),
  t('July'),
  t('August'),
  t('September'),
  t('October'),
  t('November'),
  t('December'),
];

const markPaid = () => {
  marking.value = true;
  router.post(
    route('payrolls.mark-paid', props.payroll.id),
    {},
    {
      preserveScroll: true,
      onFinish: () => (marking.value = false),
    }
  );
};
</script>

<template>
  <Head
    ><title>{{ payroll.title }}</title></Head
  >
  <Header>
    {{ payroll.title }}
    <template #subheading>
      {{ $t('Payroll for {month} {year}', { month: $monthNames[payroll.month - 1], year: payroll.year }) }}
    </template>
    <template #menu>
      <Link :href="route('payrolls.index')" class="btn-primary">{{ $t('Back to Payroll') }}</Link>
      <LoadingButton v-if="payroll.status === 'processed' && $can('update-payrolls')" :loading="marking" @click="markPaid">
        {{ $t('Mark as Paid') }}
      </LoadingButton>
    </template>
  </Header>

  <div class="px-4 py-6 sm:px-6 lg:px-8">
    <!-- Summary -->
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <p class="text-mute text-sm">{{ $t('Period') }}</p>
        <p class="text-focus mt-1 text-base font-semibold">{{ $monthNames[payroll.month - 1] }} {{ payroll.year }}</p>
      </div>
      <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <p class="text-mute text-sm">{{ $t('Employees') }}</p>
        <p class="text-focus mt-1 text-base font-semibold">{{ payroll.payslips?.length ?? 0 }}</p>
      </div>
      <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <p class="text-mute text-sm">{{ $t('Total Amount') }}</p>
        <p class="text-focus mt-1 text-base font-semibold">{{ $currency(payroll.total_amount) }}</p>
      </div>
      <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <p class="text-mute text-sm">{{ $t('Status') }}</p>
        <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-sm font-medium capitalize" :class="statusClasses[payroll.status]">
          {{ $t(payroll.status) }}
        </span>
      </div>
    </div>

    <!-- Payslips Table -->
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
      <table class="min-w-full divide-y divide-gray-200 text-base dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="text-focus px-4 py-3 text-start text-sm font-semibold">{{ $t('Employee') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Basic') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Absent Ded.') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Leave Ded.') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Overtime') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Allowances') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Deductions') }}</th>
            <th class="text-focus px-3 py-3 text-center text-sm font-semibold">{{ $t('Net Salary') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-900">
          <template v-if="payroll.payslips?.length">
            <tr v-for="payslip in payroll.payslips" :key="payslip.id">
              <td class="px-4 py-3 font-medium whitespace-nowrap">{{ payslip.employee?.user?.name }}</td>
              <td class="px-3 py-3 text-end">{{ $currency(payslip.basic_salary) }}</td>
              <td class="px-3 py-3 text-end text-red-600 dark:text-red-400">
                {{ payslip.absent_deduction > 0 ? `- ${$currency(payslip.absent_deduction)}` : '0.00' }}
              </td>
              <td class="px-3 py-3 text-end text-red-600 dark:text-red-400">
                {{ payslip.unpaid_leave_deduction > 0 ? `- ${$currency(payslip.unpaid_leave_deduction)}` : '0.00' }}
              </td>
              <td class="px-3 py-3 text-end text-green-600 dark:text-green-400">
                {{ payslip.overtime_amount > 0 ? `+ ${$currency(payslip.overtime_amount)}` : '0.00' }}
              </td>
              <td class="px-3 py-3 text-end text-green-600 dark:text-green-400">
                <template v-if="payslip.items?.filter(i => i.type === 'allowance').length">
                  + {{ $currency(payslip.items.filter(i => i.type === 'allowance').reduce((s, i) => s + parseFloat(i.amount), 0)) }}
                </template>
                <template v-else>0.00</template>
              </td>
              <td class="px-3 py-3 text-end text-red-600 dark:text-red-400">
                <template v-if="payslip.items?.filter(i => i.type === 'deduction').length">
                  - {{ $currency(payslip.items.filter(i => i.type === 'deduction').reduce((s, i) => s + parseFloat(i.amount), 0)) }}
                </template>
                <template v-else>0.00</template>
              </td>
              <td class="px-3 py-3 text-end font-bold">{{ $currency(payslip.net_salary) }}</td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="8">
              <div class="text-mute px-4 py-4 text-base">{{ $t('No payslips found.') }}</div>
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <td colspan="7" class="px-4 py-3 text-end text-base font-semibold">{{ $t('Total Net') }}:</td>
            <td class="px-3 py-3 text-end text-base font-bold">{{ $currency(payroll.total_amount) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <p v-if="payroll.notes" class="text-mute mt-4 text-base">{{ payroll.notes }}</p>
  </div>
</template>
