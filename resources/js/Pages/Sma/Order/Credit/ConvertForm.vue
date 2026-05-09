<script setup>
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { computed, ref } from 'vue';
import { Button } from '@/Components/Common';

const props = defineProps(['sale']);
const emit = defineEmits(['close']);

const form = useForm({ installments: [] });
const singlePayment = ref(true);

const remaining = computed(() => (props.sale.grand_total ?? 0) - (props.sale.paid ?? 0));
const installmentTotal = computed(() => form.installments.reduce((s, i) => s + parseFloat(i.amount || 0), 0));
const totalMatch = computed(() => Math.abs(installmentTotal.value - remaining.value) < 0.01);

function addInstallment() {
  form.installments.push({
    amount:   '',
    due_date: dayjs().add(30 * (form.installments.length + 1), 'day').format('YYYY-MM-DD'),
    notes:    '',
  });
}

function removeInstallment(i) {
  form.installments.splice(i, 1);
}

function initSingle() {
  form.installments = [{
    amount:   remaining.value.toFixed(2),
    due_date: dayjs().add(30, 'day').format('YYYY-MM-DD'),
    notes:    '',
  }];
}

function initMultiple() {
  form.installments = [
    { amount: '', due_date: dayjs().add(30, 'day').format('YYYY-MM-DD'), notes: '' },
    { amount: '', due_date: dayjs().add(60, 'day').format('YYYY-MM-DD'), notes: '' },
  ];
}

// Initialise with single payment mode
initSingle();

function toggleMode(single) {
  singlePayment.value = single;
  single ? initSingle() : initMultiple();
}

function submit() {
  form.post(route('credits.convert', { sale: props.sale.id }), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  });
}
</script>

<template>
  <div class="p-6">
    <h2 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">
      {{ $t('Convert to Credit (Deyn)') }}
    </h2>
    <p class="mb-4 text-sm text-gray-500">
      {{ $t('Reference') }}: <strong class="font-mono">{{ sale.reference }}</strong> &nbsp;|&nbsp;
      {{ $t('Remaining') }}: <strong class="text-red-600">{{ format_number(remaining) }}</strong>
    </p>

    <!-- Mode toggle -->
    <div class="mb-4 flex gap-2">
      <button @click="toggleMode(true)"
        class="rounded-full px-3 py-1 text-sm font-medium transition"
        :class="singlePayment ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
        {{ $t('Single Payment') }}
      </button>
      <button @click="toggleMode(false)"
        class="rounded-full px-3 py-1 text-sm font-medium transition"
        :class="!singlePayment ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
        {{ $t('Installments') }}
      </button>
    </div>

    <!-- Installment rows -->
    <div class="space-y-3">
      <div v-for="(row, i) in form.installments" :key="i"
           class="grid grid-cols-12 gap-2 items-end rounded-lg border border-gray-200 dark:border-gray-700 p-3">
        <div class="col-span-4 sm:col-span-5">
          <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('Amount') }}</label>
          <input type="number" v-model="row.amount" min="0.01" step="0.01"
            class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800" />
          <p v-if="form.errors['installments.' + i + '.amount']" class="mt-0.5 text-xs text-red-500">
            {{ form.errors['installments.' + i + '.amount'] }}
          </p>
        </div>
        <div class="col-span-5">
          <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('Due Date') }}</label>
          <input type="date" v-model="row.due_date"
            class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800" />
        </div>
        <div class="col-span-3 sm:col-span-2 flex justify-end">
          <button v-if="!singlePayment && form.installments.length > 1" @click="removeInstallment(i)"
            class="rounded p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
            <Icon name="trash-o" size="size-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Add installment button -->
    <button v-if="!singlePayment" @click="addInstallment"
      class="mt-3 flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
      <Icon name="plus-o" size="size-4" /> {{ $t('Add Installment') }}
    </button>

    <!-- Total validation -->
    <div v-if="!singlePayment" class="mt-3 rounded-lg p-3 text-sm"
      :class="totalMatch ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400'">
      {{ $t('Installments total') }}: <strong>{{ format_number(installmentTotal) }}</strong>
      &nbsp;/&nbsp;
      {{ $t('Remaining') }}: <strong>{{ format_number(remaining) }}</strong>
      <span v-if="!totalMatch"> &nbsp;⚠ {{ $t('Totals must match') }}</span>
    </div>

    <div class="mt-5 flex justify-end gap-3">
      <Button variant="secondary" @click="emit('close')">{{ $t('Cancel') }}</Button>
      <Button @click="submit" :disabled="form.processing || (!singlePayment && !totalMatch)">
        {{ $t('Convert to Credit') }}
      </Button>
    </div>
  </div>
</template>
