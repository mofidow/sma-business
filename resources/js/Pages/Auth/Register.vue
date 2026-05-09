<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

import Checkbox from '@/Components/Jet/Checkbox.vue';
import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head :title="$t('Register')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="name" :value="$t('Name')" />
        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div class="mt-4">
        <InputLabel for="email" :value="$t('Email')" />
        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="email" />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4">
        <InputLabel for="username" :value="$t('Username')" />
        <TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full" required autocomplete="username" />
        <InputError class="mt-2" :message="form.errors.username" />
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
          id="password_confirmation"
          class="mt-1 block w-full"
          autocomplete="new-password"
          v-model="form.password_confirmation"
        />
        <InputError class="mt-2" :message="form.errors.password_confirmation" />
      </div>

      <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
        <InputLabel for="terms">
          <div class="flex items-center">
            <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

            <div class="ms-2">
              {{ $t('I agree to the') }}
              <a
                target="_blank"
                :href="route('terms.show')"
                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                >{{ $t('Terms of Service') }}</a
              >
              {{ $t('and') }}
              <a
                target="_blank"
                :href="route('policy.show')"
                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                >{{ $t('Privacy Policy') }}</a
              >
            </div>
          </div>
          <InputError class="mt-2" :message="form.errors.terms" />
        </InputLabel>
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Link
          :href="route('login')"
          class="rounded-md text-sm text-gray-600 underline decoration-dotted underline-offset-8 hover:text-gray-900 hover:decoration-solid dark:text-gray-400 dark:hover:text-gray-100"
        >
          {{ $t('Already registered?') }}
        </Link>

        <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Register') }}
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
