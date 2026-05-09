<script setup>
import SidebarMenus from './SidebarMenus.vue';

defineProps({ open: Boolean });
</script>
<template>
  <div class="hidden xl:block">
    <SidebarMenus />
  </div>
  <Transition name="sidebar-slide">
    <SidebarMenus v-if="open" @close="$emit('close')" class="z-30" />
  </Transition>
  <Transition name="fade">
    <div v-if="open" class="relative z-20 xl:hidden">
      <div class="fixed inset-0 transform backdrop-blur-xs transition-all" @click="$emit('close')">
        <div class="absolute inset-0 bg-gray-100 opacity-75 dark:bg-gray-900" />
      </div>
    </div>
  </Transition>
</template>
