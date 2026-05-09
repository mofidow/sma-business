<script setup>
import { ref } from 'vue';

const copied = ref(false);
const props = defineProps({ text: { type: String } });

function copy() {
  copied.value = true;
  navigator.clipboard.writeText(props.text);
  setTimeout(() => (copied.value = false), 2000);
}
</script>

<template>
  <button type="button" class="relative flex items-center" @click="copy">
    <slot />
    <Icon name="copied-o" class="absolute start-full ms-1 h-full w-5 text-success-500" v-if="copied" />
  </button>
</template>
