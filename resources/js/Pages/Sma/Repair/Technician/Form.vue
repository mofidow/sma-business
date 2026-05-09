<script setup>
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { Input, LoadingButton, Textarea, Toggle } from '@/Components/Common';

const props = defineProps(['current']);
const emit = defineEmits(['close', 'done']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  name: props.current?.name || '',
  email: props.current?.email || '',
  phone: props.current?.phone || '',
  skills: props.current?.skills || '',
  description: props.current?.description || '',
  active: props.current?.active ?? true,
});

function handleSubmit() {
  const method = props.current?.id ? 'put' : 'post';
  const url = props.current?.id ? route('technicians.update', { technician: props.current.id }) : route('technicians.store');

  form
    .transform(data => ({
      ...data,
      active: data.active ? 1 : 0,
    }))
    [method](url, {
      preserveScroll: true,
      onSuccess: p => {
        emit('done', p.props.flash?.data);
        emit('close');
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Technician') }) : $t('Add {x}', { x: $t('Technician') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('technician'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6">
        <Input :label="$t('Name')" :required="true" v-model="form.name" :error="form.errors.name" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="email" :label="$t('Email')" v-model="form.email" :error="form.errors.email" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input :label="$t('Phone')" v-model="form.phone" :error="form.errors.phone" />
      </div>

      <div class="col-span-6">
        <Textarea rows="2" :label="$t('Skills')" v-model="form.skills" :error="form.errors.skills" :placeholder="$t('Skills (optional)')" />
      </div>

      <div class="col-span-6">
        <Textarea
          rows="3"
          :label="$t('Description')"
          v-model="form.description"
          :error="form.errors.description"
          :placeholder="$t('Description (optional)')"
        />
      </div>

      <div class="col-span-full">
        <Toggle :label="$t('Active')" v-model="form.active" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
