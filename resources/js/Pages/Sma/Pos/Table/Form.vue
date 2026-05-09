<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Toggle, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'halls']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  hall_id: props.current?.hall_id,
  name: props.current?.name,
  seats: props.current?.seats ?? 4,
  description: props.current?.description,
  sort_order: props.current?.sort_order ?? 0,
  active: props.current?.id ? props.current?.active == 1 : true,
});

const close = () => {
  form.reset();
  emits('close');
};

function handleSubmit() {
  form.post(props.current?.id ? route('pos.tables.update', props.current.id) : route('pos.tables.store'), {
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
    <div class="flex items-center justify-between border-b px-6 py-3 dark:border-gray-700">
      <h1 class="text-xl font-semibold">
        {{ current?.id ? $t('Edit {x}', { x: $t('Table') }) : $t('Add {x}', { x: $t('Table') }) }}
      </h1>
    </div>
    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-3">
        <Input id="name" v-model="form.name" :error="form.errors.name" :label="$t('Table Name')" />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          id="hall_id"
          :json="true"
          valueKey="id"
          labelKey="name"
          :label="$t('Hall')"
          :suggestions="halls"
          v-model="form.hall_id"
          :error="form.errors.hall_id"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input id="seats" type="number" v-model="form.seats" :error="form.errors.seats" :label="$t('Number of Seats')" />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <Input id="sort_order" type="number" v-model="form.sort_order" :error="form.errors.sort_order" :label="$t('Sort Order')" />
      </div>

      <div class="col-span-full">
        <Textarea id="description" rows="3" v-model="form.description" :error="form.errors.description" :label="$t('Description')" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Toggle id="active" :label="$t('Active')" v-model="form.active" />
      </div>
    </div>
    <div class="flex justify-end gap-2 border-t bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-950">
      <SecondaryButton type="button" @click="close"> {{ $t('Cancel') }} </SecondaryButton>
      <LoadingButton type="submit" :loading="form.processing"> {{ $t('Save') }} </LoadingButton>
    </div>
  </form>
</template>
