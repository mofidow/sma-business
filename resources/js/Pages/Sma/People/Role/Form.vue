<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { CheckBox, Input, LoadingButton } from '@/Components/Common';

const props = defineProps(['current']);
const emits = defineEmits(['close', 'done']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  permissions: props.current?.permissions?.map(p => p.name) || [],
});

const updatePermissions = (v, force) => {
  if (!force && form.permissions.includes(v)) {
    form.permissions = form.permissions.filter(r => r != v);
  } else {
    form.permissions = [...form.permissions, v];
  }
};

const close = () => {
  form.reset();
  emits('close');
};

const toggleAll = (e, type) => {
  const aa = document.querySelectorAll('input[type=checkbox]');
  for (let i = 0; i < aa.length; i++) {
    if (type) {
      if (aa[i].value.includes(type) && !['read-all', 'update-all', 'read-activity'].includes(aa[i].value)) {
        updatePermissions(aa[i].value, e.target.checked);
        // aa[i].checked = e.target.checked;
      }
    } else {
      updatePermissions(aa[i].value, e.target.checked);
      // aa[i].checked = e.target.checked;
    }
  }
};

function handleSubmit() {
  form.post(props.current?.id ? route('roles.update', props.current.id) : route('roles.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Role') }) : $t('Add {x}', { x: $t('Role') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('Role'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 px-6 pb-6">
      <!-- Name -->
      <div class="col-span-6 sm:col-span-3">
        <Input
          id="name"
          :label="$t('Name')"
          v-model="form.name"
          :error="form.errors.name"
          :readonly="['Customer', 'Supplier'].includes(current?.name)"
        />
      </div>

      <div class="col-span-6 mt-3 -mb-3 opacity-80 sm:col-span-3 xl:col-span-4">
        <CheckBox id="select-all" :label="$t('Check/Uncheck All')" @input="toggleAll" />
      </div>

      <!-- Permissions -->
      <div class="col-span-full -m-2 overflow-x-auto p-2">
        <table class="w-full">
          <tbody>
            <tr>
              <td class="pe-6 opacity-80">
                <div class="me-6">
                  <CheckBox id="vall" @input="e => toggleAll(e, 'read')" :label="$t('View All')" />
                </div>
              </td>
              <td class="pe-6 opacity-80">
                <div class="me-6">
                  <CheckBox id="call" @input="e => toggleAll(e, 'create')" :label="$t('Create All')" />
                </div>
              </td>
              <td class="pe-6 opacity-80">
                <div class="me-6">
                  <CheckBox id="uall" @input="e => toggleAll(e, 'update')" :label="$t('Update All')" />
                </div>
              </td>
              <td class="pe-6 opacity-80">
                <div class="me-6">
                  <CheckBox id="dall" @input="e => toggleAll(e, 'delete')" :label="$t('Delete All')" />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Sales') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  id="read-sales"
                  value="read-sales"
                  :label="$t('View')"
                  @input="updatePermissions('read-sales')"
                  :checked="form.permissions.includes('read-sales')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-sales"
                  value="create-sales"
                  :label="$t('Create')"
                  @input="updatePermissions('create-sales')"
                  :checked="form.permissions.includes('create-sales')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-sales"
                  value="update-sales"
                  :label="$t('Update')"
                  @input="updatePermissions('update-sales')"
                  :checked="form.permissions.includes('update-sales')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-sales"
                  value="delete-sales"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-sales')"
                  :checked="form.permissions.includes('delete-sales')"
                />
              </td>
            </tr>
            <tr v-if="$page.props.pos_module">
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="read-pos"
                    value="read-pos"
                    :label="$t('POS')"
                    @input="updatePermissions('read-pos')"
                    :checked="form.permissions.includes('read-pos')"
                  />
                  <CheckBox
                    id="read-orders"
                    value="read-orders"
                    :label="$t('Orders')"
                    @input="updatePermissions('read-orders')"
                    :checked="form.permissions.includes('read-orders')"
                  />
                  <CheckBox
                    id="delete-order-item"
                    value="delete-order-item"
                    :label="$t('Can remove order items')"
                    @input="updatePermissions('delete-order-item')"
                    :checked="form.permissions.includes('delete-order-item')"
                  />
                  <CheckBox
                    id="delete-orders"
                    value="delete-orders"
                    @input="updatePermissions('delete-orders')"
                    :label="$t('Delete {x}', { x: $t('Orders') })"
                    :checked="form.permissions.includes('delete-orders')"
                  />
                </div>
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="email-sales"
                    value="email-sales"
                    @input="updatePermissions('email-sales')"
                    :label="$t('Email {x}', { x: $t('Sales') })"
                    :checked="form.permissions.includes('email-sales')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Deliveries') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-deliveries"
                  value="read-deliveries"
                  @input="updatePermissions('read-deliveries')"
                  :checked="form.permissions.includes('read-deliveries')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-deliveries"
                  value="create-deliveries"
                  :label="$t('Create')"
                  @input="updatePermissions('create-deliveries')"
                  :checked="form.permissions.includes('create-deliveries')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-deliveries"
                  value="update-deliveries"
                  :label="$t('Update')"
                  @input="updatePermissions('update-deliveries')"
                  :checked="form.permissions.includes('update-deliveries')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-deliveries"
                  value="delete-deliveries"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-deliveries')"
                  :checked="form.permissions.includes('delete-deliveries')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Customers') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-customers"
                  value="read-customers"
                  @input="updatePermissions('read-customers')"
                  :checked="form.permissions.includes('read-customers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-customers"
                  value="create-customers"
                  :label="$t('Create')"
                  @input="updatePermissions('create-customers')"
                  :checked="form.permissions.includes('create-customers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-customers"
                  value="update-customers"
                  :label="$t('Update')"
                  @input="updatePermissions('update-customers')"
                  :checked="form.permissions.includes('update-customers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-customers"
                  value="delete-customers"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-customers')"
                  :checked="form.permissions.includes('delete-customers')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <!-- <CheckBox
                    id="statement-customers"
                    value="statement-customers"
                    :label="$t('Customer Statement')"
                    @input="updatePermissions('statement-customers')"
                    :checked="form.permissions.includes('statement-customers')"
                  /> -->
                  <CheckBox
                    id="export-customers"
                    value="export-customers"
                    @input="updatePermissions('export-customers')"
                    :label="$t('Export {x}', { x: $t('Customers') })"
                    :checked="form.permissions.includes('export-customers')"
                  />
                  <CheckBox
                    id="import-customers"
                    value="import-customers"
                    @input="updatePermissions('import-customers')"
                    :label="$t('Import {x}', { x: $t('Customers') })"
                    :checked="form.permissions.includes('import-customers')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Purchases') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-purchases"
                  value="read-purchases"
                  @input="updatePermissions('read-purchases')"
                  :checked="form.permissions.includes('read-purchases')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-purchases"
                  value="create-purchases"
                  :label="$t('Create')"
                  @input="updatePermissions('create-purchases')"
                  :checked="form.permissions.includes('create-purchases')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-purchases"
                  value="update-purchases"
                  :label="$t('Update')"
                  @input="updatePermissions('update-purchases')"
                  :checked="form.permissions.includes('update-purchases')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-purchases"
                  value="delete-purchases"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-purchases')"
                  :checked="form.permissions.includes('delete-purchases')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="email-purchases"
                    value="email-purchases"
                    @input="updatePermissions('email-purchases')"
                    :label="$t('Email {x}', { x: $t('Purchases') })"
                    :checked="form.permissions.includes('email-purchases')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Suppliers') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-suppliers"
                  value="read-suppliers"
                  @input="updatePermissions('read-suppliers')"
                  :checked="form.permissions.includes('read-suppliers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-suppliers"
                  value="create-suppliers"
                  :label="$t('Create')"
                  @input="updatePermissions('create-suppliers')"
                  :checked="form.permissions.includes('create-suppliers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-suppliers"
                  value="update-suppliers"
                  :label="$t('Update')"
                  @input="updatePermissions('update-suppliers')"
                  :checked="form.permissions.includes('update-suppliers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-suppliers"
                  value="delete-suppliers"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-suppliers')"
                  :checked="form.permissions.includes('delete-suppliers')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <!-- <CheckBox
                    id="statement-suppliers"
                    value="statement-suppliers"
                    :label="$t('Supplier Statement')"
                    @input="updatePermissions('statement-suppliers')"
                    :checked="form.permissions.includes('statement-suppliers')"
                  /> -->
                  <CheckBox
                    id="export-suppliers"
                    value="export-suppliers"
                    @input="updatePermissions('export-suppliers')"
                    :label="$t('Export {x}', { x: $t('Suppliers') })"
                    :checked="form.permissions.includes('export-suppliers')"
                  />
                  <CheckBox
                    id="import-suppliers"
                    value="import-suppliers"
                    @input="updatePermissions('import-suppliers')"
                    :label="$t('Import {x}', { x: $t('Suppliers') })"
                    :checked="form.permissions.includes('import-suppliers')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Payments') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-payments"
                  value="read-payments"
                  @input="updatePermissions('read-payments')"
                  :checked="form.permissions.includes('read-payments')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-payments"
                  value="create-payments"
                  :label="$t('Create')"
                  @input="updatePermissions('create-payments')"
                  :checked="form.permissions.includes('create-payments')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-payments"
                  value="update-payments"
                  :label="$t('Update')"
                  @input="updatePermissions('update-payments')"
                  :checked="form.permissions.includes('update-payments')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-payments"
                  value="delete-payments"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-payments')"
                  :checked="form.permissions.includes('delete-payments')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="email-payments"
                    value="email-payments"
                    @input="updatePermissions('email-payments')"
                    :label="$t('Email {x}', { x: $t('Payments') })"
                    :checked="form.permissions.includes('email-payments')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Quotations') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-quotations"
                  value="read-quotations"
                  @input="updatePermissions('read-quotations')"
                  :checked="form.permissions.includes('read-quotations')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-quotations"
                  value="create-quotations"
                  :label="$t('Create')"
                  @input="updatePermissions('create-quotations')"
                  :checked="form.permissions.includes('create-quotations')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-quotations"
                  value="update-quotations"
                  :label="$t('Update')"
                  @input="updatePermissions('update-quotations')"
                  :checked="form.permissions.includes('update-quotations')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-quotations"
                  value="delete-quotations"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-quotations')"
                  :checked="form.permissions.includes('delete-quotations')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="email-quotations"
                    value="email-quotations"
                    @input="updatePermissions('email-quotations')"
                    :label="$t('Email {x}', { x: $t('Quotations') })"
                    :checked="form.permissions.includes('email-quotations')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Expenses') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-expenses"
                  value="read-expenses"
                  @input="updatePermissions('read-expenses')"
                  :checked="form.permissions.includes('read-expenses')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-expenses"
                  value="create-expenses"
                  :label="$t('Create')"
                  @input="updatePermissions('create-expenses')"
                  :checked="form.permissions.includes('create-expenses')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-expenses"
                  value="update-expenses"
                  :label="$t('Update')"
                  @input="updatePermissions('update-expenses')"
                  :checked="form.permissions.includes('update-expenses')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-expenses"
                  value="delete-expenses"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-expenses')"
                  :checked="form.permissions.includes('delete-expenses')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Incomes') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-incomes"
                  value="read-incomes"
                  @input="updatePermissions('read-incomes')"
                  :checked="form.permissions.includes('read-incomes')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-incomes"
                  value="create-incomes"
                  :label="$t('Create')"
                  @input="updatePermissions('create-incomes')"
                  :checked="form.permissions.includes('create-incomes')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-incomes"
                  value="update-incomes"
                  :label="$t('Update')"
                  @input="updatePermissions('update-incomes')"
                  :checked="form.permissions.includes('update-incomes')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-incomes"
                  value="delete-incomes"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-incomes')"
                  :checked="form.permissions.includes('delete-incomes')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Return Orders') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-return-orders"
                  value="read-return-orders"
                  @input="updatePermissions('read-return-orders')"
                  :checked="form.permissions.includes('read-return-orders')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-return-orders"
                  value="create-return-orders"
                  :label="$t('Create')"
                  @input="updatePermissions('create-return-orders')"
                  :checked="form.permissions.includes('create-return-orders')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-return-orders"
                  value="update-return-orders"
                  :label="$t('Update')"
                  @input="updatePermissions('update-return-orders')"
                  :checked="form.permissions.includes('update-return-orders')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-return-orders"
                  value="delete-return-orders"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-return-orders')"
                  :checked="form.permissions.includes('delete-return-orders')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="email-return-order"
                    value="email-return-order"
                    @input="updatePermissions('email-return-order')"
                    :label="$t('Email {x}', { x: $t('Return Order') })"
                    :checked="form.permissions.includes('email-return-order')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Repair Orders') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-repair-orders"
                  value="read-repair-orders"
                  @input="updatePermissions('read-repair-orders')"
                  :checked="form.permissions.includes('read-repair-orders')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-repair-orders"
                  value="create-repair-orders"
                  :label="$t('Create')"
                  @input="updatePermissions('create-repair-orders')"
                  :checked="form.permissions.includes('create-repair-orders')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-repair-orders"
                  value="update-repair-orders"
                  :label="$t('Update')"
                  @input="updatePermissions('update-repair-orders')"
                  :checked="form.permissions.includes('update-repair-orders')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-repair-orders"
                  value="delete-repair-orders"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-repair-orders')"
                  :checked="form.permissions.includes('delete-repair-orders')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="email-repair-order"
                    value="email-repair-order"
                    @input="updatePermissions('email-repair-order')"
                    :label="$t('Email {x}', { x: $t('Repair Order') })"
                    :checked="form.permissions.includes('email-repair-order')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Technicians') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-technicians"
                  value="read-technicians"
                  @input="updatePermissions('read-technicians')"
                  :checked="form.permissions.includes('read-technicians')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-technicians"
                  value="create-technicians"
                  :label="$t('Create')"
                  @input="updatePermissions('create-technicians')"
                  :checked="form.permissions.includes('create-technicians')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-technicians"
                  value="update-technicians"
                  :label="$t('Update')"
                  @input="updatePermissions('update-technicians')"
                  :checked="form.permissions.includes('update-technicians')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-technicians"
                  value="delete-technicians"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-technicians')"
                  :checked="form.permissions.includes('delete-technicians')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Service Types') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-service-types"
                  value="read-service-types"
                  @input="updatePermissions('read-service-types')"
                  :checked="form.permissions.includes('read-service-types')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-service-types"
                  value="create-service-types"
                  :label="$t('Create')"
                  @input="updatePermissions('create-service-types')"
                  :checked="form.permissions.includes('create-service-types')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-service-types"
                  value="update-service-types"
                  :label="$t('Update')"
                  @input="updatePermissions('update-service-types')"
                  :checked="form.permissions.includes('update-service-types')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-service-types"
                  value="delete-service-types"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-service-types')"
                  :checked="form.permissions.includes('delete-service-types')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Gift Cards') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-gift-cards"
                  value="read-gift-cards"
                  @input="updatePermissions('read-gift-cards')"
                  :checked="form.permissions.includes('read-gift-cards')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-gift-cards"
                  value="create-gift-cards"
                  :label="$t('Create')"
                  @input="updatePermissions('create-gift-cards')"
                  :checked="form.permissions.includes('create-gift-cards')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-gift-cards"
                  value="update-gift-cards"
                  :label="$t('Update')"
                  @input="updatePermissions('update-gift-cards')"
                  :checked="form.permissions.includes('update-gift-cards')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-gift-cards"
                  value="delete-gift-cards"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-gift-cards')"
                  :checked="form.permissions.includes('delete-gift-cards')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Products') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-products"
                  value="read-products"
                  @input="updatePermissions('read-products')"
                  :checked="form.permissions.includes('read-products')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-products"
                  value="create-products"
                  :label="$t('Create')"
                  @input="updatePermissions('create-products')"
                  :checked="form.permissions.includes('create-products')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-products"
                  value="update-products"
                  :label="$t('Update')"
                  @input="updatePermissions('update-products')"
                  :checked="form.permissions.includes('update-products')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-products"
                  value="delete-products"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-products')"
                  :checked="form.permissions.includes('delete-products')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox id="show-cost" value="show-cost" :label="$t('Show Cost')" @input="updatePermissions('show-cost')" />
                  <CheckBox
                    id="read-labels"
                    value="read-labels"
                    @input="updatePermissions('read-labels')"
                    :checked="form.permissions.includes('read-labels')"
                    :label="$t('Print {x}', { x: $t('Barcode/Labels') })"
                  />
                  <CheckBox
                    id="export-products"
                    value="export-products"
                    @input="updatePermissions('export-products')"
                    :label="$t('Export {x}', { x: $t('Products') })"
                    :checked="form.permissions.includes('export-products')"
                  />
                  <CheckBox
                    id="import-products"
                    value="import-products"
                    @input="updatePermissions('import-products')"
                    :label="$t('Import {x}', { x: $t('Products') })"
                    :checked="form.permissions.includes('import-products')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Stock Counts') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-stock-counts"
                  value="read-stock-counts"
                  @input="updatePermissions('read-stock-counts')"
                  :checked="form.permissions.includes('read-stock-counts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-stock-counts"
                  value="create-stock-counts"
                  :label="$t('Initiate')"
                  @input="updatePermissions('create-stock-counts')"
                  :checked="form.permissions.includes('create-stock-counts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-stock-counts"
                  value="update-stock-counts"
                  :label="$t('Complete')"
                  @input="updatePermissions('update-stock-counts')"
                  :checked="form.permissions.includes('update-stock-counts')"
                />
              </td>
              <td class="pe-6">
                <!-- <CheckBox
                  id="delete-stock-counts"
                  value="delete-stock-counts"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-stock-counts')"
                  :checked="form.permissions.includes('delete-stock-counts')"
                /> -->
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Adjustments') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-adjustments"
                  value="read-adjustments"
                  @input="updatePermissions('read-adjustments')"
                  :checked="form.permissions.includes('read-adjustments')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-adjustments"
                  value="create-adjustments"
                  :label="$t('Create')"
                  @input="updatePermissions('create-adjustments')"
                  :checked="form.permissions.includes('create-adjustments')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-adjustments"
                  value="update-adjustments"
                  :label="$t('Update')"
                  @input="updatePermissions('update-adjustments')"
                  :checked="form.permissions.includes('update-adjustments')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-adjustments"
                  value="delete-adjustments"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-adjustments')"
                  :checked="form.permissions.includes('delete-adjustments')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Transfers') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-transfers"
                  value="read-transfers"
                  @input="updatePermissions('read-transfers')"
                  :checked="form.permissions.includes('read-transfers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-transfers"
                  value="create-transfers"
                  :label="$t('Create')"
                  @input="updatePermissions('create-transfers')"
                  :checked="form.permissions.includes('create-transfers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-transfers"
                  value="update-transfers"
                  :label="$t('Update')"
                  @input="updatePermissions('update-transfers')"
                  :checked="form.permissions.includes('update-transfers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-transfers"
                  value="delete-transfers"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-transfers')"
                  :checked="form.permissions.includes('delete-transfers')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Promotions') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-promotions"
                  value="read-promotions"
                  @input="updatePermissions('read-promotions')"
                  :checked="form.permissions.includes('read-promotions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-promotions"
                  value="create-promotions"
                  :label="$t('Create')"
                  @input="updatePermissions('create-promotions')"
                  :checked="form.permissions.includes('create-promotions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-promotions"
                  value="update-promotions"
                  :label="$t('Update')"
                  @input="updatePermissions('update-promotions')"
                  :checked="form.permissions.includes('update-promotions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-promotions"
                  value="delete-promotions"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-promotions')"
                  :checked="form.permissions.includes('delete-promotions')"
                />
              </td>
            </tr>

            <!-- <tr>
              <td colspan="5" class="pb-3 pt-8">
                <label class="block w-full font-bold">{{ $t('Calendar') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-calendar"
                  value="read-calendar"
                  @input="updatePermissions('read-calendar')"
                  :checked="form.permissions.includes('read-calendar')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-calendar"
                  value="create-calendar"
                  :label="$t('Create')"
                  @input="updatePermissions('create-calendar')"
                  :checked="form.permissions.includes('create-calendar')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-calendar"
                  value="update-calendar"
                  :label="$t('Update')"
                  @input="updatePermissions('update-calendar')"
                  :checked="form.permissions.includes('update-calendar')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-calendar"
                  value="delete-calendar"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-calendar')"
                  :checked="form.permissions.includes('delete-calendar')"
                />
              </td>
            </tr> -->

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Brands') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-brands"
                  value="read-brands"
                  @input="updatePermissions('read-brands')"
                  :checked="form.permissions.includes('read-brands')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-brands"
                  value="create-brands"
                  :label="$t('Create')"
                  @input="updatePermissions('create-brands')"
                  :checked="form.permissions.includes('create-brands')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-brands"
                  value="update-brands"
                  :label="$t('Update')"
                  @input="updatePermissions('update-brands')"
                  :checked="form.permissions.includes('update-brands')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-brands"
                  value="delete-brands"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-brands')"
                  :checked="form.permissions.includes('delete-brands')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="export-brands"
                    value="export-brands"
                    @input="updatePermissions('export-brands')"
                    :label="$t('Export {x}', { x: $t('Brands') })"
                    :checked="form.permissions.includes('export-brands')"
                  />
                  <CheckBox
                    id="import-brands"
                    value="import-brands"
                    @input="updatePermissions('import-brands')"
                    :label="$t('Import {x}', { x: $t('Brands') })"
                    :checked="form.permissions.includes('import-brands')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Categories') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-categories"
                  value="read-categories"
                  @input="updatePermissions('read-categories')"
                  :checked="form.permissions.includes('read-categories')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-categories"
                  value="create-categories"
                  :label="$t('Create')"
                  @input="updatePermissions('create-categories')"
                  :checked="form.permissions.includes('create-categories')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-categories"
                  value="update-categories"
                  :label="$t('Update')"
                  @input="updatePermissions('update-categories')"
                  :checked="form.permissions.includes('update-categories')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-categories"
                  value="delete-categories"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-categories')"
                  :checked="form.permissions.includes('delete-categories')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="export-categories"
                    value="export-categories"
                    @input="updatePermissions('export-categories')"
                    :label="$t('Export {x}', { x: $t('Categories') })"
                    :checked="form.permissions.includes('export-categories')"
                  />
                  <CheckBox
                    id="import-categories"
                    value="import-categories"
                    @input="updatePermissions('import-categories')"
                    :label="$t('Import {x}', { x: $t('Categories') })"
                    :checked="form.permissions.includes('import-categories')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Units') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-units"
                  value="read-units"
                  @input="updatePermissions('read-units')"
                  :checked="form.permissions.includes('read-units')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-units"
                  value="create-units"
                  :label="$t('Create')"
                  @input="updatePermissions('create-units')"
                  :checked="form.permissions.includes('create-units')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-units"
                  value="update-units"
                  :label="$t('Update')"
                  @input="updatePermissions('update-units')"
                  :checked="form.permissions.includes('update-units')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-units"
                  value="delete-units"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-units')"
                  :checked="form.permissions.includes('delete-units')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Restaurant') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View Halls')"
                  id="read-halls"
                  value="read-halls"
                  @input="updatePermissions('read-halls')"
                  :checked="form.permissions.includes('read-halls')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-halls"
                  value="create-halls"
                  :label="$t('Create Halls')"
                  @input="updatePermissions('create-halls')"
                  :checked="form.permissions.includes('create-halls')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-halls"
                  value="update-halls"
                  :label="$t('Update Halls')"
                  @input="updatePermissions('update-halls')"
                  :checked="form.permissions.includes('update-halls')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-halls"
                  value="delete-halls"
                  :label="$t('Delete Halls')"
                  @input="updatePermissions('delete-halls')"
                  :checked="form.permissions.includes('delete-halls')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View Tables')"
                  id="read-tables"
                  value="read-tables"
                  @input="updatePermissions('read-tables')"
                  :checked="form.permissions.includes('read-tables')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-tables"
                  value="create-tables"
                  :label="$t('Create Tables')"
                  @input="updatePermissions('create-tables')"
                  :checked="form.permissions.includes('create-tables')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-tables"
                  value="update-tables"
                  :label="$t('Update Tables')"
                  @input="updatePermissions('update-tables')"
                  :checked="form.permissions.includes('update-tables')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-tables"
                  value="delete-tables"
                  :label="$t('Delete Tables')"
                  @input="updatePermissions('delete-tables')"
                  :checked="form.permissions.includes('delete-tables')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Taxes') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-taxes"
                  value="read-taxes"
                  @input="updatePermissions('read-taxes')"
                  :checked="form.permissions.includes('read-taxes')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-taxes"
                  value="create-taxes"
                  :label="$t('Create')"
                  @input="updatePermissions('create-taxes')"
                  :checked="form.permissions.includes('create-taxes')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-taxes"
                  value="update-taxes"
                  :label="$t('Update')"
                  @input="updatePermissions('update-taxes')"
                  :checked="form.permissions.includes('update-taxes')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-taxes"
                  value="delete-taxes"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-taxes')"
                  :checked="form.permissions.includes('delete-taxes')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Accounts') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-accounts"
                  value="read-accounts"
                  @input="updatePermissions('read-accounts')"
                  :checked="form.permissions.includes('read-accounts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-accounts"
                  value="create-accounts"
                  :label="$t('Create')"
                  @input="updatePermissions('create-accounts')"
                  :checked="form.permissions.includes('create-accounts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-accounts"
                  value="update-accounts"
                  :label="$t('Update')"
                  @input="updatePermissions('update-accounts')"
                  :checked="form.permissions.includes('update-accounts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-accounts"
                  value="delete-accounts"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-accounts')"
                  :checked="form.permissions.includes('delete-accounts')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Account Transactions') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-account-transactions"
                  value="read-account-transactions"
                  @input="updatePermissions('read-account-transactions')"
                  :checked="form.permissions.includes('read-account-transactions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-account-transactions"
                  value="create-account-transactions"
                  :label="$t('Create')"
                  @input="updatePermissions('create-account-transactions')"
                  :checked="form.permissions.includes('create-account-transactions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-account-transactions"
                  value="update-account-transactions"
                  :label="$t('Update')"
                  @input="updatePermissions('update-account-transactions')"
                  :checked="form.permissions.includes('update-account-transactions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-account-transactions"
                  value="delete-account-transactions"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-account-transactions')"
                  :checked="form.permissions.includes('delete-account-transactions')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Stores') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-stores"
                  value="read-stores"
                  @input="updatePermissions('read-stores')"
                  :checked="form.permissions.includes('read-stores')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-stores"
                  value="create-stores"
                  :label="$t('Create')"
                  @input="updatePermissions('create-stores')"
                  :checked="form.permissions.includes('create-stores')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-stores"
                  value="update-stores"
                  :label="$t('Update')"
                  @input="updatePermissions('update-stores')"
                  :checked="form.permissions.includes('update-stores')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-stores"
                  value="delete-stores"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-stores')"
                  :checked="form.permissions.includes('delete-stores')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Custom Fields') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-custom-fields"
                  value="read-custom-fields"
                  @input="updatePermissions('read-custom-fields')"
                  :checked="form.permissions.includes('read-custom-fields')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-custom-fields"
                  value="create-custom-fields"
                  :label="$t('Create')"
                  @input="updatePermissions('create-custom-fields')"
                  :checked="form.permissions.includes('create-custom-fields')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-custom-fields"
                  value="update-custom-fields"
                  :label="$t('Update')"
                  @input="updatePermissions('update-custom-fields')"
                  :checked="form.permissions.includes('update-custom-fields')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-custom-fields"
                  value="delete-custom-fields"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-custom-fields')"
                  :checked="form.permissions.includes('delete-custom-fields')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Users') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-users"
                  value="read-users"
                  @input="updatePermissions('read-users')"
                  :checked="form.permissions.includes('read-users')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-users"
                  value="create-users"
                  :label="$t('Create')"
                  @input="updatePermissions('create-users')"
                  :checked="form.permissions.includes('create-users')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-users"
                  value="update-users"
                  :label="$t('Update')"
                  @input="updatePermissions('update-users')"
                  :checked="form.permissions.includes('update-users')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-users"
                  value="delete-users"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-users')"
                  :checked="form.permissions.includes('delete-users')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Roles') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-roles"
                  value="read-roles"
                  @input="updatePermissions('read-roles')"
                  :checked="form.permissions.includes('read-roles')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-roles"
                  value="create-roles"
                  :label="$t('Create')"
                  @input="updatePermissions('create-roles')"
                  :checked="form.permissions.includes('create-roles')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-roles"
                  value="update-roles"
                  :label="$t('Update')"
                  @input="updatePermissions('update-roles')"
                  :checked="form.permissions.includes('update-roles')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-roles"
                  value="delete-roles"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-roles')"
                  :checked="form.permissions.includes('delete-roles')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Customer Groups') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-customer-groups"
                  value="read-customer-groups"
                  @input="updatePermissions('read-customer-groups')"
                  :checked="form.permissions.includes('read-customer-groups')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-customer-groups"
                  value="create-customer-groups"
                  :label="$t('Create')"
                  @input="updatePermissions('create-customer-groups')"
                  :checked="form.permissions.includes('create-customer-groups')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-customer-groups"
                  value="update-customer-groups"
                  :label="$t('Update')"
                  @input="updatePermissions('update-customer-groups')"
                  :checked="form.permissions.includes('update-customer-groups')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-customer-groups"
                  value="delete-customer-groups"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-customer-groups')"
                  :checked="form.permissions.includes('delete-customer-groups')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Price Groups') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-price-groups"
                  value="read-price-groups"
                  @input="updatePermissions('read-price-groups')"
                  :checked="form.permissions.includes('read-price-groups')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-price-groups"
                  value="create-price-groups"
                  :label="$t('Create')"
                  @input="updatePermissions('create-price-groups')"
                  :checked="form.permissions.includes('create-price-groups')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-price-groups"
                  value="update-price-groups"
                  :label="$t('Update')"
                  @input="updatePermissions('update-price-groups')"
                  :checked="form.permissions.includes('update-price-groups')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-price-groups"
                  value="delete-price-groups"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-price-groups')"
                  :checked="form.permissions.includes('delete-price-groups')"
                />
              </td>
            </tr>

            <!-- ── Accounting Module ───────────────────────────────── -->
            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Accounts') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-accounts"
                  value="read-accounts"
                  @input="updatePermissions('read-accounts')"
                  :checked="form.permissions.includes('read-accounts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-accounts"
                  value="create-accounts"
                  :label="$t('Create')"
                  @input="updatePermissions('create-accounts')"
                  :checked="form.permissions.includes('create-accounts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-accounts"
                  value="update-accounts"
                  :label="$t('Update')"
                  @input="updatePermissions('update-accounts')"
                  :checked="form.permissions.includes('update-accounts')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-accounts"
                  value="delete-accounts"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-accounts')"
                  :checked="form.permissions.includes('delete-accounts')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Account Types') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-account-types"
                  value="read-account-types"
                  @input="updatePermissions('read-account-types')"
                  :checked="form.permissions.includes('read-account-types')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-account-types"
                  value="create-account-types"
                  :label="$t('Create')"
                  @input="updatePermissions('create-account-types')"
                  :checked="form.permissions.includes('create-account-types')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-account-types"
                  value="update-account-types"
                  :label="$t('Update')"
                  @input="updatePermissions('update-account-types')"
                  :checked="form.permissions.includes('update-account-types')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-account-types"
                  value="delete-account-types"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-account-types')"
                  :checked="form.permissions.includes('delete-account-types')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Account Transactions') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-account-transactions"
                  value="read-account-transactions"
                  @input="updatePermissions('read-account-transactions')"
                  :checked="form.permissions.includes('read-account-transactions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-account-transactions"
                  value="create-account-transactions"
                  :label="$t('Create')"
                  @input="updatePermissions('create-account-transactions')"
                  :checked="form.permissions.includes('create-account-transactions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-account-transactions"
                  value="update-account-transactions"
                  :label="$t('Update')"
                  @input="updatePermissions('update-account-transactions')"
                  :checked="form.permissions.includes('update-account-transactions')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-account-transactions"
                  value="delete-account-transactions"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-account-transactions')"
                  :checked="form.permissions.includes('delete-account-transactions')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Account Transfers') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-account-transfers"
                  value="read-account-transfers"
                  @input="updatePermissions('read-account-transfers')"
                  :checked="form.permissions.includes('read-account-transfers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-account-transfers"
                  value="create-account-transfers"
                  :label="$t('Create')"
                  @input="updatePermissions('create-account-transfers')"
                  :checked="form.permissions.includes('create-account-transfers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-account-transfers"
                  value="update-account-transfers"
                  :label="$t('Update')"
                  @input="updatePermissions('update-account-transfers')"
                  :checked="form.permissions.includes('update-account-transfers')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-account-transfers"
                  value="delete-account-transfers"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-account-transfers')"
                  :checked="form.permissions.includes('delete-account-transfers')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Assets') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-assets"
                  value="read-assets"
                  @input="updatePermissions('read-assets')"
                  :checked="form.permissions.includes('read-assets')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-assets"
                  value="create-assets"
                  :label="$t('Create')"
                  @input="updatePermissions('create-assets')"
                  :checked="form.permissions.includes('create-assets')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-assets"
                  value="update-assets"
                  :label="$t('Update')"
                  @input="updatePermissions('update-assets')"
                  :checked="form.permissions.includes('update-assets')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-assets"
                  value="delete-assets"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-assets')"
                  :checked="form.permissions.includes('delete-assets')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Asset Categories') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-asset-categories"
                  value="read-asset-categories"
                  @input="updatePermissions('read-asset-categories')"
                  :checked="form.permissions.includes('read-asset-categories')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-asset-categories"
                  value="create-asset-categories"
                  :label="$t('Create')"
                  @input="updatePermissions('create-asset-categories')"
                  :checked="form.permissions.includes('create-asset-categories')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-asset-categories"
                  value="update-asset-categories"
                  :label="$t('Update')"
                  @input="updatePermissions('update-asset-categories')"
                  :checked="form.permissions.includes('update-asset-categories')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-asset-categories"
                  value="delete-asset-categories"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-asset-categories')"
                  :checked="form.permissions.includes('delete-asset-categories')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Asset Allocations') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-asset-allocations"
                  value="read-asset-allocations"
                  @input="updatePermissions('read-asset-allocations')"
                  :checked="form.permissions.includes('read-asset-allocations')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-asset-allocations"
                  value="create-asset-allocations"
                  :label="$t('Create')"
                  @input="updatePermissions('create-asset-allocations')"
                  :checked="form.permissions.includes('create-asset-allocations')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-asset-allocations"
                  value="update-asset-allocations"
                  :label="$t('Update')"
                  @input="updatePermissions('update-asset-allocations')"
                  :checked="form.permissions.includes('update-asset-allocations')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-asset-allocations"
                  value="delete-asset-allocations"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-asset-allocations')"
                  :checked="form.permissions.includes('delete-asset-allocations')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Asset Maintenances') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-asset-maintenances"
                  value="read-asset-maintenances"
                  @input="updatePermissions('read-asset-maintenances')"
                  :checked="form.permissions.includes('read-asset-maintenances')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-asset-maintenances"
                  value="create-asset-maintenances"
                  :label="$t('Create')"
                  @input="updatePermissions('create-asset-maintenances')"
                  :checked="form.permissions.includes('create-asset-maintenances')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-asset-maintenances"
                  value="update-asset-maintenances"
                  :label="$t('Update')"
                  @input="updatePermissions('update-asset-maintenances')"
                  :checked="form.permissions.includes('update-asset-maintenances')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-asset-maintenances"
                  value="delete-asset-maintenances"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-asset-maintenances')"
                  :checked="form.permissions.includes('delete-asset-maintenances')"
                />
              </td>
            </tr>

            <!-- ── HR Module ───────────────────────────────────────── -->
            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Employees') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-employees"
                  value="read-employees"
                  @input="updatePermissions('read-employees')"
                  :checked="form.permissions.includes('read-employees')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-employees"
                  value="create-employees"
                  :label="$t('Create')"
                  @input="updatePermissions('create-employees')"
                  :checked="form.permissions.includes('create-employees')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-employees"
                  value="update-employees"
                  :label="$t('Update')"
                  @input="updatePermissions('update-employees')"
                  :checked="form.permissions.includes('update-employees')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-employees"
                  value="delete-employees"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-employees')"
                  :checked="form.permissions.includes('delete-employees')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Leaves') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-leaves"
                  value="read-leaves"
                  @input="updatePermissions('read-leaves')"
                  :checked="form.permissions.includes('read-leaves')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-leaves"
                  value="create-leaves"
                  :label="$t('Create')"
                  @input="updatePermissions('create-leaves')"
                  :checked="form.permissions.includes('create-leaves')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-leaves"
                  value="update-leaves"
                  :label="$t('Update')"
                  @input="updatePermissions('update-leaves')"
                  :checked="form.permissions.includes('update-leaves')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-leaves"
                  value="delete-leaves"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-leaves')"
                  :checked="form.permissions.includes('delete-leaves')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="approve-leaves"
                    value="approve-leaves"
                    @input="updatePermissions('approve-leaves')"
                    :label="$t('Approve {x}', { x: $t('Leaves') })"
                    :checked="form.permissions.includes('approve-leaves')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Attendances') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-attendances"
                  value="read-attendances"
                  @input="updatePermissions('read-attendances')"
                  :checked="form.permissions.includes('read-attendances')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-attendances"
                  value="create-attendances"
                  :label="$t('Create')"
                  @input="updatePermissions('create-attendances')"
                  :checked="form.permissions.includes('create-attendances')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-attendances"
                  value="update-attendances"
                  :label="$t('Update')"
                  @input="updatePermissions('update-attendances')"
                  :checked="form.permissions.includes('update-attendances')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-attendances"
                  value="delete-attendances"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-attendances')"
                  :checked="form.permissions.includes('delete-attendances')"
                />
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Claims') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-claims"
                  value="read-claims"
                  @input="updatePermissions('read-claims')"
                  :checked="form.permissions.includes('read-claims')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-claims"
                  value="create-claims"
                  :label="$t('Create')"
                  @input="updatePermissions('create-claims')"
                  :checked="form.permissions.includes('create-claims')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-claims"
                  value="update-claims"
                  :label="$t('Update')"
                  @input="updatePermissions('update-claims')"
                  :checked="form.permissions.includes('update-claims')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-claims"
                  value="delete-claims"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-claims')"
                  :checked="form.permissions.includes('delete-claims')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="approve-claims"
                    value="approve-claims"
                    @input="updatePermissions('approve-claims')"
                    :label="$t('Approve {x}', { x: $t('Claims') })"
                    :checked="form.permissions.includes('approve-claims')"
                  />
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="5" class="pt-8 pb-3">
                <label class="block w-full font-bold">{{ $t('Payrolls') }}</label>
              </td>
            </tr>
            <tr>
              <td class="pe-6">
                <CheckBox
                  :label="$t('View')"
                  id="read-payrolls"
                  value="read-payrolls"
                  @input="updatePermissions('read-payrolls')"
                  :checked="form.permissions.includes('read-payrolls')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="create-payrolls"
                  value="create-payrolls"
                  :label="$t('Create')"
                  @input="updatePermissions('create-payrolls')"
                  :checked="form.permissions.includes('create-payrolls')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="update-payrolls"
                  value="update-payrolls"
                  :label="$t('Update')"
                  @input="updatePermissions('update-payrolls')"
                  :checked="form.permissions.includes('update-payrolls')"
                />
              </td>
              <td class="pe-6">
                <CheckBox
                  id="delete-payrolls"
                  value="delete-payrolls"
                  :label="$t('Delete')"
                  @input="updatePermissions('delete-payrolls')"
                  :checked="form.permissions.includes('delete-payrolls')"
                />
              </td>
            </tr>
            <tr>
              <td class="pe-6 pt-3" colspan="100%">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                  <CheckBox
                    id="mark-paid-payrolls"
                    value="mark-paid-payrolls"
                    @input="updatePermissions('mark-paid-payrolls')"
                    :label="$t('Mark {x} as Paid', { x: $t('Payrolls') })"
                    :checked="form.permissions.includes('mark-paid-payrolls')"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
          <label class="block w-full font-bold">{{ $t('Settings') }}</label>
          <CheckBox
            value="settings"
            :label="$t('Settings')"
            id="settings-permissions"
            @input="updatePermissions('settings')"
            :checked="form.permissions.includes('settings')"
          />
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
          <label class="block w-full font-bold">{{ $t('Others') }}</label>
          <CheckBox
            id="see-dashboard"
            value="see-dashboard"
            :label="$t('Dashboard')"
            @input="updatePermissions('see-dashboard')"
            :checked="form.permissions.includes('see-dashboard')"
          />
          <CheckBox
            id="read-all"
            value="read-all"
            :label="$t('Read all records')"
            @input="updatePermissions('read-all')"
            :checked="form.permissions.includes('read-all')"
          />
          <CheckBox
            id="update-all"
            value="update-all"
            :label="$t('Update all records')"
            @input="updatePermissions('update-all')"
            :checked="form.permissions.includes('update-all')"
          />
          <!-- <CheckBox
            id="bulk-action"
            value="bulk-action"
            :label="$t('Perform bulk actions')"
            @input="updatePermissions('bulk-action')"
            :checked="form.permissions.includes('bulk-action')"
          /> -->
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
          <label class="block w-full font-bold">{{ $t('Reports') }}</label>
          <!-- <CheckBox
          :label="$t('View')"
                  id="read-reports"
                  value="read-reports"
                  @input="updatePermissions('read-reports')"
                  :checked="form.permissions.includes('read-reports')"
                /> -->
          <CheckBox
            id="sales-report"
            value="sales-report"
            @input="updatePermissions('sales-report')"
            :label="$t('{x} Report', { x: $t('Sales') })"
            :checked="form.permissions.includes('sales-report')"
          />
          <CheckBox
            id="profit_loss-report"
            value="profit_loss-report"
            @input="updatePermissions('profit_loss-report')"
            :label="$t('{x} Report', { x: $t('Profit & Loss') })"
            :checked="form.permissions.includes('profit_loss-report')"
          />
          <CheckBox
            id="inventory_accounting-report"
            value="inventory_accounting-report"
            @input="updatePermissions('inventory_accounting-report')"
            :label="$t('{x} Report', { x: $t('Inventory Accounting') })"
            :checked="form.permissions.includes('inventory_accounting-report')"
          />
          <CheckBox
            id="purchases-report"
            value="purchases-report"
            @input="updatePermissions('purchases-report')"
            :label="$t('{x} Report', { x: $t('Purchases') })"
            :checked="form.permissions.includes('purchases-report')"
          />
          <CheckBox
            id="registers-report"
            value="registers-report"
            v-if="$page.props.pos_module"
            @input="updatePermissions('registers-report')"
            :label="$t('{x} Report', { x: $t('Registers') })"
            :checked="form.permissions.includes('registers-report')"
          />
          <CheckBox
            id="payments-report"
            value="payments-report"
            @input="updatePermissions('payments-report')"
            :label="$t('{x} Report', { x: $t('Payments') })"
            :checked="form.permissions.includes('payments-report')"
          />
          <CheckBox
            id="expenses-report"
            value="expenses-report"
            @input="updatePermissions('expenses-report')"
            :label="$t('{x} Report', { x: $t('Expenses') })"
            :checked="form.permissions.includes('expenses-report')"
          />
          <CheckBox
            id="return-orders-report"
            value="return-orders-report"
            @input="updatePermissions('return-orders-report')"
            :label="$t('{x} Report', { x: $t('Return Orders') })"
            :checked="form.permissions.includes('return-orders-report')"
          />
          <CheckBox
            id="products-report"
            value="products-report"
            @input="updatePermissions('products-report')"
            :label="$t('{x} Report', { x: $t('Products') })"
            :checked="form.permissions.includes('products-report')"
          />
          <CheckBox
            id="categories-report"
            value="categories-report"
            @input="updatePermissions('categories-report')"
            :label="$t('{x} Report', { x: $t('Categories') })"
            :checked="form.permissions.includes('categories-report')"
          />
          <CheckBox
            id="adjustments-report"
            value="adjustments-report"
            @input="updatePermissions('adjustments-report')"
            :label="$t('{x} Report', { x: $t('Adjustments') })"
            :checked="form.permissions.includes('adjustments-report')"
          />
          <CheckBox
            id="transfers-report"
            value="transfers-report"
            @input="updatePermissions('transfers-report')"
            :label="$t('{x} Report', { x: $t('Transfers') })"
            :checked="form.permissions.includes('transfers-report')"
          />
          <CheckBox
            id="customers-report"
            value="customers-report"
            @input="updatePermissions('customers-report')"
            :label="$t('{x} Report', { x: $t('Customers') })"
            :checked="form.permissions.includes('customers-report')"
          />
          <CheckBox
            id="staff-report"
            value="staff-report"
            @input="updatePermissions('staff-report')"
            :label="$t('{x} Report', { x: $t('Staff') })"
            :checked="form.permissions.includes('staff-report')"
          />
          <CheckBox
            id="activities-report"
            value="activities-report"
            @input="updatePermissions('activities-report')"
            :label="$t('{x} Report', { x: $t('Activities') })"
            :checked="form.permissions.includes('activities-report')"
          />
        </div>
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
