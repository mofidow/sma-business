<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, DateInput, Input, LoadingButton, Textarea } from '@/Components/Common';

const props = defineProps(['current', 'employees', 'leave_types']);
const emit = defineEmits(['close', 'done']);

const employeeOptions = computed(() => props.employees.map(e => ({ value: e.id, label: e.user?.name || `#${e.id}` })));

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  employee_id: props.current?.employee_id || null,
  leave_type_id: props.current?.leave_type_id || null,
  start_date: props.current?.start_date || '',
  end_date: props.current?.end_date || '',
  days: props.current?.days ?? 1,
  reason: props.current?.reason || '',
  notes: props.current?.notes || '',
});

function handleSubmit() {
  form.post(props.current?.id ? route('leaves.update', props.current.id) : route('leaves.store'), {
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
        {{ current?.id ? $t('Edit {x}', { x: $t('Leave') }) : $t('Add {x}', { x: $t('Leave') }) }}
      </h1>
      <p class="text-mute mt-1 truncate text-sm">
        {{
          $t('Please fill the form below to {action} {record}.', {
            record: $t('leave request'),
            action: current?.id ? $t('edit') : $t('add'),
          })
        }}
      </p>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :label="$t('Employee')"
          v-model="form.employee_id"
          :suggestions="employeeOptions"
          :error="form.errors.employee_id"
          :placeholder="$t('Select employee')"
        />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :label="$t('Leave Type')"
          :suggestions="leave_types"
          v-model="form.leave_type_id"
          :error="form.errors.leave_type_id"
          :placeholder="$t('Select type')"
        />
      </div>

      <div class="col-span-6 sm:col-span-2">
        <DateInput type="date" :label="$t('Start Date')" :required="true" v-model="form.start_date" :error="form.errors.start_date" />
      </div>

      <div class="col-span-6 sm:col-span-2">
        <DateInput type="date" :label="$t('End Date')" :required="true" v-model="form.end_date" :error="form.errors.end_date" />
      </div>

      <div class="col-span-6 sm:col-span-2">
        <Input type="number" :label="$t('Days')" :required="true" v-model="form.days" :error="form.errors.days" />
      </div>

      <div class="col-span-6">
        <Textarea rows="2" :label="$t('Reason')" v-model="form.reason" :error="form.errors.reason" />
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
