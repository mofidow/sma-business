<script setup>
import dayjs from 'dayjs';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { $random } from '@/Core';
import { Modal, SecondaryButton } from '@/Components/Jet';
import CustomerForm from '@/Pages/Sma/People/Customer/Form.vue';
import { AutoComplete, CheckBox, DateInput, Input, LoadingButton, Textarea } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'countries', 'customer_fields']);

const customer = ref(null);
const add_customer = ref(false);
const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  number: props.current?.number,
  details: props.current?.details,
  customer_id: props.current?.customer_id,
  award_points: props.current?.award_points,
  use_award_points: props.current?.use_award_points == 1,
  amount: props.current ? Number(props.current.amount) : null,
  expiry_date: props.current ? props.current.expiry_date : dayjs().add(2, 'year').format('YYYY-MM-DD'),
});

if (props.current?.customer) {
  customer.value = props.current.customer;
}

const close = () => {
  form.reset();
  emits('close');
};

function generate() {
  form.number = $random(100000000000, 9999999999999999);
}

function handleSubmit() {
  if (form.use_award_points && Number(form.award_points || 0) < 1) {
    form.errors.award_points = 'Award points must be greater than or equal to 1.';
    return false;
  }
  if (form.use_award_points && customer.value && Number(form.award_points) > Number(customer.value.points)) {
    form.errors.award_points = 'Award points cannot be more than customer points.';
    return false;
  }
  form.post(props.current?.id ? route('gift_cards.update', props.current.id) : route('gift_cards.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Gift Card') }) : $t('Add {x}', { x: $t('Gift Card') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('gift card'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Number -->
      <div class="col-span-6 sm:col-span-3">
        <Input
          autofocus
          :action="generate"
          actionText="Generate"
          :label="$t('Number')"
          v-model="form.number"
          :error="$page.props.errors.number"
        />
      </div>
      <!-- Amount -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="amount" type="number" :label="$t('Amount')" v-model="form.amount" :readonly="current?.id" :error="form.errors.amount" />
      </div>

      <!-- Date -->
      <div class="col-span-6 sm:col-span-3">
        <DateInput type="date" id="expiry_date" :label="$t('Expiry Date')" v-model="form.expiry_date" :error="form.errors.expiry_date" />
      </div>
      <!-- Customer -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          valueKey="id"
          id="customer_id"
          labelKey="company"
          :searchable="true"
          :label="$t('Customer')"
          v-model="form.customer_id"
          @change="e => (customer = e)"
          :action="() => (add_customer = true)"
          :error="$page.props.errors.customer_id"
          :suggestions="route('search.customers')"
          :action-text="$t('Add {x}', { x: $t('Customer') })"
        />
      </div>

      <!-- Details -->
      <div class="col-span-full">
        <Textarea :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
      </div>

      <!-- Award Points -->
      <div
        class="col-span-full"
        v-if="
          $page.props.settings.loyalty?.customer?.spent && $page.props.settings.loyalty?.customer?.points && customer && customer.points > 0
        "
      >
        <CheckBox
          :disabled="current?.id"
          :label="$t('Use Award Points')"
          v-model:checked="form.use_award_points"
          :error="$page.props.errors.use_award_points"
        />
        <div v-if="form.use_award_points" class="mt-6">
          <Input
            :min="1"
            type="number"
            id="award_points"
            :max="customer.points"
            :readonly="current?.id"
            :label="$t('Award Points')"
            v-model="form.award_points"
            :error="form.errors.award_points"
          />
          <div class="mt-1 text-sm font-bold">{{ $t('Available Points') }}: {{ customer.points }}</div>
        </div>
      </div>
    </div>

    <div class="flex flex-row justify-end rounded-b-lg bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>

  <Modal :show="add_customer" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="add_customer = false">
    <CustomerForm
      :countries="countries"
      :custom_fields="customer_fields"
      @close="add_customer = false"
      @done="
        e => {
          form.customer_id = e?.id;
          add_customer = false;
        }
      "
    />
  </Modal>
</template>
