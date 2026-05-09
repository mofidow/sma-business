<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

import { $address, $extras } from '@/Core';
import { InputError, SecondaryButton } from '@/Components/Jet';
import {
  Attachments,
  Button,
  CustomFields,
  DateInput,
  FileInput,
  Input,
  Loading,
  LoadingButton,
  Textarea,
  Toggle,
} from '@/Components/Common';

const emits = defineEmits(['close', 'done', 'address']);
const props = defineProps(['current', 'countries', 'customer', 'sale_id', 'custom_fields', 'get_addresses']);

const loading = ref(true);
const address = ref(null);
const addresses = ref(null);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  attachments: null,
  reference: props.current?.reference,
  details: props.current?.details,
  sale_id: props.current?.sale_id || props.sale_id,
  customer_id: props.current?.customer_id || props.customer?.id,
  address_id: props.current?.address_id || (props.customer?.addresses?.length ? props.customer.addresses[0].id : null),
  received_by: props.current?.received_by,
  delivered_by: props.current?.delivered_by,
  delivered: props.current?.delivered == 1,
  delivered_at: props.current?.delivered_at,
  date: dayjs(props.current?.date).format('YYYY-MM-DD'),

  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

watch(
  () => props.get_addresses,
  async v => {
    if (v) {
      await getCustomer();
    }
  }
);

onMounted(async () => {
  if (!props.countries) {
    await axios.post(route('search.countries')).then(res => {
      props.countries = res.data;
    });
  }

  if (props.customer?.id) {
    await getCustomer();
  }

  if (props.current && addresses.value?.length) {
    address.value = addresses.value?.find(a => a.id == props.current.address_id);
    addresses.value = addresses.value;
  }

  loading.value = false;
});

const close = () => {
  form.reset();
  emits('close');
};

async function getCustomer() {
  await axios.get(route('customers.show', { customer: props.customer.id, with: 'addresses' })).then(res => {
    addresses.value = res.data.addresses;
    if (!form.address_id) {
      if (props.current?.address_id) {
        address.value = addresses.value?.find(a => a.id == props.current.address_id);
      }
    } else {
      address.value = addresses.value?.length ? addresses.value[0] : null;
      form.address_id = address.value?.id;
    }
  });
}

function handleSubmit() {
  form.post(props.current?.id ? route('deliveries.update', props.current.id) : route('deliveries.store'), {
    onSuccess: () => {
      form.reset();
      emits('done');
      emits('close');
    },
  });
}
</script>

<template>
  <div v-if="loading" class="relative h-64">
    <Loading />
  </div>
  <template v-else>
    <form @submit.prevent="handleSubmit">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="sm:flex sm:items-baseline sm:justify-between">
          <div class="sm:w-0 sm:flex-1">
            <h1 class="text-focus text-base font-semibold">
              {{ current?.id ? $t('Edit {x}', { x: $t('Delivery') }) : $t('Add {x}', { x: $t('Delivery') }) }}
            </h1>
            <p class="text-mute mt-1 truncate text-sm">
              {{
                $t('Please fill the form below to {action} {record}.', {
                  record: $t('delivery'),
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

        <!-- Address -->
        <div class="col-span-6">
          <fieldset v-if="addresses && addresses.length" class="-space-y-px rounded-md bg-white dark:bg-gray-950">
            <label
              v-for="a in addresses"
              :key="a.id"
              :value="a.id"
              :aria-label="a.name"
              :aria-description="$address(a)"
              class="group flex cursor-pointer border border-gray-200 p-4 first:rounded-tl-md first:rounded-tr-md last:rounded-br-md last:rounded-bl-md focus:outline-hidden has-checked:relative has-checked:border-primary-200 has-checked:bg-primary-50 dark:border-gray-700 dark:has-checked:border-primary-800 dark:has-checked:bg-primary-950"
            >
              <input
                type="radio"
                :value="a.id"
                v-model="form.address_id"
                class="relative mt-0.5 size-4 shrink-0 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-primary-600 checked:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 dark:checked:border-primary-400 dark:checked:bg-primary-400 forced-colors:appearance-auto forced-colors:before:hidden"
              />
              <div class="ms-3 flex flex-col">
                <span
                  class="text-focus block text-sm font-medium group-has-checked:text-primary-900 dark:group-has-checked:text-primary-100"
                  >{{ a.name }}</span
                >
                <span class="block text-sm group-has-checked:text-primary-700 dark:group-has-checked:text-primary-300">{{
                  $address(a)
                }}</span>
                <div class="text-sm" v-if="a?.phone">{{ $t('Phone') }}: {{ a?.phone }}</div>
                <div class="text-sm" v-if="a?.email">{{ $t('Email') }}: {{ a.email }}</div>
              </div>
            </label>
          </fieldset>
          <Button type="button" @click="$emit('address')" class="mt-2">
            {{ $t('Add Address') }}
          </Button>
          <InputError :message="$page.props.errors.address_id" class="mt-2" />
        </div>
        <!-- <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          valueKey="id"
          id="address_id"
          labelKey="name"
          :searchable="true"
          :label="$t('Address')"
          v-model="form.address_id"
          :suggestions="customer.addresses"
          :error="$page.props.errors.address_id"
          @change="
            () => {
              address = customer.addresses.find(a => a.id == form.address_id);
              saveForm;
            }
          "
        />
      </div> -->

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

        <!-- Address -->
        <!-- <div v-if="form.address_id" class="col-span-full">
          <div class="font-semibold text-lg">{{ address?.name }}</div>
          <div class="text-sm">{{ $address(address) }}</div>
          <div class="text-sm" v-if="address?.phone">{{ $t('Phone') }}: {{ address?.phone }}</div>
          <div class="text-sm" v-if="address?.email">{{ $t('Email') }}: {{ address.email }}</div>
        </div> -->

        <div class="col-span-full flex flex-col gap-6">
          <CustomFields :errors="form.errors" :custom_fields="custom_fields" :extra_attributes="form.extra_attributes" />
          <Textarea :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
          <Toggle id="delivered" :label="$t('Mark as delivered')" v-model="form.delivered" />
        </div>

        <template v-if="form.delivered">
          <div class="col-span-6 sm:col-span-3">
            <DateInput time id="delivered_at" :label="$t('Delivered at')" v-model="form.delivered_at" :error="form.errors.delivered_at" />
          </div>
          <!-- Delivered by -->
          <div class="col-span-6 sm:col-span-3">
            <Input
              id="delivered_by"
              @change="saveForm"
              :label="$t('Delivered by')"
              v-model="form.delivered_by"
              :error="form.errors.delivered_by"
            />
          </div>

          <!-- Received by -->
          <div class="col-span-6 sm:col-span-3">
            <Input
              id="received_by"
              @change="saveForm"
              :label="$t('Received by')"
              v-model="form.received_by"
              :error="form.errors.received_by"
            />
          </div>
        </template>
      </div>

      <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
        <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

        <LoadingButton class="ms-3" :loading="form.processing">
          {{ $t('Save') }}
        </LoadingButton>
      </div>
    </form>
  </template>
</template>
