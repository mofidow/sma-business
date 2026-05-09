<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
  align: {
    type: String,
    default: 'right',
  },
  valign: {
    type: String,
    default: 'bottom',
  },
  width: {
    type: String,
    default: '48',
  },
  autoClose: {
    type: Boolean,
    default: true,
  },
  contentClasses: {
    type: Array,
    default: () => ['py-1', 'bg-white dark:bg-gray-700'],
  },
});

const open = ref(false);

const closeOnEscape = e => {
  if (open.value && e.key === 'Escape') {
    open.value = false;
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
  return {
    40: 'w-40',
    48: 'w-48',
    56: 'w-56',
    64: 'w-64',
  }[props.width.toString()];
});

const alignmentClasses = computed(() => {
  if (props.align === 'left') {
    return 'ltr:origin-top-left rtl:origin-top-right start-0';
  }

  if (props.align === 'right') {
    return 'ltr:origin-top-right rtl:origin-top-left end-0';
  }

  return 'origin-top';
});
</script>

<template>
  <div class="relative z-20">
    <div @click="open = !open">
      <slot name="trigger" />
    </div>

    <!-- Full Screen Dropdown Overlay -->
    <div v-show="open" class="fixed inset-0 z-40" @click="open = false" />

    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="open"
        style="display: none"
        @click="open = autoClose ? false : open"
        class="absolute z-50 my-2 rounded-md shadow-lg"
        :class="[widthClass, alignmentClasses, valign == 'top' ? 'bottom-full' : 'top-full']"
      >
        <div class="rounded-md ring-1 ring-black/5" :class="contentClasses">
          <slot name="content" />
        </div>
      </div>
    </transition>
  </div>
</template>
