<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loading } from '@/Components/Common';
import { TemplateOne, TemplateTwo, TemplateThree, TemplateFour, TemplateFive, TemplateSix, TemplateMinimal } from '@/Components/Templates';

const page = usePage();

const props = defineProps(['current', 'custom_fields', 'editRow', 'xfetch']);

const loading = ref(true);
const delivery = ref(null);

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
    delivery.value = props.current;
    loading.value = false;
  } else {
    await axios
      .get(route('deliveries.show', { delivery: props.current.id, json: true }))
      .then(res => {
        delivery.value = res.data;
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
  <template v-else-if="delivery">
    <component
      type="delivery"
      :xfetch="xfetch"
      :record="delivery"
      :editRow="editRow"
      :custom_fields="custom_fields"
      :is="templateComponent[page.props.settings?.sale_template || 'One']"
    >
      <template #header-extra>
        <div class="flex gap-1 text-sm">
          {{ $t('Sale Reference') }}:
          <p class="truncate hover:text-clip print:block print:text-clip" dir="rtl">{{ delivery.sale?.reference }}</p>
        </div>
      </template>

      <template #amount="{ record }">
        <div class="mx-4 mb-4 rounded-md border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-950">
          <div class="text-sm font-bold">{{ $t('Address') }}:</div>
          <div class="text-lg font-semibold">{{ record.address?.company || record.address?.name }}</div>
          <div v-if="record.address?.company" class="text-lg font-semibold">{{ $t('Attn') }}: {{ record.address?.name }}</div>
          <div class="flex gap-1 text-lg">
            <span>{{ $address(record.address) }}</span>
          </div>
          <template v-if="record.delivered_at">
            <div class="mt-1">
              {{ $t('Delivered at') }}: <span class="font-bold">{{ $datetime(record.delivered_at) }}</span>
            </div>
            <div class="mt-1">
              {{ $t('Delivered by') }}: <span class="font-bold">{{ record.delivered_by || '' }}</span>
            </div>
            <div class="mt-1">
              {{ $t('Received by') }}: <span class="font-bold">{{ record.received_by || '' }}</span>
            </div>
          </template>
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
