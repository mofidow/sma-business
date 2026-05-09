<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton, Textarea } from '@/Components/Common';

const { t } = useI18n();
const props = defineProps(['accounts']);
const emits = defineEmits(['close', 'done']);

const typeOptions = [
  { value: 'debit', label: t('Debit') },
  { value: 'credit', label: t('Credit') },
];

const form = useForm({
  account_id: null,
  type: null,
  amount: null,
  reference: null,
  note: null,
});

function handleSubmit() {
  form.post(route('account-transactions.store'), {
    onSuccess: () => {
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
            {{ $t('Add {x}', { x: $t('Transaction') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{ $t('Please fill the form below to {action} {record}.', { record: $t('transaction'), action: $t('add') }) }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="account_id"
          :label="$t('Account')"
          v-model="form.account_id"
          :error="form.errors.account_id"
          :suggestions="accounts.map(a => ({ value: a.id, label: a.title }))"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :searchable="false"
          id="type"
          :label="$t('Type')"
          v-model="form.type"
          :error="form.errors.type"
          :suggestions="typeOptions"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="number" id="amount" :label="$t('Amount')" v-model="form.amount" :error="form.errors.amount" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input id="reference" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
      </div>

      <div class="col-span-full">
        <Textarea id="note" :label="$t('Note')" v-model="form.note" :error="form.errors.note" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="emits('close')"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
