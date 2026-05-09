<script setup>
import { ref } from 'vue';
import { discount_keypress } from '@/Core';
import { Input, LoadingButton } from '@/Components/Common';

defineProps(['show']);
const emit = defineEmits(['close']);

const discount = ref(null);
</script>

<template>
  <div>
    <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
      <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
        {{ $t('Order Discount') }}
      </h3>
      <p class="mt-1 text-sm">{{ $t('Please fill the discount below to apply to form.') }}</p>
    </div>

    <div class="m-6 flex flex-col gap-6">
      <div>
        <Input keyboard v-model="discount" @keypress="discount_keypress" id="order-discount" label="Discount (5 or 5%)" />
      </div>
      <p class="-mt-3">{{ $t('This discount will override any products discount.') }}</p>
      <LoadingButton class="justify-center" @click="emit('apply', discount)">{{ $t('Apply') }}</LoadingButton>
    </div>
  </div>
</template>
