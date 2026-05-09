<script setup>
import { usePage } from '@inertiajs/vue3';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';
import { computed, nextTick, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { Attachments, ViewCustomFields } from '@/Components/Common';
import { Modal } from '@/Components/Jet';

import PackingList from './PackingList.vue';

const { t } = useI18n();

const page = usePage();
const props = defineProps({
  record: { type: Object, required: true },
  type: { type: String, required: true },
  custom_fields: { type: Array, default: () => [] },
  editRow: { type: Function, default: null },
  xfetch: { type: Boolean, default: false },
  qrUrl: { type: String, default: null },
  showPackingList: { type: Boolean, default: false },
});

const colspan = ref(4);
const qrcode = ref(null);
const packing = ref(false);

const typeConfig = computed(() => {
  const configs = {
    sale: {
      label: t('Sale'),
      noLabel: t('{x} No.', { x: t('Sale') }),
      permission: 'update-sales',
      personLabel: t('Bill To'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      addressLabel: t('Ship To'),
      showItems: true,
      showPayments: true,
      showPaid: true,
      showBalance: true,
      priceField: 'price',
      quantityField: 'quantity',
    },
    quotation: {
      label: t('Quotation'),
      noLabel: t('{x} No.', { x: t('Quotation') }),
      permission: 'update-quotations',
      personLabel: t('Quote To'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      addressLabel: t('Ship To'),
      showItems: true,
      showPayments: false,
      showPaid: false,
      showBalance: false,
      priceField: 'price',
      quantityField: 'quantity',
    },
    purchase: {
      label: t('Purchase'),
      noLabel: t('{x} No.', { x: t('Purchase') }),
      permission: 'update-purchases',
      personLabel: t('Purchase From'),
      showPerson: true,
      personField: 'supplier',
      showAddress: false,
      showItems: true,
      showPayments: true,
      showPaid: true,
      showBalance: true,
      priceField: 'cost',
      quantityField: 'quantity',
    },
    payment: {
      label: t('Payment'),
      noLabel: t('{x} No.', { x: t('Payment') }),
      permission: 'update-payments',
      personLabel: t('From'),
      showPerson: true,
      personField: 'customer',
      showAddress: false,
      showItems: false,
      showPayments: false,
      showPaid: false,
      showBalance: false,
    },
    expense: {
      label: t('Expense'),
      noLabel: t('{x} No.', { x: t('Expense') }),
      permission: 'update-expenses',
      personLabel: t('Order To'),
      showPerson: true,
      personField: 'supplier',
      showAddress: false,
      showItems: false,
      showPayments: false,
      showPaid: false,
      showBalance: false,
    },
    transfer: {
      label: t('Transfer'),
      noLabel: t('{x} No.', { x: t('Transfer') }),
      permission: 'update-transfers',
      personLabel: t('Transfer To'),
      showPerson: true,
      personField: 'to_store',
      showAddress: false,
      showItems: true,
      showPayments: false,
      showPaid: false,
      showBalance: false,
      showPrices: false,
      quantityField: 'quantity',
    },
    adjustment: {
      label: t('Adjustment'),
      noLabel: t('{x} No.', { x: t('Adjustment') }),
      permission: 'update-adjustments',
      showPerson: false,
      showAddress: false,
      showItems: true,
      showPayments: false,
      showPaid: false,
      showBalance: false,
      showPrices: false,
      quantityField: 'quantity',
    },
    return_order: {
      label: t('Return Order'),
      noLabel: t('{x} No.', { x: t('Return Order') }),
      permission: 'update-return-orders',
      personLabel: props.record?.type === 'Purchase' ? t('Returned To') : t('Returned From'),
      showPerson: true,
      personField: props.record?.type === 'Purchase' ? 'supplier' : 'customer',
      showAddress: false,
      showItems: true,
      showPayments: false,
      showPaid: false,
      showBalance: false,
      priceField: props.record?.type === 'Purchase' ? 'cost' : 'price',
      quantityField: 'quantity',
    },
    delivery: {
      label: t('Delivery'),
      noLabel: t('{x} No.', { x: t('Delivery Order') }),
      permission: 'update-deliveries',
      personLabel: t('Deliver To'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      showItems: false,
      showPayments: false,
      showPaid: false,
      showBalance: false,
    },
  };
  return configs[props.type] || configs.sale;
});

const person = computed(() => {
  if (!typeConfig.value.showPerson || !typeConfig.value.personField) return null;
  return props.record[typeConfig.value.personField];
});

onMounted(async () => {
  if (page.props.settings.show_discount == 1) {
    colspan.value++;
  }
  if (page.props.settings.show_tax == 1) {
    colspan.value++;
  }

  await nextTick();
  if (props.qrUrl) {
    qrcode.value = await generateQR(props.qrUrl);
  }
  JsBarcode('.barcode').init();
});

async function generateQR(text) {
  try {
    return await QRCode.toString(text, { type: 'svg' });
  } catch (err) {
    console.error(err);
    return `<span class="text-red-500">${err.toString()}</span>`;
  }
}

function print() {
  window.print();
}
</script>

<template>
  <div>
    <template v-if="!xfetch">
      <span class="absolute end-12 top-4 inline-flex items-center gap-x-4 sm:end-14 print:hidden">
        <button type="button" @click="print" class="link -m-2 p-2">
          <Icon name="print-o" class="size-5" />
        </button>
        <button v-if="showPackingList" type="button" @click="packing = true" class="link -m-2 p-2">
          <Icon name="archive" class="size-5" />
        </button>
        <button v-if="editRow && $can(typeConfig.permission)" type="button" @click="() => editRow(record)" class="link -m-2 p-2">
          <Icon name="edit-o" class="size-5" />
        </button>
      </span>

      <div class="border-b border-gray-200 px-4 py-4 sm:px-6 dark:border-gray-700 print:hidden">
        <div class="sm:flex sm:items-baseline sm:justify-between">
          <div class="sm:w-0 sm:flex-1">
            <h1 class="text-focus text-base font-semibold">{{ typeConfig.label }} #{{ record?.id }} ({{ record?.reference }})</h1>
            <p class="text-mute mt-1 truncate text-sm">
              {{ $t('Please view the details below') }}
            </p>
          </div>
        </div>
      </div>
    </template>

    <div class="mb-6">
      <slot name="alerts" />
    </div>

    <div class="px-6 py-6 print:py-4">
      <!-- Centered header -->
      <div class="mb-4 text-center">
        <div class="mb-2 flex justify-center">
          <div class="max-h-16 max-w-[220px]">
            <img v-if="record.store?.logo" class="h-16 max-w-full" :src="record.store.logo" :alt="record.store.name" />
            <template v-else>
              <img
                :alt="$page.props.settings?.name"
                :src="$page.props.settings?.logo"
                v-if="$page.props.settings?.logo"
                class="h-16 max-w-full dark:hidden print:block!"
              />
              <img
                :alt="$page.props.settings?.name"
                :src="$page.props.settings?.logo_dark"
                v-if="$page.props.settings?.logo_dark"
                class="hidden h-16 max-w-full dark:block print:hidden!"
              />
            </template>
          </div>
        </div>
        <div class="text-lg font-bold">{{ record.store?.name || $page.props.settings?.name }}</div>
        <div class="mt-1 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
          <span v-if="record.store?.phone || $page.props.settings?.phone">
            {{ record.store?.phone || $page.props.settings?.phone }}
          </span>
          <span v-if="record.store?.email || $page.props.settings?.email">
            {{ record.store?.email || $page.props.settings?.email }}
          </span>
          <span v-if="record.store">{{ $address(record.store) }}</span>
        </div>
      </div>

      <!-- Decorative divider with document type -->
      <div class="relative mb-6 flex items-center gap-4">
        <div class="print:exact h-px flex-1 bg-gray-300 dark:bg-gray-600 print:bg-gray-300!"></div>
        <div
          class="shrink-0 rounded-full border border-gray-300 px-5 py-1.5 text-sm font-bold tracking-widest uppercase dark:border-gray-600 print:border-gray-300!"
        >
          {{ typeConfig.label }}
        </div>
        <div class="print:exact h-px flex-1 bg-gray-300 dark:bg-gray-600 print:bg-gray-300!"></div>
      </div>

      <!-- Invoice meta row -->
      <div class="mb-6 grid grid-cols-2 gap-4 text-sm" :class="record.due_date ? 'sm:grid-cols-4' : 'sm:grid-cols-3'">
        <div>
          <div class="text-xs text-gray-400 uppercase">{{ typeConfig.noLabel }}</div>
          <div class="mt-0.5 font-bold">#{{ record.id }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-400 uppercase">{{ $t('Date') }}</div>
          <div class="mt-0.5 font-bold">{{ $date(record.date || record.created_at) }}</div>
        </div>
        <div v-if="record.due_date">
          <div class="text-xs text-gray-400 uppercase">{{ $t('Due Date') }}</div>
          <div class="mt-0.5 font-bold">{{ $date(record.due_date) }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-400 uppercase">{{ $t('Reference') }}</div>
          <div class="mt-0.5 truncate font-bold">{{ record.reference }}</div>
        </div>
      </div>

      <div v-if="type == 'sale'" class="mb-4 text-sm font-bold">
        <div v-if="$page.props.settings?.sale_header" class="text-center">{{ $page.props.settings?.sale_header }}</div>
      </div>
      <div v-else-if="type == 'purchase'" class="mb-4 text-sm font-bold">
        <div v-if="$page.props.settings?.purchase_header" class="text-center">{{ $page.props.settings?.purchase_header }}</div>
      </div>
      <div v-else-if="type == 'payment'" class="mb-4 text-sm font-bold">
        <div v-if="$page.props.settings?.payment_header" class="text-center">{{ $page.props.settings?.payment_header }}</div>
      </div>
      <div v-else-if="type == 'quotation'" class="mb-4 text-sm font-bold">
        <div v-if="$page.props.settings?.quotation_header" class="text-center">{{ $page.props.settings?.quotation_header }}</div>
      </div>

      <!-- Barcodes -->
      <div v-if="qrUrl" class="mb-6 flex items-center justify-center gap-6">
        <div class="bc-image h-[70px] overflow-hidden">
          <svg
            class="barcode"
            :jsbarcode-width="1"
            :jsbarcode-margin="5"
            :jsbarcode-height="60"
            :jsbarcode-fontsize="11"
            :jsbarcode-textmargin="3"
            jsbarcode-format="CODE128"
            jsbarcode-fontoptions="bold"
            :jsbarcode-displayvalue="false"
            :jsbarcode-value="record.reference"
          />
        </div>
        <div v-html="qrcode" class="qr-image qrcode h-[70px] overflow-hidden" />
      </div>

      <!-- Addresses -->
      <div v-if="person" class="mb-6 grid gap-6 text-sm sm:grid-cols-2">
        <div>
          <div class="mb-1.5 flex items-center gap-2">
            <div class="h-px w-5 bg-gray-400 dark:bg-gray-500 print:bg-gray-400!"></div>
            <span class="text-xs font-bold tracking-widest text-gray-400 uppercase dark:text-gray-500 print:text-gray-400!">
              {{ typeConfig.personLabel }}
            </span>
          </div>
          <div class="font-semibold">{{ person.company || person.name }}</div>
          <div class="text-gray-500 dark:text-gray-400">{{ $address(person) }}</div>
          <div v-if="person.phone" class="text-gray-500 dark:text-gray-400">{{ $t('Phone') }}: {{ person.phone }}</div>
          <div v-if="person.email" class="text-gray-500 dark:text-gray-400">{{ $t('Email') }}: {{ person.email }}</div>
        </div>
        <div v-if="typeConfig.showAddress && record.address">
          <div class="mb-1.5 flex items-center gap-2">
            <div class="h-px w-5 bg-gray-400 dark:bg-gray-500 print:bg-gray-400!"></div>
            <span class="text-xs font-bold tracking-widest text-gray-400 uppercase dark:text-gray-500 print:text-gray-400!">
              {{ typeConfig.addressLabel || $t('Ship To') }}
            </span>
          </div>
          <div class="font-semibold">{{ record.address.company || record.address.name }}</div>
          <div class="text-gray-500 dark:text-gray-400">{{ $address(record.address) }}</div>
          <div v-if="record.address.phone" class="text-gray-500 dark:text-gray-400">{{ $t('Phone') }}: {{ record.address.phone }}</div>
          <div v-if="record.address.email" class="text-gray-500 dark:text-gray-400">{{ $t('Email') }}: {{ record.address.email }}</div>
        </div>
      </div>

      <div class="mb-4">
        <slot name="content" :record="record" :typeConfig="typeConfig" />
      </div>

      <!-- Items table: number column with dark bg, no outer border -->
      <template v-if="typeConfig.showItems && record.items">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-sm">
            <thead>
              <tr class="border-t-2 border-b-2 border-gray-300 dark:border-gray-600 print:border-gray-300!">
                <th class="print:exact bg-gray-100 px-3 py-2.5 text-center font-bold dark:bg-gray-700 print:bg-gray-100!">#</th>
                <th class="px-3 py-2.5 text-start font-bold">{{ $t('Description') }}</th>
                <template v-if="typeConfig.showPrices !== false">
                  <th class="w-[120px] px-3 py-2.5 text-end font-bold whitespace-nowrap">
                    {{ page.props.settings.show_tax == 1 ? $t('Price') : $t('Unit Price') }}
                  </th>
                </template>
                <th class="w-[80px] px-3 py-2.5 text-center font-bold">{{ $t('Qty') }}</th>
                <template v-if="typeConfig.showPrices !== false">
                  <th v-if="page.props.settings.show_discount == 1" class="w-[80px] px-3 py-2.5 text-end font-bold">
                    {{ $t('Discount') }}
                  </th>
                  <th v-if="page.props.settings.show_tax == 1" class="w-[80px] px-3 py-2.5 text-end font-bold">{{ $t('Tax') }}</th>
                  <th class="w-[120px] px-3 py-2.5 text-end font-bold">{{ $t('Total') }}</th>
                </template>
              </tr>
            </thead>

            <template v-for="(item, index) in record.items" :key="item.id">
              <template v-if="item.variations && item.variations.length">
                <tbody>
                  <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="print:exact bg-gray-100 px-3 py-2 text-center font-semibold dark:bg-gray-700 print:bg-gray-100!">
                      {{ index + 1 }}
                    </td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-2">
                        <template v-if="item.product && page.props.settings.show_image == 1">
                          <img v-if="item.product.photo" class="h-8 w-8 rounded-xs" :src="item.product.photo" alt="Product Image" />
                          <img v-else class="me-2 h-8 w-8 rounded-xs" src="img/no-image.png" alt="No Image" />
                        </template>
                        <div>
                          <div class="font-medium">{{ item.product?.name || '' }}</div>
                          <div v-if="item.comment" class="text-xs text-gray-500">{{ item.comment }}</div>
                          <div v-if="item.batch_no || item.expiry_date" class="flex items-center gap-4 text-xs font-bold">
                            <div v-if="item.batch_no">
                              <span class="text-mute">{{ $t('Batch') }}:</span> {{ item.batch_no }}
                            </div>
                            <div v-if="item.expiry_date">
                              <span class="text-mute">{{ $t('Expiry') }}:</span> {{ item.expiry_date }}
                            </div>
                          </div>
                        </div>
                        <div v-if="item.serials && item.serials.length" class="text-xs">
                          <span class="text-mute font-bold">{{ $t('Serials') }}:</span>
                          {{ item.serials.map(s => s.number).join(', ') }}
                        </div>
                      </div>
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td class="px-3 py-2"></td>
                    </template>
                    <td class="px-3 py-2"></td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td v-if="page.props.settings.show_discount == 1" class="px-3 py-2"></td>
                      <td v-if="page.props.settings.show_tax == 1" class="px-3 py-2"></td>
                      <td class="px-3 py-2"></td>
                    </template>
                  </tr>
                  <tr v-for="variation in item.variations" :key="variation.id" class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="print:exact bg-gray-100 px-3 py-2 dark:bg-gray-700 print:bg-gray-100!"></td>
                    <td class="px-3 py-2 ps-8 text-gray-600 dark:text-gray-300">{{ $meta(variation.meta) }}</td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td class="px-3 py-2 text-end">{{ $number(variation.pivot[typeConfig.priceField]) }}</td>
                    </template>
                    <td class="px-3 py-2 text-center">
                      {{ $number_qty(variation.pivot.quantity) }}{{ variation.pivot?.unit?.code || '' }}
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td v-if="page.props.settings.show_discount == 1" class="px-3 py-2 text-end">
                        {{ $number(variation.pivot.discount_amount) }}
                      </td>
                      <td v-if="page.props.settings.show_tax == 1" class="px-3 py-2 text-end">
                        {{ $number(variation.pivot.tax_amount) }}
                      </td>
                      <td class="px-3 py-2 text-end font-bold">{{ $number(variation.pivot.total) }}</td>
                    </template>
                  </tr>
                </tbody>
              </template>
              <template v-else>
                <tbody>
                  <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="print:exact bg-gray-100 px-3 py-2 text-center font-semibold dark:bg-gray-700 print:bg-gray-100!">
                      {{ index + 1 }}
                    </td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-2">
                        <template v-if="item.product && page.props.settings.show_image == 1">
                          <img v-if="item.product.photo" class="h-8 w-8 rounded-xs" :src="item.product.photo" alt="Product Image" />
                          <img v-else class="me-2 h-8 w-8 rounded-xs" src="img/no-image.png" alt="No Image" />
                        </template>
                        <div>
                          <div class="font-medium">{{ item.product?.name || '' }}</div>
                          <div v-if="item.comment" :class="item.product?.name ? 'text-xs text-gray-500' : ''">{{ item.comment }}</div>
                          <div v-if="item.batch_no || item.expiry_date" class="flex items-center gap-4 text-xs font-bold">
                            <div v-if="item.batch_no">
                              <span class="text-mute">{{ $t('Batch') }}:</span> {{ item.batch_no }}
                            </div>
                            <div v-if="item.expiry_date">
                              <span class="text-mute">{{ $t('Expiry') }}:</span> {{ item.expiry_date }}
                            </div>
                          </div>
                        </div>
                        <div v-if="item.serials && item.serials.length" class="text-xs">
                          <span class="text-mute font-bold">{{ $t('Serials') }}:</span>
                          {{ item.serials.map(s => s.number).join(', ') }}
                        </div>
                      </div>
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td class="px-3 py-2 text-end">{{ $number(item[typeConfig.priceField]) }}</td>
                    </template>
                    <td class="px-3 py-2 text-center">{{ $number_qty(item.quantity) }}{{ item.unit?.code || '' }}</td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td v-if="page.props.settings.show_discount == 1" class="px-3 py-2 text-end">
                        {{ $number(item.discount_amount) }}
                      </td>
                      <td v-if="page.props.settings.show_tax == 1" class="px-3 py-2 text-end">{{ $number(item.tax_amount) }}</td>
                      <td class="px-3 py-2 text-end font-bold">{{ $number(item.total) }}</td>
                    </template>
                  </tr>
                </tbody>
              </template>
            </template>

            <tfoot
              v-if="typeConfig.showPrices !== false"
              class="break-inside-avoid border-t-2 border-gray-300 dark:border-gray-600 print:border-gray-300!"
            >
              <!-- <tr v-if="page.props.settings.show_tax == 1">
                <th :colspan="colspan + 1" class="px-3 py-2 text-end font-bold">{{ $t('Total') }}</th>
                <th class="px-3 py-2 text-end font-bold">
                  {{ $decimal_qty(record.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}
                </th>
                <th v-if="page.props.settings.show_discount == 1" class="px-3 py-2 text-end font-bold">
                  {{ $currency(record.total_discount_amount) }}
                </th>
                <th v-if="page.props.settings.show_tax == 1" class="px-3 py-2 text-end font-bold">
                  {{ $currency(record.total_tax_amount) }}
                </th>
                <th class="px-3 py-2 text-end font-bold">{{ $currency(record.total) }}</th>
              </tr>
              <template v-else> -->
              <template v-if="page.props.settings.show_discount == 1 && Number(record.total_discount_amount) > 0">
                <tr>
                  <th :colspan="colspan" class="px-3 py-1 text-end text-gray-500">{{ $t('Discount') }}</th>
                  <th class="px-3 py-1 text-end">{{ $currency(record.total_discount_amount) }}</th>
                </tr>
              </template>
              <template v-if="$decimal(record.total_tax_amount, true) > 0 || page.props.settings.show_tax == 1">
                <tr>
                  <th :colspan="colspan" class="px-3 py-1 text-end text-gray-500">{{ $t('Subtotal') }}</th>
                  <th class="px-3 py-1 text-end">{{ $currency(record.subtotal) }}</th>
                </tr>
                <tr>
                  <th :colspan="colspan" class="px-3 py-1 text-end text-gray-500">{{ $t('Tax') }}</th>
                  <th class="px-3 py-1 text-end">{{ $currency(record.total_tax_amount) }}</th>
                </tr>
              </template>
              <template v-else-if="page.props.settings.show_zero_taxes == 1">
                <tr>
                  <th :colspan="colspan" class="px-3 py-1 text-end text-gray-500">{{ $t('Tax') }}</th>
                  <th class="px-3 py-1 text-end">{{ $currency(record.total_tax_amount) }}</th>
                </tr>
              </template>
              <tr>
                <th :colspan="colspan" class="px-3 py-2 text-end text-lg font-black">{{ $t('Total') }}</th>
                <th class="px-3 py-2 text-end text-lg font-black">{{ $currency(record.grand_total) }}</th>
              </tr>
              <template v-if="(type === 'sale' || type === 'purchase') && record.return_orders && record.return_orders.length > 0">
                <tr v-for="returnOrder in record.return_orders" :key="returnOrder.id">
                  <th :colspan="colspan" class="px-3 py-2 text-end text-lg font-black">{{ $t('Return') }}: {{ returnOrder.reference }}</th>
                  <th class="px-3 py-2 text-end text-lg font-black whitespace-nowrap">-{{ $currency(returnOrder.grand_total) }}</th>
                </tr>
                <tr
                  v-for="returnOrder in record.return_orders.filter(r => Number(r.return_payment_amount) > 0)"
                  :key="'rp-' + returnOrder.id"
                >
                  <th :colspan="colspan" class="px-3 py-2 text-end text-lg font-black">
                    {{ $t('Return Payment') }} ({{ returnOrder.return_payment_method }})
                  </th>
                  <th class="px-3 py-2 text-end text-lg font-black">-{{ $currency(returnOrder.return_payment_amount) }}</th>
                </tr>
              </template>
              <tr v-if="typeConfig.showPaid">
                <th :colspan="colspan" class="px-3 py-1 text-end text-gray-500">{{ $t('Paid') }}</th>
                <th class="px-3 py-1 text-end font-bold">
                  {{ $currency(record.paid - (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0)) }}
                </th>
              </tr>
              <tr v-if="typeConfig.showBalance">
                <th :colspan="colspan" class="px-3 py-2 text-end font-black">{{ $t('Balance') }}</th>
                <th class="px-3 py-2 text-end font-black">
                  {{
                    $currency(
                      record.grand_total -
                        record.paid +
                        (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0) -
                        (record.return_orders?.reduce((sum, r) => sum + Number(r.grand_total), 0) || 0)
                    )
                  }}
                </th>
              </tr>
              <!-- </template> -->
            </tfoot>
            <tfoot v-else class="border-t-2 border-gray-300 dark:border-gray-600 print:border-gray-300!">
              <tr>
                <td class="bg-gray-100 px-3 py-2 text-center font-bold dark:bg-gray-800 print:bg-gray-100!"></td>
                <td class="px-3 py-2 font-bold">{{ $t('Total Quantity') }}</td>
                <td class="px-3 py-2 text-center font-bold">
                  {{ $number_qty(record.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </template>

      <div class="-mx-4">
        <slot name="amount" :record="record" />
      </div>

      <ViewCustomFields :modal="false" :fields="custom_fields" :title="$t('Custom Fields')" :extra_attributes="record.extra_attributes" />

      <div v-if="record.details" class="mt-6 text-sm text-gray-600 dark:text-gray-300">
        {{ record.details }}
      </div>

      <div v-if="type == 'sale'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.sale_footer" class="text-center">{{ $page.props.settings?.sale_footer }}</div>
      </div>
      <div v-else-if="type == 'purchase'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.purchase_footer" class="text-center">{{ $page.props.settings?.purchase_footer }}</div>
      </div>
      <div v-else-if="type == 'payment'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.payment_footer" class="text-center">{{ $page.props.settings?.payment_footer }}</div>
      </div>
      <div v-else-if="type == 'quotation'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.quotation_footer" class="text-center">{{ $page.props.settings?.quotation_footer }}</div>
      </div>

      <!-- Bottom decorative divider -->
      <div class="mt-6 flex items-center gap-4">
        <div class="h-px flex-1 bg-gray-300 dark:bg-gray-600 print:bg-gray-300!"></div>
        <div class="text-xs tracking-widest text-gray-400 uppercase">{{ $page.props.settings?.name }}</div>
        <div class="h-px flex-1 bg-gray-300 dark:bg-gray-600 print:bg-gray-300!"></div>
      </div>

      <div v-if="record.attachments && record.attachments.length" class="mt-8 w-full py-2 print:hidden">
        <Attachments :attachments="record.attachments" />
      </div>
    </div>

    <div
      v-if="typeConfig.showPayments && record.payments && record.payments.length"
      class="mx-6 mb-6 rounded-sm border border-gray-200 dark:border-gray-700 print:hidden"
    >
      <h4 class="border-b border-gray-200 px-4 py-2 text-base font-extrabold dark:border-gray-700">{{ $t('Payments') }}</h4>
      <div class="overflow-x-auto py-1">
        <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
          <thead>
            <tr>
              <th class="w-[140px] p-2 text-start font-bold uppercase">{{ $t('Date') }}</th>
              <th class="p-2 text-start font-bold uppercase">{{ $t('Reference') }}</th>
              <th class="w-[100px] p-2 text-start font-bold uppercase">{{ $t('Method') }}</th>
              <th class="w-[120px] p-2 text-center font-bold uppercase">{{ $t('Amount') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in record.payments" :key="p.id">
              <td class="p-2 whitespace-nowrap">{{ $date(p.date) }}</td>
              <td class="p-2">{{ p.reference }}</td>
              <td class="p-2 whitespace-nowrap">{{ $t(p.method) }}</td>
              <td class="p-2 text-end">{{ $currency(p.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Modal v-if="showPackingList" :show="packing" max-width="2xl" @close="packing = false" class="modal-2">
      <PackingList :record="record" :type="type" />
    </Modal>
  </div>
</template>
