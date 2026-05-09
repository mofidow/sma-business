<script setup>
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton } from '@/Components/Common';

const emit = defineEmits(['update:modelValue']);
const props = defineProps({ modelValue: Boolean, settings: Object });

const show = ref(false);
const showGuide = ref(false);
const form = useForm({
  type: 'price',
  length: null,
  flag_length: null,
  code_start: null,
  code_length: null,
  price_start: null,
  price_length: null,
  price_divide_by: null,
  weight_start: null,
  weight_length: null,
  weight_divide_by: null,
});

const proxyModelValue = computed({
  get() {
    // setTimeout(() => {
    // show.value = props.modelValue;
    // }, 10);
    return props.modelValue;
  },

  set(val) {
    show.value = val;
    setTimeout(() => {
      emit('update:modelValue', val);
    }, 500);
  },
});

watch(
  () => proxyModelValue.value,
  () => {
    if (proxyModelValue.value) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = null;
    }
  }
);

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape);

  Object.keys(props.settings.scale_barcode).forEach(key => {
    form[key] = props.settings.scale_barcode[key];
  });
});

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.body.style.overflow = null;
});

const closeOnEscape = e => {
  if (e.key === 'Escape') {
    e.preventDefault();

    if (proxyModelValue.value) {
      proxyModelValue.value = false;
    }
  }
};

function handleSubmit() {
  form.post(route('settings.barcode'), {
    onSuccess: () => {
      proxyModelValue.value = false;
    },
  });
}
</script>

<template>
  <div v-if="proxyModelValue" class="relative">
    <div class="fixed inset-0 z-20 overflow-hidden">
      <div class="absolute inset-0 overflow-hidden">
        <Transition name="fade">
          <div v-show="show" class="fixed inset-0 transform backdrop-blur-xs transition-all" @click="() => (proxyModelValue = false)">
            <div class="absolute inset-0 bg-gray-100 opacity-75 dark:bg-gray-900" />
          </div>
        </Transition>
        <div class="pointer-events-none fixed inset-y-0 end-0 flex max-w-full ps-10 sm:ps-16">
          <Transition name="drawer-slide">
            <div v-if="show" class="pointer-events-auto z-10 w-screen max-w-md">
              <form @submit.prevent="handleSubmit" class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl dark:bg-gray-950">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                  <div class="flex items-start justify-between">
                    <div>
                      <h2 class="text-focus text-base font-semibold">{{ $t('Scale Barcode Settings') }}</h2>
                      <p class="text-mute mt-1 text-xs">{{ $t('Please configure the settings to parse scale barcodes.') }}</p>
                    </div>
                    <div class="ms-3 flex h-7 items-center">
                      <button
                        type="button"
                        @click="() => (showGuide = true)"
                        class="relative me-2 rounded-md hover:text-gray-500 focus:ring-2 focus:ring-primary-500"
                      >
                        <span class="absolute -inset-2.5"></span>
                        <span class="sr-only">{{ $t('Guide') }}</span>
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke-width="1.5"
                          stroke="currentColor"
                          class="size-6"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"
                          />
                        </svg>
                      </button>
                      <button
                        type="button"
                        @click="() => (proxyModelValue = false)"
                        class="relative rounded-md hover:text-gray-500 focus:ring-2 focus:ring-primary-500"
                      >
                        <span class="absolute -inset-2.5"></span>
                        <span class="sr-only">{{ $t('Close') }}</span>
                        <svg
                          class="h-6 w-6"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke-width="1.5"
                          stroke="currentColor"
                          aria-hidden="true"
                          data-slot="icon"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="flex flex-1 flex-col gap-6 overflow-y-auto p-6">
                  <div v-if="showGuide" class="relative">
                    <button
                      type="button"
                      @click="() => (showGuide = false)"
                      class="absolute -top-2 -right-2 rounded-md bg-gray-100 p-1 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 dark:bg-gray-700"
                    >
                      <span class="absolute -inset-2.5"></span>
                      <span class="sr-only">{{ $t('Hide') }}</span>
                      <svg
                        class="size-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                        data-slot="icon"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                    <img src="/img/scale_barcode.png" alt="" class="rounded-md" />
                  </div>
                  <div>
                    <AutoComplete
                      :json="true"
                      id="form.type"
                      :searchable="false"
                      v-model="form.type"
                      :error="form.errors.type"
                      :label="$t('Barcode Contains')"
                      :suggestions="[
                        { value: 'price', label: $t('Price') },
                        { value: 'weight', label: $t('Quantity/Weight') },
                      ]"
                    />
                  </div>

                  <div>
                    <Input v-model="form.length" :label="$t('Barcode Length')" :error="form.errors.length" />
                  </div>
                  <div>
                    <Input v-model="form.flag_length" :label="$t('Flag Characters Length')" :error="form.errors.flag_length" />
                  </div>
                  <div>
                    <Input v-model="form.code_start" :label="$t('Item Code Start Position')" :error="form.errors.code_start" />
                  </div>
                  <div>
                    <Input v-model="form.code_length" :label="$t('Item Code Characters Length')" :error="form.errors.code_length" />
                  </div>

                  <template v-if="form.type == 'weight'">
                    <div>
                      <Input v-model="form.weight_start" :label="$t('Weight Start Position')" :error="form.errors.weight_start" />
                    </div>
                    <div>
                      <Input v-model="form.weight_length" :label="$t('Weight Characters Length')" :error="form.errors.weight_length" />
                    </div>
                    <div>
                      <Input v-model="form.weight_divide_by" :label="$t('Weight Divide by')" :error="form.errors.weight_divide_by" />
                    </div>
                  </template>
                  <template v-else>
                    <div>
                      <Input v-model="form.price_start" :label="$t('Price Start Position')" :error="form.errors.price_start" />
                    </div>
                    <div>
                      <Input v-model="form.price_length" :label="$t('Price Characters Length')" :error="form.errors.price_length" />
                    </div>
                    <div>
                      <Input v-model="form.price_divide_by" :label="$t('Price Divide by')" :error="form.errors.price_divide_by" />
                    </div>
                  </template>

                  <div class="flex items-center justify-between">
                    <SecondaryButton class="me-3" @click="() => (proxyModelValue = false)">
                      {{ $t('Cancel') }}
                    </SecondaryButton>
                    <LoadingButton :class="{ 'opacity-25': form.processing }" :loading="form.processing">
                      {{ $t('Save') }}
                    </LoadingButton>
                  </div>
                </div>
              </form>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </div>
</template>
