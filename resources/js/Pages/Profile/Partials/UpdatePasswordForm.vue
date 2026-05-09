<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import FormSection from '@/Components/Jet/FormSection.vue';
import ActionMessage from '@/Components/Jet/ActionMessage.vue';
import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('user-password.update'), {
    errorBag: 'updatePassword',
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }

      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>

<template>
  <FormSection @submitted="updatePassword">
    <template #title> {{ $t('Update Password') }} </template>

    <template #description> {{ $t('Ensure your account is using a long, random password to stay secure.') }} </template>

    <template #form>
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="current_password" :value="$t('Current Password')" />
        <TextInput
          type="password"
          id="current_password"
          ref="currentPasswordInput"
          class="mt-1 block w-full"
          autocomplete="current-password"
          v-model="form.current_password"
        />
        <InputError :message="form.errors.current_password" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="password" :value="$t('New Password')" />
        <TextInput
          id="password"
          type="password"
          ref="passwordInput"
          v-model="form.password"
          class="mt-1 block w-full"
          autocomplete="new-password"
        />
        <InputError :message="form.errors.password" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="password_confirmation" :value="$t('Confirm Password')" />
        <TextInput
          type="password"
          class="mt-1 block w-full"
          id="password_confirmation"
          autocomplete="new-password"
          v-model="form.password_confirmation"
        />
        <InputError :message="form.errors.password_confirmation" class="mt-2" />
      </div>
    </template>

    <template #actions>
      <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

      <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> {{ $t('Save') }} </PrimaryButton>
    </template>
  </FormSection>
</template>
