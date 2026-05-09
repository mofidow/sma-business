<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  maxWidth: {
    type: String,
    default: 'lg',
  },
  closeable: {
    type: Boolean,
    default: true,
  },
  round: {
    type: Boolean,
    default: false,
  },
});

const close = () => {
  emit('close');
};
</script>

<template>
  <Modal :show="show" :max-width="maxWidth" :round="round" :closeable="closeable" @close="close">
    <div class="px-6 py-4">
      <div class="text-focus text-lg font-medium">
        <slot name="title" />
      </div>

      <div class="text-mute mt-4 text-sm">
        <slot name="content" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <slot name="footer" />
    </div>
  </Modal>
</template>
