<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';

defineOptions({ layout: AdminLayout });
defineProps({
  tokens: Array,
  availablePermissions: Array,
  defaultPermissions: Array,
});
</script>

<template>
  <Head>
    <title>{{ $t('API Tokens') }}</title>
  </Head>
  <Header>{{ $t('API Tokens') }}</Header>
  <div class="flex grow flex-col items-stretch justify-stretch self-stretch">
    <div>
      <div class="mx-auto max-w-7xl py-10 sm:px-6 lg:px-8">
        <ApiTokenManager :tokens="tokens" :available-permissions="availablePermissions" :default-permissions="defaultPermissions" />
      </div>
    </div>
  </div>
</template>
