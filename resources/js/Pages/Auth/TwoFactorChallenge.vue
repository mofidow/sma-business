<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';

const recovery = ref(false);

const form = useForm({
  code: '',
  recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
  recovery.value ^= true;

  await nextTick();

  if (recovery.value) {
    recoveryCodeInput.value.focus();
    form.code = '';
  } else {
    codeInput.value.focus();
    form.recovery_code = '';
  }
};

const submit = () => {
  form.post(route('two-factor.login'));
};
</script>

<template>
  <Head :title="$t('Two-factor Confirmation')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
      <template v-if="!recovery">
        {{ $t('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
      </template>

      <template v-else> {{ $t('Please confirm access to your account by entering one of your emergency recovery codes.') }} </template>
    </div>

    <form @submit.prevent="submit">
      <div v-if="!recovery">
        <InputLabel for="code" :value="$t('Code')" />
        <TextInput
          id="code"
          autofocus
          type="text"
          ref="codeInput"
          v-model="form.code"
          inputmode="numeric"
          class="mt-1 block w-full"
          autocomplete="one-time-code"
        />
        <InputError class="mt-2" :message="form.errors.code" />
      </div>

      <div v-else>
        <InputLabel for="recovery_code" :value="$t('Recovery Code')" />
        <TextInput
          type="text"
          id="recovery_code"
          ref="recoveryCodeInput"
          class="mt-1 block w-full"
          autocomplete="one-time-code"
          v-model="form.recovery_code"
        />
        <InputError class="mt-2" :message="form.errors.recovery_code" />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <button
          type="button"
          class="cursor-pointer text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-400"
          @click.prevent="toggleRecovery"
        >
          <template v-if="!recovery"> {{ $t('Use a recovery code') }} </template>

          <template v-else> {{ $t('Use an authentication code') }} </template>
        </button>

        <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Log in') }}
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
