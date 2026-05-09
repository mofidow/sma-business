<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';

import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import FormSection from '@/Components/Jet/FormSection.vue';
import ActionMessage from '@/Components/Jet/ActionMessage.vue';
import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';
import SecondaryButton from '@/Components/Jet/SecondaryButton.vue';

const props = defineProps({
  user: Object,
});

const form = useForm({
  _method: 'PUT',
  name: props.user.name,
  email: props.user.email,
  photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
  if (photoInput.value) {
    form.photo = photoInput.value.files[0];
  }

  form.post(route('user-profile-information.update'), {
    errorBag: 'updateProfileInformation',
    preserveScroll: true,
    onSuccess: () => clearPhotoFileInput(),
  });
};

const sendEmailVerification = () => {
  verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
  photoInput.value.click();
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

const deletePhoto = () => {
  router.delete(route('current-user-photo.destroy'), {
    preserveScroll: true,
    onSuccess: () => {
      photoPreview.value = null;
      clearPhotoFileInput();
    },
  });
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};
</script>

<template>
  <FormSection @submitted="updateProfileInformation">
    <template #title> {{ $t('Profile Information') }} </template>

    <template #description> {{ $t("Update your account's profile information and email address.") }} </template>

    <template #form>
      <!-- Profile Photo -->
      <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
        <!-- Profile Photo File Input -->
        <input id="photo" ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" />

        <InputLabel for="photo" :value="$t('Photo')" />

        <!-- Current Profile Photo -->
        <div v-show="!photoPreview" class="mt-2">
          <img :src="user.profile_photo_url" :alt="user.name" class="h-20 w-20 rounded-full object-cover" />
        </div>

        <!-- New Profile Photo Preview -->
        <div v-show="photoPreview" class="mt-2">
          <span
            class="block h-20 w-20 rounded-full bg-cover bg-center bg-no-repeat"
            :style="'background-image: url(\'' + photoPreview + '\');'"
          />
        </div>

        <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="selectNewPhoto"> {{ $t('Select A New Photo') }} </SecondaryButton>

        <SecondaryButton v-if="user.profile_photo_path" type="button" class="mt-2" @click.prevent="deletePhoto">
          {{ $t('Remove Photo') }}
        </SecondaryButton>

        <InputError :message="form.errors.photo" class="mt-2" />
      </div>

      <!-- Name -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="name" :value="$t('Name')" />
        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autocomplete="name" />
        <InputError :message="form.errors.name" class="mt-2" />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="email" :value="$t('Email')" />
        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
        <InputError :message="form.errors.email" class="mt-2" />

        <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
          <p class="mt-2 text-sm dark:text-white">
            {{ $t('Your email address is unverified.') }}

            <Link
              :href="route('verification.send')"
              method="post"
              as="button"
              class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
              @click.prevent="sendEmailVerification"
            >
              {{ $t('Click here to re-send the verification email.') }}
            </Link>
          </p>

          <div v-show="verificationLinkSent" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
            {{ $t('A new verification link has been sent to your email address.') }}
          </div>
        </div>
      </div>
    </template>

    <template #actions>
      <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

      <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> {{ $t('Save') }} </PrimaryButton>
    </template>
  </FormSection>
</template>
