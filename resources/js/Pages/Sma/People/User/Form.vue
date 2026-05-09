<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { router, useForm } from '@inertiajs/vue3';

import { $extras } from '@/Core/helpers';
import { InputError, SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CheckBox, CustomFields, Input, LoadingButton } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'custom_fields', 'roles', 'stores', 'customer', 'supplier']);

const country = ref({ states: [] });
if (props.current?.id && props.current?.country_id) {
  country.value = props.countries.find(c => c.id == props.current.country_id);
}

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  phone: props.current?.phone,
  email: props.current?.email,
  username: props.current?.username,
  settings: props.current?.settings,
  employee: props.current?.employee == 1 ? '1' : props.customer || props.supplier ? '0' : '1',

  customer_id: props.current?.customer_id,
  supplier_id: props.current?.supplier_id,

  store_id: props.current?.store_id,
  roles: props.current?.roles?.map(s => s.id) || [],
  stores: props.current?.stores?.map(s => s.id) || [],

  //   country_id: props.current?.country_id,
  //   state_id: props.current?.state_id,
  //   lot_no: props.current?.lot_no,
  //   street: props.current?.street,
  //   address_line_1: props.current?.address_line_1,
  //   address_line_2: props.current?.address_line_2,
  //   city: props.current?.city,
  //   postal_code: props.current?.postal_code,

  password: null,
  password_confirmation: null,
  //   edit_all: props.current?.edit_all == 1,
  //   view_all: props.current?.view_all == 1,
  //   bulk_actions: props.current?.bulk_actions == 1,
  can_be_impersonated: props.current?.can_be_impersonated == 1,
  active: props.current?.id ? props.current?.active == 1 : true,

  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const close = () => {
  form.reset();
  emits('close');
};

function handleSubmit() {
  if (props.customer) {
    form.employee = false;
    form.customer_id = props.customer.id;
  }
  if (props.supplier) {
    form.employee = false;
    form.supplier_id = props.supplier.id;
  }
  form.post(props.current?.id ? route('users.update', props.current.id) : route('users.store'), {
    onSuccess: () => {
      form.reset();
      emits('done');
      emits('close');
      if (props.customer) {
        router.visit(route('customers.index'));
      } else if (props.supplier) {
        router.visit(route('suppliers.index'));
      }
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
            {{ current?.id ? $t('Edit {x}', { x: $t('User') }) : $t('Add {x}', { x: $t('User') }) }}
            {{ customer ? ' (' + customer.name + ')' : '' }}{{ supplier ? ' (' + supplier.name + ')' : '' }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('User'),
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

      <!-- Phone -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="phone" :label="$t('Phone')" v-model="form.phone" :error="form.errors.phone" />
      </div>

      <!-- Username -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="username" :label="$t('Username')" v-model="form.username" :error="form.errors.username" />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-3">
        <Input type="email" id="email" :label="$t('Email')" v-model="form.email" :error="form.errors.email" />
      </div>

      <!-- Password -->
      <div class="col-span-6 sm:col-span-3">
        <Input
          id="password"
          type="password"
          v-model="form.password"
          :error="form.errors.password"
          :label="$t('Password') + (current?.id ? ' (' + $t('optional') + ')' : '')"
        />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <Input
          id="password"
          type="password"
          v-model="form.password_confirmation"
          :error="form.errors.password_confirmation"
          :label="$t('Confirm Password') + (current?.id ? ' (' + $t('optional') + ')' : '')"
        />
      </div>

      <!-- <template v-if="!customer && !supplier"> -->
      <template v-if="stores?.length">
        <!-- Default Store -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="store_id"
            :searchable="false"
            :label="$t('Store')"
            :suggestions="stores"
            v-model="form.store_id"
            :error="form.errors.store_id"
          />
        </div>

        <!-- Stores -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            id="stores"
            :json="true"
            :multiple="true"
            :searchable="false"
            :label="$t('Stores')"
            :suggestions="stores"
            v-model="form.stores"
            :error="form.errors.stores"
          />
          <!-- :suggestions="form.store_id ? stores.filter(s => s.id != form.store_id) : stores" -->
        </div>
      </template>

      <template v-if="roles?.length">
        <!-- Roles -->
        <div class="col-span-full">
          <div v-if="roles && roles.length" class="flex flex-wrap items-center gap-x-8 gap-y-4">
            <label class="block w-full font-medium">{{ $t('Roles') }}</label>
            <label v-for="role in roles" :key="role.id" :for="role.id" class="inline-flex items-center">
              <input
                :id="role.id"
                name="roles[]"
                type="checkbox"
                :value="role.id"
                v-model="form.roles"
                :true-value="role.id"
                :checked="form.roles.includes(role.id)"
                class="h-5 w-5 rounded-sm border-gray-300 text-primary-600 shadow-xs focus:rounded-sm focus:ring-primary-200 focus:ring-offset-0 dark:border-gray-700 dark:bg-gray-600 dark:focus:ring-primary-200/50"
              />
              <span v-html="role.name" class="ms-2 cursor-default"></span>
            </label>

            <InputError v-if="form.errors.roles" :message="form.errors.roles?.split('when')[0]" class="-mt-1 w-full" />
          </div>

          <!-- <div class="col-span-full pt-2">
          <h1 class="font-bold mb-2">{{ $t('Permissions') }}</h1>
          <div class="flex flex-wrap lg:flex-row gap-x-10 gap-y-3">
            <CheckBox id="view_all" v-model:checked="form.view_all" :label="$t('View all records')" />
            <CheckBox id="edit_all" v-model:checked="form.edit_all" :label="$t('Edit all records')" />
            <CheckBox id="bulk_actions" v-model:checked="form.bulk_actions" :label="$t('Bulk actions')" />
            <CheckBox id="can_be_impersonated" v-model:checked="form.can_be_impersonated" :label="$t('Can be impersonated')" />
          </div>
        </div> -->
        </div>

        <!-- Customer -->
        <div v-if="form.roles.includes(roles.find(role => role.name == 'Customer')?.id)" class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            id="customer_id"
            labelKey="company"
            :searchable="true"
            :label="$t('Customer')"
            v-model="form.customer_id"
            :error="$page.props.errors.customer_id"
            :suggestions="route('search.customers')"
          />
        </div>

        <!-- Supplier -->
        <div v-if="form.roles.includes(roles.find(role => role.name == 'Supplier')?.id)" class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            id="supplier_id"
            labelKey="company"
            :searchable="true"
            :label="$t('Supplier')"
            v-model="form.supplier_id"
            :error="$page.props.errors.supplier_id"
            :suggestions="route('search.suppliers')"
          />
        </div>
      </template>
      <!-- </template> -->

      <!-- Employee -->
      <div v-if="!(customer || supplier)" class="col-span-full">
        <label class="mb-1 block font-medium">{{ $t('User is an Employee of {x}?', { x: $page.props.settings.name }) }}</label>
        <label for="not-employee" class="me-6 inline-flex items-center gap-2">
          <input value="0" type="radio" id="not-employee" :error="form.errors.employee" v-model="form.employee" />
          <span class="me-2 cursor-default">{{ $t('No') }}</span>
        </label>
        <label for="employee" class="me-6 inline-flex items-center gap-2">
          <input value="1" type="radio" id="employee" :error="form.errors.employee" v-model="form.employee" />
          <span class="me-2 cursor-default">{{ $t('Yes') }}</span>
        </label>
      </div>

      <!-- Active -->
      <div class="col-span-full">
        <CheckBox id="active" :error="form.errors.active" v-model:checked="form.active" :label="$t('Active')" />
        <CheckBox id="can_be_impersonated" v-model:checked="form.can_be_impersonated" :label="$t('Can be impersonated')" />
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
