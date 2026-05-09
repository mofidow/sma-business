<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

import { axios } from '@/Core';
import { Modal } from '@/Components/Jet';
import { Input, LoadingButton } from '@/Components/Common';

defineProps(['action']);

const page = usePage();
const loading = ref(false);
const pin_code = ref(null);

const proxyShow = computed({
  get() {
    if (page.props.ask_pin_code) {
      document.getElementById('pin_code-input')?.focus();
    }
    return page.props.ask_pin_code ? true : false;
  },

  set(val) {
    page.props.ask_pin_code = val;
  },
});

async function verifyPinCode() {
  console.log('Verifying pin code...', pin_code.value);
  loading.value = true;
  axios
    .post(route('verify.pin_code'), { pin_code: pin_code.value })
    .then(response => {
      if (response.data.success) {
        page.props.ask_pin_code = false;
        pin_code.value = null;
        if (page.props.pin_action) {
          page.props.pin_action();
        }
      } else {
        console.error('Pin code verification failed:', response.data.message);
      }
    })
    .catch()
    .finally(() => (loading.value = false));
}
</script>

<template>
  <Modal :show="proxyShow" maxWidth="sm" :closeable="true" @close="() => (proxyShow = false)">
    <div class="print:hidden">
      <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Pin Code Required') }}
        </h3>
        <p class="mt-1 text-sm">{{ $t('Please type the pin code to perform this action.') }}</p>
      </div>

      <div class="rounded-b-lg bg-gray-100 p-6 dark:bg-gray-800">
        <form @submit.prevent="verifyPinCode" autocomplete="off" class="flex flex-col items-stretch gap-4">
          <Input keyboard type="password" v-model="pin_code" id="pin_code-input" />
          <LoadingButton class="justify-center" :loading="loading">{{ $t('Submit') }}</LoadingButton>
        </form>
      </div>
    </div>
  </Modal>
</template>
