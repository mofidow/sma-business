<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { computed, onMounted, nextTick, ref } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

import { $decimal, $extras, axios } from '@/Core';
import { SecondaryButton } from '@/Components/Jet';
import {
  Attachments,
  AutoComplete,
  CheckBox,
  CustomFields,
  DateInput,
  FileInput,
  Input,
  LoadingButton,
  Textarea,
} from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'customer', 'supplier', 'sale', 'purchase', 'custom_fields']);

const page = usePage();
const gift_card = ref(null);

const defaultMethods = ['Cash', 'Credit Card', 'Gift Card', 'Card Terminal', 'Stripe Terminal', 'Others'];
const paymentMethods = computed(() => {
  const methods = page.props.settings?.payment?.static_payment_methods;
  return [...defaultMethods, ...(methods || [])].map(m => ({ value: m, label: m }));
});

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  attachments: null,
  details: props.current?.details,
  reference: props.current?.reference,
  customer_id: props.current?.customer_id,
  supplier_id: props.current?.supplier_id,
  payment_for: props.current?.payment_for || 'Customer',
  sale_id: props.current ? props.current.sale_id : props.sale?.id,
  purchase_id: props.current ? props.current.purchase_id : props.purchase?.id,
  date: dayjs(props.current?.date).format('YYYY-MM-DD'),
  amount: props.current
    ? $decimal(props.current.amount)
    : props.sale
      ? $decimal(Number(props.sale.grand_total) - Number(props.sale.rounding || 0) - Number(props.sale.paid))
      : null,
  method: props.current ? props.current.method : 'Cash',
  method_data: props.current ? props.current.method_data : {},

  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

if (props.sale) {
  form.payment_for = 'Customer';
  form.customer_id = props.sale.customer_id;
  form.amount = $decimal(props.sale.grand_total - props.sale.rounding - props.sale.paid);
} else if (props.customer) {
  form.payment_for = 'Customer';
  form.customer_id = props.customer.id;
  form.amount = props.customer.balance > 0 ? $decimal(props.customer.balance) : null;
}

if (props.purchase) {
  form.payment_for = 'Supplier';
  form.supplier_id = props.purchase.supplier_id;
  form.amount = $decimal(props.purchase.grand_total - props.purchase.paid);
} else if (props.supplier) {
  form.payment_for = 'Supplier';
  form.supplier_id = props.supplier.id;
  form.amount = props.supplier.balance > 0 ? $decimal(props.supplier.balance) : null;
}

onMounted(async () => {
  checkGiftCard();
  await nextTick();
  document.getElementById('payment-form-amount').focus();
});

const close = () => {
  form.reset();
  emits('close');
};

function checkGiftCard() {
  if (form.method_data?.gift_card_no) {
    axios.get(route('gift_cards.details', form.method_data.gift_card_no)).then(res => (gift_card.value = res.data));
  }
}

function handleSubmit() {
  form
    .transform(data => {
      if (data.payment_for == 'Supplier') {
        data.customer_id = null;
      }
      if (data.payment_for == 'Customer') {
        data.supplier_id = null;
      }
      return data;
    })
    .post(props.current?.id ? route('payments.update', props.current.id) : route('payments.store'), {
      onSuccess: () => {
        form.reset();
        emits('done');
        emits('close');
      },
    });
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
      <div class="sm:flex sm:items-baseline sm:justify-between">
        <div class="sm:w-0 sm:flex-1">
          <h1 class="text-focus text-base font-semibold">
            {{ current?.id ? $t('Edit {x}', { x: $t('Payment') }) : $t('Add {x}', { x: $t('Payment') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('payment'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Date -->
      <div class="col-span-6 sm:col-span-3">
        <DateInput type="date" id="date" @change="saveForm" :label="$t('Date')" v-model="form.date" :error="form.errors.date" />
      </div>
      <!-- Reference -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="reference" @change="saveForm" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
      </div>

      <!-- Payment For -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          id="type"
          :json="true"
          @change="saveForm"
          :searchable="true"
          :label="$t('For')"
          v-model="form.payment_for"
          :error="$page.props.errors.payment_for"
          :suggestions="[
            { value: 'Customer', label: $t('Customer') },
            { value: 'Supplier', label: $t('Supplier') },
          ]"
        />
      </div>

      <template v-if="form.payment_for == 'Customer'">
        <!-- Customer -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            id="customer_id"
            labelKey="company"
            @change="saveForm"
            :searchable="true"
            :label="$t('Customer')"
            v-model="form.customer_id"
            :suggestions="route('search.customers')"
            :error="$page.props.errors.customer_id"
          />
        </div>
      </template>

      <template v-if="form.payment_for == 'Supplier'">
        <!-- Supplier -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            id="supplier_id"
            labelKey="company"
            @change="saveForm"
            :searchable="true"
            :label="$t('Supplier')"
            v-model="form.supplier_id"
            :suggestions="route('search.suppliers')"
            :error="$page.props.errors.supplier_id"
          />
        </div>
      </template>

      <!-- Amount -->
      <div class="col-span-6 sm:col-span-3">
        <Input
          type="number"
          :label="$t('Amount')"
          v-model="form.amount"
          id="payment-form-amount"
          :error="$page.props.errors.amount"
          :readonly="edit && ['Card Terminal', 'Stripe Terminal'].includes(edit.method)"
        />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          placement="top"
          :searchable="false"
          :label="$t('Method')"
          v-model="form.method"
          :suggestions="paymentMethods"
          :error="$page.props.errors.method"
        />
      </div>

      <div v-if="form.method == 'Gift Card'" class="col-span-full">
        <Input
          @change="checkGiftCard"
          :label="$t('Gift Card Number')"
          v-model="form.method_data.gift_card_no"
          :error="$page.props.errors['method_data.gift_card_no'] || null"
        />
        <div
          v-if="gift_card && form.method_data.gift_card_no"
          class="mt-2 rounded-md border border-gray-200 px-3 py-2 text-sm whitespace-pre-wrap dark:border-gray-700"
        >
          <div>{{ $t('Balance Amount') }}: {{ $number(Number(gift_card.balance) + Number(edit ? edit.amount : 0)) }}</div>
          <div v-if="gift_card.customer">{{ $t('Customer') }}: {{ gift_card.customer?.company || gift_card.customer?.name }}</div>
        </div>
      </div>
      <div v-if="form.method == 'Card Terminal'" class="col-span-full">
        <CheckBox
          v-model:checked="form.cc_slip"
          :error="$page.props.errors.cc_slip"
          :label="$t('I have collected payment & saved receipt')"
        />
      </div>
      <div v-if="form.method == 'Stripe Terminal'" class="col-span-full text-center text-sm font-bold text-yellow-500">
        Work in Progress
      </div>

      <!-- Attachments -->
      <div class="col-span-6 sm:col-span-3">
        <FileInput
          multiple
          id="attachments"
          :label="$t('Attachments')"
          v-model="form.attachments"
          :error="form.errors.attachments"
          :accept="$page.props.settings?.attachment_exts || '.jpg,.png,.pdf,.xlsx,.docx,.zip'"
        />
      </div>

      <div v-if="current && current.attachments && current.attachments.length" class="col-span-full">
        <Attachments :attachments="current.attachments || []" />
      </div>

      <div class="col-span-full flex flex-col gap-6">
        <CustomFields :errors="form.errors" :custom_fields="custom_fields" :extra_attributes="form.extra_attributes" />
        <Textarea :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
