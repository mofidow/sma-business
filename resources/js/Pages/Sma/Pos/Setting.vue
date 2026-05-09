<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import { $can } from '@/Core';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FormSection, ActionMessage } from '@/Components/Jet';
import { AutoComplete, Input, LoadingButton, TabMenus, Textarea, Toggle } from '@/Components/Common';

const { t } = useI18n({});
defineOptions({ layout: AdminLayout });
const props = defineProps({ current: Object, categories: Array });

const form = useForm({
  pin_code: null,
  pos_design: props.current?.pos_design || 'Modern',
  default_category: props.current?.default_category,
  default_customer: props.current?.default_customer,
  play_sound: props.current?.play_sound == 1,
  pos_server: props.current?.pos_server == 1,
  auto_open_order: props.current?.auto_open_order == 1,
  print_dialog: props.current?.print_dialog == 1,
  restaurant: props.current?.restaurant == 1,
  quick_cash: props.current?.quick_cash?.length ? props.current.quick_cash.join('|') : '10|20|50|100|200|500',
  show_order_by_default: props.current?.show_order_by_default == 1,
  allow_sale_without_payment: props.current?.allow_sale_without_payment == 1,
  onscreen_keyboard: props.current?.onscreen_keyboard == 1,
  customer_view_heading: props.current?.customer_view_heading || '',
  customer_view_message: props.current?.customer_view_message || '',
  receipt_header: props.current?.receipt_header,
  receipt_footer: props.current?.receipt_footer,
});

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

const save = () => {
  form
    .transform(data => ({
      ...data,
      quick_cash: data.quick_cash ? data.quick_cash.split('|').map(v => v.trim()) : [],
    }))
    .post(route('settings.pos'));
};
</script>

<template>
  <Head>
    <title>{{ $t('POS Settings') }}</title>
  </Head>

  <TabMenus :links="tab_links" />

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="save">
      <template #title> {{ $t('POS Settings') }} </template>

      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          {{ $t('Please update setting as you desire') }}
          <div v-if="$can('manage-modules')" class="me-3 mt-6 sm:mt-0 lg:mt-6">
            <a href="/modules" target="_blank" class="link mt-4">{{ $t('Manage Modules') }}</a>
          </div>
        </div>
      </template>

      <template #form>
        <!-- Design -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="pos_design"
            :label="$t('Design')"
            v-model="form.pos_design"
            :error="form.errors.pos_design"
            :suggestions="[
              { value: 'Modern', label: $t('Modern') },
              { value: 'Simple', label: $t('Simple') },
            ]"
          />
        </div>
        <!-- Pin Code -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="pin_code" type="password" v-model="form.pin_code" :label="$t('Pin Code')" :error="form.errors.pin_code" />

          <div v-if="current.pin_code" class="mt-1 text-xs">
            {{ $t('Only set if you want to change the current pin code.') }}
          </div>
          <div v-else class="mt-1 text-xs">
            {{ $t('Pin code should be between 4 to 8 characters.') }}
          </div>
        </div>

        <!-- Default Category -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="default_category"
            :suggestions="categories"
            :label="$t('Default Category')"
            v-model="form.default_category"
            :error="form.errors.default_category"
          />
        </div>
        <!-- Default Customer -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            labelKey="company"
            id="default_customer"
            :label="$t('Default Customer')"
            v-model="form.default_customer"
            :error="form.errors.default_customer"
            :suggestions="route('search.customers')"
          />
        </div>

        <div class="col-span-full flex flex-col gap-2 overflow-x-auto rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <h4 class="mb-1 text-sm font-bold">{{ $t('Quick Cash') }}</h4>
          <Input
            label=""
            id="quick_cash"
            v-model="form.quick_cash"
            :error="form.errors.quick_cash"
            :placeholder="$t('Enter quick cash values separated by pipe (|)')"
          />
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ $t('These values will be used for quick cash buttons in POS.') }}
          </div>
        </div>

        <div class="col-span-full flex flex-col gap-2 overflow-x-auto rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <h4 class="mb-3 text-sm font-bold">{{ $t('POS') }}</h4>
          <Toggle id="play_sound" :label="$t('Play sound on order item')" v-model="form.play_sound" />
          <Toggle id="onscreen_keyboard" :label="$t('Enable onscreen keyboard')" v-model="form.onscreen_keyboard" />
          <Toggle id="allow_sale_without_payment" v-model="form.allow_sale_without_payment" :label="$t('Allow sale without payment')" />
          <Toggle id="pos_server" :label="$t('Print using POS Print Server')" v-model="form.pos_server" />
          <Toggle id="print_dialog" :label="$t('Auto print or open browser print dialog')" v-model="form.print_dialog" />
          <Toggle id="show_order_by_default" :label="$t('Show order by default on small screens')" v-model="form.show_order_by_default" />
          <Toggle id="auto_open_order" :label="$t('Auto open new order after hold and sale')" v-model="form.auto_open_order" />
          <Toggle
            id="restaurant"
            v-model="form.restaurant"
            :label="$t('Enable restaurant features for POS i.e, halls, tables & recipes')"
          />
        </div>

        <!-- Receipt Header -->
        <div class="col-span-full">
          <Textarea id="receipt_header" :label="$t('Receipt Header')" v-model="form.receipt_header" :error="form.errors.receipt_header" />
        </div>
        <!-- Receipt Footer -->
        <div class="col-span-full">
          <Textarea id="receipt_footer" :label="$t('Receipt Footer')" v-model="form.receipt_footer" :error="form.errors.receipt_footer" />
        </div>

        <!-- Customer View Screen Heading -->
        <div class="col-span-full">
          <Input
            id="customer_view_heading"
            v-model="form.customer_view_heading"
            :error="form.errors.customer_view_heading"
            :label="$t('Customer View Screen Heading')"
          />
        </div>
        <!-- Customer View Screen Message -->
        <div class="col-span-full">
          <Textarea
            rows="4"
            id="customer_view_message"
            v-model="form.customer_view_message"
            :error="form.errors.customer_view_message"
            :label="$t('Customer View Screen Message')"
            :placeholder="$t('Customer View Screen Message')"
          />
        </div>
      </template>

      <template #actions>
        <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

        <LoadingButton :class="{ 'opacity-25': form.processing }" :loading="form.processing"> {{ $t('Save') }} </LoadingButton>
      </template>
    </FormSection>
  </div>
</template>
