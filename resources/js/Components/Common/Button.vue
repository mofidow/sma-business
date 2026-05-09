<script setup>
import LoadingButton from './LoadingButton.vue';

defineProps({
  loading: Boolean,
  href: {
    type: String,
    default: '',
  },
  away: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'submit',
  },
});
</script>

<template>
  <template v-if="away && href">
    <a :href="href" class="btn-primary">
      <slot></slot>
    </a>
  </template>
  <template v-else-if="href">
    <Link :href="href" class="btn-primary">
      <slot></slot>
    </Link>
  </template>
  <template v-else>
    <LoadingButton :type="type" :loading="loading" class="btn-primary">
      <slot></slot>
    </LoadingButton>
  </template>
</template>
