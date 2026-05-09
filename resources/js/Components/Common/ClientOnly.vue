<script setup>
import { isClient } from '@/Core';
</script>

<template>
  <slot v-if="isClient" />
</template>
