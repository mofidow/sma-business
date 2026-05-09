<script setup>
import { onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { $datetime, $meta, $number_qty, $decimal_qty } from '@/Core';

const page = usePage();
const { t } = useI18n({});
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
    const data = { type: 'print-json-order', data: {} };

    data.data.logo = page.props.opened_register.store.logo || page.props.settings?.logo || '';
    data.data.heading = page.props.settings?.name;

    data.data.store_heading = page.props.opened_register.store?.name || '';
    data.data.store_details = {};
    // data.data.store_details['name'] = page.props.opened_register.store.name || '';
    data.data.store_details['state'] = page.props.opened_register.store?.state?.name || '';
    data.data.store_details['country'] = page.props.opened_register.store?.country?.name || '';

    data.data.type = t('Order');
    data.data.headers = null;

    data.data.info = [
      [t('Ref'), props.form.reference],
      [t('Order No.'), props.form.number],
      [t('Created by'), props.form.user?.name || page.props.auth?.user?.name || ''],
      [t('Printed at'), $datetime(new Date().toISOString(), null, null, true)],
    ];
    data.data.table_headers = [{ label: t('Description') }, { label: t('Qty') }];

    let items = props.form.items.map(item => {
      if (item.variations && item.variations.length) {
        const variations = item.variations.map(variation => ({
          type: 'item',
          name: $meta(variation.meta),
          qty: $number_qty(variation.quantity),
        }));
        return { variations: [{ type: 'text', text: item.name + (item.comment ? '\n' + item.comment : '') }, ...variations] };
      } else {
        return {
          type: 'item',
          name: item.name + (item.comment ? '\n' + item.comment : ''),
          qty: $decimal_qty(item.quantity),
        };
      }
    });

    const variations = items.filter(i => i.variations).map(i => i.variations);
    const simpleItems = items.filter(i => !i.variations);
    items = [...variations.flat(), ...simpleItems];
    data.data.items = [items];

    data.data.totals = [
      {
        label: t('Total Items'),
        value: Number(props.form.items.length) + ' (' + props.form.items.reduce((a, i) => a + Number(i.quantity), 0) + ')',
      },
    ];

    data.data.footers = null;
    data.data.notice = null;
    console.log(data);

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
          {{ $t('Print Order') }}
        </h3>
      </div>
    </div>

    <div v-if="form.items && form.items.filter(i => i.quantity).length" class="m-6 flex flex-col gap-4">
      <img v-if="page.props.opened_register.store.logo" class="h-8 max-w-[200pc]" :src="page.props.opened_register.store.logo" alt="Logo" />
      <h2 v-else class="text-center text-lg font-bold">{{ page.props.opened_register.store.name || settings.name }}</h2>
      <h2 class="text-center text-lg font-bold">{{ $t('Order') }}</h2>
      <div>
        <div v-if="form.id">{{ $t('Id') }}: {{ form.id }}</div>
        <div v-if="form.customer">{{ $t('Customer') }}: {{ form.customer?.company || form.customer?.name || '' }}</div>
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
                </tr>
                <tr v-for="variation in item.variations" :key="variation.id">
                  <td class="px-2 py-0.5 text-start">{{ $meta(variation.meta) }}</td>
                  <td class="w-10 px-2 py-0.5 text-center">
                    {{ $number_qty(variation.quantity) }}
                    <!-- {{ variation.pivot?.unit?.code || '' }} -->
                  </td>
                </tr>
              </tbody>
            </template>
            <template v-else>
              <template v-if="item.quantity > 0">
                <tbody :key="item.id + '_'">
                  <tr>
                    <td class="px-2 py-0.5 text-start">
                      {{ item.name }}
                      <div v-if="item.comment" class="pb-1 text-xs">{{ item.comment }}</div>
                    </td>
                    <td class="w-10 px-2 py-0.5 text-center">{{ $decimal_qty(item.quantity) }}</td>
                  </tr>
                </tbody>
              </template>
            </template>
          </template>
        </table>
      </div>
      <div>
        <div>{{ $t('Sent at') }}: {{ $datetime(new Date().toISOString(), null, null, true) }}</div>
        <div>{{ $t('Sent by') }}: {{ page.props.auth?.user?.name || '' }}</div>
      </div>
    </div>
    <div v-else class="m-6 flex flex-col gap-4 text-yellow-600">
      {{ $t('Please add at least one item to form.') }}
    </div>
  </div>
</template>
