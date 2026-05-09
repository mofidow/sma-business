<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import LoadingButton from '@/Components/Common/LoadingButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';

const form = useForm({
  password: '',
});

const passwordInput = ref(null);

const submit = () => {
  form.post(route('password.confirm'), {
    onFinish: () => {
      form.reset();

      passwordInput.value.focus();
    },
  });
};
</script>

<template>
  <Head :title="$t('Secure Area')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
      {{ $t('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="password" :value="$t('Password')" />
        <TextInput
          required
          autofocus
          id="password"
          type="password"
          ref="passwordInput"
          v-model="form.password"
          class="mt-1 block w-full"
          autocomplete="current-password"
        />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div class="mt-4 flex justify-end">
        <LoadingButton class="ms-4" :class="{ 'opacity-25': form.processing }" :loading="form.processing">
          {{ $t('Confirm') }}
        </LoadingButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
