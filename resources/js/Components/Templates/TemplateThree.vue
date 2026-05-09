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
      personLabel: t('Billing Address'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      addressLabel: t('Shipping Address'),
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
      personLabel: t('Billing Address'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      addressLabel: t('Shipping Address'),
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

const paymentStatus = computed(() => {
  if (props.record.paid >= props.record.grand_total) {
    return { label: 'Paid', class: 'bg-green-100 text-green-800' };
  }
  if (props.record.due_date && new Date(props.record.due_date) < new Date() && props.record.paid < props.record.grand_total) {
    return { label: 'Overdue', class: 'bg-red-100 text-red-800' };
  }
  if (props.record.due_date && new Date(props.record.due_date) > new Date()) {
    return { label: 'Pending', class: 'bg-primary-100 text-primary-800' };
  }
  return { label: 'Due', class: 'bg-yellow-100 text-yellow-800' };
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

    <div class="p-6 print:p-0">
      <div class="-mx-6 -mt-6 mb-6">
        <slot name="alerts" />
      </div>

      <div class="mb-6 flex justify-between">
        <div>
          <h1 class="text-focus text-2xl font-black">
            <div class="max-h-16 min-h-10 max-w-[250px]">
              <img v-if="record.store?.logo" class="max-h-16 min-h-10 max-w-full" :src="record.store.logo" :alt="record.store.name" />
              <template v-else>
                <img
                  :alt="$page.props.settings?.name"
                  :src="$page.props.settings?.logo"
                  v-if="$page.props.settings?.logo"
                  class="max-h-16 min-h-10 max-w-full dark:hidden print:block!"
                />
                <img
                  :alt="$page.props.settings?.name"
                  :src="$page.props.settings?.logo_dark"
                  v-if="$page.props.settings?.logo_dark"
                  class="hidden max-h-16 min-h-10 max-w-full dark:block print:hidden!"
                />
              </template>
            </div>
          </h1>
          <div>{{ $address(record.store) }}</div>
        </div>
        <div class="shrink-0 space-y-1 text-end text-sm whitespace-nowrap">
          <div v-if="record.store?.phone">{{ $t('Phone') }}: {{ record.store.phone }}</div>
          <div v-if="record.store?.email">{{ $t('Email') }}: {{ record.store.email }}</div>
        </div>
      </div>

      <div
        class="text-focus grid gap-4 border-t border-b border-gray-300 py-4 text-center text-sm font-semibold uppercase dark:border-gray-700"
        :class="{
          'grid-cols-4': typeConfig.showPaid,
          'grid-cols-3': !typeConfig.showPaid && typeConfig.showPrices !== false,
          'grid-cols-2': typeConfig.showPrices === false,
        }"
      >
        <div class="text-left">
          <span class="font-bold">{{ typeConfig.noLabel }}</span
          ><br />
          <span class="text-lg font-black">{{ record.id }}</span>
        </div>
        <div>
          <span class="font-bold">{{ $t('Date') }}</span
          ><br />
          <span class="text-lg font-black">{{ $date(record.date || record.created_at) }}</span>
        </div>
        <div v-if="typeConfig.showPaid">
          <span class="font-bold">{{ $t('Payment Status') }}</span
          ><br />
          <span class="mt-1 inline-block rounded-full px-3 py-px text-sm font-semibold" :class="paymentStatus.class">
            {{ $t(paymentStatus.label) }}
          </span>
        </div>
        <div v-if="typeConfig.showPrices !== false" class="text-right">
          <span class="font-bold">{{ $t('Total Amount') }}</span
          ><br />
          <span class="text-lg font-black">{{ $currency(record.grand_total || record.amount || 0) }}</span>
        </div>
      </div>

      <div v-if="type == 'sale'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.sale_header" class="text-center">
          {{ $page.props.settings?.sale_header }}
        </div>
      </div>
      <div v-else-if="type == 'purchase'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.purchase_header" class="text-center">
          {{ $page.props.settings?.purchase_header }}
        </div>
      </div>
      <div v-else-if="type == 'payment'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.payment_header" class="text-center">
          {{ $page.props.settings?.payment_header }}
        </div>
      </div>
      <div v-else-if="type == 'quotation'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.quotation_header" class="text-center">
          {{ $page.props.settings?.quotation_header }}
        </div>
      </div>

      <div v-if="qrUrl" class="flex items-center justify-between gap-6 border-b border-gray-300 py-2 dark:border-gray-700">
        <div class="bc-image h-[80px] overflow-hidden rounded">
          <svg
            class="barcode"
            :jsbarcode-width="1"
            :jsbarcode-margin="5"
            :jsbarcode-height="70"
            :jsbarcode-fontsize="12"
            :jsbarcode-textmargin="3"
            jsbarcode-format="CODE128"
            jsbarcode-fontoptions="bold"
            :jsbarcode-displayvalue="false"
            :jsbarcode-value="record.reference"
          />
        </div>
        <div v-html="qrcode" class="qr-image qrcode h-[80px] overflow-hidden rounded" />
      </div>

      <div v-if="person" class="mt-8 grid grid-cols-2 gap-8 text-sm">
        <div>
          <p class="mb-2 font-bold uppercase">{{ typeConfig.personLabel }}</p>
          <div class="text-lg font-semibold">{{ person.company || person.name }}</div>
          <div class="text-sm">{{ $address(person) }}</div>
          <div class="text-sm" v-if="person.phone">{{ $t('Phone') }}: {{ person.phone }}</div>
          <div class="text-sm" v-if="person.email">{{ $t('Email') }}: {{ person.email }}</div>
        </div>
        <div v-if="typeConfig.showAddress && record.address">
          <p class="mb-2 font-bold uppercase">{{ typeConfig.addressLabel || $t('Ship To') }}</p>
          <div class="text-lg font-semibold">{{ record.address.company || record.address.name }}</div>
          <div class="text-sm">{{ $address(record.address) }}</div>
          <div class="text-sm" v-if="record.address.phone">{{ $t('Phone') }}: {{ record.address.phone }}</div>
          <div class="text-sm" v-if="record.address.email">{{ $t('Email') }}: {{ record.address.email }}</div>
        </div>
      </div>

      <div class="mt-6">
        <slot name="content" :record="record" :typeConfig="typeConfig" />
      </div>

      <template v-if="typeConfig.showItems && record.items">
        <div class="rounded-corners mt-6">
          <table class="w-full">
            <thead class="bg-gray-100 text-sm dark:bg-gray-900">
              <tr>
                <th class="w-7 border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">#</th>
                <th class="border border-gray-300 p-2 text-start font-bold uppercase dark:border-gray-700">{{ $t('Description') }}</th>
                <template v-if="typeConfig.showPrices !== false">
                  <th class="w-[120px] border border-gray-300 p-2 text-center font-bold whitespace-nowrap uppercase dark:border-gray-700">
                    {{ page.props.settings.show_tax == 1 ? $t('Price') : $t('Unit Price') }}
                  </th>
                </template>
                <th class="w-[80px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">{{ $t('Qty') }}</th>
                <template v-if="typeConfig.showPrices !== false">
                  <th
                    v-if="page.props.settings.show_discount == 1"
                    class="w-[80px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700"
                  >
                    {{ $t('Discount') }}
                  </th>
                  <th
                    v-if="page.props.settings.show_tax == 1"
                    class="w-[80px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700"
                  >
                    {{ $t('Tax') }}
                  </th>
                  <th class="w-[120px] border border-gray-300 p-2 text-center font-bold uppercase dark:border-gray-700">
                    {{ $t('Total') }}
                  </th>
                </template>
              </tr>
            </thead>

            <template v-for="(item, index) in record.items" :key="item.id">
              <template v-if="item.variations && item.variations.length">
                <tbody>
                  <tr>
                    <td class="w-7 border border-gray-300 p-2 text-end dark:border-gray-700">{{ index + 1 }}</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                      <div class="flex items-center gap-2">
                        <template v-if="item.product && page.props.settings.show_image == 1">
                          <template v-if="item.product.photo">
                            <img class="h-8 w-8 rounded-xs" :src="item.product.photo" alt="Product Image" />
                          </template>
                          <template v-else>
                            <img class="me-2 h-8 w-8 rounded-xs" src="img/no-image.png" alt="No Image" />
                          </template>
                        </template>
                        <div>
                          {{ item.product?.name || '' }}
                          <div class="text-mute text-sm">
                            {{ item.product?.code || '' }}
                          </div>
                          <div v-if="item.comment" class="text-xs">{{ item.comment }}</div>
                          <div v-if="item.serials && item.serials.length" class="text-xs">
                            <span class="text-mute font-bold">{{ $t('Serials') }}:</span>
                            {{ item.serials.map(s => s.number).join(', ') }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td class="border border-gray-300 p-2 dark:border-gray-700"></td>
                    </template>
                    <td class="border border-gray-300 p-2 text-center dark:border-gray-700"></td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td
                        v-if="page.props.settings.show_discount == 1"
                        class="border border-gray-300 p-2 text-end dark:border-gray-700"
                      ></td>
                      <td v-if="page.props.settings.show_tax == 1" class="border border-gray-300 p-2 dark:border-gray-700"></td>
                      <td class="border border-gray-300 p-2 dark:border-gray-700"></td>
                    </template>
                  </tr>

                  <tr v-for="variation in item.variations" :key="variation.id">
                    <td class="w-7 border border-gray-300 dark:border-gray-700"></td>
                    <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $meta(variation.meta) }}</td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ $number(variation.pivot[typeConfig.priceField]) }}
                      </td>
                    </template>
                    <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                      <div class="flex items-center justify-center">
                        {{ $number_qty(variation.pivot.quantity) }}{{ variation.pivot?.unit?.code || '' }}
                      </div>
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td v-if="page.props.settings.show_discount == 1" class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ $number(variation.pivot.discount_amount) }}
                      </td>
                      <td v-if="page.props.settings.show_tax == 1" class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ $number(variation.pivot.tax_amount) }}
                      </td>
                      <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">
                        {{ $number(variation.pivot.total) }}
                      </td>
                    </template>
                  </tr>
                </tbody>
              </template>
              <template v-else>
                <tbody
                  class="divide-y divide-gray-200 border-y border-gray-200 dark:divide-gray-700 dark:border-gray-700 print:divide-gray-400 dark:print:divide-gray-400"
                >
                  <tr>
                    <td class="w-7 border border-gray-300 p-2 text-end dark:border-gray-700">{{ index + 1 }}</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                      <div class="flex items-center gap-2">
                        <template v-if="item.product && page.props.settings.show_image == 1">
                          <template v-if="item.product.photo">
                            <img class="h-8 w-8 rounded-xs" :src="item.product.photo" alt="Product Image" />
                          </template>
                          <template v-else>
                            <img class="me-2 h-8 w-8 rounded-xs" src="img/no-image.png" alt="No Image" />
                          </template>
                        </template>
                        <div>
                          {{ item.product?.name || '' }}
                          <div class="text-mute text-sm">
                            {{ item.product?.code || '' }}
                          </div>
                          <div v-if="item.comment" :class="item.product?.name ? 'text-mute text-xs' : ''">{{ item.comment }}</div>
                          <div v-if="item.serials && item.serials.length" class="text-xs">
                            <span class="text-mute font-bold">{{ $t('Serials') }}:</span>
                            {{ item.serials.map(s => s.number).join(', ') }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ $number(item[typeConfig.priceField]) }}
                      </td>
                    </template>
                    <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                      <div class="flex items-center justify-center">{{ $number_qty(item.quantity) }}{{ item.unit?.code || '' }}</div>
                    </td>
                    <template v-if="typeConfig.showPrices !== false">
                      <td v-if="page.props.settings.show_discount == 1" class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ $number(item.discount_amount) }}
                      </td>
                      <td v-if="page.props.settings.show_tax == 1" class="border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ $number(item.tax_amount) }}
                      </td>
                      <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">{{ $number(item.total) }}</td>
                    </template>
                  </tr>
                </tbody>
              </template>
            </template>
            <tfoot v-if="typeConfig.showPrices !== false" class="break-inside-avoid">
              <!-- <tr v-if="page.props.settings.show_tax == 1">
                <th colspan="3" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">{{ $t('Total') }}</th>
                <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                  {{ $decimal_qty(record.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}
                </th>
                <th
                  v-if="page.props.settings.show_discount == 1"
                  class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700"
                >
                  {{ $currency(record.total_discount_amount) }}
                </th>
                <th
                  v-if="page.props.settings.show_tax == 1"
                  class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700"
                >
                  {{ $currency(record.total_tax_amount) }}
                </th>
                <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">{{ $currency(record.total) }}</th>
              </tr>
              <template v-else> -->
              <template v-if="page.props.settings.show_discount == 1 && Number(record.total_discount_amount) > 0">
                <tr>
                  <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $t('Discount') }}
                  </th>
                  <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $currency(record.total_discount_amount) }}
                  </th>
                </tr>
              </template>
              <template v-if="$decimal(record.total_tax_amount, true) > 0 || page.props.settings.show_tax == 1">
                <tr>
                  <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $t('Subtotal') }}
                  </th>
                  <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $currency(record.subtotal) }}
                  </th>
                </tr>
                <tr>
                  <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $t('Tax') }}
                  </th>
                  <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $currency(record.total_tax_amount) }}
                  </th>
                </tr>
              </template>
              <template v-else-if="page.props.settings.show_zero_taxes == 1">
                <tr>
                  <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $t('Tax') }}
                  </th>
                  <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $currency(record.total_tax_amount) }}
                  </th>
                </tr>
              </template>
              <tr>
                <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                  {{ $t('Total') }}
                </th>
                <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                  {{ $currency(record.grand_total) }}
                </th>
              </tr>
              <template v-if="(type === 'sale' || type === 'purchase') && record.return_orders && record.return_orders.length > 0">
                <tr v-for="returnOrder in record.return_orders" :key="returnOrder.id">
                  <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $t('Return') }}: {{ returnOrder.reference }}
                  </th>
                  <th
                    class="border border-gray-300 p-2 text-end text-lg font-bold whitespace-nowrap text-red-600 dark:border-gray-700 dark:text-red-400"
                  >
                    -{{ $currency(returnOrder.grand_total) }}
                  </th>
                </tr>
                <tr
                  v-for="returnOrder in record.return_orders.filter(r => Number(r.return_payment_amount) > 0)"
                  :key="'rp-' + returnOrder.id"
                >
                  <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                    {{ $t('Return Payment') }} ({{ returnOrder.return_payment_method }})
                  </th>
                  <th class="border border-gray-300 p-2 text-end text-lg font-bold text-red-600 dark:border-gray-700 dark:text-red-400">
                    -{{ $currency(returnOrder.return_payment_amount) }}
                  </th>
                </tr>
              </template>
              <tr v-if="typeConfig.showPaid">
                <th :colspan="colspan" class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                  {{ $t('Paid') }}
                </th>
                <th class="border border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                  {{ $currency(record.paid - (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0)) }}
                </th>
              </tr>
              <tr v-if="typeConfig.showBalance">
                <th :colspan="colspan" class="border-x border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
                  {{ $t('Balance') }}
                </th>
                <th class="border-x border-gray-300 p-2 text-end text-lg font-bold dark:border-gray-700">
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
            <tfoot v-else>
              <tr>
                <td colspan="2" class="border border-gray-300 px-2 pt-1 pb-2 text-center font-bold dark:border-gray-700">
                  {{ $t('Total Quantity') }}
                </td>
                <td class="w-[80px] border border-gray-300 px-2 pt-1 pb-2 text-center font-bold dark:border-gray-700">
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

      <div v-if="record.details" class="mt-6 rounded-md border border-gray-300 p-4 text-sm dark:border-gray-700">
        {{ record.details }}
      </div>

      <div v-if="type == 'sale'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.sale_footer" class="text-center">
          {{ $page.props.settings?.sale_footer }}
        </div>
      </div>
      <div v-else-if="type == 'purchase'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.purchase_footer" class="text-center">
          {{ $page.props.settings?.purchase_footer }}
        </div>
      </div>
      <div v-else-if="type == 'payment'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.payment_footer" class="text-center">
          {{ $page.props.settings?.payment_footer }}
        </div>
      </div>
      <div v-else-if="type == 'quotation'" class="mb-4 pt-6 text-sm font-bold">
        <div v-if="$page.props.settings?.quotation_footer" class="text-center">
          {{ $page.props.settings?.quotation_footer }}
        </div>
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
      <div class="-m-px">
        <table class="w-full text-sm">
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
              <td class="border-t border-gray-300 p-2 whitespace-nowrap dark:border-gray-700">{{ $date(p.date) }}</td>
              <td class="border-t border-gray-300 p-2 dark:border-gray-700">{{ p.reference }}</td>
              <td class="border-t border-gray-300 p-2 whitespace-nowrap dark:border-gray-700">{{ $t(p.method) }}</td>
              <td class="border-t border-gray-300 p-2 text-end dark:border-gray-700">{{ $currency(p.amount) }}</td>
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
