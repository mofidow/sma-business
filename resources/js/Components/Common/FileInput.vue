<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputLabel, InputError } from '@/Components/Jet';

const photo = ref(null);
const { t } = useI18n({});
const emits = defineEmits(['input', 'update:modelValue']);
const props = defineProps({
  id: {
    type: String,
    default() {
      return (Math.random() + '').replace('0.', '');
    },
  },
  type: {
    type: String,
    default: 'text',
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  accept: {
    type: String,
    default: 'image/*',
  },
  modelValue: String,
  label: String,
  error: String,
});

const handleFileChange = e => {
  //   photo.value = e.target.value.split(/[\\/]/).pop();
  photo.value = '';
  if (e.target.files.length == 1) {
    photo.value = '1 ' + t('file selected');
  } else if (e.target.files.length > 1) {
    photo.value = e.target.files.length + ' ' + t('files selected');
  }
  emits('input', props.multiple ? Array.from(e.target.files) : e.target.files[0]);
  emits('update:modelValue', props.multiple ? Array.from(e.target.files) : e.target.files[0]);
};
</script>

<template>
  <div>
    <InputLabel v-if="label" :for="id" :value="label" class="mb-1" />
    <input :id="id" :multiple="multiple" type="file" name="photo" :accept="accept" class="hidden" @change="handleFileChange" />
    <label
      :for="id"
      :class="{
        'border-red-500': error,
        'text-gray-700 dark:text-gray-300': photo,
        'text-gray-400 dark:text-gray-600': !photo,
        'border-gray-300 dark:border-gray-700': !error,
      }"
      class="inline-block w-full cursor-pointer rounded-md border px-4 py-2 text-start shadow-xs focus:border-primary-300 focus:ring-3 focus:ring-primary-200/50 dark:bg-gray-900"
    >
      {{ modelValue && photo ? photo : t('Select') }}
    </label>
    <InputError v-if="error" :message="error" class="mt-1" />
  </div>
</template>
