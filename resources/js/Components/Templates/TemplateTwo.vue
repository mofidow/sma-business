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
      noLabel: t('Sale No. {x}', { x: props.record?.id }),
      permission: 'update-sales',
      personLabel: t('Sell To'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      showItems: true,
      showPayments: true,
      showPaid: true,
      showBalance: true,
      priceField: 'price',
      quantityField: 'quantity',
    },
    quotation: {
      label: t('Quotation'),
      noLabel: t('Quotation No. {x}', { x: props.record?.id }),
      permission: 'update-quotations',
      personLabel: t('Quote To'),
      showPerson: true,
      personField: 'customer',
      showAddress: true,
      showItems: true,
      showPayments: false,
      showPaid: false,
      showBalance: false,
      priceField: 'price',
      quantityField: 'quantity',
    },
    purchase: {
      label: t('Purchase'),
      noLabel: t('Purchase No. {x}', { x: props.record?.id }),
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
      noLabel: t('Payment No. {x}', { x: props.record?.id }),
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
      noLabel: t('Expense No. {x}', { x: props.record?.id }),
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
      noLabel: t('Transfer No. {x}', { x: props.record?.id }),
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
      noLabel: t('Adjustment No. {x}', { x: props.record?.id }),
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
      noLabel: t('Return Order No. {x}', { x: props.record?.id }),
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
      noLabel: t('Delivery Order No. {x}', { x: props.record?.id }),
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

    <slot name="alerts" />

    <div class="flex flex-col gap-6 p-6 print:p-0">
      <div class="flex items-end justify-between gap-x-8">
        <div class="w-2/3">
          <div class="max-h-16 max-w-[250px]">
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
          <div>
            <div class="text-lg font-semibold">{{ record.store?.name }}</div>
            <div class="text-sm">{{ $address(record.store) }}</div>
            <div class="text-sm" v-if="record.store?.phone">{{ $t('Phone') }}: {{ record.store.phone }}</div>
            <div class="text-sm" v-if="record.store?.email">{{ $t('Email') }}: {{ record.store.email }}</div>
          </div>
        </div>
        <div class="w-1/3 whitespace-nowrap">
          <h1 class="text-focus text-3xl font-bold uppercase">{{ typeConfig.label }}</h1>
          <div class="mt-1 text-sm">
            <div class="text-sm">{{ typeConfig.noLabel }}</div>
            <div class="text-sm">{{ $t('Date') }}: {{ $date(record.date || record.created_at) }}</div>
            <div class="text-sm">{{ $t('Created at') }}: {{ $datetime(record.created_at) }}</div>
            <div class="flex gap-1 text-sm">
              {{ $t('Reference') }}:
              <p class="truncate hover:text-clip print:block print:text-clip" dir="rtl">{{ record.reference }}</p>
            </div>
            <div v-if="record.due_date" class="text-sm">{{ $t('Due Date') }}: {{ $date(record.due_date) }}</div>
          </div>
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

      <div v-if="qrUrl" class="flex items-center justify-center gap-6">
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

      <div v-if="person" class="flex justify-between text-sm">
        <div class="max-w-xs">
          <div class="mb-2 font-bold">{{ typeConfig.personLabel }}:</div>
          <div class="text-base font-semibold">{{ person.company || person.name }}</div>
          <div class="text-sm">{{ $address(person) }}</div>
          <div class="text-sm" v-if="person.phone">{{ $t('Phone') }}: {{ person.phone }}</div>
          <div class="text-sm" v-if="person.email">{{ $t('Email') }}: {{ person.email }}</div>
        </div>
        <div v-if="typeConfig.showAddress && record.address" class="max-w-xs text-end">
          <div class="mb-2 font-bold">{{ $t('Ship To') }}:</div>
          <div class="text-base font-semibold">{{ record.customer?.company || record.customer?.name }}</div>
          <div class="text-sm">{{ $address(record.address) }}</div>
          <div class="text-sm" v-if="record.address.phone">{{ $t('Phone') }}: {{ record.address.phone }}</div>
          <div class="text-sm" v-if="record.address.email">{{ $t('Email') }}: {{ record.address.email }}</div>
        </div>
      </div>

      <slot name="content" :record="record" :typeConfig="typeConfig" />

      <template v-if="typeConfig.showItems && record.items">
        <div>
          <div class="rounded-corners">
            <table class="w-full border-collapse text-sm">
              <thead class="bg-gray-100 dark:bg-gray-900">
                <tr>
                  <th class="border border-gray-300 p-3 text-start font-semibold dark:border-gray-700">{{ $t('Code') }}</th>
                  <th class="border border-gray-300 p-3 text-start font-semibold dark:border-gray-700">{{ $t('Name') }}</th>
                  <template v-if="typeConfig.showPrices !== false">
                    <th class="border border-gray-300 p-3 text-center font-semibold dark:border-gray-700">{{ $t('Price') }}</th>
                  </template>
                  <th class="border border-gray-300 p-3 text-center font-semibold dark:border-gray-700">{{ $t('Qty') }}</th>
                  <template v-if="typeConfig.showPrices !== false">
                    <th
                      v-if="page.props.settings.show_discount == 1"
                      class="w-[80px] border border-gray-300 p-3 text-center font-semibold dark:border-gray-700"
                    >
                      {{ $t('Discount') }}
                    </th>
                    <th
                      v-if="page.props.settings.show_tax == 1"
                      class="w-[80px] border border-gray-300 p-3 text-center font-semibold dark:border-gray-700"
                    >
                      {{ $t('Tax') }}
                    </th>
                    <th class="border border-gray-300 p-3 text-center font-semibold dark:border-gray-700">{{ $t('Total') }}</th>
                  </template>
                </tr>
              </thead>
              <template v-for="(item, index) in record.items" :key="item.id">
                <template v-if="item.variations && item.variations.length">
                  <tbody>
                    <tr>
                      <td class="w-7 border border-gray-300 p-2 text-end dark:border-gray-700">
                        {{ item.product?.code || record.repair_order_id || '' }}
                      </td>
                      <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                          <template v-if="item.product && page.props.settings.show_image == 1">
                            <template v-if="item.product.photo">
                              <img class="h-8 w-8 self-start rounded-xs" :src="item.product.photo" alt="Product Image" />
                            </template>
                            <template v-else>
                              <img class="me-2 h-8 w-8 self-start rounded-xs" src="img/no-image.png" alt="No Image" />
                            </template>
                          </template>
                          <div>
                            {{ item.product?.name || '' }}
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
                      <td class="border border-gray-300 p-2 text-end dark:border-gray-700"></td>
                      <template v-if="typeConfig.showPrices !== false">
                        <td v-if="page.props.settings.show_discount == 1" class="p-2 text-end"></td>
                        <td v-if="page.props.settings.show_tax == 1" class="p-2"></td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700"></td>
                      </template>
                    </tr>

                    <tr v-for="variation in item.variations" :key="variation.id">
                      <td class="w-7 border border-gray-300 p-2 dark:border-gray-700">
                        {{ variation?.code || '' }}
                      </td>
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
                        <td v-if="page.props.settings.show_tax == 1" class="p-2 text-end">{{ $number(variation.pivot.tax_amount) }}</td>
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
                      <td class="w-7 border border-gray-300 p-2 text-center dark:border-gray-700">
                        {{ item.product?.code || record.repair_order_id || '' }}
                      </td>
                      <td class="border border-gray-300 px-2 py-1 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                          <template v-if="item.product && page.props.settings.show_image == 1">
                            <template v-if="item.product.photo">
                              <img class="h-8 w-8 self-start rounded-xs" :src="item.product.photo" alt="Product Image" />
                            </template>
                            <template v-else>
                              <img class="me-2 h-8 w-8 self-start rounded-xs" src="img/no-image.png" alt="No Image" />
                            </template>
                          </template>
                          <div>
                            {{ item.product?.name || '' }}
                            <div v-if="item.comment" :class="item.product?.name ? 'text-xs' : ''">{{ item.comment }}</div>
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
                        <td
                          v-if="page.props.settings.show_discount == 1"
                          class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700"
                        >
                          {{ $number(item.discount_amount) }}
                        </td>
                        <td
                          v-if="page.props.settings.show_tax == 1"
                          class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700"
                        >
                          {{ $number(item.tax_amount) }}
                        </td>
                        <td class="border border-gray-300 p-2 text-end font-bold dark:border-gray-700">{{ $number(item.total) }}</td>
                      </template>
                    </tr>
                  </tbody>
                </template>
              </template>
            </table>
          </div>

          <template v-if="typeConfig.showPrices !== false">
            <div class="mt-6 flex break-inside-avoid justify-between gap-x-6 text-sm">
              <div class="max-w-sm"></div>
              <div class="max-w-xs text-end">
                <div class="flex gap-x-6">
                  <div class="grow font-semibold">{{ $t('Subtotal') }}</div>
                  <div class="w-24 font-bold">{{ $currency(record.subtotal) }}</div>
                </div>
                <div class="mt-1 flex gap-x-6">
                  <div class="grow font-semibold">{{ $t('Tax') }}</div>
                  <div class="w-24">{{ $currency(record.total_tax_amount) }}</div>
                </div>
                <div v-if="record.total_discount_amount > 0" class="mt-1 flex gap-x-6">
                  <div class="grow font-semibold">{{ $t('Discount') }}</div>
                  <div class="w-24">{{ $currency(record.total_discount_amount) }}</div>
                </div>
                <div class="mt-3 flex gap-x-6 border-t border-gray-400 pt-3 text-lg font-bold dark:border-gray-600">
                  <div class="grow">{{ $t('Grand Total') }}</div>
                  <div class="w-24">{{ $currency(record.grand_total) }}</div>
                </div>
                <template v-if="(type === 'sale' || type === 'purchase') && record.return_orders && record.return_orders.length > 0">
                  <div v-for="returnOrder in record.return_orders" :key="returnOrder.id" class="mt-1 flex gap-x-6">
                    <div class="grow font-semibold">{{ $t('Return') }}: {{ returnOrder.reference }}</div>
                    <div class="w-24 whitespace-nowrap text-red-600 dark:text-red-400">-{{ $currency(returnOrder.grand_total) }}</div>
                  </div>
                  <div
                    v-for="returnOrder in record.return_orders.filter(r => Number(r.return_payment_amount) > 0)"
                    :key="'rp-' + returnOrder.id"
                    class="mt-1 flex gap-x-6"
                  >
                    <div class="grow font-semibold">{{ $t('Return Payment') }} ({{ returnOrder.return_payment_method }})</div>
                    <div class="w-24 text-red-600 dark:text-red-400">-{{ $currency(returnOrder.return_payment_amount) }}</div>
                  </div>
                </template>
                <template v-if="typeConfig.showPaid">
                  <div class="mt-1 flex gap-x-6">
                    <div class="grow font-semibold">{{ $t('Paid') }}</div>
                    <div class="w-24 font-bold">
                      {{
                        $currency(record.paid - (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0))
                      }}
                    </div>
                  </div>
                </template>
                <template v-if="typeConfig.showBalance">
                  <div class="mt-1 flex gap-x-6">
                    <div class="grow font-semibold">{{ $t('Balance') }}</div>
                    <div class="w-24 font-bold">
                      {{
                        $currency(
                          record.grand_total -
                            record.paid +
                            (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0) -
                            (record.return_orders?.reduce((sum, r) => sum + Number(r.grand_total), 0) || 0)
                        )
                      }}
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="mt-4 flex justify-center">
              <div class="font-bold">
                {{ $t('Total Quantity') }}: {{ $number_qty(record.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}
              </div>
            </div>
          </template>
        </div>
      </template>

      <div class="-mx-4">
        <slot name="amount" :record="record" />
      </div>

      <ViewCustomFields :modal="false" :fields="custom_fields" :title="$t('Custom Fields')" :extra_attributes="record.extra_attributes" />

      <div v-if="record.details" class="rounded-md border border-gray-300 p-4 text-sm dark:border-gray-700">
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
