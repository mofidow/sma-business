<script setup>
import { axios } from '@/Core';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Loading } from '@/Components/Common';
import { TemplateOne, TemplateTwo, TemplateThree, TemplateFour, TemplateFive, TemplateSix, TemplateMinimal } from '@/Components/Templates';

const page = usePage();

const props = defineProps(['current', 'custom_fields', 'editRow', 'xfetch']);

const loading = ref(true);
const quotation = ref(null);

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
    quotation.value = props.current;
    loading.value = false;
  } else {
    await axios
      .get(route('quotations.show', { quotation: props.current.id, json: true }))
      .then(res => {
        quotation.value = res.data;
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
  <template v-else-if="quotation">
    <component
      type="quotation"
      :xfetch="xfetch"
      :editRow="editRow"
      :record="quotation"
      :custom_fields="custom_fields"
      :is="templateComponent[page.props.settings?.sale_template || 'One']"
      :qrUrl="route('quotation.url', { id: quotation.id, hash: quotation.hash || 'hash' })"
    />
  </template>
  <template v-else>
    <div class="flex min-h-64 items-center justify-center p-6 text-lg font-thin">
      {{ $t('No data found, the record might not belong to the selected store.') }}
    </div>
  </template>
</template>
