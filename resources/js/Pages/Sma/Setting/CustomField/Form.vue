<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { InputError, SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'models', 'types', 'custom_fields']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  type: props.current?.type,
  details: props.current?.details,
  order_no: props.current?.order_no,
  options: props.current?.options || [''],
  models: props.current?.models.map(m => m),
  is_required: props.current?.is_required == 1,
  show_on_details_view: props.current?.show_on_details_view == 1,
});

function handleSubmit() {
  form
    .transform(data => ({
      ...data,
      options: ['select', 'checkbox', 'radio'].includes(data.type) ? form.options.filter(o => o) : [],
    }))
    .post(props.current?.id ? route('custom_fields.update', props.current.id) : route('custom_fields.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Custom Field') }) : $t('Add {x}', { x: $t('Custom Field') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('tax'),
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

      <!-- Order Number -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="order_no" :label="$t('Order Number')" v-model="form.order_no" :error="form.errors.order_no" />
      </div>

      <!-- Models -->
      <div class="col-span-full">
        <AutoComplete
          id="models"
          :json="true"
          :multiple="true"
          :searchable="false"
          v-model="form.models"
          :label="$t('Models')"
          :error="form.errors.models"
          :suggestions="models.map(t => ({ value: t, label: $t($title(t.replace('_', ' '))) }))"
        />
      </div>

      <!-- Type -->
      <div class="col-span-full">
        <AutoComplete
          id="type"
          :json="true"
          :label="$t('Type')"
          :searchable="false"
          v-model="form.type"
          :error="form.errors.type"
          :suggestions="types.map(t => ({ value: t, label: $t($capitalize(t)) }))"
        />
      </div>
      <!-- For language only -->
      <span class="sr-only">
        {{ $t('Text') }}
        {{ $t('Select') }}
        {{ $t('Checkbox') }}
        {{ $t('Radio') }}
        {{ $t('Textarea') }}
        {{ $t('Date') }}
        {{ $t('Time') }}
        {{ $t('Number') }}
        {{ $t('Email') }}
        {{ $t('Url') }}
      </span>

      <template v-if="form.type == 'select' || form.type == 'checkbox' || form.type == 'radio'">
        <div
          class="col-span-full grid grid-cols-6 gap-6 rounded-md border p-4 md:p-6"
          :class="form.errors.options ? 'border-red-500 dark:border-red-500' : 'dark:border-gray-700'"
        >
          <div class="col-span-full flex items-center justify-start gap-x-4">
            <h4 class="font-bold">{{ $t('Options') }}</h4>
            <button type="button" @click="form.options.push('')" class="-m-2 rounded-md p-2 hover:bg-gray-100 dark:hover:bg-gray-800">
              <Icon name="plus" />
            </button>
          </div>
          <template v-for="(option, index) in form.options" :key="index">
            <div class="col-span-6 sm:col-span-3">
              <Input v-model="form.options[index]" :label="$t('Option') + ' ' + (index + 1)" />
            </div>
          </template>

          <div v-if="form.errors.options" class="col-span-full">
            <InputError :message="form.errors.options" />
          </div>
        </div>
      </template>

      <!-- Details -->
      <div class="col-span-full">
        <Textarea id="details" :label="$t('Details')" v-model="form.details" :error="form.errors.details" />
      </div>

      <!-- Is Request -->
      <div class="col-span-full">
        <CheckBox id="is_required" :error="form.errors.is_required" v-model:checked="form.is_required" :label="$t('Require this field')" />
      </div>

      <!-- Offline -->
      <div class="col-span-full -mt-3">
        <CheckBox
          id="show_on_details_view"
          :label="$t('Show on details view')"
          :error="form.errors.show_on_details_view"
          v-model:checked="form.show_on_details_view"
        />
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
