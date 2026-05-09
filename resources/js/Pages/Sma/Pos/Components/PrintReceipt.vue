<script setup>
import { onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { $datetime, $currency, $date, $meta, $number_qty, $number, $decimal_qty, $address } from '@/Core';

const page = usePage();
const { t } = useI18n({});
const props = defineProps(['receipt', 'show', 'sendPrint', 'halls']);

const tables = computed(() => {
  return props.halls.find(h => h.id === props.receipt.hall_id)?.tables || [];
});

onMounted(() => {
  if (page.props.settings?.print_dialog == 1) {
    setTimeout(print, 250);
  }
});

function print() {
  if (page.props.settings?.pos_server == 1) {
    const data = { type: 'print-json-receipt', data: {} };

    data.data.logo = props.receipt.store.logo || page.props.settings?.logo || '';
    data.data.heading = page.props.settings?.name;

    data.data.store_heading = props.receipt.store.name || '';
    data.data.store_details = {};
    data.data.store_details['name'] = props.receipt.store.name || '';
    data.data.store_details['email'] = props.receipt.store.email ? t('Email') + ': ' + props.receipt.store.email : '';
    data.data.store_details['phone'] = props.receipt.store.phone ? t('Phone') + ': ' + props.receipt.store.phone : '';
    data.data.store_details['address'] = props.receipt.store?.address ? $address(props.receipt.store) : '';
    // data.data.store_details['state'] = props.receipt.store?.state?.name || '';
    // data.data.store_details['country'] = props.receipt.store?.country?.name || '';

    data.data.type = t('Receipt');
    data.data.headers = [page.props.settings?.receipt_header || null, props.receipt.store?.header || null];

    data.data.info = [
      [t('Id'), props.receipt.id || ''],
      [t('Date'), props.receipt.date ? $date(props.receipt.date) : $date(props.receipt.created_at)],
      [t('Ref'), props.receipt.reference],
      [t('Order No.'), props.receipt.number],
      [t('Customer'), props.receipt.customer?.company || props.receipt.customer?.name || ''],
      [t('Created by'), props.receipt.user?.name || page.props.auth?.user?.name || ''],
      [
        t('Created at'),
        props.receipt.created_at ? $datetime(props.receipt.created_at) : $datetime(new Date().toISOString(), null, null, true),
      ],
    ];
    data.data.table_headers = [{ label: t('Description') }, { label: t('Price') }, { label: t('Qty') }, { label: t('Subtotal') }];

    let items = props.receipt.items.map(item => {
      if (item.variations && item.variations.length) {
        const variations = item.variations.map(variation => ({
          type: 'item',
          name: $meta(variation.meta),
          qty: $number_qty(variation.quantity),
          price: $number(variation.price),
          subtotal: $number(variation.total),
        }));
        return { variations: [{ type: 'text', text: item.product.name + (item.comment ? '\n' + item.comment : '') }, ...variations] };
      } else {
        return {
          type: 'item',
          name: item.product.name + (item.comment ? '\n' + item.comment : ''),
          qty: $decimal_qty(item.quantity),
          price: $number(item.price),
          subtotal: $number(item.total),
        };
      }
    });

    const variations = items.filter(i => i.variations).map(i => i.variations);
    const simpleItems = items.filter(i => !i.variations);
    items = [...variations.flat(), ...simpleItems];
    data.data.items = [items];

    data.data.totals = [
      { label: t('Total'), value: $currency(props.receipt.total) },
      { label: t('Discount'), value: $currency(props.receipt.total_discount_amount) },
      { label: t('Tax'), value: $currency(props.receipt.total_tax_amount) },
      { label: t('Grand Total'), value: $currency(props.receipt.grand_total) },
      { label: t('Paid'), value: $currency(props.receipt.paid) },
      { label: t('Balance'), value: $currency(props.receipt.grand_total - props.receipt.paid) },
      Number(props.receipt.tendered) > 0 ? { label: t('Tender Amount'), value: $currency(props.receipt.tendered) } : null,
      Number(props.receipt.tendered) > 0 ? { label: t('Return Amount'), value: $currency(props.receipt.change_returned) } : null,
    ];

    data.data.footers = [props.receipt.details || null, page.props.settings?.receipt_footer || null, props.receipt.store?.footer || null];
    // data.data.notice = t('This is not receipt but the bill to collect payment. Official receipt will be issued after receiving payment.');
    // console.log(data);

    props.sendPrint(data);
  } else {
    window.print();
  }
}
</script>

<template>
  <div>
    <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700 print:hidden">
      <div class="absolute end-8 top-0 flex items-center gap-3 pe-4 pt-4">
        <button
          type="button"
          @click="print()"
          class="focus relative rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          <span class="absolute -inset-2.5"></span>
          <span class="sr-only">{{ $t('Print') }}</span>
          <Icon name="print-o" className="size-6" />
        </button>
      </div>
      <div>
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Print Receipt') }}
        </h3>
      </div>
    </div>

    <div v-if="receipt && receipt.items && receipt.items.length" class="m-6 flex flex-col gap-4">
      <div class="text-center">
        <img v-if="receipt.store.logo" class="h-8 max-w-[200pc]" :src="receipt.store.logo" alt="Logo" />
        <h2 v-else class="text-center text-lg font-bold">{{ receipt.store.name || settings.name }}</h2>
        <div v-if="receipt.store?.address">{{ receipt.store.address || '' }}</div>
        <div v-if="receipt.store?.phone">{{ $t('Phone') }}: {{ receipt.store.phone || '' }}</div>
        <div v-if="receipt.store?.email">{{ $t('Email') }}: {{ receipt.store.email || '' }}</div>
      </div>

      <div v-if="$page.props.settings?.receipt_header" class="text-center">
        {{ $page.props.settings?.receipt_header }}
      </div>
      <div v-if="receipt.store?.header" class="text-center">{{ receipt.store.header || '' }}</div>

      <h2 class="text-center text-lg font-bold">{{ $t('Receipt') }}</h2>
      <div>
        <div>{{ $t('Sale No.') }}: {{ receipt.id || '' }}</div>
        <div>{{ $t('Customer') }}: {{ receipt.customer?.name || '' }}</div>
        <div>{{ $t('Reference') }}: {{ receipt.reference }}</div>
        <div>{{ $t('Order Number') }}: #{{ receipt.order_number }}</div>
        <div v-if="receipt.table">
          {{ $t('Table') }}: {{ receipt.table?.name || tables.find(table => table.id == receipt.table_id)?.name || '' }} ({{
            receipt.hall?.name || halls.find(hall => hall.id == receipt.hall_id)?.name || 'Main'
          }})
        </div>
        <div>
          {{ $t('Created at') }}:
          {{ receipt.created_at ? $datetime(receipt.created_at) : $datetime(new Date().toISOString(), null, null, true) }}
        </div>
        <div>{{ $t('Created  by') }}: {{ receipt.user?.name || '' }}</div>
      </div>
      <div class="-mx-2">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 print:divide-gray-400 dark:print:divide-gray-400">
          <thead>
            <tr>
              <th class="px-2 py-0.5 text-start">{{ $t('Description') }}</th>
              <th class="w-10 px-2 py-0.5 text-center">{{ $t('Qty') }}</th>
              <th class="w-10 px-2 py-0.5 text-center">{{ $t('Price') }}</th>
              <th class="w-10 px-2 py-0.5 text-center">{{ $t('Total') }}</th>
            </tr>
          </thead>

          <template v-for="item in receipt.items">
            <template v-if="item.variations && item.variations.length">
              <tbody :key="item.id">
                <tr>
                  <td class="px-2 py-0.5 text-start">
                    {{ item.product.name }}
                    <div v-if="item.comment" class="pb-1 text-xs">{{ item.comment }}</div>
                  </td>
                  <td class="w-10 px-2 py-0.5 text-center"></td>
                  <td class="w-10 px-2 py-0.5 text-end"></td>
                  <td class="w-10 px-2 py-0.5 text-end"></td>
                </tr>
                <tr v-for="variation in item.variations" :key="variation.id">
                  <td class="px-2 py-0.5 text-start">{{ $meta(variation.meta) }}</td>
                  <td class="w-10 px-2 py-0.5 text-center">
                    <div class="flex">{{ $number_qty(variation.pivot.quantity) }}{{ variation.pivot?.unit?.code || '' }}</div>
                  </td>
                  <td class="w-10 px-2 py-0.5 text-end">{{ $number(variation.pivot.price) }}</td>
                  <td class="p</template>y-0.5 w-10 px-2 text-end">{{ $number(variation.pivot.total) }}</td>
                </tr>
              </tbody>
            </template>
            <template v-else>
              <template v-if="item.quantity > 0">
                <tbody :key="item.id + '_'">
                  <tr>
                    <td class="px-2 py-0.5 text-start">
                      <div v-if="item.type == 'Gift Card'" class="flex items-center">
                        {{ $t('Gift Card') }} <span class="ms-1 text-xs font-bold">({{ item.code }})</span>
                      </div>
                      <template v-else>
                        {{ item.product?.name }}
                        <div v-if="item.comment" class="pb-1 text-xs">{{ item.comment }}</div>
                      </template>
                    </td>
                    <td class="w-10 px-2 py-0.5 text-center">
                      <div class="flex">{{ $number_qty(item.quantity) }}{{ item.unit?.code || '' }}</div>
                    </td>
                    <td class="w-10 px-2 py-0.5 text-end">{{ $number(item.price) }}</td>
                    <td class="w-10 px-2 py-0.5 text-end">{{ $number(item.total) }}</td>
                  </tr>
                </tbody>
              </template>
            </template>
          </template>

          <tfoot>
            <tr>
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Total') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">{{ $number(receipt.total) }}</th>
            </tr>
            <tr v-if="receipt.discount_amount > 0">
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Discount') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $number(receipt.discount_amount) }}
              </th>
            </tr>
            <tr v-if="receipt.total_tax_amount > 0">
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Tax') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $number(receipt.total_tax_amount) }}
              </th>
            </tr>
            <tr v-else-if="page.props.settings.show_zero_taxes == 1">
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Tax') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $number(receipt.total_tax_amount) }}
              </th>
            </tr>
            <tr
              v-if="
                Number(receipt.total_tax_amount) > 0 ||
                Number(receipt.discount_amount) > 0 ||
                Number(receipt.total) != Number(receipt.grand_total)
              "
            >
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Grand Total') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $number(receipt.grand_total) }}
              </th>
            </tr>
            <tr>
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Paid') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $number(receipt.paid) }}
              </th>
            </tr>
            <tr>
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Balance') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $number(receipt.grand_total - receipt.paid) }}
              </th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div v-if="Number(receipt.tendered) > 0">
        <div>{{ $t('Tender Amount') }}: {{ $number(receipt.tendered) }}</div>
        <div>{{ $t('Return Amount') }}: {{ $number(receipt.change_returned) }}</div>
      </div>

      <div v-if="receipt.details" class="text-center">
        {{ receipt.details || '' }}
      </div>
      <div v-if="receipt.store?.footer" class="text-center">
        {{ receipt.store.footer || '' }}
      </div>
      <div v-if="$page.props.settings?.receipt_footer" class="text-center">
        {{ $page.props.settings?.receipt_footer }}
      </div>
    </div>
  </div>
</template>
