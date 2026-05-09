<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import Checkbox from '@/Components/Jet/Checkbox.vue';
import TextInput from '@/Components/Jet/TextInput.vue';
import InputError from '@/Components/Jet/InputError.vue';
import InputLabel from '@/Components/Jet/InputLabel.vue';
import LoadingButton from '@/Components/Common/LoadingButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';
import { ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const scriptEl = ref(null);
const props = defineProps({
  demo: Boolean,
  status: String,
  canResetPassword: Boolean,
  captcha_src: String,
});

const form = useForm({
  username: props.demo ? 'super' : '',
  password: props.demo ? 'password' : '',
  remember: false,
  captcha: '',
});

onMounted(() => {
  if (page.props.settings.captcha_provider == 'recaptcha') {
    const recaptchaScript = document.createElement('script');
    recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js?render=' + page.props.settings.captcha_site_key);
    scriptEl.value = document.head.appendChild(recaptchaScript);
  } else if (page.props.settings.captcha_provider == 'turnstile') {
    const turnstileScript = document.createElement('script');
    turnstileScript.setAttribute('src', 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit');
    turnstileScript.onload = () => {
      turnstile.render('#cf-widget', {
        sitekey: page.props.settings.captcha_site_key,
        theme: localStorage.getItem('theme') == 'dark' ? 'dark' : localStorage.getItem('theme') == 'light' ? 'light' : 'auto',
        callback: function (token) {
          form.captcha = token;
        },
      });
    };
    scriptEl.value = document.head.appendChild(turnstileScript);
  }
});

onUnmounted(() => {
  if (scriptEl.value) {
    document.head.removeChild(scriptEl.value);
    document.getElementsByClassName('grecaptcha-badge').item(0)?.remove();
  }
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};

const handle = () => {
  grecaptcha.ready(function () {
    grecaptcha
      .execute(page.props.settings.captcha_site_key, {
        action: 'submit',
      })
      .then(function (token) {
        form.captcha = token;
        submit();
      });
  });
};
</script>

<template>
  <Head :title="$t('Log in')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="username" :value="$t('Username')" />
        <TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full" required autofocus autocomplete="username" />
        <InputError class="mt-2" :message="form.errors.username" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" :value="$t('Password')" />
        <TextInput
          required
          id="password"
          type="password"
          v-model="form.password"
          class="mt-1 block w-full"
          autocomplete="current-password"
        />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <template v-if="$page.props.settings.captcha_provider">
        <template v-if="$page.props.settings.captcha_provider == 'recaptcha'">
          <InputError class="mt-2" :message="form.errors.captcha" />
        </template>
        <template v-if="$page.props.settings.captcha_provider == 'turnstile'">
          <div id="cf-widget" class="mt-4" wire:ignore></div>
        </template>
        <template v-if="$page.props.settings.captcha_provider == 'local'">
          <div class="mt-4">
            <InputLabel for="captcha" :value="$t('Captcha')" />
            <div class="flex items-end justify-start gap-4">
              <TextInput id="captcha" v-model="form.captcha" type="text" class="mt-1 block w-full" required autocomplete="captcha" />

              <img :src="captcha_src" alt="" class="mb-px h-10 rounded-md p-px shadow-xs" />
            </div>
            <InputError class="mt-2" :message="form.errors.captcha" />
          </div>
        </template>
      </template>

      <div class="mt-4 block">
        <label class="inline-flex items-center">
          <Checkbox v-model:checked="form.remember" name="remember" />
          <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $t('Remember me') }}</span>
        </label>
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="rounded-md text-sm text-gray-600 underline decoration-dotted underline-offset-8 hover:text-gray-900 hover:decoration-solid dark:text-gray-400 dark:hover:text-gray-100"
        >
          {{ $t('Forgot your password?') }}
        </Link>

        <template v-if="page.props.settings.captcha_provider == 'recaptcha'">
          <LoadingButton class="ms-4" type="button" @click="handle" :class="{ 'opacity-25': form.processing }" :loading="form.processing">
            {{ $t('Log in') }}
          </LoadingButton>
        </template>
        <template v-else>
          <LoadingButton class="ms-4" :class="{ 'opacity-25': form.processing }" :loading="form.processing">
            {{ $t('Log in') }}
          </LoadingButton>
        </template>
      </div>
    </form>
  </AuthenticationCard>
</template>
