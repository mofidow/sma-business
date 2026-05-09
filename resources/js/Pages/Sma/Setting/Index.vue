<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { computed, onMounted, ref, version, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

import { $can } from '@/Core';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AutoComplete, CheckBox, Input, LoadingButton, TabMenus, Textarea, Toggle, RadioGroup } from '@/Components/Common';
import { InputError, InputLabel, TextInput, FormSection, ActionMessage, SecondaryButton } from '@/Components/Jet';

const { t } = useI18n({});
defineOptions({ layout: AdminLayout });
const props = defineProps({
  current: Object,
  accounts: Array,
  categories: Array,
  countries: Array,
  currencies: Array,
  timezones: Array,
  taxes: Array,
  stores: Array,
  laravel_version: String,
  fiscal_services: Array,
  fiscal_service_fields: Object,
});

const country = ref({ states: [] });
if (props.current?.country_id) {
  country.value = props.countries.find(c => c.id == props.current.country_id);
}

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  icon: null,
  icon_dark: null,
  logo: null,
  logo_dark: null,

  name: props.current?.name,
  short_name: props.current?.short_name,
  company: props.current?.company,
  reg_no: props.current?.reg_no,
  email: props.current?.email,
  phone: props.current?.phone,
  country_id: props.current?.country_id,
  state_id: props.current?.state_id,
  lot_no: props.current?.lot_no,
  street: props.current?.street,
  address_line_1: props.current?.address_line_1,
  address_line_2: props.current?.address_line_2,
  city: props.current?.city,
  postal_code: props.current?.postal_code,
  timezone_id: props.current?.timezone_id,
  currency_id: props.current?.currency_id,
  default_account: props.current?.default_account,
  default_store: props.current?.default_store,
  theme: props.current?.theme,
  hide_id: props.current?.hide_id,
  rows_per_page: props.current?.rows_per_page,
  language: props.current?.language,
  rtl_support: props.current?.rtl_support == 1 ? '1' : '0',
  date_number_locale: props.current?.date_number_locale,
  date_format: props.current?.date_format,
  fraction: props.current?.fraction,
  quantity_fraction: props.current?.quantity_fraction,
  max_discount: props.current?.max_discount,
  confirmation: props.current?.confirmation,
  dimension_unit: props.current?.dimension_unit,
  weight_unit: props.current?.weight_unit,
  reference: props.current?.reference,
  inventory_accounting: props.current?.inventory_accounting,
  search_delay: props.current?.search_delay,
  sale_template: props.current?.sale_template || 'One',
  stock: props.current?.stock == 1,
  overselling: props.current?.overselling == 1,
  impersonation: props.current?.impersonation == 1,
  hide_out_of_stock: props.current?.hide_out_of_stock == 1,
  require_country: props.current?.require_country == 1,
  show_image: props.current?.show_image == 1,
  show_tax: props.current?.show_tax == 1,
  show_tax_summary: props.current?.show_tax_summary == 1,
  show_discount: props.current?.show_discount == 1,
  show_zero_taxes: props.current?.show_zero_taxes == 1,
  loyalty: props.current?.loyalty || {
    staff: { spent: null, points: null },
    customer: { spent: null, points: null },
  },
  product_taxes: props.current?.product_taxes,
  inclusive_tax_formula: props.current?.inclusive_tax_formula,
  sale_header: props.current?.sale_header,
  sale_footer: props.current?.sale_footer,
  purchase_header: props.current?.purchase_header,
  purchase_footer: props.current?.purchase_footer,
  payment_header: props.current?.payment_header,
  payment_footer: props.current?.payment_footer,
  quotation_header: props.current?.quotation_header,
  quotation_footer: props.current?.quotation_footer,
  support_links: props.current?.support_links == 1,
  dark_sidebar: props.current?.dark_sidebar == 1,
  dark_topbar: props.current?.dark_topbar == 1,
  sidebar_dropdown: props.current?.sidebar_dropdown == 1,
  sidebar_scroll_to_view: props.current?.sidebar_scroll_to_view == 1,
  captcha_provider: props.current?.captcha_provider,
  captcha_site_key: props.current?.captcha_site_key,
  captcha_secret_key: props.current?.captcha_secret_key,
  fiscal_service_driver: props.current?.fiscal_service_driver,
  fiscal_service_settings: props.current?.fiscal_service_settings || {},
  telegram_bot_token: props.current?.telegram_bot_token,
  twilio_account_sid: props.current?.twilio_account_sid,
  twilio_auth_token: props.current?.twilio_auth_token,
  twilio_from: props.current?.twilio_from,
  twilio_sms_service_sid: props.current?.twilio_sms_service_sid,
});

const iconInput = ref(null);
const logoInput = ref(null);
const app_version = ref(null);
const iconPreview = ref(null);
const logoPreview = ref(null);
const iconDarkInput = ref(null);
const logoDarkInput = ref(null);
const iconDarkPreview = ref(null);
const logoDarkPreview = ref(null);

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

onMounted(async () => {
  const composer = await import('@r/../composer.json');
  app_version.value = composer.version;
});

const ToggleSupportLinks = v => {
  router.put(route('settings.support_links'), { value: v }, { preserveScroll: true });
};

const fiscalServices = computed(() => props.fiscal_services ?? []);
const fiscalServiceFields = computed(() => props.fiscal_service_fields ?? {});
const activeFiscalFields = computed(() => fiscalServiceFields.value?.[form.fiscal_service_driver] ?? []);
const hasFiscalServices = computed(() => fiscalServices.value.length > 0);

watch(
  () => fiscalServices.value.map(service => service.value),
  driverValues => {
    if (form.fiscal_service_driver && !driverValues.includes(form.fiscal_service_driver)) {
      form.fiscal_service_driver = null;
    }
  },
  { immediate: true }
);

const ensureFiscalSettings = driver => {
  if (!driver) {
    return;
  }
  if (!form.fiscal_service_settings || typeof form.fiscal_service_settings !== 'object') {
    form.fiscal_service_settings = {};
  }
  if (!form.fiscal_service_settings[driver]) {
    form.fiscal_service_settings = {
      ...form.fiscal_service_settings,
      [driver]: {},
    };
  }
};

const updateFiscalSetting = (key, value) => {
  const driver = form.fiscal_service_driver;
  if (!driver) {
    return;
  }
  ensureFiscalSettings(driver);
  form.fiscal_service_settings = {
    ...form.fiscal_service_settings,
    [driver]: {
      ...form.fiscal_service_settings[driver],
      [key]: value,
    },
  };
};

const getFiscalSettingValue = key => {
  const driver = form.fiscal_service_driver;
  if (!driver) {
    return '';
  }
  return form.fiscal_service_settings?.[driver]?.[key] ?? '';
};

const getFiscalSettingError = key => {
  const driver = form.fiscal_service_driver;
  if (!driver) {
    return '';
  }
  return form.errors?.[`fiscal_service_settings.${driver}.${key}`] ?? '';
};

watch(
  () => form.fiscal_service_driver,
  driver => {
    if (driver) {
      ensureFiscalSettings(driver);
    }
  },
  { immediate: true }
);

const save = () => {
  if (iconInput.value) {
    form.icon = iconInput.value.files[0];
  }
  if (iconDarkInput.value) {
    form.icon_dark = iconDarkInput.value.files[0];
  }
  if (logoInput.value) {
    form.logo = logoInput.value.files[0];
  }
  if (logoDarkInput.value) {
    form.logo_dark = logoDarkInput.value.files[0];
  }

  form.post(route('settings.store'), {
    onSuccess: () => {
      clearLogoFileInput();
      clearLogoFileInput(true);
    },
  });
};

const selectNewIcon = dark => {
  if (dark) {
    iconDarkInput.value.click();
  } else {
    iconInput.value.click();
  }
};

const selectNewLogo = dark => {
  if (dark) {
    logoDarkInput.value.click();
  } else {
    logoInput.value.click();
  }
};

const updateIconPreview = dark => {
  if (dark) {
    const iconDark = iconDarkInput.value.files[0];
    if (!iconDark) return;

    const reader = new FileReader();
    reader.onload = e => {
      iconDarkPreview.value = e.target.result;
    };
    reader.readAsDataURL(iconDark);
  } else {
    const icon = iconInput.value.files[0];
    if (!icon) return;

    const reader = new FileReader();
    reader.onload = e => {
      iconPreview.value = e.target.result;
    };
    reader.readAsDataURL(icon);
  }
};

const updateLogoPreview = dark => {
  if (dark) {
    const logoDark = logoDarkInput.value.files[0];
    if (!logoDark) return;

    const reader = new FileReader();
    reader.onload = e => {
      logoDarkPreview.value = e.target.result;
    };
    reader.readAsDataURL(logoDark);
  } else {
    const logo = logoInput.value.files[0];
    if (!logo) return;

    const reader = new FileReader();
    reader.onload = e => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(logo);
  }
};

const deleteIcon = dark => {
  router.delete(route('settings.icon.destroy', { dark }), {
    preserveScroll: true,
    onSuccess: () => {
      if (dark) {
        iconDarkPreview.value = null;
        clearIconFileInput(dark);
      } else {
        iconPreview.value = null;
        clearIconFileInput();
      }
    },
  });
};

const deleteLogo = dark => {
  router.delete(route('settings.logo.destroy', { dark }), {
    preserveScroll: true,
    onSuccess: () => {
      if (dark) {
        logoDarkPreview.value = null;
        clearLogoFileInput(dark);
      } else {
        logoPreview.value = null;
        clearLogoFileInput();
      }
    },
  });
};

const clearIconFileInput = dark => {
  if (dark && iconDarkInput.value?.value) {
    iconDarkInput.value.value = null;
  } else if (iconInput.value?.value) {
    iconInput.value.value = null;
  }
};

const clearLogoFileInput = dark => {
  if (dark && logoDarkInput.value?.value) {
    logoDarkInput.value.value = null;
  } else if (logoInput.value?.value) {
    logoInput.value.value = null;
  }
};
</script>

<template>
  <Head>
    <title>{{ $t('Application Settings') }}</title>
  </Head>

  <TabMenus :links="tab_links" />

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="save">
      <template #title> {{ $t('Application Settings') }} </template>

      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          {{ $t('General settings for the application use and views') }}
          <div class="mt-6 flex gap-x-4 gap-y-1">
            <div class="me-3">
              <Link :href="route('settings.import')" class="link">{{ $t('Import Data from SMA v3') }}</Link>
            </div>
            <div v-if="$can('manage-modules') && !$page.props.demo" class="me-3">
              <a :href="route('modules')" target="_blank" class="link mt-4">{{ $t('Manage Modules') }}</a>
            </div>
          </div>
        </div>
      </template>

      <template #form>
        <!-- Icon -->
        <div class="col-span-6 sm:col-span-3">
          <!-- Icon File Input -->
          <input id="icon" ref="iconInput" type="file" class="hidden" @change="() => updateIconPreview()" />

          <InputLabel for="icon" :value="$t('Icon')" />

          <!-- Current Icon -->
          <div v-show="!iconPreview && current.icon" class="mt-2 rounded-md bg-gray-50 p-1">
            <img :alt="$t('Icon')" :src="current.icon" class="max-h-20 w-full max-w-64 rounded-md" />
          </div>

          <!-- New Icon Preview -->
          <div v-show="iconPreview" class="mt-2 rounded-md bg-gray-50 p-1">
            <span
              class="block h-20 w-full max-w-64 rounded-md bg-center bg-no-repeat"
              :style="'background-image: url(\'' + iconPreview + '\');'"
            />
          </div>

          <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="() => selectNewIcon()">
            {{ $t('Select A New Icon') }}
          </SecondaryButton>

          <SecondaryButton type="button" v-if="current.icon" @click.prevent="() => deleteIcon()" class="mt-2 rounded-md bg-gray-50 p-1">
            {{ $t('Remove Icon') }}
          </SecondaryButton>

          <InputError :message="form.errors.icon" class="mt-2" />
        </div>

        <!-- Dark Icon -->
        <div class="col-span-6 sm:col-span-3">
          <!-- Dark Icon File Input -->
          <input id="icon_dark" ref="iconDarkInput" type="file" class="hidden" @change="() => updateIconPreview(true)" />

          <InputLabel for="icon_dark" :value="$t('Dark Mode Icon')" />

          <!-- Current Dark Icon -->
          <div v-show="!iconDarkPreview && current.icon_dark" class="mt-2 rounded-md bg-gray-950 p-1">
            <img :alt="$t('Dark Mode Icon')" :src="current.icon_dark" class="max-h-20 w-full max-w-64 rounded-md" />
          </div>

          <!-- New Dark Icon Preview -->
          <div v-show="iconDarkPreview" class="mt-2 rounded-md bg-gray-950 p-1">
            <span
              class="block h-20 w-full max-w-64 rounded-md bg-center bg-no-repeat"
              :style="'background-image: url(\'' + iconDarkPreview + '\');'"
            />
          </div>

          <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="() => selectNewIcon(true)">
            {{ $t('Select A New Icon') }}
          </SecondaryButton>

          <SecondaryButton v-if="current.icon_dark" type="button" class="mt-2" @click.prevent="() => deleteIcon(true)">
            {{ $t('Remove Icon') }}
          </SecondaryButton>

          <InputError :message="form.errors.icon_dark" class="mt-2" />
        </div>

        <!-- Logo -->
        <div class="col-span-6 sm:col-span-3">
          <!-- Logo File Input -->
          <input id="logo" ref="logoInput" type="file" class="hidden" @change="() => updateLogoPreview()" />

          <InputLabel for="logo" :value="$t('Logo')" />

          <!-- Current Logo -->
          <div v-show="!logoPreview && current.logo" class="mt-2 rounded-md bg-gray-50 p-1">
            <img :alt="$t('Logo')" :src="current.logo" class="max-h-20 w-full max-w-64 rounded-md" />
          </div>

          <!-- New Logo Preview -->
          <div v-show="logoPreview" class="mt-2 rounded-md bg-gray-50 p-1">
            <span
              class="block h-20 w-full max-w-64 rounded-md bg-center bg-no-repeat"
              :style="'background-image: url(\'' + logoPreview + '\');'"
            />
          </div>

          <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="() => selectNewLogo()">
            {{ $t('Select A New Logo') }}
          </SecondaryButton>

          <SecondaryButton v-if="current.logo" type="button" class="mt-2" @click.prevent="() => deleteLogo()">
            {{ $t('Remove Logo') }}
          </SecondaryButton>

          <InputError :message="form.errors.logo" class="mt-2" />
        </div>

        <!-- Dark Logo -->
        <div class="col-span-6 sm:col-span-3">
          <!-- Dark Logo File Input -->
          <input id="logo_dark" ref="logoDarkInput" type="file" class="hidden" @change="() => updateLogoPreview(true)" />

          <InputLabel for="logo_dark" :value="$t('Dark Mode Logo')" />

          <!-- Current Dark Logo -->
          <div v-show="!logoDarkPreview && current.logo_dark" class="mt-2 rounded-md bg-gray-950 p-1">
            <img :alt="$t('Dark Mode Logo')" :src="current.logo_dark" class="max-h-20 w-full max-w-64 rounded-md" />
          </div>

          <!-- New Dark Logo Preview -->
          <div v-show="logoDarkPreview" class="mt-2 rounded-md bg-gray-950 p-1">
            <span
              class="block h-20 w-full max-w-64 rounded-md bg-center bg-no-repeat"
              :style="'background-image: url(\'' + logoDarkPreview + '\');'"
            />
          </div>

          <SecondaryButton class="me-2 mt-2" type="button" @click.prevent="() => selectNewLogo(true)">
            {{ $t('Select A New Logo') }}
          </SecondaryButton>

          <SecondaryButton v-if="current.logo_dark" type="button" class="mt-2" @click.prevent="() => deleteLogo(true)">
            {{ $t('Remove Logo') }}
          </SecondaryButton>

          <InputError :message="form.errors.logo_dark" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="name" :label="$t('Name')" v-model="form.name" :error="form.errors.name" />
        </div>
        <!-- Short Name -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="short_name" :label="$t('Short Name')" v-model="form.short_name" :error="form.errors.short_name" />
        </div>

        <!-- Company -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="company" :label="$t('Company')" v-model="form.company" :error="form.errors.company" />
        </div>
        <!-- Registration Number -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="reg_no" :label="$t('Registration Number')" v-model="form.reg_no" :error="form.errors.reg_no" />
        </div>

        <!-- Phone -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="phone" :label="$t('Phone')" v-model="form.phone" :error="form.errors.phone" />
        </div>
        <!-- Email -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="email" :label="$t('Email')" type="email" v-model="form.email" :error="form.errors.email" />
        </div>

        <!-- Address Fields -->
        <div class="col-span-full -mt-6"></div>
        <!-- Country -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="country_id"
            :label="$t('Country')"
            v-model="form.country_id"
            :error="form.errors.country_id"
            :suggestions="countries.map(c => ({ ...c, value: c.id, label: c.name }))"
            @change="
              e => {
                country = e;
                form.state_id = country?.states[0]?.id;
                form.timezone_id = timezones.filter(t => (country?.id ? t.country_id == country.id : true))[0]?.id;
              }
            "
          />
        </div>
        <!-- State -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="state_id"
            :label="$t('State')"
            v-model="form.state_id"
            :error="form.errors.state_id"
            :suggestions="
              country?.states?.length ? country.states.map(s => ({ ...s, value: s.id, label: s.name })) : [{ value: '0', label: $t('N/A') }]
            "
          />
        </div>

        <!-- Lot No -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="lot_no" :label="$t('Lot No.')" v-model="form.lot_no" :error="form.errors.lot_no" />
        </div>
        <!-- Street -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="street" :label="$t('Street')" v-model="form.street" :error="form.errors.street" />
        </div>
        <!-- Address Line 1 -->
        <div class="col-span-full">
          <Input id="address_line_1" :label="$t('Address Line 1')" v-model="form.address_line_1" :error="form.errors.address_line_1" />
        </div>
        <!-- Address Line 2 -->
        <div class="col-span-full">
          <Input id="address_line_2" :label="$t('Address Line 2')" v-model="form.address_line_2" :error="form.errors.address_line_2" />
        </div>
        <!-- City -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="city" :label="$t('City')" v-model="form.city" :error="form.errors.city" />
        </div>
        <!-- Postal/ZIP Code -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="postal_code" :label="$t('Postal/ZIP Code')" v-model="form.postal_code" :error="form.errors.postal_code" />
        </div>

        <div class="col-span-full -mt-6"></div>
        <!-- Timezone -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="timezone_id"
            :label="$t('Timezone')"
            v-model="form.timezone_id"
            :error="form.errors.timezone_id"
            :suggestions="
              timezones.filter(t => (country?.id ? t.country_id == country.id : true)).map(c => ({ ...c, value: c.id, label: c.name }))
            "
          />
        </div>
        <!-- Default Currency -->
        <!-- <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="currency_id"
            :suggestions="currencies"
            v-model="form.currency_id"
            :label="$t('Default Currency')"
            :error="form.errors.currency_id"
          />
        </div> -->

        <!-- Default Account -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="default_account"
            :suggestions="accounts"
            :label="$t('Default Account')"
            v-model="form.default_account"
            :error="form.errors.default_account"
          />
        </div>

        <!-- Default Store -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="default_store"
            :suggestions="stores"
            v-model="form.default_store"
            :error="form.errors.default_store"
            :label="$t('Default Store') + ' (' + $t('For AI/MCP Server') + ')'"
          />
        </div>

        <!-- Language -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="language"
            :searchable="false"
            v-model="form.language"
            :label="$t('Language')"
            :error="$page.props.errors.language"
            :suggestions="$page.props.languages"
          />
        </div>

        <!-- RTL Support -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="rtl_support"
            :searchable="false"
            v-model="form.rtl_support"
            :label="$t('RTL Support')"
            :error="$page.props.errors.rtl_support"
            :suggestions="[
              { value: '1', label: $t('Enabled') },
              { value: '0', label: $t('Disabled') },
            ]"
          />
        </div>

        <!-- Date & Number Locale -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            id="date_number_locale"
            v-model="form.date_number_locale"
            :label="$t('Date & Number Locale')"
            :error="form.errors.date_number_locale"
          />
        </div>

        <!-- Date format -->
        <!-- <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="date_format"
            :searchable="false"
            v-model="form.date_format"
            :label="$t('Date Format')"
            :error="form.errors.date_format"
            :suggestions="[
              { value: 'php', label: $t('On Server Side (PHP)') },
              { value: 'js', label: $t('In Browser (Javascript)') },
            ]"
          />
        </div> -->

        <!-- Reference -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="reference"
            :searchable="false"
            v-model="form.reference"
            :label="$t('Reference')"
            :error="$page.props.errors.reference"
            :suggestions="[
              { value: '', label: $t('SequenceNumber') },
              { value: 'Y', label: $t('YearSequenceNumber') },
              { value: 'Y/', label: $t('Year/SequenceNumber') },
              { value: 'Y-', label: $t('Year-SequenceNumber') },
              { value: 'Ym', label: $t('YearMonthSequenceNumber') },
              { value: 'Y/m/', label: $t('Year/Month/SequenceNumber') },
              { value: 'Y-m-', label: $t('Year-Month-SequenceNumber') },
              { value: 'ulid', label: 'ULID - Universally Unique Lexicographically Sortable Identifier' },
              { value: 'ai', label: 'Auto Increment (MYSQL only)' },
              { value: 'uniqid', label: 'Uniqid - PHP Generate a Unique ID' },
              { value: 'uuid', label: 'UUID - Universally Unique Identifier' },
            ]"
          />
        </div>

        <!-- Sale Template -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="sale_template"
            :searchable="false"
            v-model="form.sale_template"
            :label="$t('Sale Template')"
            :error="$page.props.errors.sale_template"
            :suggestions="[
              { value: 'Minimal', label: $t('Template {x}', { x: $t('Minimal') }) },
              { value: 'One', label: $t('Template {x}', { x: $t('One') }) },
              { value: 'Two', label: $t('Template {x}', { x: $t('Two') }) },
              { value: 'Three', label: $t('Template {x}', { x: $t('Three') }) },
              { value: 'Four', label: $t('Template {x}', { x: $t('Four') }) },
              { value: 'Five', label: $t('Template {x}', { x: $t('Five') }) },
              { value: 'Six', label: $t('Template {x}', { x: $t('Six') }) },
            ]"
          />
        </div>

        <!-- Inventory Accounting Method -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            :searchable="false"
            id="inventory_accounting"
            v-model="form.inventory_accounting"
            :label="$t('Inventory Accounting Method')"
            :error="$page.props.errors.inventory_accounting"
            :suggestions="[
              { value: 'FIFO', label: 'FIFO - First In First Out' },
              { value: 'LIFO', label: 'LIFO - Last In First Out' },
              { value: 'AVCO', label: 'AVCO - Average Cost' },
              { value: 'EXPF', label: 'Items Expiring Soon' },
              { value: 'Batch', label: 'Use Batch Number', disabled: true },
            ]"
          />
        </div>

        <!-- Row Per Page -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            min="10"
            max="100"
            type="number"
            id="rows_per_page"
            :label="$t('Row Per Page')"
            v-model="form.rows_per_page"
            :error="form.errors.rows_per_page"
          />
        </div>

        <!-- Search Delay -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            min="0"
            max="1000"
            type="number"
            id="search_delay"
            :label="$t('Search Delay') + ' (ms)'"
            v-model="form.search_delay"
            :error="form.errors.search_delay"
          />
        </div>

        <!-- Fraction -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            min="0"
            max="4"
            type="number"
            id="fraction"
            v-model="form.fraction"
            :error="form.errors.fraction"
            :label="$t('Fraction') + ' (' + $t('Decimal Places') + ')'"
          />
        </div>

        <!-- Quantity Fraction -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            min="0"
            max="4"
            type="number"
            id="quantity_fraction"
            v-model="form.quantity_fraction"
            :error="form.errors.quantity_fraction"
            :label="$t('Quantity Fraction') + ' (' + $t('Decimal Places') + ')'"
          />
        </div>

        <!-- Maximum Discount -->
        <div class="col-span-6 sm:col-span-3">
          <Input
            min="0"
            max="100"
            type="number"
            id="max_discount"
            v-model="form.max_discount"
            :label="$t('Maximum Discount') + ' (%)'"
            :error="form.errors.max_discount"
          />
        </div>

        <!-- Dimension Unit -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            :searchable="false"
            id="dimension_unit"
            v-model="form.dimension_unit"
            :label="$t('Dimension Unit')"
            :error="$page.props.errors.dimension_unit"
            :suggestions="['Millimeter', 'Centimeter', 'Inch', 'Foot', 'Yard', 'Meter']"
          />
        </div>

        <!-- Weight Unit -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            id="weight_unit"
            v-model="form.weight_unit"
            :label="$t('Weight Unit')"
            :error="$page.props.errors.weight_unit"
            :suggestions="['Gram', 'Kilogram', 'Ounce', 'Pound', 'Tonne']"
          />
        </div>

        <!-- Captcha -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            json
            id="captcha_provider"
            :label="$t('Captcha Provider')"
            v-model="form.captcha_provider"
            :error="$page.props.errors.captcha_provider"
            :suggestions="[
              { label: 'None', value: '' },
              { label: 'Local', value: 'local' },
              { label: 'Google reCAPTCHA', value: 'recaptcha' },
              { label: 'CloudFlare Turnstile', value: 'trunstile' },
            ]"
          />
        </div>
        <template v-if="form.captcha_provider && form.captcha_provider != 'local'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              id="captcha_site_key"
              v-model="form.captcha_site_key"
              :label="$t('Captcha Site Key')"
              :error="form.errors.captcha_site_key"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <Input
              id="captcha_secret_key"
              v-model="form.captcha_secret_key"
              :label="$t('Captcha Secret Key')"
              :error="form.errors.captcha_secret_key"
            />
          </div>
        </template>

        <!-- Default Product Taxes -->
        <div class="col-span-full">
          <AutoComplete
            :json="true"
            value-key="id"
            label-key="name"
            :multiple="true"
            id="product_taxes"
            :suggestions="taxes"
            v-model="form.product_taxes"
            :label="$t('Default Product Taxes')"
            :error="$page.props.errors.product_taxes"
          />
        </div>
        <div class="col-span-full">
          <InputLabel for="inclusive_tax_formula" :value="$t('Inclusive Tax Calculation Formula')" />
          <fieldset aria-label="Privacy setting" class="mt-1 -space-y-px rounded-md bg-white dark:bg-gray-900">
            <label
              class="group flex cursor-pointer border border-gray-200 p-4 first:rounded-tl-md first:rounded-tr-md last:rounded-br-md last:rounded-bl-md focus:outline-hidden has-checked:relative has-checked:border-primary-200 has-checked:bg-primary-50 dark:border-gray-700 dark:has-checked:border-primary-800 dark:has-checked:bg-primary-950"
            >
              <input
                type="radio"
                value="inclusive"
                name="inclusive_tax_formula"
                v-model="form.inclusive_tax_formula"
                class="relative mt-0.5 size-4 shrink-0 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-primary-600 checked:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden"
              />
              <span class="ms-3 flex flex-col">
                <span class="text-focused block text-sm font-bold">{{ $t('Inclusive') }}</span>
                <span class="mt-2 block font-mono text-xs">
                  100 * 10 / (100 + 10) = 9.09<br />
                  {{ $t('Price * Tax Rate / (100 + Tax Rate) = Tax Amount') }}<br />
                  <span class="mt-2 block font-bold">{{ $t('So the net price will be 100 - 9.09 = 90.91') }}</span>
                </span>
              </span>
            </label>
            <label
              class="group flex cursor-pointer border border-gray-200 p-4 first:rounded-tl-md first:rounded-tr-md last:rounded-br-md last:rounded-bl-md focus:outline-hidden has-checked:relative has-checked:border-primary-200 has-checked:bg-primary-50 dark:border-gray-700 dark:has-checked:border-primary-800 dark:has-checked:bg-primary-950"
            >
              <input
                type="radio"
                value="exclusive"
                name="inclusive_tax_formula"
                v-model="form.inclusive_tax_formula"
                class="relative mt-0.5 size-4 shrink-0 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-primary-600 checked:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden"
              />
              <span class="ms-3 flex flex-col">
                <span class="text-focused block text-sm font-bold">{{ $t('Exclusive') }}</span>
                <span class="mt-2 block font-mono text-xs">
                  100 * 10 / 100 = 10<br />
                  {{ $t('Price * Tax Rate / 100 = Tax Amount') }}<br />
                  <span class="mt-2 block font-bold">{{ $t('So the net price will be 100 - 10 = 90.00') }}</span>
                </span>
              </span>
            </label>
          </fieldset>
        </div>

        <div class="col-span-full rounded-md border border-yellow-600 p-4 dark:border-yellow-400">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h4 class="font-bold">{{ $t('Fiscal Service Settings') }} ({{ $t('Electronic Invoicing') }})</h4>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Configure the integration used for fiscal reporting.') }}
              </p>
              <p class="mt-3 animate-[bounce_2s_ease-in-out_infinite] text-sm font-black text-yellow-600 dark:text-yellow-400">
                {{ $t('This feature is not yet tested, please help us test in sandbox/test environment only.') }}
              </p>
              <p class="mt-1 text-sm font-bold">
                {{ $t('If your country have electronic invoicing requirements, please let us know on support portal.') }}
              </p>
            </div>
          </div>

          <div v-if="hasFiscalServices" class="mt-4">
            <AutoComplete
              :json="true"
              :clearable="true"
              :searchable="true"
              id="fiscal_service_driver"
              :label="$t('Fiscal Service')"
              :suggestions="fiscalServices"
              v-model="form.fiscal_service_driver"
              :error="form.errors.fiscal_service_driver"
            />

            <div class="col-span-full mt-4 flex flex-col gap-4">
              <template v-for="field in activeFiscalFields" :key="field.key">
                <div>
                  <component
                    :key="field.key"
                    :label="field.label"
                    :options="field.options"
                    :id="`fiscal-${field.key}`"
                    :error="getFiscalSettingError(field.key)"
                    :placeholder="field.placeholder || field.label"
                    :model-value="getFiscalSettingValue(field.key)"
                    :type="field.component === 'input' ? field.type : undefined"
                    @update:modelValue="value => updateFiscalSetting(field.key, value)"
                    :rows="field.component === 'textarea' ? field.rows || '4' : undefined"
                    :is="field.component === 'textarea' ? Textarea : field.component === 'radio' ? RadioGroup : Input"
                  />
                  <p v-if="field.help" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ field.help }}
                  </p>
                </div>
              </template>
            </div>

            <template v-if="form.fiscal_service_driver">
              <div class="relative">
                <div
                  class="absolute inset-0 -mx-4 -mt-2 -mb-0.5 flex items-center justify-end bg-yellow-200/50 pe-4 text-sm font-bold dark:bg-yellow-500/20"
                >
                  Coming Soon
                </div>
                <div v-if="form.fiscal_service_driver" class="mt-6 flex gap-x-8 gap-y-3">
                  <CheckBox
                    id="fiscal_service_when_paid"
                    :label="$t('Report when sales are paid')"
                    v-model:checked="form.fiscal_service_settings.fiscal_service_when_paid"
                  />
                  <CheckBox
                    id="fiscal_service_end_of_day"
                    v-model:checked="form.fiscal_service_settings.fiscal_service_end_of_day"
                    :label="$t('Report all sales at the end of day')"
                  />
                </div>
              </div>
              <div class="mt-4 text-sm font-bold text-yellow-700 dark:text-yellow-400">
                <template
                  v-if="form.fiscal_service_settings.fiscal_service_when_paid && form.fiscal_service_settings?.fiscal_service_end_of_day"
                >
                  {{ $t('Paid sales are reported at the end of the day.') }}
                </template>
                <template v-else-if="form.fiscal_service_settings.fiscal_service_when_paid">
                  {{ $t('Sale is reported once payment is received.') }}
                </template>
                <template v-else-if="form.fiscal_service_settings?.fiscal_service_end_of_day">
                  {{ $t('Sales are reported at the end of the day.') }}
                </template>
                <template
                  v-else-if="
                    !form.fiscal_service_settings.fiscal_service_when_paid && !form.fiscal_service_settings?.fiscal_service_end_of_day
                  "
                >
                  {{ $t('Sale is reported once saved.') }}
                </template>
              </div>
            </template>
          </div>

          <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ $t('No fiscal service drivers are available yet.') }}
          </p>
        </div>

        <!-- Notification Settings -->
        <div class="col-span-full rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h4 class="font-bold">{{ $t('Notification Settings') }}</h4>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Configure notification channels for sending alerts via Telegram and SMS.') }}
              </p>
            </div>
          </div>

          <!-- Telegram Settings -->
          <div class="mt-4">
            <h5 class="text-sm font-bold">{{ $t('Telegram') }}</h5>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ $t('Get your bot token from {x} on Telegram.', { x: '@BotFather' }) }}
            </p>
            <div class="mt-2">
              <Input
                id="telegram_bot_token"
                :label="$t('Bot Token')"
                v-model="form.telegram_bot_token"
                :error="form.errors.telegram_bot_token"
                :placeholder="$t('Enter your Telegram Bot Token')"
              />
            </div>
          </div>

          <!-- Twilio Settings -->
          <div class="mt-6">
            <h5 class="text-sm font-bold">{{ $t('Twilio SMS') }}</h5>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ $t('Get your credentials from the Twilio Console.') }}
            </p>
            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <Input
                  id="twilio_account_sid"
                  :label="$t('Account SID')"
                  v-model="form.twilio_account_sid"
                  :error="form.errors.twilio_account_sid"
                />
              </div>
              <div>
                <Input
                  id="twilio_auth_token"
                  :label="$t('Auth Token')"
                  v-model="form.twilio_auth_token"
                  :error="form.errors.twilio_auth_token"
                />
              </div>
              <div>
                <Input
                  id="twilio_from"
                  :label="$t('From Number')"
                  v-model="form.twilio_from"
                  :error="form.errors.twilio_from"
                  :placeholder="$t('+15551234567')"
                />
              </div>
              <div>
                <Input
                  id="twilio_sms_service_sid"
                  :label="$t('Messaging Service SID')"
                  v-model="form.twilio_sms_service_sid"
                  :error="form.errors.twilio_sms_service_sid"
                  :placeholder="$t('Optional but recommended')"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="col-span-full flex flex-col gap-2 overflow-x-auto rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <!-- <Toggle id="hide_id" :label="$t('Hide ID')" v-model="form.hide_id" /> -->
          <Toggle id="stock" :label="$t('Track stock')" v-model="form.stock" />
          <Toggle id="overselling" :label="$t('Allow overselling')" v-model="form.overselling" />
          <Toggle id="impersonation" :label="$t('Enable impersonation')" v-model="form.impersonation" />
          <Toggle id="hide_out_of_stock" :label="$t('Hide out of stock products in store')" v-model="form.hide_out_of_stock" />
          <Toggle
            id="require_country"
            v-model="form.require_country"
            :label="$t('Require Country & State')"
            :text="'(' + $t('you must enable if you have state level taxes') + ')'"
          />
        </div>

        <div class="col-span-full flex flex-col gap-2 overflow-x-auto rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <h4 class="mb-3 text-sm font-bold">{{ $t('Sidebar') }}</h4>
          <Toggle :label="$t('Dark top bar')" v-model="form.dark_topbar" />
          <Toggle :label="$t('Dark side bar')" v-model="form.dark_sidebar" />
          <Toggle :label="$t('Show menus as dropdown')" v-model="form.sidebar_dropdown" />
          <Toggle :label="$t('Scroll to view on menu & page change')" v-model="form.sidebar_scroll_to_view" />
        </div>

        <div class="col-span-full flex flex-col gap-2 overflow-x-auto rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <h4 class="mb-3 text-sm font-bold">{{ $t('Receipt/Sale View') }}</h4>
          <Toggle id="show_image" :label="$t('Show image')" v-model="form.show_image" />
          <Toggle id="show_taxes" :label="$t('Show tax column')" v-model="form.show_tax" />
          <Toggle id="show_tax_summary" :label="$t('Show tax summary')" v-model="form.show_tax_summary" />
          <Toggle id="show_discount" :label="$t('Show discount column')" v-model="form.show_discount" />
          <Toggle id="show_zero_taxes" :label="$t('Show taxes with zero value')" v-model="form.show_zero_taxes" />
        </div>

        <!-- Loyalty Points -->
        <div class="col-span-full overflow-x-auto rounded-md border border-gray-200 p-4 dark:border-gray-700">
          <h4 class="font-bold">{{ $t('Loyalty Points') }}</h4>
          <div class="mt-4 text-sm">
            <h5 class="font-bold">{{ $t('For Customers') }}</h5>
            <div class="flex items-center gap-2">
              {{ $t('Each sale amounting to') }}
              <TextInput class="w-28" id="loyalty_customer_spent" type="number" v-model="form.loyalty.customer.spent" />
              {{ $t('will earn') }}
              <TextInput class="w-28" id="loyalty_customer_points" type="number" v-model="form.loyalty.customer.points" />
              {{ $t('points') }}.
            </div>
          </div>
          <div class="mt-4 text-sm">
            <h5 class="font-bold">{{ $t('For Staff') }}</h5>
            <div class="flex items-center gap-2">
              {{ $t('Each sale amounting to') }}
              <TextInput class="w-28" id="loyalty_staff_spent" type="number" v-model="form.loyalty.staff.spent" />
              {{ $t('will earn') }} <TextInput class="w-28" id="loyalty_staff_points" type="number" v-model="form.loyalty.staff.points" />
              {{ $t('points') }}.
            </div>
          </div>
        </div>

        <!-- Sale Header -->
        <div class="col-span-full">
          <Textarea id="sale_header" :label="$t('Sale Header')" v-model="form.sale_header" :error="form.errors.sale_header" />
        </div>
        <!-- Sale Footer -->
        <div class="col-span-full">
          <Textarea id="sale_footer" :label="$t('Sale Footer')" v-model="form.sale_footer" :error="form.errors.sale_footer" />
        </div>

        <!-- Purchase Header -->
        <div class="col-span-full">
          <Textarea
            id="purchase_header"
            :label="$t('Purchase Header')"
            v-model="form.purchase_header"
            :error="form.errors.purchase_header"
          />
        </div>
        <!-- Purchase Footer -->
        <div class="col-span-full">
          <Textarea
            id="purchase_footer"
            :label="$t('Purchase Footer')"
            v-model="form.purchase_footer"
            :error="form.errors.purchase_footer"
          />
        </div>

        <!-- Payment Header -->
        <div class="col-span-full">
          <Textarea id="payment_header" :label="$t('Payment Header')" v-model="form.payment_header" :error="form.errors.payment_header" />
        </div>
        <!-- Payment Footer -->
        <div class="col-span-full">
          <Textarea id="payment_footer" :label="$t('Payment Footer')" v-model="form.payment_footer" :error="form.errors.payment_footer" />
        </div>

        <!-- Quotation Header -->
        <div class="col-span-full">
          <Textarea
            id="quotation_header"
            :label="$t('Quotation Header')"
            v-model="form.quotation_header"
            :error="form.errors.quotation_header"
          />
        </div>
        <!-- Quotation Footer -->
        <div class="col-span-full">
          <Textarea
            id="quotation_footer"
            :label="$t('Quotation Footer')"
            v-model="form.quotation_footer"
            :error="form.errors.quotation_footer"
          />
        </div>
      </template>

      <template #actions>
        <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

        <LoadingButton :class="{ 'opacity-25': form.processing }" :loading="form.processing"> {{ $t('Save') }} </LoadingButton>
      </template>
    </FormSection>

    <div v-if="app_version" class="mt-8 mb-2 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 sm:-mb-4">
      <div>
        App: <span class="font-bold"> {{ app_version }} </span>
      </div>
      <div>
        <Toggle :label="$t('Support Links')" v-model="form.support_links" @change="ToggleSupportLinks" />
      </div>
      <div>
        Laravel: <span class="font-bold"> {{ laravel_version }} </span>
      </div>
      <div>
        Vue: <span class="font-bold"> {{ version }} </span>
      </div>
      <div class="flex items-center gap-2">
        Docs:
        <a target="_blank" href="/documentation.pdf" class="group relative me-4 font-bold">
          PDF
          <Icon name="a-blank" class="absolute start-full top-0.5 ms-1 hidden h-4 w-4 group-hover:block" />
        </a>
        <a target="_blank" href="https://tecdiary.github.io/sma-guide" class="group relative text-sm font-bold">
          Online Version
          <Icon name="a-blank" class="absolute start-full top-0 ms-1 hidden h-4 w-4 group-hover:block" />
        </a>
      </div>
    </div>
  </div>
</template>
