<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loading } from '@/Components/Common';
import { TemplateOne, TemplateTwo, TemplateThree, TemplateFour, TemplateFive, TemplateSix, TemplateMinimal } from '@/Components/Templates';

const page = usePage();

const props = defineProps(['current', 'custom_fields', 'editRow', 'xfetch']);

const loading = ref(true);
const return_order = ref(null);

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
    return_order.value = props.current;
    loading.value = false;
  } else {
    await axios
      .get(route('return_orders.show', { return_order: props.current.id, json: true }))
      .then(res => {
        return_order.value = res.data;
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
  <template v-else-if="return_order">
    <component
      :xfetch="xfetch"
      :editRow="editRow"
      type="return_order"
      :record="return_order"
      :custom_fields="custom_fields"
      :is="templateComponent[page.props.settings?.sale_template || 'One']"
    >
      <template #alerts>
        <div v-if="return_order.sale_id > 0" class="col-span-full -mb-3 px-6 pt-6">
          <a
            :href="route('sales.index', { id: return_order.sale_id })"
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
                  {{ $t('This return order has attached to a sale.') }}
                </h3>
              </div>
            </div>
          </a>
        </div>
        <div v-if="return_order.purchase_id > 0" class="col-span-full -mb-3 px-6 pt-6">
          <a
            :href="route('purchases.index', { id: return_order.purchase_id })"
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
                  {{ $t('This return order has attached to a purchase.') }}
                </h3>
              </div>
            </div>
          </a>
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
