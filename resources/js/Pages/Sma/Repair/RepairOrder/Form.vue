<script setup>
import dayjs from 'dayjs';
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import { AutoComplete, DateInput, LoadingButton, Input, Textarea, CustomFields } from '@/Components/Common';
import { ActionMessage, FormSection, SecondaryButton, Modal } from '@/Components/Jet';
import { $extras } from '@/Core';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServiceTypeForm from '@/Pages/Sma/Repair/ServiceType/Form.vue';
import TechnicianForm from '@/Pages/Sma/Repair/Technician/Form.vue';
import CustomerForm from '@/Pages/Sma/People/Customer/Form.vue';
import { route } from 'ziggy-js';

const { t } = useI18n();
defineOptions({ layout: AdminLayout });

const props = defineProps({
  current: Object,
  serviceTypes: Array,
  technicians: Array,
  custom_fields: Array,
  customer_fields: Array,
  countries: Array,
  taxes: Array,
});

const form = useForm({
  _method: props.current?.id ? 'PUT' : 'POST',
  customer_id: props.current?.customer_id || null,
  service_type_id: props.current?.service_type_id || null,
  store_id: props.current?.store_id || null,
  technician_id: props.current?.technician_id || null,
  reference: props.current?.reference || '',
  serial_no: props.current?.serial_no || '',
  device_password: props.current?.device_password || '',
  device_pattern: props.current?.device_pattern || '',
  device_condition: props.current?.device_condition || 'fair',
  problem_description: props.current?.problem_description || '',
  technician_comment: props.current?.technician_comment || '',
  internal_notes: props.current?.internal_notes || '',
  customer_notes: props.current?.customer_notes || '',
  price: props.current?.price ? Number(props.current?.price) : '',
  actual_cost: props.current?.actual_cost ? Number(props.current?.actual_cost) : '',
  tax_amount: props.current?.tax_amount ? Number(props.current?.tax_amount) : 0,
  tax_included: props.current?.tax_included || false,
  taxes: props.current?.taxes?.map(t => t.id) || [],
  received_date: props.current?.received_date ? dayjs(props.current.received_date).format('YYYY-MM-DD') : dayjs().format('YYYY-MM-DD'),
  due_date: props.current?.due_date ? dayjs(props.current.due_date).format('YYYY-MM-DD') : '',
  completed_date: props.current?.completed_date ? dayjs(props.current.completed_date).format('YYYY-MM-DD') : '',
  status: props.current?.status || 'pending',
  priority: props.current?.priority || 'normal',
  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
  attachments: [],
});

const fileInput = ref(null);
const uploadedFiles = ref([]);
const add_customer = ref(false);
const add_technician = ref(false);
const add_service_type = ref(false);

const conditionOptions = [
  { value: 'excellent', label: t('Excellent') },
  { value: 'good', label: t('Good') },
  { value: 'fair', label: t('Fair') },
  { value: 'poor', label: t('Poor') },
];

const statusOptions = [
  { value: 'pending', label: t('Pending') },
  { value: 'in_progress', label: t('In Progress') },
  { value: 'waiting_parts', label: t('Waiting Parts') },
  { value: 'completed', label: t('Completed') },
  { value: 'delivered', label: t('Delivered') },
  { value: 'cancelled', label: t('Cancelled') },
];

const priorityOptions = [
  { value: 'low', label: t('Low') },
  { value: 'normal', label: t('Normal') },
  { value: 'high', label: t('High') },
  { value: 'urgent', label: t('Urgent') },
];

function handleFileChange(event) {
  const files = Array.from(event.target.files);
  uploadedFiles.value = [...uploadedFiles.value, ...files];
  form.attachments = uploadedFiles.value;
}

function removeFile(index) {
  uploadedFiles.value.splice(index, 1);
  form.attachments = uploadedFiles.value;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
}

function removeExistingAttachment(attachmentId) {
  if (confirm('Are you sure you want to delete this attachment?')) {
    router.delete(route('repair-orders.attachments.destroy', { repair_order: props.current.id, attachment: attachmentId }), {
      preserveScroll: true,
    });
  }
}

function formatFileSize(bytes) {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function handleSubmit(event, redirectToList = false) {
  form.errors = {};
  form
    .transform(data => {
      const formData = { ...data };
      formData.received_date = formData.received_date ? dayjs(formData.received_date).format('YYYY-MM-DD') : null;
      formData.due_date = formData.due_date ? dayjs(formData.due_date).format('YYYY-MM-DD') : null;
      formData.completed_date = formData.completed_date ? dayjs(formData.completed_date).format('YYYY-MM-DD') : null;
      return formData;
    })
    .post(props.current?.id ? route('repair-orders.update', props.current.id) : route('repair-orders.store'), {
      forceFormData: true,
      onSuccess: () => {
        if (redirectToList) {
          router.get(route('repair-orders.index'));
        } else {
          uploadedFiles.value = [];
          if (fileInput.value) {
            fileInput.value.value = '';
          }
        }
      },
    });
}
</script>

<template>
  <Head>
    <title>{{ current?.id ? $t('Edit {x}', { x: $t('Repair Order') }) : $t('Add {x}', { x: $t('Repair Order') }) }}</title>
  </Head>

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="e => handleSubmit(e, false)">
      <template #title>
        {{ current?.id ? $t('Edit {x}', { x: $t('Repair Order') }) : $t('Add {x}', { x: $t('Repair Order') }) }}
      </template>

      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          <div>
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('repair order'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </div>
          <div class="me-3 mt-6 sm:mt-0 lg:mt-6">
            <Link class="link" :href="route('repair-orders.index')">{{ $t('List {x}', { x: $t('Repair Orders') }) }}</Link>
          </div>
        </div>
      </template>

      <template #form>
        <!-- Reference -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            id="reference"
            :label="$t('Reference')"
            v-model="form.reference"
            :error="form.errors.reference"
            :placeholder="$t('Auto-generated if empty')"
          />
        </div>

        <!-- Received Date -->
        <div class="col-span-6 sm:col-span-3">
          <DateInput
            type="date"
            id="received_date"
            :label="$t('Received Date')"
            v-model="form.received_date"
            :error="form.errors.received_date"
          />
        </div>

        <!-- Customer -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            valueKey="id"
            id="customer_id"
            labelKey="company"
            :searchable="true"
            :label="$t('Customer')"
            v-model="form.customer_id"
            :action="() => (add_customer = true)"
            :error="$page.props.errors.customer_id"
            :suggestions="route('search.customers')"
            :action-text="$t('Add {x}', { x: $t('Customer') })"
          />
        </div>

        <!-- Service Type -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            :searchable="true"
            id="service_type_id"
            :label="$t('Service Type')"
            v-model="form.service_type_id"
            :error="form.errors.service_type_id"
            :placeholder="$t('Select Service Type')"
            :action="() => (add_service_type = true)"
            :action-text="$t('Add {x}', { x: $t('Service Type') })"
            :suggestions="serviceTypes.map(st => ({ value: st.id, label: st.name }))"
          />
        </div>

        <!-- Serial Number -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            id="serial_no"
            v-model="form.serial_no"
            :label="$t('Serial Number')"
            :error="form.errors.serial_no"
            :placeholder="$t('Device serial number or IMEI')"
          />
        </div>

        <!-- Device Condition -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="device_condition"
            :label="$t('Device Condition')"
            v-model="form.device_condition"
            :suggestions="conditionOptions"
            :error="form.errors.device_condition"
          />
        </div>

        <!-- Device Password -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            id="device_password"
            type="text"
            v-model="form.device_password"
            :label="$t('Device Password/PIN')"
            :error="form.errors.device_password"
            :placeholder="$t('For phone/tablet unlock')"
          />
        </div>

        <!-- Device Pattern -->
        <!-- <div class="col-span-6 sm:col-span-3">
          <Input
            id="device_pattern"
            :label="$t('Device Pattern')"
            v-model="form.device_pattern"
            :error="form.errors.device_pattern"
            :placeholder="$t('Pattern lock description')"
          />
        </div> -->

        <!-- Custom Fields -->
        <div class="col-span-full flex flex-col gap-6">
          <CustomFields :errors="form.errors" :custom_fields="custom_fields" :extra_attributes="form.extra_attributes" />
        </div>

        <!-- Problem Description -->
        <div class="col-span-6">
          <Textarea
            rows="3"
            id="problem_description"
            :label="$t('Problem Description')"
            v-model="form.problem_description"
            :error="form.errors.problem_description"
            :placeholder="$t('Describe the issue reported by customer')"
          />
        </div>

        <!-- Technician -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="technician_id"
            :searchable="true"
            v-model="form.technician_id"
            :error="form.errors.technician_id"
            :label="$t('Assign to Technician')"
            :placeholder="$t('Select Technician')"
            :action="() => (add_technician = true)"
            :action-text="$t('Add {x}', { x: $t('Technician') })"
            :suggestions="technicians.map(t => ({ value: t.id, label: t.name }))"
          />
        </div>
        <div class="col-span-6 sm:col-span-2"></div>

        <!-- Status -->
        <div class="col-span-6 sm:col-span-2">
          <AutoComplete
            :json="true"
            id="status"
            :label="$t('Status')"
            v-model="form.status"
            :error="form.errors.status"
            :suggestions="statusOptions"
          />
        </div>

        <!-- Priority -->
        <div class="col-span-6 sm:col-span-2">
          <AutoComplete
            :json="true"
            id="priority"
            :label="$t('Priority')"
            v-model="form.priority"
            :error="form.errors.priority"
            :suggestions="priorityOptions"
          />
        </div>

        <!-- Due Date -->
        <div class="col-span-6 sm:col-span-2">
          <DateInput type="date" id="due_date" :label="$t('Due Date')" v-model="form.due_date" :error="form.errors.due_date" />
        </div>

        <!-- Price -->
        <div class="col-span-6 sm:col-span-3">
          <Input type="number" step="0.01" min="0" id="price" :label="$t('Price')" v-model="form.price" :error="form.errors.price" />
        </div>

        <!-- Actual Cost -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            type="number"
            step="0.01"
            min="0"
            id="actual_cost"
            :label="$t('Actual Cost')"
            v-model="form.actual_cost"
            :error="form.errors.actual_cost"
          />
        </div>

        <!-- Taxes -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            :multiple="true"
            :searchable="true"
            id="taxes"
            :label="$t('Taxes')"
            v-model="form.taxes"
            :error="form.errors.taxes"
            :placeholder="$t('Select Taxes')"
            :suggestions="taxes.map(t => ({ value: t.id, label: `${t.name} (${t.rate}%)` }))"
          />
        </div>

        <!-- Tax Amount -->
        <!-- <div class="col-span-6 sm:col-span-2">
          <Input
            type="number"
            step="0.01"
            min="0"
            id="tax_amount"
            :label="$t('Tax Amount')"
            v-model="form.tax_amount"
            :error="form.errors.tax_amount"
            :placeholder="$t('Calculated tax amount')"
          />
        </div> -->

        <!-- Tax Included -->
        <div class="col-span-6 sm:col-span-4">
          <div class="flex items-center">
            <input
              id="tax_included"
              type="checkbox"
              v-model="form.tax_included"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800"
            />
            <label for="tax_included" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ $t('Tax Included in Price') }}
            </label>
          </div>
          <p class="mt-1 text-xs text-gray-500">{{ $t('Check if the price/actual cost already includes taxes') }}</p>
          <div v-if="form.errors.tax_included" class="mt-2 text-sm text-red-600">
            {{ form.errors.tax_included }}
          </div>
        </div>

        <!-- Completed Date (only show if status is completed or delivered) -->
        <div v-if="['completed', 'delivered'].includes(form.status)" class="col-span-6 sm:col-span-3">
          <DateInput
            type="date"
            id="completed_date"
            :label="$t('Completed Date')"
            v-model="form.completed_date"
            :error="form.errors.completed_date"
          />
        </div>

        <!-- Technician Comment -->
        <div class="col-span-6">
          <Textarea
            rows="2"
            id="technician_comment"
            :label="$t('Technician Comment')"
            v-model="form.technician_comment"
            :error="form.errors.technician_comment"
            :placeholder="$t('Notes from the technician')"
          />
        </div>

        <!-- Internal Notes -->
        <div class="col-span-6 sm:col-span-6">
          <Textarea
            rows="2"
            id="internal_notes"
            :label="$t('Internal Notes')"
            v-model="form.internal_notes"
            :error="form.errors.internal_notes"
            :placeholder="$t('Internal notes (not visible to customer)')"
          />
        </div>

        <!-- Customer Notes -->
        <div class="col-span-6 sm:col-span-6">
          <Textarea
            rows="2"
            id="customer_notes"
            :label="$t('Customer Notes')"
            v-model="form.customer_notes"
            :error="form.errors.customer_notes"
            :placeholder="$t('Notes visible to customer on status page')"
          />
        </div>

        <!-- Attachments -->
        <div class="col-span-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $t('Attachments') }}
          </label>
          <div class="mt-1">
            <input
              multiple
              type="file"
              ref="fileInput"
              @change="handleFileChange"
              accept="image/*,application/pdf"
              class="block w-full rounded-md border border-gray-300 text-sm text-gray-500 file:-mx-1 file:mr-4 file:rounded-l-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 focus:border-primary-500 focus:outline-primary-500 dark:border-gray-700 dark:file:bg-blue-900 dark:file:text-blue-300 dark:hover:file:bg-blue-800"
            />
            <p class="mt-1 text-xs text-gray-500">{{ $t('Max 2MB per file. Images and PDF only.') }}</p>
          </div>

          <!-- Uploaded Files Preview -->
          <div v-if="uploadedFiles.length > 0" class="mt-4">
            <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('New Attachments') }}</h4>
            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
              <li v-for="(file, index) in uploadedFiles" :key="index" class="flex items-center justify-between py-2 pr-4 pl-3 text-sm">
                <div class="flex w-0 flex-1 items-center">
                  <Icon name="paperclip" class="size-5 flex-shrink-0 text-gray-400" />
                  <span class="ml-2 w-0 flex-1 truncate">{{ file.name }}</span>
                  <span class="ml-2 shrink-0 text-gray-400">{{ formatFileSize(file.size) }}</span>
                </div>
                <div class="ml-4 shrink-0">
                  <button @click="removeFile(index)" type="button" class="font-medium text-red-600 hover:text-red-500">
                    {{ $t('Remove') }}
                  </button>
                </div>
              </li>
            </ul>
          </div>

          <!-- Existing Attachments (Edit Mode) -->
          <div v-if="current?.attachments && current.attachments.length > 0" class="mt-4">
            <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('Existing Attachments') }}</h4>
            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
              <li
                v-for="attachment in current.attachments"
                :key="attachment.id"
                class="flex items-center justify-between py-2 pr-4 pl-3 text-sm"
              >
                <div class="flex w-0 flex-1 items-center">
                  <Icon name="paperclip" class="size-5 shrink-0 text-gray-400" />
                  <span class="ml-2 w-0 flex-1 truncate">{{ attachment.original_filename }}</span>
                  <span class="ml-2 shrink-0 text-gray-400">{{ formatFileSize(attachment.size) }}</span>
                </div>
                <div class="ml-4 flex shrink-0 space-x-2">
                  <a
                    target="_blank"
                    class="font-medium text-blue-600 hover:text-blue-500"
                    :href="route('repair-order-attachments.download', { attachment: attachment.id })"
                  >
                    {{ $t('Download') }}
                  </a>
                  <button
                    @click="removeExistingAttachment(attachment.id)"
                    type="button"
                    class="font-medium text-red-600 hover:text-red-500"
                  >
                    {{ $t('Delete') }}
                  </button>
                </div>
              </li>
            </ul>
          </div>

          <div v-if="form.errors.attachments" class="mt-2 text-sm text-red-600">
            {{ form.errors.attachments }}
          </div>
        </div>
      </template>

      <template #actions>
        <div class="flex w-full items-center justify-between">
          <SecondaryButton type="button" @click="resetForm" class="me-3">{{ $t('Reset') }}</SecondaryButton>

          <div class="flex items-center justify-end">
            <button
              type="button"
              v-if="current"
              class="btn-secondary"
              :loading="form.processing"
              :disabled="form.processing"
              @click="e => handleSubmit(e, true)"
            >
              {{ $t('Save & go to listing') }}
            </button>

            <ActionMessage :on="form.recentlySuccessful" class="ms-3 me-3"> {{ $t('Saved.') }} </ActionMessage>

            <LoadingButton type="button" @click="handleSubmit" :class="{ 'opacity-25': form.processing }" :loading="form.processing">
              {{ $t('Save') }}
            </LoadingButton>
          </div>
        </div>
        <!--
        <Button variant="secondary" @click="() => router.visit(route('repair-orders.index'))" type="button">
          {{ $t('Cancel') }}
        </Button>

        <Button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Save') }}
        </Button>

        <Button type="button" @click="e => handleSubmit(e, true)" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Save & Close') }}
        </Button> -->
      </template>
    </FormSection>
  </div>

  <Modal :show="add_service_type" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="add_service_type = false">
    <ServiceTypeForm
      @close="add_service_type = false"
      @done="
        e => {
          serviceTypes.push({ ...e });
          form.service_type_id = e?.id;
          add_service_type = false;
        }
      "
    />
  </Modal>

  <Modal :show="add_technician" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="add_technician = false">
    <TechnicianForm
      @close="add_technician = false"
      @done="
        e => {
          technicians.push({ ...e });
          form.technician_id = e?.id;
          add_technician = false;
        }
      "
    />
  </Modal>

  <Modal :show="add_customer" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="add_customer = false">
    <CustomerForm
      :countries="countries"
      :custom_fields="customer_fields"
      @close="add_customer = false"
      @done="
        e => {
          form.customer = { ...e };
          form.customer_id = e?.id;
          add_customer = false;
        }
      "
    />
  </Modal>
</template>
