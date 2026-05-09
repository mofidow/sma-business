<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import { FormSection } from '@/Components/Jet';
import { LoadingButton } from '@/Components/Common';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const file = ref(null);
const { t } = useI18n({});
const selected = ref(null);
defineOptions({ layout: AdminLayout });
const form = useForm({ _method: 'POST', excel: null });

function updateFile(e) {
  selected.value = e.target.files[0].name;
}

function submit() {
  if (file.value) {
    form.excel = file.value.files[0];
  }

  // var data = new FormData();
  // data.append('excel', this.form.excel);
  // data.append('_method', this.form._method);
  // this.$inertia.post(route('brands.import.save'), data);
  form.post(route('brands.import.save'), { preserveScroll: true });
}
</script>

<template>
  <Head :title="$t('Import {x}', { x: t('Brands') })" />
  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="submit">
      <template #title>{{ $t('Import {x}', { x: t('Brands') }) }}</template>
      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          <div>
            {{ $t('Please upload the excel file to import records.') }}
          </div>
          <div class="me-3 mt-6 flex gap-x-8 gap-y-4 sm:mt-0 lg:mt-6">
            <Link class="link" :href="route('brands.index')">{{ $t('List {x}', { x: $t('Brands') }) }}</Link>
            <a class="link" v-if="route().has('brands.export') && $can('export-brands')" :href="route('brands.export', { template: true })">
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
                <div>{{ $t('Excel file should have name column.') }}</div>
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
