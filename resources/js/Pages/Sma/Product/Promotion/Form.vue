<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import { $decimal } from '@/Core/helpers';
import { InputLabel, SecondaryButton } from '@/Components/Jet';
import { AutoComplete, CheckBox, Input, LoadingButton, Textarea } from '@/Components/Common';

const { t } = useI18n();
const emits = defineEmits(['close', 'done']);
const props = defineProps(['current', 'types', 'stores']);
const promo_types = [
  { value: 'simple', label: t('Simple') },
  { value: 'advance', label: t('Advance') },
  { value: 'BXGY', label: t('Buy X get Y (BXGY)') },
  { value: 'SXGD', label: t('Spend X Get Discount (SXGD)') },
];

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  name: props.current?.name,
  type: props.current?.type,
  discount: props.current?.discount ? $decimal(props.current?.discount) : null,
  discount_method: props.current?.discount_method || 'percentage',
  details: props.current?.details,
  products: props.current?.products?.length ? props.current?.products?.map(product => product.id) : null,
  categories: props.current?.categories?.length ? props.current?.categories?.map(category => category.id) : null,
  stores: props.current?.stores?.length ? props.current?.stores?.map(store => store.id) : null,
  amount_to_spend: props.current?.amount_to_spend,
  start_date: props.current?.start_date ? dayjs(props.current.start_date).format('YYYY-MM-DD') : null,
  end_date: props.current?.end_date ? dayjs(props.current.end_date).add(1, 'year').format('YYYY-MM-DD') : null,
  product_id_to_buy: props.current?.product_id_to_buy,
  product_id_to_get: props.current?.product_id_to_get,
  quantity_to_buy: props.current?.quantity_to_buy ? $decimal(props.current?.quantity_to_buy) : null,
  quantity_to_get: props.current?.quantity_to_get ? $decimal(props.current?.quantity_to_get) : null,
  active: props.current?.id ? props.current?.active == 1 : true,
});

function handleSubmit() {
  form.post(props.current?.id ? route('promotions.update', props.current.id) : route('promotions.store'), {
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
            {{ current?.id ? $t('Edit {x}', { x: $t('Promotion') }) : $t('Add {x}', { x: $t('Promotion') }) }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('tax'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Name -->
      <div class="col-span-6 sm:col-span-3">
        <Input id="name" :label="$t('Name')" v-model="form.name" :error="form.errors.name" />
      </div>

      <!-- Type -->
      <div class="col-span-6 sm:col-span-3">
        <AutoComplete
          id="type"
          :json="true"
          :label="$t('Type')"
          :searchable="false"
          v-model="form.type"
          :error="form.errors.type"
          :suggestions="promo_types"
        />
      </div>

      <!-- For language only -->
      <span class="sr-only">
        {{ $t('Simple') }}
        {{ $t('Advance') }}
        {{ $t('BXGY') }}
        {{ $t('SXGD') }}
        {{ $t('Buy X get Y (BXGY)') }}
        {{ $t('Spend X Get Discount (SXGD)') }}
      </span>

      <template v-if="form.type != 'BXGY'">
        <!-- Discount -->
        <div class="col-span-6 sm:col-span-3">
          <!-- <Input type="number" id="discount" :label="$t('Discount')" v-model="form.discount" :error="form.errors.discount" /> -->
          <InputLabel :value="$t('Discount')" class="mb-1" />
          <div
            class="flex items-center rounded-md bg-white pt-0.5 outline outline-1 -outline-offset-1 outline-gray-300 has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-primary-600 dark:bg-gray-900 dark:outline-gray-700"
          >
            <input
              type="number"
              id="discount"
              pattern="[0-9]"
              :placeholder="$t('Discount')"
              v-model="form.discount"
              class="grow rounded-md border-0 py-2 shadow-xs focus:ring-0 focus:outline-hidden dark:bg-gray-900 dark:text-gray-300"
            />
            <!-- <TextInput type="number" id="discount" :label="$t('Discount')" v-model="form.discount" :error="form.errors.discount" /> -->
            <div class="grid shrink-0 grid-cols-1 focus-within:relative">
              <select
                id="discount_method"
                v-model="form.discount_method"
                class="col-start-1 row-start-1 w-full appearance-none rounded-md border-0 bg-white bg-none px-3 py-1.5 text-end text-base placeholder:text-gray-400 focus:ring-0 focus:outline-hidden sm:text-sm/6 dark:bg-gray-900"
              >
                <option value="fixed">{{ $t('Fixed') }}</option>
                <option value="percentage">{{ $t('Percentage') }}</option>
              </select>
            </div>
          </div>
        </div>
      </template>

      <template v-if="form.type == 'advance'">
        <!-- Quantity to buy -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            type="number"
            id="quantity_to_buy"
            :label="$t('Quantity to buy')"
            v-model="form.quantity_to_buy"
            :error="form.errors.quantity_to_buy"
          />
        </div>
      </template>
      <template v-else-if="form.type == 'SXGD'">
        <!-- Amount to spend -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            type="number"
            id="amount_to_spend"
            :label="$t('Amount to spend')"
            v-model="form.amount_to_spend"
            :error="form.errors.amount_to_spend"
          />
        </div>
      </template>
      <template v-else-if="form.type == 'BXGY'">
        <!-- Product to buy -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            labelKey="name"
            :searchable="true"
            id="product_id_to_buy"
            :label="$t('Product to buy')"
            v-model="form.product_id_to_buy"
            :error="form.errors.product_id_to_buy"
            :suggestions="route('search.products')"
          />
        </div>
        <!-- Quantity to buy -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            type="number"
            id="quantity_to_buy"
            :label="$t('Quantity to buy')"
            v-model="form.quantity_to_buy"
            :error="form.errors.quantity_to_buy"
          />
        </div>
        <!-- Product to get -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            labelKey="name"
            :searchable="true"
            id="product_id_to_get"
            :label="$t('Product to get')"
            v-model="form.product_id_to_get"
            :error="form.errors.product_id_to_get"
            :suggestions="route('search.products')"
          />
        </div>
        <!-- Quantity to get -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            type="number"
            id="quantity_to_get"
            :label="$t('Quantity to get')"
            v-model="form.quantity_to_get"
            :error="form.errors.quantity_to_get"
          />
        </div>
      </template>

      <!-- Start Date -->
      <div class="col-span-6 sm:col-span-3">
        <Input type="date" id="start_date" :label="$t('Start Date')" v-model="form.start_date" :error="form.errors.start_date" />
      </div>

      <!-- End Date -->
      <div class="col-span-6 sm:col-span-3">
        <Input type="date" id="end_date" :label="$t('End Date')" v-model="form.end_date" :error="form.errors.end_date" />
      </div>

      <!-- Products -->
      <div class="col-span-full">
        <AutoComplete
          :json="true"
          id="products"
          valueKey="id"
          labelKey="name"
          :multiple="true"
          :searchable="true"
          v-model="form.products"
          :label="$t('Products')"
          :error="form.errors.products"
          :suggestions="route('search.products')"
        />
      </div>

      <!-- Categories -->
      <div class="col-span-full">
        <AutoComplete
          :json="true"
          valueKey="id"
          labelKey="name"
          id="categories"
          :multiple="true"
          :searchable="true"
          v-model="form.categories"
          :label="$t('Categories')"
          :error="form.errors.categories"
          :suggestions="route('search.categories')"
        />
      </div>

      <!-- Stores -->
      <div class="col-span-full">
        <AutoComplete
          :json="true"
          id="stores"
          valueKey="id"
          labelKey="name"
          :multiple="true"
          :searchable="true"
          v-model="form.stores"
          :label="$t('Stores')"
          :suggestions="stores"
          :error="form.errors.stores"
        />
      </div>

      <!-- Details -->
      <div class="col-span-full">
        <Textarea id="details" :label="$t('Details')" v-model="form.details" :error="form.errors.details" />
      </div>

      <!-- Active -->
      <div class="col-span-full">
        <CheckBox id="active" :error="form.errors.active" v-model:checked="form.active" :label="$t('Active')" />
      </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="emits('close')"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3" :loading="form.processing">
        {{ $t('Save') }}
      </LoadingButton>
    </div>
  </form>
</template>
