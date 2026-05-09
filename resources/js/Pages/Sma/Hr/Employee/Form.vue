<script setup>
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, DateInput, Input, LoadingButton, NumberInput, Textarea } from '@/Components/Common';

const props = defineProps(['current', 'stores', 'users']);
const emit = defineEmits(['close', 'done']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  user_id: props.current?.user_id || null,
  store_id: props.current?.store_id || null,
  department: props.current?.department || '',
  job_title: props.current?.job_title || '',
  hire_date: props.current?.hire_date || '',
  basic_salary: props.current?.basic_salary ?? 0,
  working_days_per_month: props.current?.working_days_per_month ?? 26,
  working_hours_per_day: props.current?.working_hours_per_day ?? 8,
  notes: props.current?.notes || '',
});

function handleSubmit() {
  form.post(props.current?.id ? route('employees.update', props.current.id) : route('employees.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Employee') }) : $t('Add {x}', { x: $t('Employee') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('employee'),
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
          :label="$t('Store')"
          :suggestions="stores"
          v-model="form.store_id"
          :error="form.errors.store_id"
          :placeholder="$t('Select store')"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :label="$t('User')"
          :suggestions="users"
          v-model="form.user_id"
          :error="form.errors.user_id"
          :placeholder="$t('Select employee user')"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input :label="$t('Department')" v-model="form.department" :error="form.errors.department" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input :label="$t('Job Title')" v-model="form.job_title" :error="form.errors.job_title" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <DateInput type="date" :label="$t('Hire Date')" v-model="form.hire_date" :error="form.errors.hire_date" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input type="number" :label="$t('Basic Salary')" v-model="form.basic_salary" :error="form.errors.basic_salary" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input
          type="number"
          :required="true"
          :label="$t('Working Days/Month')"
          v-model="form.working_days_per_month"
          :error="form.errors.working_days_per_month"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <Input
          type="number"
          :required="true"
          :label="$t('Working Hours/Day')"
          v-model="form.working_hours_per_day"
          :error="form.errors.working_hours_per_day"
        />
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
