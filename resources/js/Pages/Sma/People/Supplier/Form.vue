<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $decimal, $extras } from '@/Core/helpers';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CustomFields, Input, LoadingButton } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'countries', 'custom_fields']);

const country = ref({ states: [] });
if (props.current?.id && props.current?.country_id) {
  country.value = props.countries.find(c => c.id == props.current.country_id);
}

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  company: props.current?.company,
  phone: props.current?.phone,
  email: props.current?.email,
  due_limit: props.current?.due_limit ? $decimal(props.current?.due_limit) : null,
  opening_balance: $decimal(props.current?.opening_balance),

  country_id: props.current?.country_id,
  state_id: props.current?.state_id,
  lot_no: props.current?.lot_no,
  street: props.current?.street,
  address_line_1: props.current?.address_line_1,
  address_line_2: props.current?.address_line_2,
  city: props.current?.city,
  postal_code: props.current?.postal_code,

  telegram_user_id: props.current?.telegram_user_id,
  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const close = () => {
  form.reset();
  emits('close');
};

function handleSubmit() {
  form.post(props.current?.id ? route('suppliers.update', props.current.id) : route('suppliers.store'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: p => {
      form.reset();
      emits('done', p.props.flash?.data);
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Supplier') }) : $t('Add {x}', { x: $t('Supplier') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('Supplier'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Name -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="name" :label="$t('Name')" v-model="form.name" :error="form.errors.name" />
      </div>

      <!-- Company -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="company" :label="$t('Company')" v-model="form.company" :error="form.errors.company" />
      </div>

      <!-- Phone -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="phone" :label="$t('Phone')" v-model="form.phone" :error="form.errors.phone" />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-3">
        <Input type="email" id="email" :label="$t('Email')" v-model="form.email" :error="form.errors.email" />
      </div>

      <!-- Telegram User ID -->
      <div class="col-span-6 sm:col-span-3">
        <Input
          :keyboard="keyboard"
          id="telegram_user_id"
          :label="$t('Telegram User ID')"
          v-model="form.telegram_user_id"
          :error="form.errors.telegram_user_id"
        />
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

      <!-- Due Limit -->
      <div class="col-span-6 sm:col-span-3">
        <Input type="number" id="due_limit" :label="$t('Due Limit')" v-model="form.due_limit" :error="form.errors.due_limit" />
      </div>

      <!-- Address Fields -->
      <div class="col-span-full"></div>
      <!-- Country -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="country_id"
          :label="$t('Country')"
          v-model="form.country_id"
          :error="form.errors.country_id"
          :suggestions="countries.map(c => ({ ...c, value: c.id, label: c.name }))"
          @change="
            e => {
              country = e;
              form.state_id = country?.states[0]?.id;
            }
          "
        />
      </div>
      <!-- State -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="state_id"
          :label="$t('State')"
          v-model="form.state_id"
          :error="form.errors.state_id"
          :suggestions="
            country?.states?.length ? country.states.map(s => ({ ...s, value: s.id, label: s.name })) : [{ value: '0', label: $t('N/A') }]
          "
        />
      </div>

      <!-- Lot No -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="lot_no" :label="$t('Lot No.')" v-model="form.lot_no" :error="form.errors.lot_no" />
      </div>
      <!-- Street -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="street" :label="$t('Street')" v-model="form.street" :error="form.errors.street" />
      </div>
      <!-- Address Line 1 -->
      <div class="col-span-full">
        <Input id="address_line_1" :label="$t('Address Line 1')" v-model="form.address_line_1" :error="form.errors.address_line_1" />
      </div>
      <!-- Address Line 2 -->
      <div class="col-span-full">
        <Input id="address_line_2" :label="$t('Address Line 2')" v-model="form.address_line_2" :error="form.errors.address_line_2" />
      </div>
      <!-- City -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="city" :label="$t('City')" v-model="form.city" :error="form.errors.city" />
      </div>
      <!-- Postal/ZIP Code -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="postal_code" :label="$t('Postal/ZIP Code')" v-model="form.postal_code" :error="form.errors.postal_code" />
      </div>

      <!-- Custom Fields -->
      <div class="col-span-full">
        <CustomFields :custom_fields="custom_fields" :errors="form.errors" :extra_attributes="form.extra_attributes" />
      </div>
    </div>

    <div class="flex flex-row justify-end rounded-b-lg bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
