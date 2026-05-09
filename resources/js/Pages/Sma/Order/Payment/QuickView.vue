<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loading } from '@/Components/Common';
import { TemplateOne, TemplateTwo, TemplateThree, TemplateFour, TemplateFive, TemplateSix, TemplateMinimal } from '@/Components/Templates';

const page = usePage();

const props = defineProps(['current', 'custom_fields', 'editRow', 'xfetch', 'force']);

const loading = ref(true);
const payment = ref(null);

const templateComponent = {
  One: TemplateOne,
  Two: TemplateTwo,
  Three: TemplateThree,
  Four: TemplateFour,
  Five: TemplateFive,
  Six: TemplateSix,
  Minimal: TemplateMinimal,
};

onMounted(async () => {
  if (props.xfetch) {
    payment.value = props.current;
    loading.value = false;
  } else {
    await axios
      .get(
        props.force
          ? route('payments.report.show', { id: props.current.id })
          : route('payments.show', { payment: props.current.id, json: true })
      )
      .then(res => {
        payment.value = res.data;
        loading.value = false;
      })
      .catch(() => (loading.value = false));
  }
});
</script>

<template>
  <div v-if="loading" class="relative h-64">
    <Loading />
  </div>
  <template v-else-if="payment">
    <component
      type="payment"
      :record="payment"
      :xfetch="xfetch"
      :editRow="editRow"
      :custom_fields="custom_fields"
      :is="templateComponent[page.props.settings?.sale_template || 'One']"
    >
      <template #alerts>
        <div v-if="payment.received != 1" class="col-span-full px-6 pb-4">
          <div
            class="block rounded-md border border-yellow-700 bg-yellow-50 px-4 py-2.5 dark:bg-yellow-800 print:border print:border-yellow-700"
          >
            <div class="flex">
              <div class="shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path
                    fill-rule="evenodd"
                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ms-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                  {{ $t('This payment is not yet received') }}
                </h3>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #content="{ record }">
        <div v-if="record.payment_for == 'Supplier' && record.supplier" class="pb-4">
          <h2 class="mb-1 text-xs font-bold">{{ $t('To') }}</h2>
          <div class="text-lg font-semibold">{{ record.supplier.company || record.supplier.name }}</div>
          <div class="text-sm">{{ $address(record.supplier) }}</div>
          <div class="text-sm" v-if="record.supplier.phone">{{ $t('Phone') }}: {{ record.supplier.phone }}</div>
          <div class="text-sm" v-if="record.supplier.email">{{ $t('Email') }}: {{ record.supplier.email }}</div>
        </div>

        <div v-if="record.sales && record.sales.length" class="pb-4">
          <h2 class="mb-1 text-xs font-bold">{{ $t('Sales') }}</h2>
          <div class="text-sm">
            <span v-for="(sale, i) in record.sales" :key="sale.id">
              <span v-if="i > 0">, </span>
              <Link :href="route('sales.index', { id: sale.id })" class="link">
                #{{ sale.id }}<span v-if="sale.reference"> ({{ sale.reference }})</span>
              </Link>
            </span>
          </div>
        </div>

        <div v-if="record.purchases && record.purchases.length" class="pb-4">
          <h2 class="mb-1 text-xs font-bold">{{ $t('Purchases') }}</h2>
          <div class="text-sm">
            <span v-for="(purchase, i) in record.purchases" :key="purchase.id">
              <span v-if="i > 0">, </span>
              <Link :href="route('purchases.index', { id: purchase.id })" class="link">
                #{{ purchase.id }}<span v-if="purchase.reference"> ({{ purchase.reference }})</span>
              </Link>
            </span>
          </div>
        </div>
      </template>

      <template #amount="{ record }">
        <div class="mx-4 rounded-md border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-950">
          <div class="flex items-center justify-between text-lg font-bold">
            {{ $t('Amount') }}: <span>{{ $currency(record.amount) }}</span>
          </div>
          <div v-if="record.method" class="mt-3 flex items-center justify-start gap-1">
            {{ $t('Method') }}:
            <div class="font-bold">
              {{ $t(record.method) }}
            </div>
          </div>
          <div v-if="record.method_data && Object.keys(record.method_data).length" class="mt-2 flex items-center justify-start gap-1">
            <div class="capitalize">
              {{
                record.method_data && Object.keys(record.method_data)
                  ? Object.keys(record.method_data)
                      .map(k => $t(k.replaceAll('_', ' ')) + ': ' + record.method_data[k])
                      .join(', ')
                  : ''
              }}
            </div>
          </div>
        </div>
      </template>
    </component>
  </template>
  <template v-else>
    <div class="flex min-h-64 items-center justify-center p-6 text-lg font-thin">
      {{ $t('No data found, the record might not belong to the selected store.') }}
    </div>
  </template>
</template>
