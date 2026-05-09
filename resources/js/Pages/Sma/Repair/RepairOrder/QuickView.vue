<script setup>
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import QRCode from 'qrcode';
import { Button, ViewCustomFields } from '@/Components/Common';
import SecondaryButton from '@/Components/Jet/SecondaryButton.vue';
import { axios } from '@/Core';

const emit = defineEmits(['close']);
const props = defineProps(['record', 'custom_fields', 'xfetch']);

const qrcode = ref(null);
const order = ref(props.record);
const statusColors = computed(() => {
  const colors = {
    pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    waiting_parts: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  };
  return colors[order.value?.status] || colors.pending;
});

const priorityColors = computed(() => {
  const colors = {
    low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    normal: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
    high: 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
  };
  return colors[order.value?.priority] || colors.normal;
});

const isOverdue = computed(() => {
  if (!order.value?.due_date) return false;
  if (['completed', 'delivered', 'cancelled'].includes(order.value.status)) return false;
  return dayjs(order.value.due_date).isBefore(dayjs());
});

async function generateQR(text) {
  try {
    return await QRCode.toString(text, { type: 'svg' });
  } catch (err) {
    console.error(err);
    return null;
  }
}

onMounted(async () => {
  if (!props.xfetch && order.value) {
    await axios.get(route('repair-orders.show', order.value.id)).then(res => (order.value = res.data));
  }
  if (order.value?.hash) {
    qrcode.value = await generateQR(route('repair-status.index', { hash: order.value.hash }));
  }
});

function editRecord() {
  router.visit(route('repair-orders.edit', { repair_order: order.value.id }));
  emit('close');
}

function generateInvoice() {
  router.post(
    route('repair-orders.generate-invoice', { repair_order: order.value.id }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    }
  );
}

function formatFileSize(bytes) {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function downloadAttachment(attachment) {
  window.open(attachment.url, '_blank');
}

function print() {
  window.print();
}
</script>

<template>
  <div v-if="order" class="p-6 print:p-0">
    <template v-if="!xfetch">
      <span class="absolute end-12 top-4 inline-flex items-center gap-x-4 sm:end-14 print:hidden">
        <button type="button" @click="print" class="link -m-2 p-2">
          <Icon name="print-o" class="size-5" />
        </button>

        <button v-if="$can('update-repair-orders')" @click="editRecord" type="button" class="link -m-2 p-2">
          <Icon name="edit-o" class="size-5" />
        </button>
      </span>
    </template>

    <div class="mb-6 flex items-end justify-between capitalize">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ order.reference }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ $t('Repair Order Details') }}
        </p>
      </div>
      <div class="mr-8 flex gap-2">
        <span :class="priorityColors" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize">
          {{ $t(order.priority) }}
        </span>
        <span :class="statusColors" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize">
          {{ $t(order.status.replace('_', ' ')) }}
        </span>
        <span
          v-if="isOverdue"
          class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800 capitalize dark:bg-red-900 dark:text-red-300"
        >
          {{ $t('Overdue') }}
        </span>
      </div>
    </div>

    <div class="space-y-6 print:space-y-4">
      <!-- Customer Information -->
      <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Customer Information') }}</h3>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
          <div class="grid grid-cols-1 gap-x-4 gap-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Name') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.customer?.company || order.customer?.name || '' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Phone') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.customer?.phone || '' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Email') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.customer?.email || '' }}</dd>
            </div>
          </div>
          <div v-if="qrcode" class="flex items-start justify-end">
            <div v-html="qrcode" class="size-38 overflow-hidden rounded" />
          </div>
        </dl>
      </div>

      <!-- Service Information -->
      <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Service Information') }}</h3>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Service Type') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.service_type?.name || '' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Serial Number') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.serial_no || '' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Device Condition') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 capitalize dark:text-white">
              {{ order.device_condition ? $t(order.device_condition) : '' }}
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Technician') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.technician?.name || $t('Not Assigned') }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Store') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.store?.name || '' }}</dd>
          </div>

          <!-- Custom Field -->
          <ViewCustomFields
            :line="true"
            :modal="false"
            :fields="custom_fields"
            :title="$t('Custom Fields')"
            :extra_attributes="order.extra_attributes"
          />
          <div class="sm:col-span-2">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Problem Description') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.problem_description || '' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Timeline -->
      <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Timeline') }}</h3>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-3">
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Received Date') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $date(order.received_date) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Due Date') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white" :class="{ 'font-semibold text-red-600 dark:text-red-400': isOverdue }">
              {{ order.due_date ? $date(order.due_date) : '' }}
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Completed Date') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.completed_date ? $date(order.completed_date) : '' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Cost Information -->
      <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Cost Information') }}</h3>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Price') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.price ? $currency(order.price) : '' }}</dd>
          </div>
          <div class="print:hidden">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Actual Cost') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.actual_cost ? $currency(order.actual_cost) : '' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Notes -->
      <div
        class="border-b border-gray-200 pb-4 dark:border-gray-700 print:border-0 print:pb-0"
        v-if="order.technician_comment || order.internal_notes || order.customer_notes"
      >
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Notes') }}</h3>
        <dl class="space-y-3">
          <div v-if="order.technician_comment" class="print:hidden">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Technician Comment') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.technician_comment }}</dd>
          </div>
          <div v-if="order.internal_notes" class="print:hidden">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Internal Notes') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.internal_notes }}</dd>
          </div>
          <div v-if="order.customer_notes">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Customer Notes') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ order.customer_notes }}</dd>
          </div>
        </dl>
      </div>

      <!-- Attachments -->
      <div v-if="order?.attachments && order.attachments.length > 0" class="mt-4">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Attachments') }}</h3>
        <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
          <li v-for="attachment in order.attachments" :key="attachment.id" class="flex items-center justify-between py-2 pr-4 pl-3 text-sm">
            <div class="flex w-0 flex-1 items-center">
              <Icon name="paperclip" class="size-5 shrink-0 text-gray-400" />
              <span class="ml-2 w-0 flex-1 truncate">{{ attachment.original_filename }}</span>
              <span class="ml-2 shrink-0 text-gray-400">{{ formatFileSize(attachment.size) }}</span>
            </div>
            <div class="ml-4 flex shrink-0 space-x-2">
              <a
                target="_blank"
                class="font-medium text-blue-600 hover:text-blue-500"
                :href="route('repair-order-attachments.download', { attachment: attachment.id })"
              >
                {{ $t('Download') }}
              </a>
              <button @click="removeExistingAttachment(attachment.id)" type="button" class="font-medium text-red-600 hover:text-red-500">
                {{ $t('Delete') }}
              </button>
            </div>
          </li>
        </ul>
      </div>

      <!-- Invoice Information -->
      <div v-if="order.invoice_id" class="rounded-md bg-green-50 p-4 dark:bg-green-950">
        <div class="flex">
          <div class="shrink-0">
            <Icon name="check-circle" class="size-5 text-green-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800 dark:text-green-200">
              {{ $t('Invoice Generated') }}
            </p>
            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
              {{ $t('Invoice') }}: {{ order.invoice?.reference || order.invoice_id }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 flex justify-between gap-3 print:hidden">
      <SecondaryButton @click="$emit('close')">
        {{ $t('Close') }}
      </SecondaryButton>
      <div class="flex gap-4">
        <Button v-if="$can('update-repair-orders')" @click="editRecord">
          {{ $t('Edit') }}
        </Button>
        <Button v-if="order.status === 'completed' && !order.invoice_id && $can('create-sales')" @click="generateInvoice">
          {{ $t('Generate Invoice') }}
        </Button>
      </div>
    </div>
  </div>
</template>
