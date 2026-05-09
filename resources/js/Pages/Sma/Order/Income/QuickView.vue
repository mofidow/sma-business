<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loading } from '@/Components/Common';
import { TemplateOne, TemplateTwo, TemplateThree, TemplateFour, TemplateFive, TemplateSix, TemplateMinimal } from '@/Components/Templates';

const page = usePage();

const props = defineProps(['current', 'custom_fields', 'editRow', 'force']);

const loading = ref(true);
const income = ref(null);

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
  await axios
    .get(route('incomes.show', { income: props.current.id, json: true }))
    .then(res => {
      income.value = res.data;
      loading.value = false;
    })
    .catch(() => (loading.value = false));
});
</script>

<template>
  <div v-if="loading" class="relative h-64">
    <Loading />
  </div>
  <template v-else-if="income">
    <component
      type="income"
      :record="income"
      :editRow="editRow"
      :custom_fields="custom_fields"
      :is="templateComponent[page.props.settings?.sale_template || 'One']"
    >
      <template #amount="{ record }">
        <div class="mx-4 mt-4 rounded-md border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-950">
          <div class="flex items-center justify-between text-lg font-bold">
            {{ $t('Amount') }}: <span>{{ $currency(record.amount) }}</span>
          </div>
          <div class="mt-2">{{ $t('By') }}: {{ record.user?.name }}</div>
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
