<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'assets', 'users']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  asset_id: props.current?.asset_id,
  allocated_to: props.current?.allocated_to,
  allocated_date: props.current?.allocated_date ? dayjs(props.current?.allocated_date).format('YYYY-MM-DD') : null,
  return_date: props.current?.return_date ? dayjs(props.current?.return_date).format('YYYY-MM-DD') : null,
  note: props.current?.note,
});

function handleSubmit() {
  form.post(props.current?.id ? route('asset-allocations.update', props.current.id) : route('asset-allocations.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Asset Allocation') }) : $t('Add {x}', { x: $t('Asset Allocation') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('asset allocation'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="asset_id"
          :label="$t('Asset')"
          v-model="form.asset_id"
          :error="form.errors.asset_id"
          :suggestions="assets.map(a => ({ value: a.id, label: a.name }))"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="allocated_to"
          :label="$t('Allocated To')"
          v-model="form.allocated_to"
          :error="form.errors.allocated_to"
          :suggestions="users.map(u => ({ value: u.id, label: u.name }))"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input
          type="date"
          id="allocated_date"
          :label="$t('Allocated Date')"
          v-model="form.allocated_date"
          :error="form.errors.allocated_date"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="date" id="return_date" :label="$t('Return Date')" v-model="form.return_date" :error="form.errors.return_date" />
      </div>

      <div class="col-span-full">
        <Textarea id="note" :label="$t('Note')" v-model="form.note" :error="form.errors.note" />
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
