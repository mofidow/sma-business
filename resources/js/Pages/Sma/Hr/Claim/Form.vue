<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, DateInput, Input, LoadingButton, NumberInput, Textarea } from '@/Components/Common';

const props = defineProps(['current', 'stores', 'employees']);
const emit = defineEmits(['close', 'done']);

const employeeOptions = computed(() => props.employees.map(e => ({ value: e.id, label: e.user?.name || `#${e.id}` })));

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  employee_id: props.current?.employee_id || null,
  store_id: props.current?.store_id || null,
  date: props.current?.date || new Date().toISOString().slice(0, 10),
  title: props.current?.title || '',
  description: props.current?.description || '',
  amount: props.current?.amount ?? 0,
  notes: props.current?.notes || '',
});

function handleSubmit() {
  form.post(props.current?.id ? route('claims.update', props.current.id) : route('claims.store'), {
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
      <h1 class="text-focus text-base font-semibold">
        {{ current?.id ? $t('Edit {x}', { x: $t('Claim') }) : $t('Add {x}', { x: $t('Claim') }) }}
      </h1>
      <p class="text-mute mt-1 truncate text-sm">
        {{
          $t('Please fill the form below to {action} {record}.', {
            record: $t('claim'),
            action: current?.id ? $t('edit') : $t('add'),
          })
        }}
      </p>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-4">
        <AutoComplete
          :json="true"
          :label="$t('Employee')"
          v-model="form.employee_id"
          :suggestions="employeeOptions"
          :error="form.errors.employee_id"
          :placeholder="$t('Select employee')"
        />
      </div>

      <div class="col-span-6 sm:col-span-2">
        <DateInput type="date" :label="$t('Date')" :required="true" v-model="form.date" :error="form.errors.date" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <AutoComplete
          :json="true"
          :label="$t('Store')"
          :suggestions="stores"
          v-model="form.store_id"
          :error="form.errors.store_id"
          :placeholder="$t('Select store')"
        />
      </div>

      <div class="col-span-6 sm:col-span-2">
        <Input type="number" :label="$t('Amount')" v-model="form.amount" :error="form.errors.amount" />
      </div>

      <div class="col-span-6">
        <Input type="text" :label="$t('Title')" :required="true" v-model="form.title" :error="form.errors.title" />
      </div>

      <div class="col-span-6">
        <Textarea rows="2" :label="$t('Description')" v-model="form.description" :error="form.errors.description" />
      </div>

      <div class="col-span-6">
        <Textarea rows="2" :label="$t('Notes')" v-model="form.notes" :error="form.errors.notes" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="$emit('close')">{{ $t('Cancel') }}</SecondaryButton>
      <LoadingButton class="ms-3" :loading="form.processing">{{ $t('Save') }}</LoadingButton>
    </div>
  </form>
</template>
