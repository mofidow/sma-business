<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';

import { FormSection } from '@/Components/Jet';
import { LoadingButton } from '@/Components/Common';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const form = useForm({ excel: null });
const selected = ref(null);

function updateFile(e) {
  const f = e.target.files[0];
  if (f) {
    form.excel = f;
    selected.value = f.name;
  }
}

function submit() {
  if (!form.excel) {
    form.setError('excel', 'Please select an Excel file before importing.');
    return;
  }
  form.post(route('products.import.save'), { forceFormData: true, preserveScroll: true });
}
</script>

<template>
  <Head :title="$t('Import {x}', { x: $t('Products') })" />
  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="submit">
      <template #title>{{ $t('Import {x}', { x: $t('Products') }) }}</template>
      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          <div>
            {{ $t('Please upload the excel file to import records.') }}
          </div>
          <div class="me-3 mt-6 flex gap-x-8 gap-y-4 sm:mt-0 lg:mt-6">
            <Link class="link" :href="route('products.index')">{{ $t('List {x}', { x: $t('Products') }) }}</Link>
            <a
              class="link"
              v-if="route().has('products.export') && $can('export-products')"
              :href="route('products.export', { template: true })"
            >
              {{ $t('Download {x}', { x: $t('Template') }) }}
            </a>
          </div>
        </div>
      </template>

      <template #form>
        <div class="col-span-full">
          <label for="file-upload" class="block font-medium">{{ $t('Excel File') }}</label>
          <div
            :class="$page.props.errors.excel ? 'border-red-500' : 'border-gray-300 dark:border-gray-700'"
            class="mt-1 flex justify-center rounded-md border-2 border-dashed px-6 pt-5 pb-6"
          >
            <div class="space-y-1 text-center">
              <Icons name="doc-text" className="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" />
              <div class="flex items-center justify-center py-2 text-gray-600 dark:text-gray-400">
                <label for="file-upload" class="link relative cursor-pointer rounded-md font-medium">
                  <span v-if="selected" class="font-semibold">{{ $t('Change file') }}</span>
                  <span v-else class="font-semibold">{{ $t('Select file') }}</span>
                  <input
                    ref="file"
                    type="file"
                    class="sr-only"
                    id="file-upload"
                    name="file-upload"
                    @change="updateFile"
                    accept=".xls,.xlsx,application/vnd.ms-excel"
                  />
                </label>
                <p class="ps-1">{{ $t('or drag and drop') }}</p>
              </div>
              <div class="text-xs text-gray-600 dark:text-gray-400">
                <div class="text-justify">
                  {{
                    $t('Excel file should have {x} columns.', {
                      x: 'type (Standard/Service/Digital/Combo/Recipe), name, secondary_name, code, symbology (CODE128, CODE39, EAN8, EAN13, UPC), category_name, subcategory_name, brand_name, unit_code, cost (numeric), price (numeric), min_price (numeric), max_price (numeric), max_discount (numeric), hsn_number, sac_number, weight, dimensions, rack_location, supplier_company, supplier_part_id, featured (yes/no), hide_in_pos (yes/no), hide_in_shop (yes/no), tax_included (yes/no), has_serials (yes/no), can_edit_price (yes/no), has_expiry_date (yes/no), dont_track_stock (yes/no), photo (URL), photos (comma separated URLs), video_url, alert_quantity (numeric), has_variants (yes/no), variants (name:values,comma,separated|other:same,too), tax_names (comma separated), slug (seo - url), title (seo - page title), keywords (seo), noindex  (yes/no), nofollow  (yes/no), description (seo), details, extra_attributes (key:value,key2:value2)',
                    })
                  }}
                </div>
                <div class="mt-2 text-justify">
                  {{
                    $t('You must fill the {x} columns.', {
                      x: 'type, name, code, symbology, category_name, brand_name, cost, price, supplier_company',
                    })
                  }}
                </div>
              </div>
              <div v-if="selected" class="inline-block pt-4">
                <div class="rounded-md border px-3 py-1 text-lg font-bold">{{ $t('Selected File') }}: {{ selected }}</div>
              </div>
              <div v-if="$page.props.errors.excel" class="mt-4 rounded-md pt-2 text-red-600">
                {{ $page.props.errors.excel }}
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #actions>
        <LoadingButton :loading="form.processing" :disabled="form.processing">{{ $t('Import') }}</LoadingButton>
      </template>
    </FormSection>
  </div>
</template>
