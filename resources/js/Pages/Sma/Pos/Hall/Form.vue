<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { InputError, InputLabel, SecondaryButton } from '@/Components/Jet';
import { CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  description: props.current?.description,
  sort_order: props.current?.sort_order ?? 0,
  active: props.current?.id ? props.current?.active == 1 : true,
});

const close = () => {
  form.reset();
  emits('close');
};

function handleSubmit() {
  form.post(props.current?.id ? route('pos.halls.update', props.current.id) : route('pos.halls.store'), {
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
        {{ current?.id ? $t('Edit {x}', { x: $t('Hall') }) : $t('Add {x}', { x: $t('Hall') }) }}
      </h1>
    </div>
    <div class="max-h-[70vh] space-y-6 overflow-y-auto p-6">
      <div>
        <InputLabel for="name" :value="$t('Hall Name')" />
        <Input id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div>
        <InputLabel for="description" :value="$t('Description')" />
        <Textarea
          id="description"
          class="mt-1 block w-full"
          v-model="form.description"
          :placeholder="$t('Optional description for this hall')"
        />
        <InputError class="mt-2" :message="form.errors.description" />
      </div>

      <div>
        <InputLabel for="sort_order" :value="$t('Sort Order')" />
        <Input id="sort_order" type="number" class="mt-1 block w-full" v-model="form.sort_order" min="0" autocomplete="sort_order" />
        <InputError class="mt-2" :message="form.errors.sort_order" />
      </div>

      <div>
        <label class="flex items-center">
          <CheckBox v-model:checked="form.active" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $t('Active') }}</span>
        </label>
        <InputError class="mt-2" :message="form.errors.active" />
      </div>
    </div>
    <div class="flex justify-end gap-2 border-t bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-950">
      <SecondaryButton type="button" @click="close"> {{ $t('Cancel') }} </SecondaryButton>
      <LoadingButton type="submit" :loading="form.processing"> {{ $t('Save') }} </LoadingButton>
    </div>
  </form>
</template>
