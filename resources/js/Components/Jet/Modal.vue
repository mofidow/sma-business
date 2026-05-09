<script setup>
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  maxWidth: {
    type: String,
    default: '2xl',
  },
  closeable: {
    type: Boolean,
    default: true,
  },
  backdrop: {
    type: Boolean,
    default: true,
  },
  round: {
    type: Boolean,
    default: false,
  },
  transparent: {
    type: Boolean,
    default: false,
  },
  overflow: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const dialog = ref(null);
const showSlot = ref(props.show);

// watch(
//   () => props.show,
//   async () => {
//     await nextTick();
//     if (props.show) {
//       document.body.style.overflow = 'hidden';
//       showSlot.value = true;
//       dialog.value?.showModal();
//     } else {
//       document.body.style.overflow = null;
//       setTimeout(() => {
//         dialog.value?.close();
//         showSlot.value = false;
//       }, 200);
//     }
//   }
// );

watch(
  () => props.show,
  async () => {
    if (props.show) {
      document.body.classList.add('modal-open');
      document.body.style.overflow = 'hidden';
      showSlot.value = true;
    } else {
      await nextTick();
      let rc = 0;
      document.querySelectorAll('body .modal').forEach(el => {
        if (el.checkVisibility()) {
          rc++;
        }
      });

      if (rc <= 0) {
        document.body.style.overflow = null;
        document.body.classList.remove('modal-open');
      }
      setTimeout(() => {
        showSlot.value = false;
      }, 200);
    }
  }
);

const close = backdrop => {
  if (backdrop && props.backdrop) {
    emit('close');
  } else if (!backdrop && props.closeable) {
    emit('close');
  }
};

const closeOnEscape = e => {
  if (e.key === 'Escape') {
    e.preventDefault();

    if (props.show) {
      close();
    }
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
  return {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
  }[props.maxWidth];
});
</script>

<template>
  <Teleport to="body">
    <div v-show="show" class="modal m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent" ref="dialog">
      <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 print:static" scroll-region>
        <transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-show="show" class="fixed inset-0 transform backdrop-blur-xs transition-all print:hidden" @click="close(true)">
            <div class="absolute inset-0 bg-gray-100 opacity-75 dark:bg-gray-900" />
          </div>
        </transition>

        <transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-50"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-50"
        >
          <div v-show="show" class="flex min-h-full items-center justify-center sm:px-4 lg:px-0 print:items-start sm:print:items-start">
            <div
              v-show="show"
              :class="[
                { 'overflow-hidden': !overflow },
                round ? 'rounded-2xl' : 'rounded-lg',
                transparent ? 'max-w-fit bg-transparent' : 'bg-white shadow-xl dark:bg-gray-800 ' + maxWidthClass,
              ]"
              class="relative w-full transform transition-all sm:mx-auto print:shadow-none sm:print:w-full sm:print:max-w-full"
            >
              <div v-if="closeable" class="absolute end-0 top-0 pe-4 pt-4 print:hidden">
                <button
                  type="button"
                  @click="close(false)"
                  class="text-mute rounded-md hover:text-gray-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:hover:text-gray-300"
                >
                  <span class="sr-only">{{ $t('Close') }}</span>
                  <Icon name="x" size="size-6" />
                </button>
              </div>
              <slot v-if="showSlot" />
            </div>
          </div>
        </transition>
      </div>
    </div>
  </Teleport>
</template>
