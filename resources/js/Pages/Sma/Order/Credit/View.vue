<script setup>
import { useForm, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ref, computed } from 'vue';
import { Button } from '@/Components/Common';
import { Modal } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps(['sale']);

const payModal = ref(false);
const current = ref(null);

const payForm = useForm({
  amount:  '',
  method:  'Cash',
  date:    dayjs().format('YYYY-MM-DD'),
  notes:   '',
});

const totalAmount = computed(() => props.sale.credit_installments?.reduce((s, i) => s + parseFloat(i.amount), 0) ?? 0);
const totalPaid   = computed(() => props.sale.credit_installments?.filter(i => i.status === 'paid').reduce((s, i) => s + parseFloat(i.amount), 0) ?? 0);
const remaining   = computed(() => totalAmount.value - totalPaid.value);

function openPay(installment) {
  current.value = installment;
  payForm.amount = parseFloat(installment.amount).toFixed(2);
  payForm.date   = dayjs().format('YYYY-MM-DD');
  payForm.notes  = '';
  payModal.value = true;
}

function submitPayment() {
  payForm.post(route('credits.pay', { sale: props.sale.id, installment: current.value.id }), {
    preserveScroll: true,
    onSuccess: () => { payModal.value = false; router.reload({ only: ['sale'] }); },
  });
}

function statusClass(status) {
  return {
    pending:  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    paid:     'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    overdue:  'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  }[status] ?? 'bg-gray-100 text-gray-800';
}

function agingBucket(due_date) {
  const days = dayjs().diff(dayjs(due_date), 'day');
  if (days <= 0) return null;
  if (days <= 30) return '1–30d';
  if (days <= 60) return '31–60d';
  if (days <= 90) return '61–90d';
  return '90d+';
}
</script>

<template>
  <Head><title>{{ $t('Credit (Deyn)') }} — {{ sale.reference }}</title></Head>

  <Header>
    {{ $t('Credit (Deyn)') }}: {{ sale.reference }}
    <template #subheading>{{ sale.customer?.name }}</template>
    <template #menu>
      <Button variant="secondary" :href="route('credits.index')">← {{ $t('Back') }}</Button>
    </template>
  </Header>

  <div class="p-4 sm:p-6 space-y-6">

    <!-- Summary cards -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $t('Grand Total') }}</p>
        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ format_number(sale.grand_total) }}</p>
      </div>
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $t('Total Paid') }}</p>
        <p class="mt-1 text-xl font-bold text-green-600 dark:text-green-400">{{ format_number(totalPaid) }}</p>
      </div>
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $t('Remaining') }}</p>
        <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400">{{ format_number(remaining) }}</p>
      </div>
      <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $t('Status') }}</p>
        <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
          :class="statusClass(sale.credit_status)">{{ $t(sale.credit_status ?? 'pending') }}</span>
      </div>
    </div>

    <!-- Customer & Sale info -->
    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="mb-3 font-semibold text-gray-900 dark:text-white">{{ $t('Sale Details') }}</h3>
      <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
        <div>
          <dt class="text-gray-500">{{ $t('Customer') }}</dt>
          <dd class="font-medium text-gray-900 dark:text-white">{{ sale.customer?.name }}</dd>
        </div>
        <div>
          <dt class="text-gray-500">{{ $t('Phone') }}</dt>
          <dd class="font-medium text-gray-900 dark:text-white">{{ sale.customer?.phone ?? '—' }}</dd>
        </div>
        <div>
          <dt class="text-gray-500">{{ $t('Date') }}</dt>
          <dd class="font-medium text-gray-900 dark:text-white">{{ sale.date }}</dd>
        </div>
        <div>
          <dt class="text-gray-500">{{ $t('Store') }}</dt>
          <dd class="font-medium text-gray-900 dark:text-white">{{ sale.store?.name }}</dd>
        </div>
      </dl>
    </div>

    <!-- Installment timeline -->
    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 font-semibold text-gray-900 dark:text-white">
        {{ $t('Payment Plan') }}
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">#</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Amount') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Due Date') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Status') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Aging') }}</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">{{ $t('Paid Date') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="(inst, i) in sale.credit_installments" :key="inst.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-4 py-3 text-gray-500">{{ i + 1 }}</td>
              <td class="px-4 py-3 text-right font-medium">{{ format_number(inst.amount) }}</td>
              <td class="px-4 py-3 text-center">{{ inst.due_date }}</td>
              <td class="px-4 py-3 text-center">
                <span class="rounded-full px-2 py-0.5 text-xs font-medium capitalize" :class="statusClass(inst.status)">
                  {{ $t(inst.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-xs"
                :class="agingBucket(inst.due_date) ? 'text-red-500 font-medium' : 'text-gray-400'">
                {{ agingBucket(inst.due_date) ?? '—' }}
              </td>
              <td class="px-4 py-3 text-center text-gray-500">{{ inst.paid_date ?? '—' }}</td>
              <td class="px-4 py-3 text-right">
                <button v-if="inst.status !== 'paid'" @click="openPay(inst)"
                  class="rounded px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                  {{ $t('Mark as Paid') }}
                </button>
                <span v-else class="text-xs text-green-500">✓ {{ $t('Paid') }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Items sold -->
    <div v-if="sale.items?.length" class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
      <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 font-semibold text-gray-900 dark:text-white">
        {{ $t('Items') }}
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">{{ $t('Product') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Qty') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Price') }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">{{ $t('Total') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="item in sale.items" :key="item.id">
              <td class="px-4 py-2">{{ item.product?.name }}</td>
              <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
              <td class="px-4 py-2 text-right">{{ format_number(item.price) }}</td>
              <td class="px-4 py-2 text-right font-medium">{{ format_number(item.quantity * item.price) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Pay installment modal -->
  <Modal :show="payModal" max-width="sm" @close="payModal = false">
    <div class="p-6">
      <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">
        {{ $t('Record Installment Payment') }}
      </h3>
      <div class="space-y-3">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Amount') }}</label>
          <input type="number" v-model="payForm.amount" min="0.01" step="0.01"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Payment Method') }}</label>
          <select v-model="payForm.method"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800">
            <option value="Cash">{{ $t('Cash') }}</option>
            <option value="Card">{{ $t('Card') }}</option>
            <option value="WAAFI Pay">WAAFI Pay</option>
            <option value="Bank Transfer">{{ $t('Bank Transfer') }}</option>
            <option value="Cheque">{{ $t('Cheque') }}</option>
            <option value="Other">{{ $t('Other') }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Date') }}</label>
          <input type="date" v-model="payForm.date"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Notes') }}</label>
          <input type="text" v-model="payForm.notes" :placeholder="$t('Optional notes')"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800" />
        </div>
      </div>
      <div class="mt-5 flex justify-end gap-3">
        <Button variant="secondary" @click="payModal = false">{{ $t('Cancel') }}</Button>
        <Button @click="submitPayment" :disabled="payForm.processing">{{ $t('Record Payment') }}</Button>
      </div>
    </div>
  </Modal>
</template>
