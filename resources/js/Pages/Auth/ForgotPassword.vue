<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';

defineProps({
  status: String,
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <Head :title="$t('Forgot Password')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
      {{
        $t(
          'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.'
        )
      }}
    </div>

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" :value="$t('Email')" />
        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus autocomplete="username" />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Link
          :href="route('login')"
          class="rounded-md text-sm text-gray-600 underline decoration-dotted underline-offset-8 hover:text-gray-900 hover:decoration-solid dark:text-gray-400 dark:hover:text-gray-100"
        >
          {{ $t('Back to Login') }}
        </Link>

        <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Email Password Reset Link') }}
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
