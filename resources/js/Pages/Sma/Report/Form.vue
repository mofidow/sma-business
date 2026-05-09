<script setup>
import { router } from '@inertiajs/vue3';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, DateInput, LoadingButton } from '@/Components/Common';

const emits = defineEmits(['close', 'done']);
const props = defineProps(['fields', 'filters', 'categories', 'brands', 'stores', 'users', 'paymentMethods']);

const close = () => {
  emits('close');
};

function update() {
  const form = Object.entries(props.filters).reduce((a, [k, v]) => (v ? ((a[k] = v), a) : a), {});

  router.visit(route(route().current(), Object.keys(form).length ? { filters: form } : ''), {
    // only: ['pagination', 'totals'],
    // replace: true,
    preserveScroll: true,
    // onStart: () => (searching.value = true),
    // onFinish: () => (searching.value = false),
  });

  //   emits('done', props.filters);
  emits('close');
}
</script>

<template>
  <form @submit.prevent="update" method="get">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
      <div class="sm:flex sm:items-baseline sm:justify-between">
        <div class="sm:w-0 sm:flex-1">
          <h1 class="text-focus text-base font-semibold">
            {{ $t('Customize Report') }}
          </h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{ $t('Please select the desired filters below.') }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-6 gap-6 p-6">
      <!-- Payment Method -->
      <div v-if="fields.includes('payment_methods')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          :clearable="true"
          :searchable="false"
          :label="$t('Method')"
          v-model="filters.method"
          :suggestions="paymentMethods"
          :error="$page.props.errors.method"
        />
      </div>

      <!-- Brands -->
      <div v-if="fields.includes('brands')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="brands"
          :multiple="true"
          :searchable="true"
          :label="$t('Brands')"
          :suggestions="brands"
          v-model="filters.brands"
        />
      </div>

      <!-- Categories -->
      <div v-if="fields.includes('categories')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="categories"
          :multiple="true"
          :searchable="true"
          :label="$t('Categories')"
          :suggestions="categories"
          v-model="filters.categories"
        />
      </div>

      <!-- Products -->
      <div v-if="fields.includes('products')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          valueKey="id"
          id="products"
          labelKey="name"
          :multiple="true"
          :searchable="true"
          :label="$t('Products')"
          v-model="filters.products"
          :suggestions="route('search.products')"
        />
      </div>

      <!-- Customer -->
      <div v-if="fields.includes('customer')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          valueKey="id"
          id="customer_id"
          labelKey="company"
          :clearable="true"
          :searchable="true"
          :label="$t('Customer')"
          v-model="filters.customer_id"
          :suggestions="route('search.customers')"
        />
      </div>

      <!-- Supplier -->
      <div v-if="fields.includes('supplier')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          valueKey="id"
          id="supplier_id"
          labelKey="company"
          :clearable="true"
          :searchable="true"
          :label="$t('Supplier')"
          v-model="filters.supplier_id"
          :suggestions="route('search.suppliers')"
        />
      </div>

      <!-- Store -->
      <div v-if="fields.includes('store')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="store_id"
          :clearable="true"
          :searchable="false"
          :label="$t('Store')"
          :suggestions="stores"
          v-model="filters.store_id"
        />
      </div>

      <!-- User -->
      <div v-if="fields.includes('user')" class="col-span-6 sm:col-span-3">
        <AutoComplete
          :json="true"
          id="user_id"
          :clearable="true"
          :searchable="false"
          :label="$t('User')"
          :suggestions="users"
          v-model="filters.user_id"
        />
      </div>

      <!-- Start Date -->
      <div class="col-span-6 sm:col-span-3">
        <DateInput id="start_date" :label="$t('Start Date')" v-model="filters.start_date" />
      </div>

      <!-- End Date -->
      <div class="col-span-6 sm:col-span-3">
        <DateInput id="end_date" :label="$t('End Date')" v-model="filters.end_date" />
      </div>
    </div>

    <div class="flex flex-row justify-end rounded-b-lg bg-gray-100 px-6 py-4 text-end dark:bg-gray-950">
      <SecondaryButton @click="close"> {{ $t('Cancel') }} </SecondaryButton>

      <LoadingButton class="ms-3">{{ $t('Update') }}</LoadingButton>
    </div>
  </form>
</template>
