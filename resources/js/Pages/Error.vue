<script setup>
import { useI18n } from 'vue-i18n';
import { computed, onMounted } from 'vue';

const { t } = useI18n({});
const props = defineProps({ status: Number });

onMounted(() => {
  if (props.status === 503) {
    setInterval(() => {
      window.location.reload();
    }, 10000);
  }
});

const title = computed(() => {
  return {
    503: t('503: Service Unavailable'),
    500: t('500: Server Error'),
    404: t('404: Page Not Found'),
    403: t('403: Forbidden'),
  }[props.status];
});

const description = computed(() => {
  return {
    503: t('Sorry, we are doing some maintenance. Please check back soon.'),
    500: t('Whoops, something went wrong on our servers.'),
    404: t('Sorry, the page you are looking for could not be found.'),
    403: t('Sorry, you are forbidden from accessing this page.'),
  }[props.status];
});
</script>

<template>
  <Head :title="title" />
  <div class="flex min-h-screen min-w-full flex-col items-center justify-center text-center">
    <h1 class="text-lg font-bold">{{ title }}</h1>
    <div class="my-2">{{ description }}</div>
    <a v-if="status != 503" href="/" class="btn-primary mt-4">{{ $t('Back to Homepage') }}</a>
  </div>
</template>
