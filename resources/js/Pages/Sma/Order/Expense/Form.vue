<script setup>
import dayjs from 'dayjs';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $extras } from '@/Core';
import { Modal } from '@/Components/Jet';
import SupplierForm from '@/Pages/Sma/People/Supplier/Form.vue';
import { SecondaryButton } from '@/Components/Jet';
import { Attachments, AutoComplete, CustomFields, DateInput, FileInput, Input, LoadingButton, Textarea } from '@/Components/Common';

const add_supplier = ref(false);
const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'custom_fields', 'countries', 'supplier_fields', 'accounts']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  attachments: null,
  reference: props.current?.reference,
  details: props.current?.details,
  account_id: props.current?.account_id,
  supplier_id: props.current?.supplier_id,
  amount: props.current ? Number(props.current.amount) : null,
  date: dayjs(props.current?.date).format('YYYY-MM-DD'),

  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const close = () => {
  form.reset();
  emits('close');
};

function handleSubmit() {
  form.post(props.current?.id ? route('expenses.update', props.current.id) : route('expenses.store'), {
    onSuccess: () => {
      form.reset();
      emits('done');
      emits('close');
    },
  });
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
      <div class="sm:flex sm:items-baseline sm:justify-between">
        <div class="sm:w-0 sm:flex-1">
          <h1 class="text-focus text-base font-semibold">
            {{ current?.id ? $t('Edit {x}', { x: $t('Expense') }) : $t('Add {x}', { x: $t('Expense') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('expense'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Date -->
      <div class="col-span-6 sm:col-span-3">
        <DateInput type="date" id="date" @change="saveForm" :label="$t('Date')" v-model="form.date" :error="form.errors.date" />
      </div>
      <!-- Reference -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="reference" @change="saveForm" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
      </div>

      <!-- Supplier -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          valueKey="id"
          id="supplier_id"
          labelKey="company"
          @change="saveForm"
          :searchable="true"
          :label="$t('Supplier')"
          v-model="form.supplier_id"
          :action="() => (add_supplier = true)"
          :error="$page.props.errors.supplier_id"
          :suggestions="route('search.suppliers')"
          :action-text="$t('Add {x}', { x: $t('Supplier') })"
        />
      </div>

      <!-- Account -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="account_id"
          :searchable="false"
          :label="$t('Account')"
          :suggestions="accounts"
          v-model="form.account_id"
          :error="form.errors.account_id"
        />
      </div>

      <!-- Amount -->
      <div class="col-span-6 sm:col-span-3">
        <Input type="number" id="amount" :label="$t('Amount')" v-model="form.amount" :error="form.errors.amount" />
      </div>

      <!-- Attachments -->
      <div class="col-span-6 sm:col-span-3">
        <FileInput
          multiple
          id="attachments"
          :label="$t('Attachments')"
          v-model="form.attachments"
          :error="form.errors.attachments"
          :accept="$page.props.settings?.attachment_exts || '.jpg,.png,.pdf,.xlsx,.docx,.zip'"
        />
      </div>

      <div v-if="current && current.attachments && current.attachments.length" class="col-span-full">
        <Attachments :attachments="current.attachments || []" />
      </div>

      <div class="col-span-full flex flex-col gap-6">
        <CustomFields :errors="form.errors" :custom_fields="custom_fields" :extra_attributes="form.extra_attributes" />
        <Textarea :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>

  <Modal :show="add_supplier" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="add_supplier = false">
    <SupplierForm
      :countries="countries"
      :custom_fields="supplier_fields"
      @close="add_supplier = false"
      @done="
        e => {
          form.supplier_id = e?.id;
          add_supplier = false;
        }
      "
    />
  </Modal>
</template>
