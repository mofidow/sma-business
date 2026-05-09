<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { router, useForm } from '@inertiajs/vue3';

import { InputError, InputLabel, SecondaryButton } from '@/Components/Jet';
import { CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'parents']);

const photoInput = ref(null);
const photoPreview = ref(null);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  photo: null,
  name: props.current?.name,
  slug: props.current?.slug,
  order: props.current?.order,
  title: props.current?.title,
  description: props.current?.description,
  active: props.current?.id ? props.current?.active == 1 : true,
});

const close = () => {
  form.reset();
  photoPreview.value = null;
  clearPhotoFileInput();
  emits('close');
};

const updatePhotoPreview = () => {
  const photo = photoInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = e => {
    photoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
};

const deletePhoto = row => {
  router.delete(route('brands.photo.destroy', { id: row.id }), {
    preserveScroll: true,
    onSuccess: () => {
      photoPreview.value = null;
      clearPhotoFileInput();
    },
  });
};

const selectNewPhoto = () => {
  photoInput.value.click();
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};

function handleSubmit() {
  if (photoInput.value) {
    form.photo = photoInput.value.files[0];
  }

  form.post(props.current?.id ? route('brands.update', props.current.id) : route('brands.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Brand') }) : $t('Add {x}', { x: $t('Brand') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('brand'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Photo -->
      <div class="col-span-full">
        <!-- Photo File Input -->
        <input id="photo" ref="photoInput" type="file" class="hidden" @change="() => updatePhotoPreview()" />

        <InputLabel for="photo" :value="$t('Photo')" />

        <!-- Current Photo -->
        <div v-show="!photoPreview && current?.photo" class="mt-2 rounded-md p-1">
          <img :alt="$t('Photo')" :src="current?.photo" class="h-full max-h-40 min-h-20 w-full max-w-64 rounded-md object-contain" />
        </div>

        <!-- New Photo Preview -->
        <div v-show="photoPreview" class="mt-2 rounded-md p-1">
          <span
            class="block h-full max-h-40 min-h-24 w-full max-w-64 rounded-md bg-contain bg-no-repeat"
            :style="'background-image: url(\'' + photoPreview + '\');'"
          />
        </div>

        <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="() => selectNewPhoto()">
          {{ $t('Select A New Photo') }}
        </SecondaryButton>

        <SecondaryButton
          type="button"
          v-if="current?.photo"
          @click.prevent="() => deletePhoto()"
          class="mt-2 flex items-center justify-center rounded-md bg-gray-50 p-1"
        >
          {{ $t('Remove Photo') }}
        </SecondaryButton>

        <InputError :message="form.errors.photo" class="mt-2" />
      </div>

      <!-- Name -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="name" :label="$t('Name')" v-model="form.name" :error="form.errors.name" />
      </div>
      <!-- Slug -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="slug" :label="$t('Slug')" v-model="form.slug" :error="form.errors.slug" />
      </div>
      <!-- Order -->
      <div class="col-span-6 sm:col-span-2">
        <Input type="number" id="order" :label="$t('Order')" v-model="form.order" :error="form.errors.order" />
      </div>

      <!-- Title -->
      <div class="col-span-6 sm:col-span-4">
        <Input id="title" max="50" :label="$t('Title')" v-model="form.title" :error="form.errors.title" />
      </div>

      <!-- Description -->
      <div class="col-span-full">
        <Textarea max="160" id="description" :label="$t('Description')" v-model="form.description" :error="form.errors.description" />
      </div>

      <!-- Active -->
      <div class="col-span-full">
        <CheckBox id="active" :error="form.errors.active" v-model:checked="form.active" :label="$t('Active')" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
