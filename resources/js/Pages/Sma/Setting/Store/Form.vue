<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { router, useForm } from '@inertiajs/vue3';

import { $extras } from '@/Core/helpers';
import { InputError, InputLabel, SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CheckBox, CustomFields, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'show', 'accounts', 'countries', 'price_groups', 'custom_fields']);

const logoInput = ref(null);
const logoPreview = ref(null);
const country = ref({ states: [] });
const logo = ref(props.current?.logo || null);
if (props.current?.id && props.current?.country_id) {
  country.value = props.countries.find(c => c.id == props.current.country_id);
}

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  logo: null,
  name: props.current?.name,
  phone: props.current?.phone,
  email: props.current?.email,
  account_id: props.current?.account_id,
  price_group_id: props.current?.price_group_id,
  country_id: props.current?.country_id,
  state_id: props.current?.state_id,
  lot_no: props.current?.lot_no,
  street: props.current?.street,
  address_line_1: props.current?.address_line_1,
  address_line_2: props.current?.address_line_2,
  city: props.current?.city,
  postal_code: props.current?.postal_code,
  footer: props.current?.footer,
  header: props.current?.header,
  active: props.current?.id ? props.current?.active == 1 : true,

  telegram_user_id: props.current?.telegram_user_id,
  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const close = () => {
  form.reset();
  logoPreview.value = null;
  clearLogoFileInput();
  emits('close');
};

const updateLogoPreview = () => {
  const logo = logoInput.value.files[0];
  if (!logo) return;

  const reader = new FileReader();
  reader.onload = e => {
    logoPreview.value = e.target.result;
  };
  reader.readAsDataURL(logo);
};

const deleteLogo = () => {
  router.delete(route('stores.logo.destroy', { id: props.current.id }), {
    preserveScroll: true,
    onSuccess: () => {
      logo.value = null;
      logoPreview.value = null;
      clearLogoFileInput();
    },
  });
};

const selectNewLogo = () => {
  logoInput.value.click();
};

const clearLogoFileInput = () => {
  if (logoInput.value?.value) {
    logoInput.value.value = null;
  }
};

function handleSubmit() {
  if (logoInput.value) {
    form.logo = logoInput.value.files[0];
  }

  form.post(props.current?.id ? route('stores.update', props.current.id) : route('stores.store'), {
    onSuccess: () => {
      close();
      emits('done');
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Store') }) : $t('Add {x}', { x: $t('Store') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('store'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Logo -->
      <div class="col-span-full">
        <!-- Logo File Input -->
        <input id="logo" ref="logoInput" type="file" class="hidden" @change="() => updateLogoPreview()" />

        <InputLabel for="logo" :value="$t('Logo')" />

        <!-- Current Logo -->
        <div v-show="!logoPreview && logo" class="mt-2 rounded-md p-1">
          <img :alt="$t('Logo')" :src="logo" class="h-full max-h-40 min-h-20 w-full max-w-64 rounded-md object-contain" />
        </div>

        <!-- New Logo Preview -->
        <div v-show="logoPreview" class="mt-2 rounded-md p-1">
          <span
            class="block h-full max-h-40 min-h-24 w-full max-w-64 rounded-md bg-contain bg-no-repeat"
            :style="'background-image: url(\'' + logoPreview + '\');'"
          />
        </div>

        <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="() => selectNewLogo()">
          {{ $t('Select A New Logo') }}
        </SecondaryButton>

        <SecondaryButton
          v-if="logo"
          type="button"
          @click.prevent="deleteLogo"
          class="mt-2 flex items-center justify-center rounded-md bg-gray-50 p-1"
        >
          {{ $t('Remove Logo') }}
        </SecondaryButton>

        <InputError :message="form.errors.logo" class="mt-2" />
      </div>

      <!-- Name -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="name" :label="$t('Name')" v-model="form.name" :error="form.errors.name" />
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

      <!-- Price Group -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :searchable="false"
          id="price_group_id"
          :label="$t('Price Group')"
          :suggestions="price_groups"
          v-model="form.price_group_id"
          :error="form.errors.price_group_id"
        />
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

      <!-- Receipt Header -->
      <div class="col-span-full">
        <Textarea id="header" :error="form.errors.header" v-model="form.header" :label="$t('Receipt Header')" />
      </div>

      <!-- Receipt Footer -->
      <div class="col-span-full">
        <Textarea id="footer" :error="form.errors.footer" v-model="form.footer" :label="$t('Receipt Footer')" />
      </div>

      <!-- Active -->
      <div class="col-span-full">
        <CheckBox id="active" :error="form.errors.active" v-model:checked="form.active" :label="$t('Active')" />
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
