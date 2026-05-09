<script setup>
import QuickView from './QuickView.vue';

defineProps(['payment', 'custom_fields']);
</script>

<template>
  <Head>
    <title>{{ $t('Payment') }}</title>
  </Head>

  <div class="p-6">
    <div class="relative mx-auto w-full max-w-3xl rounded-lg border border-gray-200 dark:border-gray-700 print:border-0">
      <QuickView :current="payment" :fields="custom_fields" :xfetch="true" />
    </div>
  </div>
</template>
