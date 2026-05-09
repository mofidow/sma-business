<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  record: { type: Object, required: true },
  type: { type: String, required: true },
  xfetch: { type: Boolean, default: false },
  editRow: { type: Function, default: null },
});

const typeConfig = computed(() => {
  const configs = {
    sale: {
      label: t('Sale'),
      noLabel: t('{x} No.', { x: t('Sale') }),
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
    },
    quotation: {
      label: t('Quotation'),
      noLabel: t('{x} No.', { x: t('Quotation') }),
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

function print() {
  window.print();
}
</script>

<template>
  <div class="font-mono text-sm">
    <template v-if="!xfetch">
      <span class="absolute end-12 top-4 inline-flex items-center gap-x-4 sm:end-14 print:hidden">
        <button type="button" @click="print" class="link -m-2 p-2">
          <Icon name="print-o" class="size-5" />
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

    <div class="mx-auto mb-1 max-h-16 max-w-60 px-4">
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
    <div class="px-4 py-2 text-center">
      <div class="text-sm font-bold">{{ record.store?.name }}</div>
      <div>{{ $address(record.store) }}</div>
      <div v-if="record.store?.phone">{{ $t('Phone') }}: {{ record.store.phone }}</div>
      <div v-if="record.store?.email">{{ $t('Email') }}: {{ record.store.email }}</div>
    </div>

    <div class="border-t border-dashed border-gray-400 px-4 py-2 dark:border-gray-600">
      <div class="flex justify-between">
        <span>{{ typeConfig.noLabel }}:</span>
        <span>#{{ record.id }}</span>
      </div>
      <div class="flex justify-between">
        <span>{{ $t('Ref') }}:</span>
        <span>{{ record.reference }}</span>
      </div>
      <div class="flex justify-between">
        <span>{{ $t('Date') }}:</span>
        <span>{{ $date(record.date || record.created_at) }}</span>
      </div>
    </div>

    <div v-if="type == 'sale'" class="mb-4 pt-4 text-sm font-bold">
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

    <div v-if="person" class="flex w-full gap-6 px-4 py-4">
      <div class="">
        <h2 class="mb-1 text-xs font-bold">{{ typeConfig.personLabel }}</h2>
        <div class="font-semibold">{{ person.company || person.name }}</div>
        <div v-if="person.company" class="font-semibold">{{ $t('Attn') }}: {{ person.name }}</div>
        <div class="text-sm">{{ $address(person) }}</div>
        <div class="text-sm" v-if="person.phone">{{ $t('Phone') }}: {{ person.phone }}</div>
        <div class="text-sm" v-if="person.email">{{ $t('Email') }}: {{ person.email }}</div>
      </div>
      <div v-if="typeConfig.showAddress && record.address">
        <h2 class="mb-1 text-xs font-bold">{{ $t('Ship To') }}</h2>
        <div class="font-semibold">{{ record.address.company || record.address.name }}</div>
        <div v-if="record.address.company" class="font-semibold">{{ $t('Attn') }}: {{ record.address.name }}</div>
        <div class="text-sm">{{ $address(record.address) }}</div>
        <div class="text-sm" v-if="record.address.phone">{{ $t('Phone') }}: {{ record.address.phone }}</div>
        <div class="text-sm" v-if="record.address.email">{{ $t('Email') }}: {{ record.address.email }}</div>
      </div>
    </div>

    <slot name="content" :record="record" :typeConfig="typeConfig" />

    <template v-if="typeConfig.showItems && record.items">
      <div class="border-t border-dashed border-gray-400 px-4 py-2 dark:border-gray-600">
        <table class="w-full">
          <thead>
            <tr class="border-b border-dashed border-gray-300 dark:border-gray-600">
              <th class="py-1 text-start">#</th>
              <th class="py-1 text-start">{{ $t('Item') }}</th>
              <th class="py-1 text-end">{{ $t('Qty') }}</th>
              <template v-if="typeConfig.showPrices !== false">
                <th class="py-1 text-end">{{ $t('Price') }}</th>
                <th class="py-1 text-end">{{ $t('Total') }}</th>
              </template>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, index) in record.items" :key="item.id">
              <template v-if="item.variations && item.variations.length">
                <tr>
                  <td class="py-0.5">{{ index + 1 }}</td>
                  <td colspan="4" class="py-0.5 font-semibold">{{ item.product?.name || '' }}</td>
                </tr>
                <tr v-if="item.serials && item.serials.length">
                  <td></td>
                  <td colspan="4" class="py-0.5 text-xs">
                    <span class="font-bold">{{ $t('Serials') }}:</span>
                    {{ item.serials.map(s => s.number).join(', ') }}
                  </td>
                </tr>
                <tr v-for="v in item.variations" :key="v.id">
                  <td></td>
                  <td class="truncate py-0.5 ps-1">{{ $meta(v.meta) }}</td>
                  <td class="py-0.5 text-end">{{ $number_qty(v.pivot.quantity) }}</td>
                  <template v-if="typeConfig.showPrices !== false">
                    <td class="py-0.5 text-end">{{ $number(v.pivot[typeConfig.priceField]) }}</td>
                    <td class="py-0.5 text-end">{{ $number(v.pivot.total) }}</td>
                  </template>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td class="py-0.5">{{ index + 1 }}</td>
                  <td class="truncate py-0.5">{{ item.product?.name || item.comment || '' }}</td>
                  <td class="py-0.5 text-end">{{ $number_qty(item.quantity) }}</td>
                  <template v-if="typeConfig.showPrices !== false">
                    <td class="py-0.5 text-end">{{ $number(item[typeConfig.priceField]) }}</td>
                    <td class="py-0.5 text-end">{{ $number(item.total) }}</td>
                  </template>
                </tr>
                <tr v-if="item.serials && item.serials.length">
                  <td></td>
                  <td colspan="4" class="py-0.5 text-xs">
                    <span class="font-bold">{{ $t('Serials') }}:</span>
                    {{ item.serials.map(s => s.number).join(', ') }}
                  </td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      <div class="border-t border-dashed border-gray-400 px-4 py-2 dark:border-gray-600">
        <template v-if="typeConfig.showPrices !== false">
          <template v-if="$decimal(record.total_discount_amount, true) > 0">
            <div class="flex justify-end gap-4">
              <span>{{ $t('Discount') }}:</span>
              <span class="w-20 text-end">{{ $currency(record.total_discount_amount) }}</span>
            </div>
          </template>
          <template v-if="$decimal(record.total_tax_amount, true) > 0 || $page.props.settings.show_tax == 1">
            <div class="flex justify-end gap-4">
              <span>{{ $t('Subtotal') }}:</span>
              <span class="w-20 text-end">{{ $currency(record.subtotal) }}</span>
            </div>
            <div class="flex justify-end gap-4">
              <span>{{ $t('Tax') }}:</span>
              <span class="w-20 text-end">{{ $currency(record.total_tax_amount) }}</span>
            </div>
          </template>
          <div class="flex justify-end gap-4 font-bold">
            <span>{{ $t('Total') }}:</span>
            <span class="w-20 text-end">{{ $currency(record.grand_total) }}</span>
          </div>
          <template v-if="(type === 'sale' || type === 'purchase') && record.return_orders && record.return_orders.length > 0">
            <div v-for="returnOrder in record.return_orders" :key="returnOrder.id" class="flex justify-end gap-4">
              <span>{{ $t('Return') }}: {{ returnOrder.reference }}:</span>
              <span class="w-20 text-end whitespace-nowrap text-red-600 dark:text-red-400">-{{ $currency(returnOrder.grand_total) }}</span>
            </div>
            <div
              v-for="returnOrder in record.return_orders.filter(r => Number(r.return_payment_amount) > 0)"
              :key="'rp-' + returnOrder.id"
              class="flex justify-end gap-4"
            >
              <span>{{ $t('Return Payment') }} ({{ returnOrder.return_payment_method }}):</span>
              <span class="w-20 text-end text-red-600 dark:text-red-400">-{{ $currency(returnOrder.return_payment_amount) }}</span>
            </div>
          </template>
          <template v-if="typeConfig.showPaid">
            <div class="flex justify-end gap-4">
              <span>{{ $t('Paid') }}:</span>
              <span class="w-20 text-end">{{
                $currency(record.paid - (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0))
              }}</span>
            </div>
          </template>
          <template v-if="typeConfig.showBalance">
            <div class="flex justify-end gap-4 font-bold">
              <span>{{ $t('Balance') }}:</span>
              <span class="w-20 text-end">{{
                $currency(
                  record.grand_total -
                    record.paid +
                    (record.return_orders?.reduce((sum, r) => sum + Number(r.return_payment_amount), 0) || 0) -
                    (record.return_orders?.reduce((sum, r) => sum + Number(r.grand_total), 0) || 0)
                )
              }}</span>
            </div>
          </template>
        </template>
        <template v-else>
          <div class="flex justify-center gap-4 font-bold">
            <span>{{ $t('Total Quantity') }}:</span>
            <span class="w-20 text-center">{{ $number_qty(record.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}</span>
          </div>
        </template>
      </div>
    </template>

    <slot name="amount" :record="record" />

    <div v-if="record.details" class="mb-4 px-4 pt-6 text-center text-sm">
      {{ record.details }}
    </div>

    <div v-if="type == 'sale'" class="mb-4 pt-4 text-sm font-bold">
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

    <div
      v-if="typeConfig.showPayments && record.payments && record.payments.length"
      class="border-t border-dashed border-gray-400 px-4 py-2 dark:border-gray-600 print:hidden"
    >
      <div class="mb-1 font-bold">{{ $t('Payments') }}</div>
      <div v-for="p in record.payments" :key="p.id" class="flex justify-between">
        <span>{{ $date(p.date) }} {{ $t(p.method) }}</span>
        <span>{{ $currency(p.amount) }}</span>
      </div>
    </div>
  </div>
</template>
