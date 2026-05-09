<script setup>
import { Head, useForm } from '@inertiajs/vue3';

import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';

const props = defineProps({
  email: String,
  token: String,
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('password.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head :title="$t('Reset Password')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" :value="$t('Email')" />
        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus autocomplete="username" />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" :value="$t('Password')" />
        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div class="mt-4">
        <InputLabel for="password_confirmation" :value="$t('Confirm Password')" />
        <TextInput
          required
          type="password"
          class="mt-1 block w-full"
          id="password_confirmation"
          autocomplete="new-password"
          v-model="form.password_confirmation"
        />
        <InputError class="mt-2" :message="form.errors.password_confirmation" />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> {{ $t('Reset Password') }} </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
