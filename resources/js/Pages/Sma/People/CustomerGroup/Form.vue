<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $decimal } from '@/Core/helpers';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'price_groups']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  details: props.current?.details,
  discount: $decimal(props.current?.discount),
  price_group_id: props.current?.price_group_id,
  apply_as_discount: true,
  //   apply_as_discount: props.current?.id ? props.current?.apply_as_discount == 1 : true,
});

function handleSubmit() {
  form.post(props.current?.id ? route('customer_groups.update', props.current.id) : route('customer_groups.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Customer Group') }) : $t('Add {x}', { x: $t('Customer Group') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('Customer group'),
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

      <!-- Discount -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="discount" :label="$t('Discount')" v-model="form.discount" :error="form.errors.discount" />
      </div>

      <!-- Price Group -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :searchable="false"
          id="price_group_id"
          :suggestions="price_groups"
          v-model="form.price_group_id"
          :label="$t('Customer Group Price Group')"
          :error="form.errors.price_group_id"
        />
      </div>

      <!-- Details -->
      <div class="col-span-full">
        <Textarea id="details" :label="$t('Details')" v-model="form.details" :error="form.errors.details" />
      </div>

      <!-- Apply as discount -->
      <!-- <div class="col-span-full">
        <CheckBox
          id="apply_as_discount"
          :error="form.errors.apply_as_discount"
          v-model:checked="form.apply_as_discount"
          :label="$t('Apply as discount')"
        />
      </div> -->
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="emits('close')"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
