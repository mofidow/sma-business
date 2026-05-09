<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { route } from 'ziggy-js';
import axios from 'axios';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Input, LoadingButton, CheckBox, TabMenus } from '@/Components/Common';
import { InputLabel, InputError, FormSection, ActionSection } from '@/Components/Jet';

const { t } = useI18n({});
defineOptions({ layout: AdminLayout });

const props = defineProps({
  defaultConnection: Object,
});

const tab_links = ref([
  { label: t('General Settings'), route: 'settings.index', icon: 'settings' },
  { label: t('Mail Settings'), route: 'settings.mail', icon: 'envelope' },
  { label: t('Payment Settings'), route: 'settings.payment', icon: 'dollar' },
  { label: t('Scale Barcode Settings'), route: 'settings.barcode', icon: 'scale' },
  { label: t('Import Data'), route: 'settings.import', icon: 'import' },
]);

if (route().has('settings.pos')) {
  tab_links.value.push({ label: t('POS Settings'), route: 'settings.pos', icon: 'pos' });
}
if (route().has('shop.settings')) {
  tab_links.value.push({ label: t('Shop Settings'), route: 'shop.settings', icon: 'shop', external: true });
}

const form = reactive({
  host: props.defaultConnection.host,
  port: props.defaultConnection.port,
  database: props.defaultConnection.database,
  username: props.defaultConnection.username,
  password: props.defaultConnection.password,
});

const importTypes = reactive({
  units: false,
  brands: false,
  tax_rates: false,
  customers: false,
  suppliers: false,
  categories: false,
  warehouses: false,
  products: false,
});

const errors = ref({});
const message = ref('');
const results = ref(null);
const testing = ref(false);
const importing = ref(false);
const connectionTested = ref(false);
const connectionSuccess = ref(false);

onMounted(() => {
  const savedForm = localStorage.getItem('v3_import_connection_form');
  if (savedForm) {
    Object.assign(form, JSON.parse(savedForm));
  }
});

const testConnection = async () => {
  errors.value = {};
  message.value = '';
  testing.value = true;
  connectionTested.value = false;
  connectionSuccess.value = false;

  try {
    const response = await axios.post(route('settings.import.test'), form);
    connectionTested.value = true;
    message.value = response.data.message;
    connectionSuccess.value = response.data.success;
    localStorage.setItem('v3_import_connection_form', JSON.stringify({ ...form, password: '' }));
    setTimeout(() => {
      document.getElementById('import-section')?.scrollIntoView({ behavior: 'smooth' });
    }, 150);
  } catch (error) {
    connectionTested.value = true;
    connectionSuccess.value = false;
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    message.value = error.response?.data?.message || t('Connection failed');
  } finally {
    testing.value = false;
  }
};

const processImport = async () => {
  const selectedTypes = Object.keys(importTypes).filter(key => importTypes[key]);

  if (selectedTypes.length === 0) {
    message.value = t('Please select at least one data type to import');
    return;
  }

  if (!connectionTested.value || !connectionSuccess.value) {
    message.value = t('Please test the connection first');
    return;
  }

  errors.value = {};
  message.value = '';
  results.value = null;
  importing.value = true;

  try {
    const response = await axios.post(route('settings.import.process'), {
      ...form,
      import_types: selectedTypes,
    });

    message.value = response.data.message;
    results.value = response.data.results;
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    message.value = error.response?.data?.message || t('Import failed');
  } finally {
    importing.value = false;
    setTimeout(() => {
      document.getElementById('import-results')?.scrollIntoView({ behavior: 'smooth' });
    }, 150);
  }
};

const getLevelClass = level => {
  switch (level) {
    case 'success':
      return 'text-green-600';
    case 'error':
      return 'text-red-600';
    case 'warning':
      return 'text-yellow-600';
    case 'skipped':
      return 'text-blue-600';
    default:
      return 'text-gray-600';
  }
};

const getLevelIcon = level => {
  switch (level) {
    case 'success':
      return '✓';
    case 'error':
      return '✗';
    case 'warning':
      return '⚠';
    case 'skipped':
      return '⊘';
    default:
      return '•';
  }
};
</script>

<template>
  <div>
    <Head :title="t('Import Data from SMA v3')" />
    <TabMenus :menus="tab_links" />

    <div class="mx-auto max-w-7xl py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="testConnection">
        <template #title>{{ t('Import Data from SMA v3') }}</template>
        <template #description>
          <div class="mt-4 mb-1 font-bold">{{ t('Database Connection') }}</div>
          {{ t('Enter the connection details for your old V3 database. Test the connection before proceeding with the import.') }}
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-4">
            <InputLabel for="host" :value="t('Host')" />
            <Input id="host" v-model="form.host" type="text" class="mt-1 block w-full" required />
            <InputError :message="errors.host?.[0]" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-2">
            <InputLabel for="port" :value="t('Port')" />
            <Input id="port" v-model="form.port" type="number" class="mt-1 block w-full" required />
            <InputError :message="errors.port?.[0]" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <InputLabel for="database" :value="t('Database')" />
            <Input id="database" v-model="form.database" type="text" class="mt-1 block w-full" required />
            <InputError :message="errors.database?.[0]" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <InputLabel for="username" :value="t('Username')" />
            <Input id="username" v-model="form.username" type="text" class="mt-1 block w-full" required />
            <InputError :message="errors.username?.[0]" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <InputLabel for="password" :value="t('Password')" />
            <Input id="password" v-model="form.password" type="password" class="mt-1 block w-full" />
            <InputError :message="errors.password?.[0]" class="mt-2" />
          </div>

          <div v-if="connectionTested" class="col-span-6">
            <div :class="['rounded-md p-4', connectionSuccess ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
              <p class="text-sm font-medium">{{ message }}</p>
            </div>
          </div>
        </template>

        <template #actions>
          <LoadingButton :loading="testing" @click.prevent="testConnection">
            {{ t('Test Connection') }}
          </LoadingButton>
        </template>
      </FormSection>

      <div v-if="connectionSuccess" class="pt-10" id="import-section">
        <FormSection @submitted="processImport">
          <template #title>{{ t('Select Data to Import') }}</template>
          <template #description>
            {{ t('Choose which data types you want to import from the old database. Existing records will be skipped.') }}
          </template>

          <template #form>
            <div class="col-span-6 space-y-4">
              <CheckBox v-model:checked="importTypes.warehouses" :label="t('Warehouses → Stores')" />
              <CheckBox v-model:checked="importTypes.categories" :label="t('Categories')" />
              <CheckBox v-model:checked="importTypes.brands" :label="t('Brands')" />
              <CheckBox v-model:checked="importTypes.units" :label="t('Units')" />
              <CheckBox v-model:checked="importTypes.tax_rates" :label="t('Taxes')" />
              <CheckBox v-model:checked="importTypes.customers" :label="t('Customers')" />
              <CheckBox v-model:checked="importTypes.suppliers" :label="t('Suppliers')" />
              <CheckBox v-model:checked="importTypes.products" :label="t('Products')" />
              <div>
                <div class="font-bold">Products should be imported last.</div>
                <div>Please ensure that you have imported Brands, Categories, Taxes, Units and Suppliers before importing Products.</div>
              </div>
            </div>

            <div v-if="message && !results" class="col-span-6">
              <div class="rounded-md bg-yellow-50 p-4 text-yellow-800">
                <p class="text-sm font-medium">{{ message }}</p>
              </div>
            </div>
          </template>

          <template #actions>
            <LoadingButton :loading="importing" :disabled="!connectionSuccess" @click.prevent="processImport">
              {{ t('Start Import') }}
            </LoadingButton>
          </template>
        </FormSection>
      </div>
      <ActionSection v-if="results" class="pt-10" id="import-results">
        <template #title>{{ t('Import Results') }}</template>
        <template #description>{{ message }}</template>
        <template #content>
          <div>
            <div class="">
              <dl>
                <div
                  :key="type"
                  v-for="(stat, type) in results.stats"
                  class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 dark:bg-gray-900"
                >
                  <dt class="text-sm font-medium text-gray-500 capitalize dark:text-gray-400">
                    {{ t(type) }}
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 dark:text-gray-100">
                    <div class="flex space-x-4">
                      <span>{{ t('Total') }}: {{ stat.total }}</span>
                      <span class="text-green-600">{{ t('Created') }}: {{ stat.created }}</span>
                      <span class="text-blue-600">{{ t('Skipped') }}: {{ stat.skipped }}</span>
                      <span class="text-red-600">{{ t('Failed') }}: {{ stat.failed }}</span>
                    </div>
                  </dd>
                </div>
              </dl>
            </div>

            <div v-if="results.logs && results.logs.length > 0" class="mt-6">
              <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
                <div class="px-4 py-5 sm:px-6">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                    {{ t('Import Logs') }}
                  </h3>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700">
                  <div class="max-h-96 overflow-y-auto">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                      <li v-for="(log, index) in results.logs" :key="index" class="px-4 py-3">
                        <div class="flex items-start space-x-3">
                          <span :class="getLevelClass(log.level)" class="flex-shrink-0 text-lg font-bold">
                            {{ getLevelIcon(log.level) }}
                          </span>
                          <div class="flex-1">
                            <p :class="getLevelClass(log.level)" class="text-sm">
                              <span class="font-medium capitalize">[{{ t(log.type) }}]</span>
                              {{ log.message }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ log.time }}</p>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </ActionSection>
    </div>
  </div>
</template>
