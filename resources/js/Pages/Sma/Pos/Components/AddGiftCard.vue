<script setup>
import dayjs from 'dayjs';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import { $random } from '@/Core';
import { Modal } from '@/Components/Jet';
import { AutoComplete, DateInput, Input, LoadingButton } from '@/Components/Common';

const { t } = useI18n({});
defineProps(['show']);
const emit = defineEmits(['close']);

const gist_card_form = useForm({ number: null, amount: null, customer_id: null, expiry_date: dayjs().add(2, 'year').format('YYYY-MM-DD') });

async function addGiftCard() {
  gist_card_form.errors = {};
  if (!gist_card_form.number) {
    gist_card_form.errors.number = t('The gift card number is required.');
  }
  if (!gist_card_form.amount) {
    gist_card_form.errors.amount = t('The gift card amount is required.');
  }

  if (!Object.keys(gist_card_form.errors).length) {
    const item = {
      taxes: null,
      quantity: 1,
      type: 'Gift Card',
      name: t('Gift Card'),
      code: gist_card_form.number,
      price: gist_card_form.amount,
      net_price: gist_card_form.amount,
      unit_price: gist_card_form.amount,
      customer_id: gist_card_form.customer_id,
      expiry_date: gist_card_form.expiry_date,
    };
    gist_card_form.reset();
    emit('close', item);
  }
}
</script>

<template>
  <Modal :show="show" maxWidth="xl" :closeable="true" :overflow="true" :round="$page.props.settings?.pos_design == 'Modern'">
    <div class="transition-all" :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-2xl' : 'rounded-lg'">
      <div class="relative border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <div class="absolute end-0 top-0 flex items-center gap-x-4 pe-4 pt-4">
          <button
            type="button"
            @click="emit('close')"
            class="rounded-md text-gray-400 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden"
          >
            <span class="sr-only">{{ $t('Close') }}</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div>
          <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
            {{ $t('Sell Gift Card') }}
          </h3>
          <p class="mt-1 text-sm">{{ $t('Please add card details below.') }}</p>
        </div>
      </div>

      <form @submit.prevent="addGiftCard" autocomplete="off">
        <div class="bg-gray-100 dark:bg-gray-800">
          <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-6">
            <div class="col-span-6 sm:col-span-3">
              <Input
                keyboard
                autofocus
                :label="t('Number')"
                action-text="Generate"
                v-model="gist_card_form.number"
                :error="gist_card_form.errors.number"
                :action="() => (gist_card_form.number = $random(100000000000, 9999999999999999))"
              />
            </div>
            <div class="col-span-6 sm:col-span-3">
              <Input
                keyboard
                type="number"
                :label="t('Amount Value')"
                v-model="gist_card_form.amount"
                :error="gist_card_form.errors.amount"
              />
            </div>
            <div class="col-span-6 sm:col-span-3">
              <AutoComplete
                keyboard
                :json="true"
                valueKey="id"
                labelKey="company"
                @change="saveForm"
                :searchable="true"
                id="gc_customer_id"
                :label="t('Customer')"
                v-model="gist_card_form.customer_id"
                :suggestions="route('search.customers')"
                :error="gist_card_form.errors.customer_id"
              />
            </div>
            <div class="col-span-6 sm:col-span-3">
              <DateInput :label="$t('Expiry Date')" v-model="gist_card_form.expiry_date" :error="gist_card_form.errors.expiry_date" />

              <!-- <Input type="date" v-model="gist_card_form.expiry_date" :error="gist_card_form.errors.expiry_date" :label="t('Expiry Date')" /> -->
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end border-t border-gray-200 px-6 py-4 dark:border-gray-700">
          <button type="button" @click="emit('close')" class="link me-4">
            {{ $t('Cancel') }}
          </button>
          <LoadingButton class="justify-center" :disabled="gist_card_form.processing" :loading="gist_card_form.processing">{{
            $t('Add to Order')
          }}</LoadingButton>
        </div>
      </form>
    </div>
  </Modal>
</template>
