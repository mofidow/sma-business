<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $decimal } from '@/Core/helpers';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'types']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  account_type_id: props.current?.account_type_id,
  title: props.current?.title,
  details: props.current?.details,
  reference: props.current?.reference,
  opening_balance: $decimal(props.current?.opening_balance),
  offline: props.current?.offline == 1,
  fees: props.current?.fees == 1,
  fixed: props.current?.fixed,
  percentage: props.current?.percentage,
  apply_to: props.current?.apply_to,
  active: props.current?.id ? props.current?.active == 1 : true,
});

function handleSubmit() {
  form.post(props.current?.id ? route('accounts.update', props.current.id) : route('accounts.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Account') }) : $t('Add {x}', { x: $t('Account') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('account'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Type -->
      <div class="col-span-6 sm:col-span-3">
        <!-- <Input id="type" :label="$t('Type')" v-model="form.type" :error="form.errors.type" /> -->
        <AutoComplete
          id="type"
          :json="true"
          :searchable="false"
          :label="$t('Type')"
          v-model="form.account_type_id"
          :error="form.errors.account_type_id"
          :suggestions="types.map(type => ({ label: type.name, value: type.id }))"
        />
      </div>

      <!-- Title -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="title" :label="$t('Title')" v-model="form.title" :error="form.errors.title" />
      </div>

      <!-- Opening Balance -->
      <div class="col-span-6 sm:col-span-3">
        <Input
          type="number"
          id="opening_balance"
          :readonly="current?.id"
          :label="$t('Opening Balance')"
          v-model="form.opening_balance"
          :error="form.errors.opening_balance"
        />
      </div>

      <!-- Reference -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="reference" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
      </div>

      <!-- Details -->
      <div class="col-span-full">
        <Textarea id="details" :label="$t('Details')" v-model="form.details" :error="form.errors.details" />
      </div>

      <!-- Transaction Fees -->
      <div class="col-span-full">
        <CheckBox id="fees" :error="form.errors.fees" v-model:checked="form.fees" :label="$t('Apply transaction fees')" />
      </div>
      <template v-if="form.fees">
        <!-- Fixed Fees -->
        <div class="col-span-6 sm:col-span-3">
          <Input type="number" id="fixed" :label="$t('Fixed Fees')" v-model="form.fixed" :error="form.errors.fixed" />
        </div>

        <!-- Percentage Fees -->
        <div class="col-span-6 sm:col-span-3">
          <Input type="number" id="percentage" :label="$t('Percentage Fees')" v-model="form.percentage" :error="form.errors.percentage" />
        </div>

        <!-- Percentage Fees -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="apply_to"
            :searchable="false"
            :label="$t('Apply to')"
            v-model="form.apply_to"
            :error="form.errors.apply_to"
            :suggestions="[
              { value: 'Credit', label: $t('Credit') },
              { value: 'Debit', label: $t('Debit') },
              { value: 'Both', label: $t('Both (credit & debit)') },
            ]"
          />
        </div>
      </template>

      <!-- Offline -->
      <div class="col-span-full">
        <CheckBox id="offline" :label="$t('Show this in offline payments')" v-model:checked="form.offline" :error="form.errors.offline" />
      </div>

      <!-- Active -->
      <div class="col-span-full">
        <CheckBox id="active" :error="form.errors.active" v-model:checked="form.active" :label="$t('Active')" />
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
