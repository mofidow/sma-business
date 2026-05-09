<script setup>
import { notify } from 'notiwind';
import { useI18n } from 'vue-i18n';
import { computed, onMounted, ref, watch } from 'vue';

import { axios } from '@/Core';
import { Modal } from '@/Components/Jet';
import { AutoComplete, LoadingButton } from '@/Components/Common';

const { t } = useI18n({});
const props = defineProps(['show', 'halls', 'currentHallId', 'currentTableId']);
const emit = defineEmits(['close', 'changed']);

let tableId = null;
const loading = ref(false);
const selectedHallId = ref(null);
const selectedTableId = ref(null);

const tables = computed(() => {
  return props.halls.find(h => h.id == selectedHallId.value)?.tables || [];
});

const availableTables = computed(() => {
  return tables.value.filter(t => t.available || t.status === 'available' || t.id == props.currentTableId);
});

watch(
  () => props.show,
  value => {
    if (value) {
      tableId = props.currentTableId;
      selectedHallId.value = props.currentHallId;
      selectedTableId.value = props.currentTableId;
    }
  }
);

onMounted(() => {
  tableId = props.currentTableId;
  selectedHallId.value = props.currentHallId;
  selectedTableId.value = props.currentTableId;
});

async function changeTable() {
  if (!selectedHallId.value || !selectedTableId.value) {
    notify({
      group: 'main',
      type: 'error',
      title: 'Error!',
      text: t('Please select both hall and table.'),
    });
    return;
  }

  // If no change, just close
  if (selectedHallId.value === props.currentHallId && selectedTableId.value === props.currentTableId) {
    emit('close');
    return;
  }

  loading.value = true;

  await axios
    .post(route('pos.table-status'), {
      hall_id: selectedHallId.value,
      table_id: selectedTableId.value,
    })
    .then(response => {
      if (response.data.available) {
        emit('changed', {
          hall_id: selectedHallId.value,
          table_id: selectedTableId.value,
        });
        notify({
          group: 'main',
          type: 'success',
          title: 'Success!',
          text: t('Table changed successfully.'),
        });
      } else {
        notify({
          group: 'main',
          type: 'error',
          title: 'Error!',
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
}
</script>

<template>
  <Modal :show="show" maxWidth="sm" :closeable="true" @close="emit('close')" :overflow="true">
    <div>
      <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Change Table') }}
        </h3>
        <p class="mt-1 text-sm">{{ $t('Select a new table for this order.') }}</p>
      </div>

      <div class="rounded-b-lg bg-gray-100 p-6 dark:bg-gray-800">
        <form @submit.prevent="changeTable" autocomplete="off" class="flex flex-col items-stretch gap-4">
          <div>
            <label for="change_hall_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Hall') }}</label>
            <AutoComplete
              json
              keyboard
              valueKey="id"
              labelKey="name"
              id="change_hall_id"
              v-model="selectedHallId"
              inputClass="focus rounded-md shadow-sm border-gray-300 dark:border-gray-700"
              :suggestions="halls.map(h => ({ id: h.id, name: h.name }))"
            />
          </div>

          <div>
            <label for="change_table_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Table') }}</label>
            <AutoComplete
              json
              keyboard
              valueKey="value"
              labelKey="label"
              id="change_table_id"
              v-model="selectedTableId"
              :disabled="!selectedHallId"
              inputClass="focus rounded-md shadow-sm border-gray-300 dark:border-gray-700"
              :suggestions="
                availableTables.map(t => ({
                  value: t.id,
                  label: `${t.name} (${t.seats} ${$t('Seats')})${t.id == tableId ? ' - ' + $t('Current') : ''}`,
                }))
              "
            />
            <p v-if="selectedHallId && availableTables.length === 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              {{ $t('No available tables in selected hall.') }}
            </p>
          </div>

          <div class="flex gap-2">
            <button type="button" @click="emit('close')" class="btn-secondary flex-1 justify-center">
              {{ $t('Cancel') }}
            </button>
            <LoadingButton class="flex-1 justify-center" :loading="loading">{{ $t('Change Table') }}</LoadingButton>
          </div>
        </form>
      </div>
    </div>
  </Modal>
</template>
