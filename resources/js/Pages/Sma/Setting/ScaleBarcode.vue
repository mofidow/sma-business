<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Input, AutoComplete, LoadingButton, TabMenus } from '@/Components/Common';
import { FormSection, ActionMessage } from '@/Components/Jet';

const { t } = useI18n({});
defineOptions({ layout: AdminLayout });
const props = defineProps({ current: Object });

const showGuide = ref(false);

const tab_links = ref([
  { label: t('General Settings'), route: 'settings.index', icon: 'settings' },
  { label: t('Mail Settings'), route: 'settings.mail', icon: 'envelope' },
  { label: t('Payment Settings'), route: 'settings.payment', icon: 'dollar' },
  { label: t('Scale Barcode Settings'), route: 'settings.barcode', icon: 'scale' },
]);

if (route().has('settings.pos')) {
  tab_links.value.push({ label: t('POS Settings'), route: 'settings.pos', icon: 'pos' });
}
if (route().has('shop.settings')) {
  tab_links.value.push({ label: t('Shop Settings'), route: 'shop.settings', icon: 'shop', external: true });
}

const form = useForm({
  type: props.current?.type || 'price',
  length: props.current?.length || null,
  flag_length: props.current?.flag_length || null,
  code_start: props.current?.code_start || null,
  code_length: props.current?.code_length || null,
  price_start: props.current?.price_start || null,
  price_length: props.current?.price_length || null,
  price_divide_by: props.current?.price_divide_by || null,
  weight_start: props.current?.weight_start || null,
  weight_length: props.current?.weight_length || null,
  weight_divide_by: props.current?.weight_divide_by || null,
});

function save() {
  form.post(route('settings.barcode.store'));
}
</script>

<template>
  <Head>
    <title>{{ $t('Scale Barcode Settings') }}</title>
  </Head>
  <!-- <Header>{{ $t('Settings') }}</Header> -->

  <TabMenus :links="tab_links" />

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="save">
      <template #title>
        <div class="flex items-center gap-2">
          {{ $t('Scale Barcode Settings') }}
          <button
            type="button"
            @click="() => (showGuide = true)"
            class="relative me-2 rounded-md hover:text-gray-500 focus:ring-2 focus:ring-primary-500"
          >
            <span class="absolute -inset-2.5"></span>
            <span class="sr-only">{{ $t('Guide') }}</span>
            <Icon name="info" />
          </button>
        </div>
      </template>

      <template #description> {{ $t('Please set the settings to parse scale barcodes.') }} </template>

      <template #form>
        <div v-if="showGuide" class="relative col-span-full -mx-4 -mt-6 sm:-mx-6">
          <button
            type="button"
            @click="() => (showGuide = false)"
            class="absolute end-1 top-1 rounded-md bg-gray-100 p-1 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 dark:bg-gray-700"
          >
            <span class="absolute -inset-2.5"></span>
            <span class="sr-only">{{ $t('Hide') }}</span>
            <svg
              class="size-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              aria-hidden="true"
              data-slot="icon"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
          <img src="/img/scale_barcode.png" alt="" class="w-full sm:rounded-t-md" />
        </div>

        <!-- Barcode Contains -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="form.type"
            :searchable="false"
            v-model="form.type"
            :error="form.errors.type"
            :label="$t('Barcode Contains')"
            :suggestions="[
              { value: 'price', label: $t('Price') },
              { value: 'weight', label: $t('Quantity/Weight') },
            ]"
          />
        </div>

        <!-- Barcode Length -->
        <div class="col-span-6 sm:col-span-3">
          <Input v-model="form.length" :label="$t('Barcode Length')" :error="form.errors.length" />
        </div>

        <!-- Flag Characters Length -->
        <div class="col-span-6 sm:col-span-3">
          <Input v-model="form.flag_length" :label="$t('Flag Characters Length')" :error="form.errors.flag_length" />
        </div>

        <!-- Item Code Start Position -->
        <div class="col-span-6 sm:col-span-3">
          <Input v-model="form.code_start" :label="$t('Item Code Start Position')" :error="form.errors.code_start" />
        </div>

        <!-- Item Code Characters Length -->
        <div class="col-span-6 sm:col-span-3">
          <Input v-model="form.code_length" :label="$t('Item Code Characters Length')" :error="form.errors.code_length" />
        </div>

        <template v-if="form.type == 'weight'">
          <!-- Weight Start Position -->
          <div class="col-span-6 sm:col-span-3">
            <Input v-model="form.weight_start" :label="$t('Weight Start Position')" :error="form.errors.weight_start" />
          </div>

          <!-- Weight Characters Length -->
          <div class="col-span-6 sm:col-span-3">
            <Input v-model="form.weight_length" :label="$t('Weight Characters Length')" :error="form.errors.weight_length" />
          </div>

          <!-- Weight Divide by -->
          <div class="col-span-6 sm:col-span-3">
            <Input v-model="form.weight_divide_by" :label="$t('Weight Divide by')" :error="form.errors.weight_divide_by" />
          </div>
        </template>
        <template v-else>
          <!-- Price Start Position -->
          <div class="col-span-6 sm:col-span-3">
            <Input v-model="form.price_start" :label="$t('Price Start Position')" :error="form.errors.price_start" />
          </div>

          <!-- Price Characters Length -->
          <div class="col-span-6 sm:col-span-3">
            <Input v-model="form.price_length" :label="$t('Price Characters Length')" :error="form.errors.price_length" />
          </div>

          <!-- Price Divide by -->
          <div class="col-span-6 sm:col-span-3">
            <Input v-model="form.price_divide_by" :label="$t('Price Divide by')" :error="form.errors.price_divide_by" />
          </div>
        </template>
      </template>

      <template #actions>
        <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

        <LoadingButton :class="{ 'opacity-25': form.processing }" :loading="form.processing"> {{ $t('Save') }} </LoadingButton>
      </template>
    </FormSection>
  </div>
</template>
