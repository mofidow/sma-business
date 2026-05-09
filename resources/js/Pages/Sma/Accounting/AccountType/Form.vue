<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  code: props.current?.code,
  description: props.current?.description,
  active: props.current?.id ? props.current?.active == 1 : true,
});

function handleSubmit() {
  form.post(props.current?.id ? route('account-types.update', props.current.id) : route('account-types.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Account Type') }) : $t('Add {x}', { x: $t('Account Type') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('account type'),
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
