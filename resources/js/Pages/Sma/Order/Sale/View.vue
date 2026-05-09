<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { TemplateOne } from '@/Components/Templates';
import { Modal } from '@/Components/Jet';
import { Button } from '@/Components/Common';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConvertForm from '@/Pages/Sma/Order/Credit/ConvertForm.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps(['sale', 'custom_fields']);

const showConvert = ref(false);
</script>

<template>
  <Head>
    <title>{{ $t('Sale') }}</title>
  </Head>

  <div class="p-4 sm:p-6">
    <!-- Convert to Credit action bar -->
    <div v-if="!sale.is_credit" class="mx-auto mb-4 w-full max-w-3xl flex justify-end">
      <button @click="showConvert = true"
        class="flex items-center gap-2 rounded-lg border border-blue-300 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition">
        <Icon name="dollar" size="size-4" />
        {{ $t('Convert to Credit (Deyn)') }}
      </button>
    </div>
    <div v-else class="mx-auto mb-4 w-full max-w-3xl flex justify-end">
      <a :href="route('credits.show', { credit: sale.id })"
        class="flex items-center gap-2 rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:border-green-700 dark:bg-green-900/20 dark:text-green-400 transition">
        <Icon name="dollar" size="size-4" />
        {{ $t('View Credit (Deyn)') }}
      </a>
    </div>

    <div class="relative mx-auto w-full max-w-3xl rounded-lg border border-gray-200 dark:border-gray-700 print:border-0">
      <TemplateOne :record="sale" type="sale" :custom_fields="custom_fields" :xfetch="true" />
    </div>
  </div>

  <Modal :show="showConvert" max-width="lg" @close="showConvert = false">
    <ConvertForm :sale="sale" @close="showConvert = false" />
  </Modal>
</template>
