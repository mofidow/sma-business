<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $decimal } from '@/Core/helpers';
import { SecondaryButton } from '@/Components/Jet';
import { CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'types']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  code: props.current?.code,
  number: props.current?.number,
  rate: props.current?.rate ? $decimal(props.current.rate) : null,
  details: props.current?.details,
  compound: props.current?.compound == 1,
  recoverable: props.current?.recoverable == 1,
  state: props.current?.state == 1,
  same: props.current?.same == 1,
});

function handleSubmit() {
  form.post(props.current?.id ? route('taxes.update', props.current.id) : route('taxes.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Tax') }) : $t('Add {x}', { x: $t('Tax') }) }}
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

      <!-- Code -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="code" :label="$t('Code')" v-model="form.code" :error="form.errors.code" />
      </div>

      <!-- Number -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="number" :label="$t('Number')" v-model="form.number" :error="form.errors.number" />
      </div>

      <!-- Rate -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="rate" type="number" :label="$t('Rate')" v-model="form.rate" :error="form.errors.rate" />
      </div>

      <!-- Details -->
      <div class="col-span-full">
        <Textarea id="details" :label="$t('Details')" v-model="form.details" :error="form.errors.details" />
      </div>

      <!-- State -->
      <div class="col-span-full">
        <CheckBox id="state" :error="form.errors.state" v-model:checked="form.state" :label="$t('This is state tax')" />
      </div>
      <template v-if="form.state">
        <!-- Same State -->
        <div class="col-span-full">
          <CheckBox id="same" :error="form.errors.same" v-model:checked="form.same" :label="$t('Apply to same state customers')" />
        </div>
      </template>

      <!-- Offline -->
      <div class="col-span-full">
        <CheckBox
          id="recoverable"
          :error="form.errors.recoverable"
          v-model:checked="form.recoverable"
          :label="$t('This tax is recoverable')"
        />
      </div>

      <!-- Compound -->
      <div class="col-span-full">
        <CheckBox
          id="compound"
          :error="form.errors.compound"
          v-model:checked="form.compound"
          :label="$t('Compound: this tax should be applied after other taxes')"
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
