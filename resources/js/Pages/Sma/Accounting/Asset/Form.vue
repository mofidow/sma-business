<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $decimal } from '@/Core/helpers';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'categories', 'conditions']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  code: props.current?.code,
  asset_category_id: props.current?.asset_category_id,
  serial_number: props.current?.serial_number,
  purchase_cost: $decimal(props.current?.purchase_cost),
  purchase_date: props.current?.purchase_date ? dayjs(props.current?.purchase_date).format('YYYY-MM-DD') : null,
  warranty_expiry: props.current?.warranty_expiry ? dayjs(props.current?.warranty_expiry).format('YYYY-MM-DD') : null,
  condition: props.current?.condition ?? 'New',
  description: props.current?.description,
  active: props.current?.id ? props.current?.active == 1 : true,
});

function handleSubmit() {
  form.post(props.current?.id ? route('assets.update', props.current.id) : route('assets.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Asset') }) : $t('Add {x}', { x: $t('Asset') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('asset'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-3">
        <Input id="name" :label="$t('Name')" v-model="form.name" :error="form.errors.name" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input id="code" :label="$t('Code')" v-model="form.code" :error="form.errors.code" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="asset_category_id"
          :label="$t('Category')"
          v-model="form.asset_category_id"
          :error="form.errors.asset_category_id"
          :suggestions="categories.map(c => ({ value: c.id, label: c.name }))"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input id="serial_number" :label="$t('Serial Number')" v-model="form.serial_number" :error="form.errors.serial_number" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input
          type="number"
          id="purchase_cost"
          :label="$t('Purchase Cost')"
          v-model="form.purchase_cost"
          :error="form.errors.purchase_cost"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input
          type="date"
          id="purchase_date"
          :label="$t('Purchase Date')"
          v-model="form.purchase_date"
          :error="form.errors.purchase_date"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input
          type="date"
          id="warranty_expiry"
          :label="$t('Warranty Expiry')"
          v-model="form.warranty_expiry"
          :error="form.errors.warranty_expiry"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="condition"
          :searchable="false"
          :label="$t('Condition')"
          v-model="form.condition"
          :error="form.errors.condition"
          :suggestions="conditions"
        />
      </div>

      <div class="col-span-full">
        <Textarea id="description" :label="$t('Description')" v-model="form.description" :error="form.errors.description" />
      </div>

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
