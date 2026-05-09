<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { Input, SignaturePad } from '@/Components/Common';
import QuickView from './QuickView.vue';

const { t } = useI18n();

const props = defineProps({
  quotation: { type: Object, required: true },
  hash: { type: String, default: null },
  custom_fields: { type: Array, default: () => [] },
});

const signaturePad = ref(null);
const isAlreadySigned = computed(() => !!props.quotation.signed_at);
const canSign = computed(() => !!props.hash && !isAlreadySigned.value);

const form = useForm({
  signed_by_name: props.quotation.customer?.name ?? '',
  signature: null,
});

const submitSignature = () => {
  if (!form.signature) {
    return;
  }
  form.post(route('quotation.sign', { id: props.quotation.id, hash: props.hash }), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head>
    <title>{{ $t('Quotation') }}</title>
  </Head>

  <div class="p-6">
    <div class="relative mx-auto w-full max-w-3xl rounded-lg border border-gray-200 dark:border-gray-700 print:border-0">
      <QuickView :current="quotation" :fields="custom_fields" :xfetch="true" />

      <!-- Signature Section -->
      <div class="border-t border-gray-200 px-6 pb-6 dark:border-gray-700 print:hidden">
        <!-- Already Signed -->
        <template v-if="isAlreadySigned">
          <div class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $t('Customer Approval') }}</h3>
            <div class="rounded-md border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
              <div class="flex items-start gap-2 text-green-700 dark:text-green-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <div>
                  <div class="text-sm font-medium">{{ $t('Approved by {name}', { name: quotation.signed_by_name }) }}</div>
                  <p class="mt-1 text-xs text-green-600 dark:text-green-500">
                    {{ $datetime(quotation.signed_at) }}
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-4 rounded-md border border-gray-200 bg-white p-3 dark:border-gray-600 dark:bg-gray-50">
              <img :src="quotation.signature" alt="Customer Signature" class="max-h-32 w-auto" />
            </div>
          </div>
        </template>

        <!-- Sign Form -->
        <template v-else-if="canSign">
          <div class="mt-6">
            <h3 class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $t('Approve Quotation') }}</h3>
            <p class="mb-4 text-xs text-gray-500">{{ $t('Sign below to approve and accept this quotation.') }}</p>

            <form @submit.prevent="submitSignature" class="space-y-4">
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">{{ $t('Full Name') }}</label>
                <Input v-model="form.signed_by_name" :placeholder="$t('Enter your full name')" class="w-full" required />
                <p v-if="form.errors.signed_by_name" class="mt-1 text-xs text-red-500">{{ form.errors.signed_by_name }}</p>
              </div>

              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">{{ $t('Signature') }}</label>
                <SignaturePad ref="signaturePad" v-model="form.signature">
                  <template #placeholder>{{ $t('Please draw your signature here') }}</template>
                  <template #clear>{{ $t('Clear') }}</template>
                </SignaturePad>
                <p v-if="form.errors.signature" class="mt-1 text-xs text-red-500">{{ form.errors.signature }}</p>
              </div>

              <button
                type="submit"
                :disabled="form.processing || !form.signature || !form.signed_by_name"
                class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <svg
                  v-if="form.processing"
                  class="mr-2 -ml-1 size-4 animate-spin"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ $t('Approve & Sign') }}
              </button>
            </form>
          </div>
        </template>
      </div>

      <!-- Print: show signature if signed -->
      <div v-if="isAlreadySigned" class="hidden px-6 pb-6 print:block">
        <div class="mt-4 border-t border-gray-300 pt-4">
          <p class="text-xs font-semibold text-gray-600">{{ $t('Approved by') }}: {{ quotation.signed_by_name }}</p>
          <img :src="quotation.signature" alt="Customer Signature" class="mt-2 max-h-24 w-auto" />
        </div>
      </div>
    </div>
  </div>
</template>
