<script setup>
import { isServer } from '@/Core';
</script>

<template>
  <slot v-if="isServer" />
</template>
