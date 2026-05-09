<script setup>
defineProps({
  value: String,
});
</script>

<template>
  <label class="text-mute inline-block text-sm font-medium">
    <span v-if="value">{{ value }}</span>
    <span v-else><slot /></span>
  </label>
</template>
