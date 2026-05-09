<script setup>
import dayjs from 'dayjs';
import { notify } from 'notiwind';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

import { axios, $random } from '@/Core';
import { Modal } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton } from '@/Components/Common';

const page = usePage();
const { t } = useI18n({});
const props = defineProps(['show', 'halls', 'loadOrder']);
const emit = defineEmits(['close', 'showOpenOrders', 'viewRegisterDetails']);

const details = ref({});
const loading = ref(false);
const reference = ref(null);
const selectedHallId = ref(null);
const selectedTableId = ref(null);
const referenceNumber = ref(null);

const tables = computed(() => {
  return props.halls.find(h => h.id === selectedHallId.value)?.tables || [];
});

const availableTables = computed(() => {
  return tables.value.filter(t => t.available || t.status === 'available');
});

watch(
  () => props.show,
  value => {
    if (value) {
      reference.value = getNewReference();
      // Auto-select hall if there's only one
      if (page.props.settings?.restaurant == 1 && props.halls && props.halls.length === 1) {
        selectedHallId.value = props.halls[0].id;
      }
    }
  }
);

onMounted(() => {
  reference.value = getNewReference();
  selectedHallId.value = null;
  selectedTableId.value = null;
  referenceNumber.value = null;
  // Auto-select hall if there's only one
  if (page.props.settings?.restaurant == 1 && props.halls && props.halls.length === 1) {
    selectedHallId.value = props.halls[0].id;
  }
});

function getNewReference() {
  let no = localStorage.getItem('pos_order_number');
  if (!no) {
    no = 1;
    localStorage.setItem('pos_order_number', no);
  }

  return 'Order ' + dayjs().format('M.D') + '.' + (no < 10 ? '00' + no : no < 100 ? '0' + no : no).toString();
}

async function checkOrderReference(reference) {
  return await axios
    .post(route('pos.orders.checkReference'), { reference })
    .then(() => true)
    .catch(() => false);
}

async function openOrderNow() {
  loading.value = true;

  if (reference.value) {
    const available = await checkOrderReference(reference.value);
    if (!available) {
      loading.value = false;
      return;
    }

    details.value.tendered = 0;
    details.value.discount = 0;
    details.value.reference = reference.value;
    details.value.user_id = page.props.auth.user.id;
    details.value.customer_id = page.props.settings.default_customer;
    details.value.payments = [{ amount: null, method: 'Cash', method_data: {} }];
    details.value.number = dayjs().format('YYMD') + '-' + String($random(0, 9999)).padStart(4, '0');

    let tableAvailable = false;
    if (page.props.settings?.restaurant == 1) {
      details.value.hall_id = selectedHallId.value;
      details.value.table_id = selectedTableId.value;
      details.value.reference_number = referenceNumber.value;
      if (details.value.table_id) {
        await axios
          .post(route('pos.table-status'), {
            hall_id: selectedHallId.value,
            table_id: selectedTableId.value,
          })
          .then(response => {
            if (response.data.available) {
              tableAvailable = true;
            } else {
              props.loadOrder(response.data.order);
              notify({
                group: 'main',
                type: 'success',
                title: 'Info!',
                text: t('Selected table is not available. It is already assigned to Order #: {order_number}', {
                  order_number: response.data.order.number,
                }),
              });
            }
          })
          .catch(() => {
            notify(
              {
                group: 'main',
                type: 'error',
                title: 'Error!',
                text: t('Failed to check table availability. Please try again.'),
              },
              10000
            );
          })
          .finally(() => (loading.value = false));
      } else if (details.value.reference_number) {
        tableAvailable = true;
      } else {
        notify({
          group: 'main',
          type: 'error',
          title: 'Error!',
          text: t('Please select a table or enter reference number for takeaway/delivery.'),
        });
      }
    }

    if (tableAvailable || page.props.settings?.restaurant != 1) {
      const no = localStorage.getItem('pos_order_number');
      localStorage.setItem('pos_order_number', Number(no) + 1);
      emit('close', details.value);
    }
  }
  loading.value = false;
}
</script>

<template>
  <Modal :show="show" maxWidth="sm" :closeable="true" @close="emit('close')">
    <div class="print:hidden">
      <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Open New Order') }}
        </h3>
        <p class="mt-1 text-sm">{{ $t('Please type the reference to open form.') }}</p>
      </div>

      <div class="rounded-b-lg bg-gray-100 p-6 dark:bg-gray-800">
        <form @submit.prevent="openOrderNow" autocomplete="off" class="flex flex-col items-stretch gap-4">
          <div>
            <label for="order-reference" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{
              $t('Order Reference')
            }}</label>
            <Input keyboard v-model="reference" id="order-reference" />
          </div>

          <!-- Restaurant Table Selection -->
          <template v-if="$page.props.settings?.restaurant == 1 && halls && halls.length">
            <div>
              <label for="hall_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Hall') }}</label>
              <AutoComplete
                json
                keyboard
                id="hall_id"
                valueKey="id"
                labelKey="name"
                v-model="selectedHallId"
                inputClass="focus rounded-md shadow-sm border-gray-300 dark:border-gray-700"
                :suggestions="halls.map(h => ({ id: h.id, name: h.name }))"
              />
            </div>

            <div>
              <label for="table_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Table') }}</label>
              <AutoComplete
                json
                keyboard
                clearable
                id="table_id"
                v-model="selectedTableId"
                :disabled="!selectedHallId"
                inputClass="focus rounded-md shadow-sm border-gray-300 dark:border-gray-700"
                :suggestions="availableTables.map(t => ({ value: t.id, label: `${t.name} (${t.seats} ${$t('Seats')})` }))"
              />
              <p v-if="selectedHallId && availableTables.length === 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $t('No available tables in selected hall.') }}
              </p>
            </div>

            <div v-if="!selectedTableId">
              <label for="reference_number" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{
                $t('Reference Number')
              }}</label>
              <Input keyboard id="reference_number" v-model="referenceNumber" :placeholder="$t('For takeaway or delivery')" />
            </div>
          </template>
          <template v-else-if="$page.props.settings?.restaurant == 1 && !halls?.length">
            <p class="text-sm text-yellow-600 dark:text-yellow-400">
              {{
                $can('read-halls') ? $t('No halls available. Please create a hall first.') : $t('You do not have permission to view halls.')
              }}
            </p>
          </template>

          <LoadingButton class="justify-center" :loading="loading">{{ $t('Open') }}</LoadingButton>
        </form>
        <div class="mt-6 grid grid-cols-2 gap-2">
          <button type="button" @click="emit('showOpenOrders')" class="btn-secondary justify-center">
            {{ $t('Opened Orders') }}
          </button>
          <button type="button" @click="emit('viewRegisterDetails')" class="btn-secondary justify-center">
            {{ $t('Register Details') }}
          </button>
          <Link :href="route('dashboard')" class="btn-secondary justify-center">
            {{ $t('Dashboard') }}
          </Link>
          <Link :href="route('sales.index')" class="btn-secondary justify-center">
            {{ $t('List Sales') }}
          </Link>
        </div>
      </div>
    </div>
  </Modal>
</template>
