<script setup>
import { notify } from 'notiwind';
import { useI18n } from 'vue-i18n';
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

import { axios } from '@/Core';
import { Modal } from '@/Components/Jet';
import { Input, LoadingButton, Textarea } from '@/Components/Common';

const { t } = useI18n({});

defineProps(['show']);
const emit = defineEmits(['close']);

const loading = ref(false);
const closing = ref(false);
const register = ref(null);
const closeRegister = ref(false);

const registerForm = {
  note: '',
  cash_submitted: null,
  cash_in_register: null,
  cc_payments_submitted: null,
  stripe_payments_submitted: null,
  other_payments_submitted: null,
};

onMounted(() => {
  viewRegisterDetails();
});

async function viewRegisterDetails() {
  loading.value = true;
  await axios
    .get(route('pos.register.details'))
    .then(res => {
      register.value = res.data.register;
      if (register.value) {
        registerForm.cash_submitted = Number(register.value.cash_payments || 0);
        registerForm.cash_in_register = Number(register.value.cash_in_hand || 0);
        registerForm.cc_payments_submitted = Number(register.value.cc_payments || 0);
        registerForm.stripe_payments_submitted = Number(register.value.other_payments || 0);
        registerForm.other_payments_submitted = Number(register.value.stripe_payments || 0);
      }
    })
    .finally(() => (loading.value = false));
}

async function closeRegisterNow() {
  closing.value = true;
  axios
    .put(route('pos.register.close'), registerForm)
    .then(() => {
      notify({
        group: 'main',
        type: 'success',
        title: 'Success!',
        text: t('The register has been closed!'),
      });
      emit('close');
      closing.value = false;
      router.visit(route('dashboard'));
    })
    // .catch(err => (registerFromErrors.value = err.response?.data?.errors))
    .finally(() => (closing.value = false));
}
</script>

<template>
  <Modal :show="show" maxWidth="xl" :closeable="true" @close="emit('close')" :round="$page.props.settings?.pos_design == 'Modern'">
    <div class="transition-all" :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-2xl' : 'rounded-lg'">
      <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <div class="absolute end-8 top-0 flex items-center gap-x-4 pe-6 pt-4">
          <button
            type="button"
            v-if="!loading"
            @click="() => (closeRegister = true)"
            class="rounded-md text-gray-500 hover:text-gray-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-200"
          >
            <span class="sr-only">{{ $t('Close Register') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 256 256">
              <path
                d="M208,40H48A24,24,0,0,0,24,64V176a24,24,0,0,0,24,24H208a24,24,0,0,0,24-24V64A24,24,0,0,0,208,40Zm8,136a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V64a8,8,0,0,1,8-8H208a8,8,0,0,1,8,8Zm-48,48a8,8,0,0,1-8,8H96a8,8,0,0,1,0-16h64A8,8,0,0,1,168,224ZM157.66,106.34a8,8,0,0,1-11.32,11.32L136,107.31V152a8,8,0,0,1-16,0V107.31l-10.34,10.35a8,8,0,0,1-11.32-11.32l24-24a8,8,0,0,1,11.32,0Z"
              ></path>
            </svg>
          </button>
        </div>
        <div>
          <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
            {{ closeRegister ? $t('Close Register') : $t('Register Details') }}
          </h3>
          <p class="mt-1 text-sm">{{ $t('Please view the register details below.') }}</p>
        </div>
      </div>

      <div class="bg-gray-100 dark:bg-gray-800">
        <div v-if="loading" class="relative h-80">
          <Loading />
        </div>
        <div v-else-if="register">
          <div class="">
            <dl class="divide-y divide-gray-100 dark:divide-gray-700">
              <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                <dt class="text-sm leading-6 font-medium">{{ $t('Opened at') }}</dt>
                <dd class="text-sm leading-6 sm:col-span-2 sm:mt-0">{{ $datetime(register.created_at) }}</dd>
              </div>
              <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm leading-6 font-medium">{{ $t('Cash in hand') }}</dt>
                <dd class="flex items-center gap-x-2 text-sm leading-6 sm:col-span-2 sm:mt-0">
                  <div class="w-1/3">{{ $number(register.cash_in_hand) }}</div>
                  <div class="-my-1 flex-1">
                    <Input
                      keyboard
                      label=""
                      size="sm"
                      v-if="closeRegister"
                      :placeholder="t('Cash in register')"
                      v-model="registerForm.cash_in_register"
                    />
                  </div>
                </dd>
              </div>
              <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                <dt class="text-sm leading-6 font-medium">{{ $t('Total Sales') }}</dt>
                <dd class="text-sm leading-6 sm:col-span-2 sm:mt-0">{{ $number(register.sales_sum_grand_total) }}</dd>
              </div>
              <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm leading-6 font-medium">{{ $t('Expenses') }}</dt>
                <dd class="text-sm leading-6 sm:col-span-2 sm:mt-0">{{ $number(register.expenses_sum_amount) }}</dd>
              </div>
              <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                <dt class="text-sm leading-6 font-medium">{{ $t('Purchases') }}</dt>
                <dd class="text-sm leading-6 sm:col-span-2 sm:mt-0">{{ $number(register.purchases_sum_grand_total) }}</dd>
              </div>
              <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm leading-6 font-medium">{{ $t('Cash Amount') }}</dt>
                <dd class="flex items-center gap-x-2 text-sm leading-6 sm:col-span-2 sm:mt-0">
                  <div class="w-1/3">{{ $number(register.cash_payments) }}</div>
                  <div class="-my-1 flex-1">
                    <Input
                      keyboard
                      label=""
                      size="sm"
                      v-if="closeRegister"
                      :placeholder="t('Cash Submitted')"
                      v-model="registerForm.cash_submitted"
                    />
                  </div>
                </dd>
              </div>
              <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                <dt class="text-sm leading-6 font-medium">{{ $t('Credit Card Amount') }}</dt>
                <dd class="flex items-center gap-x-2 text-sm leading-6 sm:col-span-2 sm:mt-0">
                  <div class="w-1/3">{{ $number(register.cc_payments) }}</div>
                  <div class="-my-1 flex-1">
                    <Input
                      keyboard
                      label=""
                      size="sm"
                      v-if="closeRegister"
                      :placeholder="t('Credit Card Value')"
                      v-model="registerForm.cc_payments_submitted"
                    />
                  </div>
                </dd>
              </div>
              <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm leading-6 font-medium">{{ $t('Stripe Terminal Amount') }}</dt>
                <dd class="flex items-center gap-x-2 text-sm leading-6 sm:col-span-2 sm:mt-0">
                  <div class="w-1/3">{{ $number(register.stripe_payments) }}</div>
                  <div class="-my-1 flex-1">
                    <Input
                      keyboard
                      label=""
                      size="sm"
                      v-if="closeRegister"
                      :placeholder="t('Stripe Payments Value')"
                      v-model="registerForm.stripe_payments_submitted"
                    />
                  </div>
                </dd>
              </div>
              <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                <dt class="text-sm leading-6 font-medium">{{ $t('Other Payment Amount') }}</dt>
                <dd class="flex items-center gap-x-2 text-sm leading-6 sm:col-span-2 sm:mt-0">
                  <div class="w-1/3">{{ $number(register.other_payments) }}</div>
                  <div class="-my-1 flex-1">
                    <Input
                      keyboard
                      label=""
                      size="sm"
                      v-if="closeRegister"
                      :placeholder="t('Other Payments Value')"
                      v-model="registerForm.other_payments_submitted"
                    />
                  </div>
                </dd>
              </div>
              <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm leading-6 font-medium">{{ $t('Gift Card Amount') }}</dt>
                <dd class="flex items-center gap-x-2 text-sm leading-6 sm:col-span-2 sm:mt-0">
                  <div class="w-1/3">{{ $number(register.gift_card_payments) }}</div>
                  <div class="-my-1 flex-1">
                    <!-- <Input
                     keyboard
                      label=""
                      size="sm"
                      v-if="closeRegister"
                      :placeholder="t('Cash Submitted')"
                      v-model="registerForm.cash_submitted"
                    /> -->
                  </div>
                </dd>
              </div>
              <template v-if="closeRegister">
                <div class="bg-gray-50 px-6 py-2 pb-3 transition-all sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                  <Textarea keyboard label="" :placeholder="t('Any Notes')" v-model="registerForm.note" />
                </div>
                <div class="bg-gray-50 px-6 py-2 transition-all sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
                  <dt class="text-sm leading-6 font-medium"></dt>
                  <dd class="flex items-center justify-end gap-x-4 text-sm leading-6 sm:col-span-2 sm:mt-0">
                    <button type="button" class="rounded-xs p-1" @click="closeRegister = false">{{ $t('Cancel') }}</button>
                    <LoadingButton type="button" @click="closeRegisterNow" :loading="closing">{{ $t('Close Register') }}</LoadingButton>
                  </dd>
                </div>
              </template>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>
