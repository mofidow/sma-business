<script setup>
import { route } from 'ziggy-js';
import { computed, onMounted } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

import PrimaryButton from '@/Components/Jet/PrimaryButton.vue';
import AuthenticationCard from '@/Components/Jet/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Jet/AuthenticationCardLogo.vue';

const page = usePage();
const form = useForm({});
const props = defineProps({ status: String });

onMounted(() => {
  if (page.props.auth.user.email_verified_at) {
    router.push(route('home'));
  }
});

const submit = () => {
  form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
  <Head :title="$t('Email Verification')" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
      {{
        $t(
          "Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."
        )
      }}
    </div>

    <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
      {{ $t('A new verification link has been sent to the email address you provided in your profile settings.') }}
    </div>

    <form @submit.prevent="submit">
      <div class="mt-4 flex items-center justify-between">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Resend Verification Email') }}
        </PrimaryButton>

        <div>
          <Link
            :href="route('profile.show')"
            class="rounded-md text-sm text-gray-600 underline decoration-dotted underline-offset-8 hover:text-gray-900 hover:decoration-solid dark:text-gray-400 dark:hover:text-gray-100"
          >
            {{ $t('Edit Profile') }}
          </Link>

          <Link
            as="button"
            method="post"
            :href="route('logout')"
            class="ms-2 cursor-pointer rounded-md text-sm text-gray-600 underline decoration-dotted underline-offset-8 hover:text-gray-900 hover:decoration-solid dark:text-gray-400 dark:hover:text-gray-100"
          >
            {{ $t('Log Out') }}
          </Link>
        </div>
      </div>
    </form>
  </AuthenticationCard>
</template>
