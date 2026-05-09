<script setup>
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';
import { notify } from 'notiwind';
import { useI18n } from 'vue-i18n';
import debounce from 'lodash/debounce';

import { axios, $decimal } from '@/Core';
import { Modal, SecondaryButton } from '@/Components/Jet';
import { vKeyboard } from '@/Pages/Sma/Pos/Components/Keyboard';
import { AutoComplete, CheckBox, Input, LoadingButton, Textarea, CustomFields } from '@/Components/Common';

const { t } = useI18n({});
const emit = defineEmits(['close']);
const props = defineProps(['show', 'form', 'saveForm', 'custom_fields', 'payment_fields']);

const saving = ref(false);
const totalPaymentsAmount = ref(0);
const cash_notes = ref(props.form.notes || []);

watch(cash_notes, notes => {
  props.form.notes = notes;
  props.form.tendered = notes.reduce((a, i) => Number($decimal(i)) + a, 0);
  props.form.change_returned = $decimal(
    props.form.tendered - (props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) + Number(props.form.rounding || 0))
  );
  props.form.payable_amount = $decimal(props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) + (props.form.rounding || 0));
  props.form.payments[0].amount = $decimal(
    props.form.tendered < props.form.payable_amount ? props.form.tendered : props.form.payable_amount
  );
  props.saveForm();
});

watch(
  () => props.show,
  show => {
    if (show) {
      if (!props.form.payments || !Object.keys(props.form.payments).length) {
        props.form.payments = [
          { amount: $decimal(props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)), method: 'Cash', method_data: {} },
        ];
      } else {
        props.form.payments[0].amount = $decimal(Number(props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)));
        props.form.payments[0].method = 'Cash';
      }
      calcTotalPaymentsAmount();
    }
  }
);

function closeNow() {
  emit('close');
  clearNotes();
}

function clearNotes() {
  cash_notes.value = [];
  props.form.tendered = 0;
  props.form.change_returned = 0;
}

function addMorePayment() {
  if (!props.form.payments) {
    props.form.payments = [
      { amount: props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0), method: 'Cash', method_data: {} },
    ];
  }
  props.form.payments = [...props.form.payments, { amount: 0, method: 'Cash', method_data: {} }];
}

function checkPayments(re) {
  if (!props.form.payments) {
    return false;
  }

  let payments = props.form.payments.map(p => p);
  payments = payments.filter(p => p.amount > 0 && p.method != 'Stripe Terminal');
  payments = payments.filter(p => (p.method == 'Card Terminal' && !p.cc_slip ? false : true));

  payments = payments.filter(p => {
    if (p.method == 'Gift Card') {
      if (!p.gift_card) {
        return false;
      }
      if (Number(p.gift_card.balance) < Number(p.amount)) {
        return false;
      }
      if (p.gift_card.customer_id && p.gift_card.customer_id != props.form.customer_id) {
        return false;
      }
    }
    return true;
  });

  const total_amount = $decimal(payments.reduce((a, p) => a + Number($decimal(p.amount)), 0));

  if (re) {
    return total_amount;
  }

  return (
    Number(total_amount) ==
    Number($decimal(props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) + Number(props.form.rounding || 0))
  );
}

function calcTotalPaymentsAmount(index = null) {
  totalPaymentsAmount.value = checkPayments(true);
  if (index !== null) {
    const payment = props.form.payments[index];
    if (payment && payment.method == 'Gift Card') {
      payment.method_data = { ...payment.method_data, gift_card_no: payment.method_data?.gift_card_no || null };
    }
  }
}

const checkGiftCard = debounce((e, index) => {
  if (e.target.value) {
    axios.get(route('gift_cards.details', e.target.value)).then(res => {
      props.form.payments[index].gift_card = res.data;
      if (props.form.payments[index].amount > res.data.balance) {
        notify({
          group: 'main',
          type: 'error',
          title: 'Error!',
          text: t('Gift card balance is not enough.'),
        });

        props.form.payments[index].amount = res.data.balance;
        calcTotalPaymentsAmount();
      }
    });
  }
}, 500);
</script>

<template>
  <Modal :show="show" @close="closeNow" maxWidth="lg" :backdrop="false" :round="$page.props.settings?.pos_design == 'Modern'">
    <div>
      <div class="relative border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <div class="absolute end-0 top-0 hidden pe-4 pt-4 sm:block">
          <button
            type="button"
            @click="closeNow"
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
            {{ $t('Finalize Sale') }}
          </h3>
          <p class="mt-1 text-sm">{{ $t('Please fill the payment details to finalize.') }}</p>
        </div>
      </div>

      <div v-if="form.items && form.items.length" class="mb-6 flex flex-col gap-6">
        <div class="text-base">
          <dl class="divide-y divide-gray-100 dark:divide-gray-700">
            <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
              <dt class="leading-6 font-medium">{{ $t('Total') }}</dt>
              <dd class="mt-1 text-end leading-6 font-bold sm:col-span-2 sm:mt-0">
                {{ $currency(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) }}
              </dd>
            </div>
            <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="leading-6 font-medium">{{ $t('Rounding') }}</dt>
              <dd class="mt-1 text-end leading-6 font-bold sm:col-span-2 sm:mt-0">
                {{ $number(form.rounding || 0) }}
              </dd>
            </div>
            <div class="bg-gray-50 px-6 py-2 text-lg sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
              <dt class="leading-6 font-medium">{{ $t('Payable Amount') }}</dt>
              <dd class="mt-1 text-end leading-6 font-bold sm:col-span-2 sm:mt-0">
                {{ $currency(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) + (form.rounding || 0)) }}
              </dd>
            </div>

            <div class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="leading-6 font-medium">
                {{ $t('Tender Amount') }} <br />
                {{
                  Number(form.tendered || 0) <
                  Number((props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) || 0) + (form.rounding || 0))
                    ? $t('Get') +
                      ' ' +
                      $number(
                        0 -
                          (form.tendered -
                            ((props.form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) || 0) + (form.rounding || 0)))
                      ) +
                      ' ' +
                      $t('more')
                    : ''
                }}
              </dt>
              <dd class="mt-1 flex items-end justify-end text-end leading-6 font-bold sm:col-span-2 sm:mt-0">
                <input
                  v-keyboard
                  id="tender-amount"
                  v-model="form.tendered"
                  @click="e => e.target.select()"
                  @change="
                    () => {
                      form.tendered = $decimal(form.tendered);
                      form.change_returned = $decimal(
                        Number(form.tendered || 0) >
                          Number(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) + (form.rounding || 0)
                          ? form.tendered - (form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) + (form.rounding || 0))
                          : 0
                      );
                      props.saveForm();
                    }
                  "
                  class="-mx-2 rounded-xs border-0 border-b border-gray-200 bg-transparent px-2 py-0.5 text-end focus:ring-0 dark:border-gray-700"
                />
              </dd>
            </div>
            <div class="bg-gray-50 px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4 dark:bg-gray-900/50">
              <dt class="leading-6 font-medium">{{ $t('Return Amount') }}</dt>
              <dd class="mt-1 text-end leading-6 font-bold sm:col-span-2 sm:mt-0">
                {{
                  form.change_returned ||
                  $number(
                    Number(form.tendered || 0) >
                      Number(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) + (form.rounding || 0)
                      ? form.tendered - (form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0) + (form.rounding || 0))
                      : 0
                  )
                }}
              </dd>
            </div>
            <!-- <div v-if="form.booking_id && Number(form.paid) > 0" class="px-6 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="font-medium leading-6">{{ $t('Advance Payment') }}</dt>
              <dd class="mt-1 font-bold text-end leading-6 sm:col-span-2 sm:mt-0">
                {{ $number(form.paid || 0) }}
              </dd>
            </div> -->
          </dl>
        </div>

        <div class="px-6" v-if="$page.props.settings?.quick_cash?.length">
          <div class="flex items-start justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
              <button
                :key="index"
                type="button"
                class="btn-primary relative"
                @click="() => (cash_notes = [...cash_notes, Number(cash.replace(',', '').replace('.', ''))])"
                v-for="(cash, index) in $page.props.settings.quick_cash"
              >
                {{ cash }}
                <div
                  v-if="cash_notes.filter(c => c == Number(cash.replace(',', '').replace('.', ''))).length"
                  class="absolute top-0 -right-2.5 z-10 flex min-h-5 min-w-5 -translate-y-1/2 items-center justify-center rounded-full bg-gray-700 px-1 text-xs text-gray-100 dark:bg-white dark:text-gray-700"
                >
                  {{ cash_notes.filter(c => c == Number(cash.replace(',', '').replace('.', ''))).length }}
                </div>
              </button>
            </div>
            <button type="button" class="btn-danger" @click="clearNotes">
              <Icon name="x" class="h-4 w-4" />
            </button>
          </div>
        </div>
        <template v-for="(payment, index) in form.payments" :key="index">
          <div class="grid grid-cols-2 gap-4 border-t border-gray-200 px-6 pt-6 dark:border-gray-700">
            <div>
              <Input
                keyboard
                type="number"
                :label="$t('Amount')"
                @change="calcTotalPaymentsAmount"
                v-model="form.payments[index].amount"
                :max="Number(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) - Number(form.rounding || 0)"
              />
            </div>
            <div>
              <AutoComplete
                keyboard
                :json="false"
                placement="top"
                :searchable="false"
                :label="$t('Method')"
                v-model="form.payments[index].method"
                @change="calcTotalPaymentsAmount(index)"
                :suggestions="['Cash', 'Gift Card', 'Card Terminal', 'Stripe Terminal', 'Others']"
              />
            </div>
          </div>
          <div v-if="form.payments[index]?.method == 'Gift Card'" class="px-6">
            <Input
              keyboard
              :label="$t('Gift Card Number')"
              @change="calcTotalPaymentsAmount"
              @input="e => checkGiftCard(e, index)"
              v-model="form.payments[index].method_data.gift_card_no"
            />
            <div
              v-if="form.payments[index].gift_card && form.payments[index].method_data.gift_card_no"
              class="mt-3 rounded-md border px-3 py-2 text-sm whitespace-pre-wrap dark:border-gray-700"
            >
              <div>{{ $t('Balance Amount') }}: {{ $number(form.payments[index].gift_card.balance) }}</div>
              <div v-if="form.payments[index].gift_card.customer">
                {{ $t('Customer') }}:
                {{ form.payments[index].gift_card.customer?.company || form.payments[index].gift_card.customer?.name }}
              </div>
            </div>
          </div>
          <div v-if="form.payments[index]?.method == 'Card Terminal'" class="px-6">
            <CheckBox
              @change="calcTotalPaymentsAmount"
              v-model:checked="form.payments[index].cc_slip"
              :label="$t('I have collected payment & saved receipt')"
            />
          </div>
          <div v-if="form.payments[index]?.method == 'Stripe Terminal'" class="px-6 text-sm font-bold text-yellow-500">
            Work in Progress
          </div>

          <div
            v-if="payment_fields.length"
            class="col-span-full -mt-6 flex flex-col gap-6 border-y border-gray-200 px-6 py-6 dark:border-gray-700"
          >
            <CustomFields
              @update="saveForm"
              :errors="form.errors"
              :custom_fields="payment_fields"
              :extra_attributes="form.payments[index].extra_attributes"
            />
          </div>
        </template>

        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
          <div
            class="mb-6 flex items-center justify-center"
            v-if="Number(totalPaymentsAmount) < Number(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0))"
          >
            <SecondaryButton
              type="button"
              @click="addMorePayment()"
              class="flex items-center justify-center rounded-md px-6 py-2 text-sm text-gray-600 hover:text-gray-700"
            >
              <icons name="plus" class="me-1 h-4 w-4"></icons>
              {{ $t('Add another Payment') }}
            </SecondaryButton>
          </div>
          <h4 class="bg-gray-100 px-6 py-2 text-center dark:bg-gray-900">
            {{ $t('Total Payment Amount') }}: <strong>{{ $number(totalPaymentsAmount) }}</strong>
          </h4>
        </div>

        <div
          v-if="custom_fields.length"
          class="col-span-full -mt-6 flex flex-col gap-6 border-y border-gray-200 px-6 py-6 dark:border-gray-700"
        >
          <CustomFields :errors="form.errors" :custom_fields="custom_fields" :extra_attributes="form.extra_attributes" @update="saveForm" />
        </div>

        <div class="px-6">
          <Textarea keyboard label="" v-model="form.details" @change="saveForm" :placeholder="$t('Any sale details')" />
        </div>

        <div class="px-6">
          <LoadingButton
            type="button"
            :loading="saving"
            class="w-full justify-center"
            @click="emit('finalize', form)"
            :disabled="
              saving ||
              ($page.props.settings?.allow_sale_without_payment != 1 &&
                Number(totalPaymentsAmount) !=
                  Number(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) + Number(form.rounding || 0))
            "
          >
            <span class="text-base">{{ $t('Submit') }}</span>
          </LoadingButton>
        </div>
      </div>
      <div v-else class="m-6 flex flex-col gap-4 text-yellow-600">
        {{ $t('Please add at least one item to form.') }}
      </div>
    </div>
  </Modal>
</template>
