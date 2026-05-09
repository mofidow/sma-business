<script setup>
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FormSection, ActionMessage } from '@/Components/Jet';
import { Input, AutoComplete, CheckBox, LoadingButton, TabMenus, Textarea } from '@/Components/Common';

const { t } = useI18n({});
defineOptions({ layout: AdminLayout });
const props = defineProps({ current: Object, payment_methods: Array });

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
  gateway: props.current?.gateway || null,
  default_currency: props.current?.default_currency || 'USD',
  stripe_terminal: props.current?.stripe_terminal == 1 || false,
  static_payment_methods: props.current?.static_payment_methods ? props.current.static_payment_methods.join('\n') : '',
  services: {
    ...props.current?.services,
    paypal: {
      enabled: props.current?.services?.paypal?.enabled == true,
      client_id: props.current?.services?.paypal?.client_id || null,
      secret: props.current?.services?.paypal?.secret || null,
      fixed: props.current?.services?.paypal?.fixed || null,
      same_country: props.current?.services?.paypal?.same_country || null,
      other_countries: props.current?.services?.paypal?.other_countries || null,
    },
    stripe: {
      key: props.current?.services?.stripe?.key || null,
      secret: props.current?.services?.stripe?.secret || null,
      fixed: props.current?.services?.stripe?.fixed || null,
      same_country: props.current?.services?.stripe?.same_country || null,
      other_countries: props.current?.services?.stripe?.other_countries || null,
    },
    paymes: {
      public_key: props.current?.services?.paymes?.public_key || null,
      secret_key: props.current?.services?.paymes?.secret_key || null,
    },
    authorize: {
      login: props.current?.services?.authorize?.login || null,
      transaction_key: props.current?.services?.authorize?.transaction_key || null,
    },
  },
});

onMounted(() => {
  Object.keys(props.payment_methods).forEach(key => {
    Object.keys(props.payment_methods[key].fields).map(field => {
      setDeepValue(
        form,
        props.payment_methods[key].fields[field].config,
        getDeepValue(props.current, props.payment_methods[key].fields[field].config) || null
      );
    });
  });
});

function getDeepValue(object, path) {
  return path.split('.').reduce((obj, key) => {
    return obj && obj[key] !== 'undefined' ? obj[key] : undefined;
  }, object);
}

function setDeepValue(object, path, value) {
  const keys = path.split('.');
  let current = object;

  for (let i = 0; i < keys.length - 1; i++) {
    const key = keys[i];
    if (!current[key] || typeof current[key] !== 'object') {
      current[key] = {};
    }
    current = current[key];
  }

  current[keys[keys.length - 1]] = value;
  object[path] = value;
}

const staticMethodsText = ref(form.static_payment_methods || '');

function save() {
  form
    .transform(data => ({
      ...data,
      static_payment_methods: staticMethodsText.value
        .split('\n')
        .map(m => m.trim())
        .filter(Boolean),
    }))
    .post(route('settings.payment.store'));
}
</script>

<template>
  <Head>
    <title>{{ $t('Payment Settings') }}</title>
  </Head>
  <!-- <Header>{{ $t('Settings') }}</Header> -->

  <TabMenus :links="tab_links" />

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="save">
      <template #title>
        {{ $t('Payment Settings') }}
      </template>

      <template #description> {{ $t('Please configure the payment settings.') }} </template>

      <template #form>
        <div class="col-span-full flex gap-x-8 gap-y-4">
          <CheckBox
            id="paypal-enabled"
            :label="$t('Enable PayPal Payments')"
            v-model:checked="form.services.paypal.enabled"
            :error="form.errors.services?.paypal?.enabled"
          />

          <CheckBox
            id="stripe-terminal"
            :label="$t('Enable Stripe Terminal')"
            v-model:checked="form.stripe_terminal"
            :error="form.errors.stripe_terminal"
          />
        </div>

        <div class="col-span-6 sm:col-span-3">
          <Input v-model="form.default_currency" :error="form.errors.default_currency" :label="$t('Default Currency')" />
        </div>

        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            id="gateway"
            :searchable="false"
            :label="$t('Credit Card Gateway')"
            v-model="form.gateway"
            :error="form.errors.gateway"
            :suggestions="[
              { value: null, label: $t('Disabled') },
              // { value: 'PayPal Pro', label: 'PayPal Pro' },
              { value: 'PayPal Rest', label: 'PayPal Rest' },
              { value: 'Stripe', label: 'Stripe' },
              ...Object.keys(payment_methods)?.map(key => {
                return { value: key, label: payment_methods[key].name };
              }),
            ]"
          />
        </div>

        <!-- <template v-if="form.gateway == 'Paymes'">
          <div class="col-span-full">
            <Input
              :label="$t('Paymes Public Key')"
              v-model="form.services.paymes.public_key"
              :error="
                form.errors['services.paymes.public_key']
                  ? form.errors['services.paymes.public_key'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
          <div class="col-span-full">
            <Input
              :label="$t('Paymes Secret Key')"
              v-model="form.services.paymes.secret_key"
              :error="
                form.errors['services.paymes.secret_key']
                  ? form.errors['services.paymes.secret_key'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
        </template>
        <template v-if="form.gateway == 'Authorize.net'">
          <div class="col-span-full">
            <Input
              :label="$t('Authorize.net Login')"
              v-model="form.services.authorize.login"
              :error="
                form.errors['services.authorize.login']
                  ? form.errors['services.authorize.login'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
          <div class="col-span-full">
            <Input
              :label="$t('Authorize.net Transaction Key')"
              v-model="form.services.authorize.transaction_key"
              :error="
                form.errors['services.authorize.transaction_key']
                  ? form.errors['services.authorize.transaction_key'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
        </template> -->
        <template v-if="payment_methods[form.gateway]">
          <div class="col-span-full flex flex-col gap-4 rounded-md bg-gray-100 p-4 dark:bg-gray-800">
            <div v-for="(field, key) in payment_methods[form.gateway].fields" :key="key">
              <CheckBox
                :id="field.config"
                v-if="field.type == 'checkbox'"
                :checked="form[field.config] == 1"
                @update:checked="e => setDeepValue(form, field.config, e ? 1 : 0)"
                :label="field.label || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"
                :error="form.errors[field.config] ? form.errors[field.config].replace('services.', '').replaceAll('.', ' ') : null"
              />
              <Input
                v-else
                v-model="form[field.config]"
                @change="e => setDeepValue(form, field.config, e.target.value)"
                :label="field.label || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"
                :error="form.errors[field.config] ? form.errors[field.config].replace('services.', '').replaceAll('.', ' ') : null"
              />
            </div>
          </div>
        </template>

        <template v-if="form.services.paypal.enabled || form.gateway == 'PayPal Rest'">
          <div class="col-span-full">
            <Input
              :label="$t('PayPal Client Id')"
              v-model="form.services.paypal.client_id"
              :error="
                form.errors['services.paypal.client_id']
                  ? form.errors['services.paypal.client_id'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
          <div class="col-span-full">
            <Input
              :label="$t('PayPal Secret')"
              v-model="form.services.paypal.secret"
              :error="
                form.errors['services.paypal.secret']
                  ? form.errors['services.paypal.secret'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
          <!-- <div class="col-span-6 sm:col-span-2">
            <Input :label="$t('Fixed Fee Amount')" v-model="form.services.paypal.fixed" />
          </div>
          <div class="col-span-6 sm:col-span-2">
            <Input
              :label="$t('Same Country Percentage')"
              v-model="form.services.paypal.same_country"
            />
          </div>
          <div class="col-span-6 sm:col-span-2">
            <Input
              :label="$t('Other Countries Percentage')"
              v-model="form.services.paypal.other_countries"
            />
          </div> -->
        </template>

        <template v-if="form.stripe_terminal || form.gateway == 'Stripe'">
          <div class="col-span-full">
            <Input
              :label="$t('Stripe Publishable Key')"
              v-model="form.services.stripe.key"
              :error="
                form.errors['services.stripe.key'] ? form.errors['services.stripe.key'].replace('services.', '').replaceAll('.', ' ') : null
              "
            />
          </div>
          <div class="col-span-full">
            <Input
              :label="$t('Stripe Secret Key')"
              v-model="form.services.stripe.secret"
              :error="
                form.errors['services.stripe.secret']
                  ? form.errors['services.stripe.secret'].replace('services.', '').replaceAll('.', ' ')
                  : null
              "
            />
          </div>
          <!-- <div class="col-span-2">
            <Input :label="$t('Fixed Fee Amount')" v-model="form.services.stripe.fixed" />
          </div>
          <div class="col-span-2">
            <Input
              :label="$t('Same Country Percentage')"
              v-model="form.services.stripe.same_country"
            />
          </div>
          <div class="col-span-2">
            <Input
              :label="$t('Other Countries Percentage')"
              v-model="form.services.stripe.other_countries"
            />
          </div> -->
        </template>

        <div class="col-span-full">
          <Textarea
            rows="3"
            v-model="staticMethodsText"
            :label="$t('Payment Methods')"
            :error="form.errors.static_payment_methods"
            :placeholder="$t('Enter payment methods, one per line')"
          />
          <p class="text-mute mt-1 text-xs">{{ $t('Enter one method per line. These will appear in the payment method dropdown.') }}</p>
        </div>

        <!-- <template v-if="form.stripe_terminal">
          <div class="col-span-6 sm:col-span-3">
            <Input
              v-model="form.services.paypal.other_countries"
              :error="form.errors.services?.paypal?.other_countries"
              :label="$t('Other Countries Percentage')"
            />
          </div>
        </template> -->
      </template>

      <template #actions>
        <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

        <LoadingButton :class="{ 'opacity-25': form.processing }" :loading="form.processing"> {{ $t('Save') }} </LoadingButton>
      </template>
    </FormSection>
  </div>
</template>
