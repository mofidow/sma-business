<script setup>
import { InputError, InputLabel } from '@/Components/Jet';
import { AutoComplete, Input, Textarea } from '@/Components/Common';

const emits = defineEmits(['update']);
const props = defineProps({
  errors: { type: Object, default: () => ({}) },
  keyboard: { type: Boolean, default: false },
  custom_fields: { type: Array, default: () => [] },
  extra_attributes: { type: Object, default: () => ({}) },
});

function isChecked(field, opt) {
  return props.extra_attributes[field] && props.extra_attributes[field].includes(opt);
}

function updateValue(field, e, opt) {
  if (!props.extra_attributes[field]) {
    props.extra_attributes[field] = [];
  }
  if (e.target.checked) {
    props.extra_attributes[field] = [...props.extra_attributes[field], opt];
  } else {
    props.extra_attributes[field] = props.extra_attributes[field].filter(v => v != opt);
  }
  emits('update', e);
}
</script>

<template>
  <template v-if="custom_fields && custom_fields.length">
    <div class="col-span-full grid grid-cols-6 gap-6">
      <template v-for="(field, fi) in custom_fields" :key="field.id">
        <template v-if="field.type == 'text' || field.type == 'number'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              :type="field.type"
              :keyboard="keyboard"
              :label="$t(field.name)"
              @change="e => emits('update', e)"
              v-model="extra_attributes[field.name]"
              :error="errors['extra_attributes.' + field.name]"
            />
          </div>
        </template>
        <template v-else-if="field.type == 'select'">
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :keyboard="keyboard"
              :label="$t(field.name)"
              :suggestions="field.options"
              @change="e => emits('update', e)"
              v-model="extra_attributes[field.name]"
              :error="errors['extra_attributes.' + field.name] || null"
            />
          </div>
        </template>
        <template v-else-if="field.type == 'date'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              type="date"
              :label="$t(field.name)"
              @change="e => emits('update', e)"
              v-model="extra_attributes[field.name]"
              :error="errors['extra_attributes.' + field.name] || null"
            />
          </div>
        </template>
        <template v-else-if="field.type == 'time'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              type="time"
              :label="$t(field.name)"
              @change="e => emits('update', e)"
              v-model="extra_attributes[field.name]"
              :error="errors['extra_attributes.' + field.name] || null"
            />
          </div>
        </template>
        <template v-else-if="field.type == 'checkbox'">
          <div class="col-span-full">
            <InputLabel :value="$t(field.name)" />
            <div class="my-2 flex flex-wrap items-center gap-x-12 gap-y-3">
              <InputLabel
                class="flex items-center"
                :key="fi + '_ex_cb_' + index"
                :for-id="fi + '_ex_cb_' + index"
                v-for="(opt, index) in field.options"
              >
                <input
                  :value="opt"
                  type="checkbox"
                  :name="field.name + '[]'"
                  :id="fi + '_ex_cb_' + index"
                  :checked="isChecked(field.name, opt)"
                  @change="e => updateValue(field.name, e, opt)"
                  class="h-5 w-5 rounded-sm border border-gray-300 text-primary-600 shadow-xs dark:border-gray-700 dark:bg-gray-900"
                />
                <span v-html="opt" class="ms-2 cursor-default"></span>
              </InputLabel>
            </div>
            <InputError
              v-if="errors['extra_attributes.' + field.name] || null"
              :message="errors['extra_attributes.' + field.name] || null"
            />
          </div>
        </template>
        <template v-else-if="field.type == 'radio'">
          <div class="col-span-full">
            <InputLabel :value="$t(field.name)" />
            <div class="my-2 flex flex-wrap items-center gap-x-12 gap-y-3">
              <InputLabel
                class="flex items-center"
                :key="fi + '_ex_radio_' + index"
                :for-id="fi + '_ex_radio_' + index"
                v-for="(opt, index) in field.options"
              >
                <input
                  type="radio"
                  :value="opt"
                  :name="field.name"
                  :id="fi + '_ex_radio_' + index"
                  @change="e => emits('update', e)"
                  v-model="extra_attributes[field.name]"
                  :checked="extra_attributes[field.name] == opt"
                  class="h-5 w-5 rounded-full border border-gray-300 text-primary-600 shadow-xs dark:border-gray-700 dark:bg-gray-900"
                />
                <span v-html="opt" class="ms-2 cursor-default"></span>
              </InputLabel>
            </div>
            <InputError
              v-if="errors['extra_attributes.' + field.name] || null"
              :message="errors['extra_attributes.' + field.name] || null"
            />
          </div>
        </template>
        <template v-else-if="field.type == 'textarea'">
          <div class="col-span-full">
            <Textarea
              :keyboard="keyboard"
              :label="$t(field.name)"
              @change="e => emits('update', e)"
              v-model="extra_attributes[field.name]"
              :error="errors['extra_attributes.' + field.name] || null"
            />
          </div>
        </template>
      </template>
    </div>
  </template>
</template>
