<script setup>
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { notify } from 'notiwind';
import { useI18n } from 'vue-i18n';
import { onMounted, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { $extras } from '@/Core';

import { FormHelper } from '@/Core/FormHelper';
import { VariationSelection } from '@/Core/VariationSelection';
import { ActionMessage, FormSection, SecondaryButton } from '@/Components/Jet';
import { Attachments, AutoComplete, CustomFields, DateInput, FileInput, Input, LoadingButton, Textarea, Toggle } from '@/Components/Common';

const { t } = useI18n();
defineOptions({ layout: AdminLayout });
const props = defineProps(['current', 'brands', 'categories', 'custom_fields']);

const finalize = ref(false);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  file: null,
  date: dayjs(props.current?.date).format('YYYY-MM-DD'),
  reference: props.current?.reference,
  type: props.current?.type,
  details: props.current?.details,
  brands: props.current?.brands,
  categories: props.current?.categories,
  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const { resetForm, saveForm } = FormHelper(form);
const { currentItem, variantModal, SelectVariant } = VariationSelection();

onMounted(() => {
  if (props.current?.id && props.current?.completed_at) {
    notify({ group: 'main', type: 'error', title: t('Stock count has already completed.') });
    router.get(route('stock_counts.index'));
  }
});

function handleSubmit() {
  form.errors = {};
  form
    .transform(data => {
      const form = { ...data };

      try {
        form.date = form.date.toString().includes('T') ? form.date.toString().split('T')[0] : form.date;
      } catch {}

      return form;
    })
    .post(props.current?.id ? route('stock_counts.update', props.current.id) : route('stock_counts.store'), {
      forceFormData: true,
      onSuccess: () => {
        resetForm();
        // if (listing) {
        //   router.get(route('stock_counts.index'));
        // } else if (props.current?.id) {
        //   router.get(route('stock_counts.edit', props.current.id));
        // }
      },
    });
}
</script>

<template>
  <Head>
    <title>{{ current?.id ? $t('Complete Stock Count') : $t('Start Stock Count') }}</title>
  </Head>

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="handleSubmit">
      <template #title>
        {{ current?.id ? $t('Complete Stock Count') : $t('Start Stock Count') }}
      </template>

      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          <div>
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('stock count'),
                action: current?.id ? $t('complete') : $t('start'),
              })
            }}
          </div>
          <div class="me-3 mt-6 sm:mt-0 lg:mt-6">
            <Link class="link" :href="route('stock_counts.index')">{{ $t('List {x}', { x: $t('Stock Counts') }) }}</Link>
          </div>
        </div>
      </template>

      <template #form>
        <template v-if="!current?.id || !finalize">
          <!-- Date -->
          <div class="col-span-6 sm:col-span-3">
            <DateInput type="date" id="date" @change="saveForm" :label="$t('Date')" v-model="form.date" :error="form.errors.date" />
          </div>
          <!-- Reference -->
          <div class="col-span-6 sm:col-span-3">
            <Input id="reference" @change="saveForm" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
          </div>
          <!-- Type -->
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="type"
              :json="true"
              @change="saveForm"
              :label="$t('Type')"
              v-model="form.type"
              :error="form.errors.type"
              :suggestions="[
                { value: 'full', label: $t('Full Stock Count') },
                { value: 'partial', label: $t('Partial Stock Count') },
              ]"
            />
          </div>

          <!-- Attachments -->
          <div class="col-span-6 sm:col-span-3">
            <FileInput
              multiple
              id="attachments"
              :label="$t('Attachments')"
              v-model="form.attachments"
              :error="form.errors.attachments"
              :accept="$page.props.settings?.attachment_exts || '.jpg,.png,.pdf,.xlsx,.docx,.zip'"
            />
          </div>

          <div v-if="current && current.attachments && current.attachments.length" class="col-span-full">
            <Attachments :attachments="current.attachments || []" />
          </div>

          <template v-if="form.type == 'partial'">
            <!-- Brand -->
            <div class="col-span-6 sm:col-span-3">
              <AutoComplete
                json
                multiple
                id="brands"
                valueKey="id"
                labelKey="name"
                :searchable="false"
                :label="$t('Brands')"
                :suggestions="brands"
                v-model="form.brands"
                :error="form.errors.brands"
              />
            </div>

            <!-- Category -->
            <div class="col-span-6 sm:col-span-3">
              <AutoComplete
                json
                multiple
                valueKey="id"
                labelKey="name"
                id="categories"
                :searchable="false"
                :label="$t('Categories')"
                :suggestions="categories"
                v-model="form.categories"
                :error="form.errors.categories"
              />
            </div>
          </template>

          <div class="col-span-full flex flex-col gap-6">
            <CustomFields
              :errors="form.errors"
              :custom_fields="custom_fields"
              :extra_attributes="form.extra_attributes"
              @update="saveForm"
            />
            <Textarea @change="saveForm" :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
          </div>
        </template>
        <template v-else-if="current?.id && finalize">
          <!-- Final File -->
          <div v-if="current?.id" class="col-span-full">
            <FileInput id="file" accept=".xlsx" v-model="form.file" :label="$t('Final File')" :error="form.errors.file" />
          </div>
        </template>
        <div v-if="current?.id" class="col-span-full">
          <Toggle id="finalize" :label="$t('Finalize')" v-model="finalize" />
        </div>
      </template>

      <template #actions>
        <div class="flex w-full items-center justify-between">
          <div>
            <SecondaryButton v-if="!current?.id" type="button" @click="resetForm" class="me-3">{{ $t('Reset') }}</SecondaryButton>
          </div>

          <div class="flex items-center justify-end">
            <ActionMessage :on="form.recentlySuccessful" class="ms-3 me-3"> {{ $t('Saved.') }} </ActionMessage>

            <LoadingButton type="button" @click="handleSubmit" :class="{ 'opacity-25': form.processing }" :loading="form.processing">
              {{ $t('Save') }}
            </LoadingButton>
          </div>
        </div>
      </template>
    </FormSection>

    <SelectVariant
      v-if="variantModal"
      :item="currentItem"
      :show="variantModal"
      @close="
        () => {
          currentItem.variations = currentItem.variations.filter(v => v.id);
          form.items = form.items.map(i => (i.product_id == currentItem.product_id ? currentItem : i));
          variantModal = false;
        }
      "
      @update="
        item => {
          item.variations = item.variations.filter(v => v.id);
          form.items = form.items.map(i => (i.product_id == item.product_id ? item : i));
          saveForm();
          variantModal = false;
        }
      "
    />
  </div>
</template>
