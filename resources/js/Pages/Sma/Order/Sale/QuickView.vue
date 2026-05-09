<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loading } from '@/Components/Common';
import { TemplateOne, TemplateTwo, TemplateThree, TemplateFour, TemplateFive, TemplateSix, TemplateMinimal } from '@/Components/Templates';

const page = usePage();

const props = defineProps(['current', 'custom_fields', 'editRow', 'xfetch', 'force']);

const loading = ref(true);
const sale = ref(null);

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
    sale.value = props.current;
    loading.value = false;
  } else {
    await axios
      .get(props.force ? route('sales.report.show', { id: props.current.id }) : route('sales.show', { sale: props.current.id, json: true }))
      .then(res => {
        sale.value = res.data;
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
  <template v-else-if="sale">
    <component
      type="sale"
      :record="sale"
      :xfetch="xfetch"
      :custom_fields="custom_fields"
      :editRow="sale.repair_order_id ? () => {} : editRow"
      :is="templateComponent[page.props.settings?.sale_template || 'One']"
      :qrUrl="route('sale.url', { id: sale.id, hash: sale.hash || 'hash' })"
    >
      <template #alerts>
        <div v-if="sale.return_orders_count > 0" class="col-span-full -mb-3 px-6 pt-6">
          <a
            :href="route('return_orders.index', { sale_id: sale.id })"
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
                  {{ $t('This sale has attached to return orders.') }}
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
