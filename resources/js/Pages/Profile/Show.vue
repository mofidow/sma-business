<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionBorder from '@/Components/Jet/SectionBorder.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';

defineOptions({ layout: AdminLayout });
defineProps({
  confirmsTwoFactorAuthentication: Boolean,
  sessions: Array,
});
</script>

<template>
  <Head>
    <title>{{ $t('Profile') }}</title>
  </Head>
  <Header>{{ $t('Profile') }}</Header>

  <div class="bg-gray-50 dark:bg-gray-800">
    <div class="mx-auto max-w-7xl py-10 sm:px-6 lg:px-8">
      <div v-if="$page.props.jetstream.canUpdateProfileInformation">
        <UpdateProfileInformationForm :user="$page.props.auth.user" />

        <SectionBorder />
      </div>

      <div v-if="$page.props.jetstream.canUpdatePassword">
        <UpdatePasswordForm class="mt-10 sm:mt-0" />

        <SectionBorder />
      </div>

      <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
        <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" class="mt-10 sm:mt-0" />

        <SectionBorder />
      </div>

      <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

      <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
        <SectionBorder />

        <DeleteUserForm class="mt-10 sm:mt-0" />
      </template>
    </div>
  </div>
</template>
