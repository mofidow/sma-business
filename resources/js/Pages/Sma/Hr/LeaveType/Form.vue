<script setup>
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { Input, LoadingButton, Textarea, Toggle } from '@/Components/Common';

const props = defineProps(['current']);
const emit = defineEmits(['close', 'done']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  name: props.current?.name || '',
  days_per_year: props.current?.days_per_year ?? 0,
  is_paid: props.current?.is_paid ?? true,
  carry_forward: props.current?.carry_forward ?? false,
  description: props.current?.description || '',
});

function handleSubmit() {
  form.post(props.current?.id ? route('leave-types.update', props.current.id) : route('leave-types.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Leave Type') }) : $t('Add {x}', { x: $t('Leave Type') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('leave type'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-4">
        <Input :label="$t('Name')" :required="true" v-model="form.name" :error="form.errors.name" />
      </div>

      <div class="col-span-6 sm:col-span-2">
        <Input
          type="number"
          :label="$t('Days Per Year')"
          :required="true"
          v-model="form.days_per_year"
          :error="form.errors.days_per_year"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Toggle :label="$t('Paid Leave')" v-model="form.is_paid" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Toggle :label="$t('Carry Forward')" v-model="form.carry_forward" />
      </div>

      <div class="col-span-6">
        <Textarea rows="2" :label="$t('Description')" v-model="form.description" :error="form.errors.description" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="$emit('close')"> {{ $t('Cancel') }} </SecondaryButton>
      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
