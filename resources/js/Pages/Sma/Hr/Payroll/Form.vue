<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import { AutoComplete, Button, Input, LoadingButton, Textarea } from '@/Components/Common';
import Icon from '@r/js/Components/Icon.vue';

const { t } = useI18n();
const props = defineProps({
  current: { type: Object, default: null },
  stores: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
});
const emit = defineEmits(['close']);

const expandedRow = ref(null);

const typeOptions = [
  { value: 'allowance', label: t('Allowance') },
  { value: 'deduction', label: t('Deduction') },
];

const makePayslip = employee => ({
  employee_id: employee.id,
  _name: employee.user?.name || `#${employee.id}`,
  basic_salary: parseFloat(employee.basic_salary) || 0,
  working_days: employee.working_days_per_month || 26,
  present_days: employee.working_days_per_month || 26,
  absent_days: 0,
  on_leave_days_paid: 0,
  on_leave_days_unpaid: 0,
  overtime_hours: 0,
  overtime_rate: 0,
  absent_deduction: 0,
  unpaid_leave_deduction: 0,
  overtime_amount: 0,
  gross_salary: parseFloat(employee.basic_salary) || 0,
  total_allowances: 0,
  total_deductions: 0,
  net_salary: parseFloat(employee.basic_salary) || 0,
  notes: '',
  items: [],
});

const buildPayslips = () => {
  if (props.current?.payslips?.length) {
    return props.current.payslips.map(p => ({
      employee_id: p.employee_id,
      _name: p.employee?.user?.name || `#${p.employee_id}`,
      basic_salary: parseFloat(p.basic_salary) || 0,
      working_days: p.working_days || 26,
      present_days: p.present_days || 0,
      absent_days: p.absent_days || 0,
      on_leave_days_paid: p.on_leave_days_paid || 0,
      on_leave_days_unpaid: p.on_leave_days_unpaid || 0,
      overtime_hours: Number(p.overtime_hours || 0),
      overtime_rate: p.overtime_rate || 0,
      absent_deduction: parseFloat(p.absent_deduction) || 0,
      unpaid_leave_deduction: parseFloat(p.unpaid_leave_deduction) || 0,
      overtime_amount: parseFloat(p.overtime_amount) || 0,
      gross_salary: parseFloat(p.gross_salary) || 0,
      total_allowances: parseFloat(p.total_allowances) || 0,
      total_deductions: parseFloat(p.total_deductions) || 0,
      net_salary: parseFloat(p.net_salary) || 0,
      notes: p.notes || '',
      items: (p.items || []).map(i => ({ type: i.type, description: i.description, amount: parseFloat(i.amount) })),
    }));
  }
  return [];
};

const form = useForm({
  store_id: props.current?.store_id ?? null,
  month: props.current?.month ?? new Date().getMonth() + 1,
  year: props.current?.year ?? new Date().getFullYear(),
  title: props.current?.title ?? '',
  status: props.current?.status ?? 'draft',
  notes: props.current?.notes ?? '',
  payslips: buildPayslips(),
});

const storeEmployees = computed(() => props.employees.filter(e => !form.store_id || e.store_id == form.store_id));

const initPayslips = () => {
  form.payslips = storeEmployees.value.map(makePayslip);
};

watch(
  () => form.store_id,
  () => {
    if (!props.current) {
      initPayslips();
    }
  }
);

const recalculate = payslip => {
  const daily = (payslip.working_days || 26) > 0 ? payslip.basic_salary / (payslip.working_days || 26) : 0;

  payslip.present_days = Math.max(
    0,
    (payslip.working_days || 0) - (payslip.absent_days || 0) - (payslip.on_leave_days_paid || 0) - (payslip.on_leave_days_unpaid || 0)
  );
  payslip.absent_deduction = Math.round(daily * (payslip.absent_days || 0) * 100) / 100;
  payslip.unpaid_leave_deduction = Math.round(daily * (payslip.on_leave_days_unpaid || 0) * 100) / 100;
  payslip.overtime_amount = Math.round((payslip.overtime_hours || 0) * (payslip.overtime_rate || 0) * 100) / 100;

  const totalAllowances = payslip.items.filter(i => i.type === 'allowance').reduce((s, i) => s + (parseFloat(i.amount) || 0), 0);
  const totalDeductions = payslip.items.filter(i => i.type === 'deduction').reduce((s, i) => s + (parseFloat(i.amount) || 0), 0);

  payslip.total_allowances = Math.round((totalAllowances + payslip.overtime_amount) * 100) / 100;
  payslip.total_deductions = Math.round((payslip.absent_deduction + payslip.unpaid_leave_deduction + totalDeductions) * 100) / 100;
  payslip.gross_salary = Math.round((payslip.basic_salary + payslip.total_allowances) * 100) / 100;
  payslip.net_salary = Math.round((payslip.gross_salary - payslip.total_deductions) * 100) / 100;
};

const addItem = payslip => {
  payslip.items.push({ type: 'allowance', description: '', amount: 0 });
};

const removeItem = (payslip, index) => {
  payslip.items.splice(index, 1);
  recalculate(payslip);
};

const totalNet = computed(() => form.payslips.reduce((s, p) => s + (parseFloat(p.net_salary) || 0), 0));

const monthOptions = Array.from({ length: 12 }, (_, i) => ({
  value: i + 1,
  label: new Date(2000, i).toLocaleString('default', { month: 'long' }),
}));

const statusOptions = [
  { value: 'draft', label: t('Draft') },
  { value: 'processed', label: t('Processed') },
];

const submit = () => {
  const payload = {
    ...form.data(),
    payslips: form.payslips.map(({ _name, ...p }) => p),
  };

  if (props.current?.id) {
    form
      .transform(() => payload)
      .put(route('payrolls.update', props.current.id), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
      });
  } else {
    form
      .transform(() => payload)
      .post(route('payrolls.store'), {
        preserveScroll: true,
        onSuccess: () => {
          form.reset();
          emit('close');
        },
      });
  }
};
</script>

<template>
  <div class="flex flex-col">
    <!-- Header -->
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
      <h2 class="text-focus text-base font-semibold">{{ current ? $t('Edit Payroll') : $t('New Payroll') }}</h2>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6 overflow-y-auto p-6">
      <!-- Basic Info -->
      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 sm:col-span-4">
          <AutoComplete
            :json="true"
            :label="$t('Store')"
            :suggestions="stores"
            v-model="form.store_id"
            :error="form.errors.store_id"
            :placeholder="$t('Select store')"
          />
        </div>
        <div class="col-span-12 sm:col-span-4">
          <AutoComplete
            :json="true"
            :label="$t('Month')"
            v-model="form.month"
            :error="form.errors.month"
            :suggestions="monthOptions.map(m => ({ value: m.value, label: $t(m.label) }))"
          />
        </div>
        <div class="col-span-12 sm:col-span-4">
          <Input :label="$t('Year')" type="number" :required="true" v-model="form.year" :error="form.errors.year" />
        </div>
        <div class="col-span-12 sm:col-span-8">
          <Input :label="$t('Title')" :required="true" type="text" v-model="form.title" :error="form.errors.title" />
        </div>
        <div class="col-span-12 sm:col-span-4">
          <AutoComplete
            :json="true"
            :label="$t('Status')"
            v-model="form.status"
            :error="form.errors.status"
            :suggestions="statusOptions.map(s => ({ value: s.value, label: $t(s.label) }))"
          />
        </div>
        <div class="col-span-12">
          <Textarea :label="$t('Notes')" v-model="form.notes" rows="2" />
        </div>
      </div>

      <!-- Payslips -->
      <div>
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-focus text-sm font-semibold">{{ $t('Payslips') }}</h3>
          <Button v-if="!current" type="button" variant="secondary" size="sm" @click="initPayslips" :disabled="!form.store_id">
            {{ $t('Load Employees') }}
          </Button>
        </div>

        <div v-if="form.payslips.length" class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
          <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 whitespace-nowrap dark:bg-gray-800">
              <tr>
                <th class="text-focus px-3 py-2 text-start text-xs font-semibold">{{ $t('Employee') }}</th>
                <th class="text-focus w-20 px-2 py-2 text-start text-xs font-semibold">{{ $t('Basic') }}</th>
                <th class="text-focus w-12 px-2 py-2 text-start text-xs font-semibold">{{ $t('Absent') }}</th>
                <th class="text-focus w-12 px-2 py-2 text-start text-xs font-semibold">{{ $t('Leave Paid') }}</th>
                <th class="text-focus w-12 px-2 py-2 text-start text-xs font-semibold">{{ $t('Leave Unpaid') }}</th>
                <th class="text-focus w-12 px-2 py-2 text-start text-xs font-semibold">{{ $t('OT Hrs') }}</th>
                <th class="text-focus w-12 px-2 py-2 text-start text-xs font-semibold">{{ $t('OT Rate') }}</th>
                <th class="text-focus w-20 px-2 py-2 text-start text-xs font-semibold">{{ $t('Net Salary') }}</th>
                <!-- <th class="px-2 py-2"></th> -->
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-for="(payslip, idx) in form.payslips" :key="payslip.employee_id">
                <tr>
                  <td class="px-3 py-2 font-medium whitespace-nowrap">
                    <div class="flex items-center justify-between gap-x-6">
                      {{ payslip._name }}
                      <button
                        type="button"
                        @click="expandedRow = expandedRow === idx ? null : idx"
                        class="text-xs text-primary-600 hover:underline dark:text-primary-400"
                      >
                        {{ $t('Items') }} ({{ payslip.items.length }})
                      </button>
                    </div>
                  </td>
                  <td class="px-2 py-1">
                    <input
                      v-model="payslip.basic_salary"
                      @change="recalculate(payslip)"
                      class="w-32 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                    />
                  </td>
                  <td class="px-2 py-1">
                    <input
                      type="number"
                      v-model.number="payslip.absent_days"
                      min="0"
                      @input="recalculate(payslip)"
                      class="w-16 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                    />
                  </td>
                  <td class="px-2 py-1">
                    <input
                      type="number"
                      v-model.number="payslip.on_leave_days_paid"
                      min="0"
                      @input="recalculate(payslip)"
                      class="w-16 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                    />
                  </td>
                  <td class="px-2 py-1">
                    <input
                      type="number"
                      v-model.number="payslip.on_leave_days_unpaid"
                      min="0"
                      @input="recalculate(payslip)"
                      class="w-16 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                    />
                  </td>
                  <td class="px-2 py-1">
                    <input
                      type="number"
                      v-model.number="payslip.overtime_hours"
                      min="0"
                      step="0.5"
                      @input="recalculate(payslip)"
                      class="w-16 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                    />
                  </td>
                  <td class="px-2 py-1">
                    <input
                      type="number"
                      v-model.number="payslip.overtime_rate"
                      min="0"
                      step="0.01"
                      @input="recalculate(payslip)"
                      class="w-16 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                    />
                  </td>
                  <td class="px-2 py-2 text-end font-semibold whitespace-nowrap">{{ $n(payslip.net_salary) }}</td>
                </tr>
                <!-- Expanded items -->
                <tr v-if="expandedRow === idx">
                  <td colspan="100%" class="bg-gray-50 px-6 py-3 dark:bg-gray-800">
                    <div class="space-y-2">
                      <div v-for="(item, itemIdx) in payslip.items" :key="itemIdx" class="flex items-center gap-2">
                        <select
                          v-model="item.type"
                          @change="recalculate(payslip)"
                          class="w-32 rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700"
                        >
                          <option v-for="t in typeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                        <input
                          type="text"
                          v-model="item.description"
                          :placeholder="$t('Description')"
                          class="w-48 rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700"
                        />
                        <input
                          type="number"
                          v-model.number="item.amount"
                          min="0"
                          step="0.01"
                          @input="recalculate(payslip)"
                          class="w-24 rounded border border-gray-300 px-2 py-1 text-end text-sm dark:border-gray-600 dark:bg-gray-700"
                        />
                        <button type="button" @click="removeItem(payslip, itemIdx)" class="text-xs text-red-500 hover:text-red-700">
                          <Icon name="x" />
                        </button>
                      </div>
                      <button
                        type="button"
                        @click="addItem(payslip)"
                        class="text-xs text-primary-600 hover:underline dark:text-primary-400"
                      >
                        + {{ $t('Add Item') }}
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <td colspan="7" class="px-3 py-2 text-end text-sm font-semibold">{{ $t('Total') }}:</td>
                <td class="px-2 py-2 text-end text-sm font-bold">{{ $n(totalNet) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <p v-else class="text-mute text-sm">{{ $t('Select a store and click "Load Employees" to add payslips.') }}</p>
      </div>

      <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-4 dark:border-gray-700">
        <button type="button" class="text-mute text-sm" @click="$emit('close')">{{ $t('Cancel') }}</button>
        <LoadingButton type="submit" :loading="form.processing">{{ $t('Save') }}</LoadingButton>
      </div>
    </form>
  </div>
</template>
