<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineEmits(['submitted']);

const hasActions = computed(() => !!useSlots().actions);
</script>

<template>
  <div class="lg:grid lg:grid-cols-3 lg:gap-6">
    <SectionTitle>
      <template #title>
        <slot name="title" />
      </template>
      <template #description>
        <slot name="description" />
      </template>
    </SectionTitle>

    <div class="mt-5 lg:col-span-2 lg:mt-0">
      <form @submit.prevent="$emit('submitted')">
        <div
          class="bg-white px-4 py-5 shadow-sm sm:p-6 dark:bg-gray-900"
          :class="hasActions ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md'"
        >
          <div class="grid grid-cols-6 gap-6">
            <slot name="form" />
          </div>
        </div>

        <div
          v-if="hasActions"
          class="flex items-center justify-end bg-gray-100 px-4 py-3 text-end shadow-sm sm:rounded-br-md sm:rounded-bl-md sm:px-6 dark:bg-gray-950"
        >
          <slot name="actions" />
        </div>
      </form>
    </div>
  </div>
</template>
