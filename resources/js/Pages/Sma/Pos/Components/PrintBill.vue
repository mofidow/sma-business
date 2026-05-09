<script setup>
import { onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { $datetime, $currency, $date, $meta, $number_qty, $number, $decimal_qty } from '@/Core';

const page = usePage();
const { t } = useI18n({});
const emit = defineEmits(['close']);
const props = defineProps(['form', 'show', 'sendPrint', 'halls']);

const tables = computed(() => {
  return props.halls.find(h => h.id === props.form.selectedHallId)?.tables || [];
});

onMounted(() => {
  if (page.props.settings?.print_dialog == 1) {
    setTimeout(print, 250);
  }
});

function print() {
  if (page.props.settings?.pos_server == 1) {
    const data = { type: 'print-json-bill', data: {} };

    data.data.logo = page.props.opened_register.store.logo || page.props.settings?.logo || '';
    data.data.heading = page.props.settings?.name;

    data.data.store_heading = page.props.opened_register.store.name || '';
    data.data.store_details = {};
    data.data.store_details['name'] = page.props.opened_register.store.name || '';
    data.data.store_details['email'] = page.props.opened_register.store.phone || '';
    data.data.store_details['phone'] = page.props.opened_register.store.email || '';
    data.data.store_details['address'] = page.props.opened_register.store?.address || '';
    data.data.store_details['state'] = page.props.opened_register.store?.state?.name || '';
    data.data.store_details['country'] = page.props.opened_register.store?.country?.name || '';

    data.data.type = t('Bill');
    data.data.headers = null; // [page.props.settings?.receipt_header || null, page.props.opened_register.store?.header || null];

    data.data.info = [
      [t('Id'), props.form.id || ''],
      [t('Ref'), props.form.reference],
      [t('Order No.'), props.form.number],
      [t('Customer'), props.form.customer?.company || props.form.customer?.name || ''],
      [t('Date'), props.form.created_at ? $date(props.form.created_at) : $date(new Date().toISOString(), null, null, true)],
      [t('Created by'), props.form.user?.name || page.props.auth?.user?.name || ''],
      [t('Created at'), props.form.created_at ? $datetime(props.form.created_at) : $datetime(new Date().toISOString(), null, null, true)],
      [t('Printed at'), $datetime(new Date().toISOString(), null, null, true)],
    ];
    data.data.table_headers = [{ label: t('Description') }, { label: t('Price') }, { label: t('Qty') }, { label: t('Subtotal') }];

    let items = props.form.items.map(item => {
      if (item.variations && item.variations.length) {
        const variations = item.variations.map(variation => ({
          type: 'item',
          name: $meta(variation.meta),
          qty: $number_qty(variation.quantity),
          price: $number(variation.price),
          subtotal: $number(variation.total),
        }));
        return { variations: [{ type: 'text', text: item.name + (item.comment ? '\n' + item.comment : '') }, ...variations] };
      } else {
        return {
          type: 'item',
          name: item.name + (item.comment ? '\n' + item.comment : ''),
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
      { label: t('Total'), value: $currency(props.form.items.reduce((a, i) => Number(i.subtotal) + a, 0)) },
      { label: t('Discount'), value: $currency(props.form.items.reduce((a, i) => Number(i.total_discount_amount) + a, 0)) },
      { label: t('Tax'), value: $currency(props.form.items.reduce((a, i) => Number(i.total_tax_amount) + a, 0)) },
      { label: t('Grand Total'), value: $currency(props.form.items.reduce((a, i) => Number(i.total) + a, 0)) },
    ];

    data.data.footers = null; // [page.props.settings?.receipt_footer || null, page.props.opened_register.store?.footer || null];
    data.data.notice =
      t('This is not receipt but the bill to collect payment.') + '\n' + t('Official receipt will be issued after receiving payment.');

    props.sendPrint(data);
  } else {
    window.print();
  }
}
</script>

<template>
  <div>
    <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700 print:hidden">
      <div class="absolute end-0 top-0 flex items-center gap-3 pe-4 pt-4">
        <button type="button" @click="print()" class="focus relative rounded-md text-gray-400 hover:text-gray-500">
          <span class="absolute -inset-2.5"></span>
          <span class="sr-only">{{ $t('Print') }}</span>
          <Icon name="print-o" className="size-6" />
        </button>
        <button
          type="button"
          @click="emit('close')"
          class="rounded-md text-gray-400 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden"
        >
          <span class="sr-only">{{ $t('Close') }}</span>
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div>
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Print Bill') }}
        </h3>
      </div>
    </div>

    <div v-if="form.items && form.items.length" class="m-6 flex flex-col gap-4">
      <div class="text-center">
        <img
          v-if="page.props.opened_register.store.logo"
          class="h-8 max-w-[200pc]"
          :src="page.props.opened_register.store.logo"
          alt="Logo"
        />
        <h2 v-else class="text-center text-lg font-bold">{{ page.props.opened_register.store.name || settings.name }}</h2>
        <div v-if="page.props.opened_register.store?.address">{{ page.props.opened_register.store.address || '' }}</div>
        <div v-if="page.props.opened_register.store?.phone">{{ $t('Phone') }}: {{ page.props.opened_register.store.phone || '' }}</div>
        <div v-if="page.props.opened_register.store?.email">{{ $t('Email') }}: {{ page.props.opened_register.store.email || '' }}</div>
      </div>

      <h2 class="text-center text-lg font-bold">{{ $t('Bill') }}</h2>
      <div>
        <div v-if="form.id">{{ $t('Id') }}: {{ form.id }}</div>
        <div>{{ $t('Customer') }}: {{ form.customer?.company || form.customer?.name || '' }}</div>
        <div>{{ $t('Order Ref') }}: {{ form.reference }}</div>
        <div>{{ $t('Order Number') }}: {{ form.number }}</div>
        <div v-if="form.table">
          {{ $t('Table') }}: {{ form.table?.name || tables.find(table => table.id === form.selectedTableId)?.name || '' }} ({{
            form.table?.hall?.name || halls.find(hall => hall.id === form.selectedHallId)?.name || 'Main'
          }})
        </div>
        <div>
          {{ $t('Created at') }}: {{ form.created_at ? $datetime(form.created_at) : $datetime(new Date().toISOString(), null, null, true) }}
        </div>
        <div>{{ $t('Created  by') }}: {{ form.user?.name || page.props.auth?.user?.name || '' }}</div>
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
          <template v-for="item in form.items">
            <template v-if="item.variations && item.variations.length">
              <tbody :key="item.id">
                <tr>
                  <td class="px-2 py-0.5 text-start">
                    {{ item.name }}
                    <div v-if="item.comment" class="pb-1 text-xs">{{ item.comment }}</div>
                  </td>
                  <td class="w-10 px-2 py-0.5 text-center"></td>
                  <td class="w-10 px-2 py-0.5 text-end"></td>
                  <td class="w-10 px-2 py-0.5 text-end"></td>
                </tr>
                <tr v-for="variation in item.variations" :key="variation.id">
                  <td class="px-2 py-0.5 text-start">{{ $meta(variation.meta) }}</td>
                  <td class="w-10 px-2 py-0.5 text-center">
                    {{ $number_qty(variation.quantity) }}
                    <!-- {{ variation.pivot?.unit?.code || '' }} -->
                  </td>
                  <td class="w-10 px-2 py-0.5 text-end">{{ $number(variation.price) }}</td>
                  <td class="p</template>y-0.5 w-10 px-2 text-end">{{ $number(variation.total) }}</td>
                </tr>
              </tbody>
            </template>
            <tbody v-else :key="item.id + '_'">
              <!-- <template v-if="item.quantity > 0"> -->
              <tr>
                <td class="px-2 py-0.5 text-start">
                  {{ item.name }}
                  <div v-if="item.comment" class="pb-1 text-xs">{{ item.comment }}</div>
                </td>
                <td class="w-10 px-2 py-0.5 text-center">{{ $decimal_qty(item.quantity) }}</td>
                <td class="w-10 px-2 py-0.5 text-end">{{ $number(item.price) }}</td>
                <td class="w-10 px-2 py-0.5 text-end">{{ $number(item.total) }}</td>
              </tr>
              <!-- </template> -->
            </tbody>
          </template>
          <tfoot>
            <tr>
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Total') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $currency(form.items.reduce((a, i) => Number(i.subtotal) + a, 0)) }}
              </th>
            </tr>
            <tr v-if="form.items.reduce((a, i) => Number(i.total_discount_amount) + a, 0) > 0">
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Discount') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $currency(form.items.reduce((a, i) => Number(i.total_discount_amount) + a, 0)) }}
              </th>
            </tr>
            <tr>
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Tax') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $currency(form.items.reduce((a, i) => Number(i.total_tax_amount) + a, 0)) }}
              </th>
            </tr>
            <tr>
              <th colspan="3" class="px-2 py-0.5 text-end">{{ $t('Grand Total') }}</th>
              <th class="w-10 px-2 py-0.5 text-end whitespace-nowrap">
                {{ $currency(form.items.reduce((a, i) => Number(i.total) + a, 0)) }}
              </th>
            </tr>
          </tfoot>
        </table>
      </div>
      <div>
        <div>{{ $t('Printed at') }}: {{ $datetime(new Date().toISOString(), null, null, true) }}</div>
        <div>{{ $t('Printed by') }}: {{ page.props.auth?.user?.name || '' }}</div>
        <div class="mt-3 text-center text-sm">
          <div>{{ $t('This is not receipt but the bill to collect payment.') }}</div>
          <div>{{ $t('Official receipt will be issued after receiving payment.') }}</div>
        </div>
      </div>
    </div>
    <div v-else class="m-6 flex flex-col gap-4 text-yellow-600">
      {{ $t('Please add at least one item to form.') }}
    </div>
  </div>
</template>
