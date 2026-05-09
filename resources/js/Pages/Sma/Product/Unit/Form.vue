<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'parents']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  code: props.current?.code,
  unit_id: props.current?.unit_id,
  operator: props.current?.operator,
  operation_value: props.current?.operation_value,
  //   active: props.current?.id ? props.current?.active == 1 : true,
});

function handleSubmit() {
  form.post(props.current?.id ? route('units.update', props.current.id) : route('units.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Unit') }) : $t('Add {x}', { x: $t('Unit') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('unit'),
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
      <!-- Code -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="code" :label="$t('Code')" v-model="form.code" :error="form.errors.code" />
      </div>

      <!-- Base Unit -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="unit_id"
          :searchable="false"
          :suggestions="parents"
          v-model="form.unit_id"
          :label="$t('Base Unit')"
          :error="form.errors.unit_id"
        />
      </div>

      <template v-if="form.unit_id">
        <!-- Operator -->
        <div class="col-span-6 sm:col-span-3">
          <!-- <Input id="operator" :label="$t('Operator')" v-model="form.operator" :error="form.errors.operator" /> -->
          <AutoComplete
            :json="true"
            id="operator"
            :searchable="false"
            :label="$t('Operator')"
            v-model="form.operator"
            :error="form.errors.operator"
            :suggestions="[
              { value: '+', label: $t('Plus') + ' (+)' },
              { value: '-', label: $t('Minus') + ' (-)' },
              { value: '*', label: $t('Multiple') + ' (*)' },
              { value: '/', label: $t('Divide') + ' (/)' },
            ]"
          />
        </div>

        <!-- Operation Value -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="operation_value" :label="$t('Operation Value')" v-model="form.operation_value" :error="form.errors.operation_value" />
        </div>
      </template>
    </div>

    <div class="flex flex-row justify-end rounded-b-lg bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
