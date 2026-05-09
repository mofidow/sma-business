<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $decimal } from '@/Core/helpers';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'assets', 'types', 'statuses']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  asset_id: props.current?.asset_id,
  title: props.current?.title,
  type: props.current?.type ?? 'Scheduled',
  start_date: props.current?.start_date ? dayjs(props.current?.start_date).format('YYYY-MM-DD') : null,
  end_date: props.current?.end_date ? dayjs(props.current?.end_date).format('YYYY-MM-DD') : null,
  cost: $decimal(props.current?.cost),
  note: props.current?.note,
  status: props.current?.status ?? 'Scheduled',
});

function handleSubmit() {
  form.post(props.current?.id ? route('asset-maintenances.update', props.current.id) : route('asset-maintenances.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Asset Maintenance') }) : $t('Add {x}', { x: $t('Asset Maintenance') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('asset maintenance'),
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
        <Input id="title" :label="$t('Title')" v-model="form.title" :error="form.errors.title" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="type"
          :searchable="false"
          :label="$t('Type')"
          v-model="form.type"
          :error="form.errors.type"
          :suggestions="types"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="status"
          :searchable="false"
          :label="$t('Status')"
          v-model="form.status"
          :error="form.errors.status"
          :suggestions="statuses"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="date" id="start_date" :label="$t('Start Date')" v-model="form.start_date" :error="form.errors.start_date" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="date" id="end_date" :label="$t('End Date')" v-model="form.end_date" :error="form.errors.end_date" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="number" id="cost" :label="$t('Cost')" v-model="form.cost" :error="form.errors.cost" />
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
