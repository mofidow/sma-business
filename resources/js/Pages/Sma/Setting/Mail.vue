<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FormSection, ActionMessage } from '@/Components/Jet';
import { Input, AutoComplete, CheckBox, LoadingButton, TabMenus } from '@/Components/Common';

const { t } = useI18n({});
defineOptions({ layout: AdminLayout });
const props = defineProps({ current: Object });

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
  prefer_biller_email: props.current?.prefer_biller_email || false,
  mail: {
    from: {
      name: props.current?.mail?.from?.name || '',
      address: props.current?.mail?.from?.address || '',
    },
    default: props.current?.mail?.default || 'log',
    mailers: {
      smtp: {
        host: props.current?.mail?.mailers?.smtp?.host || null,
        port: props.current?.mail?.mailers?.smtp?.port || null,
        username: props.current?.mail?.mailers?.smtp?.username || null,
        password: props.current?.mail?.mailers?.smtp?.password || null,
        encryption: props.current?.mail?.mailers?.smtp?.encryption || 'tls',
      },

      sendmail: {
        path: props.current?.mail?.mailers?.sendmail?.path || null,
      },
    },
  },
  services: {
    mailgun: {
      domain: props.current?.services?.mailgun?.domain || null,
      secret: props.current?.services?.mailgun?.secret || null,
      endpoint: props.current?.services?.mailgun?.endpoint || 'api.mailgun.net',
    },

    postmark: {
      token: props.current?.services?.postmark?.token || null,
    },

    ses: {
      key: props.current?.services?.ses?.key || null,
      secret: props.current?.services?.ses?.secret || null,
      region: props.current?.services?.ses?.region || 'us-east-1',
    },

    resend: {
      key: props.current?.services?.resend?.key || null,
    },
  },
  'mailersend-driver': {
    api_key: props.current && props.current['mailersend-driver'] ? props.current['mailersend-driver']['api_key'] : null,
  },
});

function save() {
  form.post(route('settings.mail.store'));
}
</script>

<template>
  <Head>
    <title>{{ $t('Mail Settings') }}</title>
  </Head>
  <!-- <Header>{{ $t('Settings') }}</Header> -->

  <TabMenus :links="tab_links" />

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="save">
      <template #title>
        {{ $t('Mail Settings') }}
      </template>

      <template #description> {{ $t('Please configure the mail driver and settings.') }} </template>

      <template #form>
        <div class="col-span-6 sm:col-span-3">
          <Input
            :label="$t('Mail From Name')"
            v-model="form.mail.from.name"
            :error="form.errors['mail.from.name'] ? form.errors['mail.from.name'].replace('mail.', '') : null"
          />
        </div>
        <div class="col-span-6 sm:col-span-3">
          <Input
            type="email"
            v-model="form.mail.from.address"
            :label="$t('Mail From Email Address')"
            :error="form.errors['mail.from.address'] ? form.errors['mail.from.address'].replace('mail.', '') : null"
          />
        </div>

        <div class="col-span-full">
          <CheckBox
            id="prefer_biller_email"
            :error="form.errors.prefer_biller_email"
            v-model:checked="form.prefer_biller_email"
            :label="$t('Please prefer store name and email')"
          />
          <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-500">
            {{ $t('This could result in email failure depending on your mail server and biller email address.') }}
          </p>
        </div>

        <div class="col-span-full">
          <AutoComplete
            :json="true"
            id="default"
            :searchable="false"
            :label="$t('Mail Driver')"
            v-model="form.mail.default"
            :error="form.errors['mail.default'] ? form.errors['mail.default'].replace('mail.', '') : null"
            :suggestions="[
              { value: 'log', label: 'Log to File' },
              { value: 'smtp', label: 'SMTP' },
              { value: 'sendmail', label: 'SendMail' },
              { value: 'ses', label: 'AWS SES' },
              { value: 'mailgun', label: 'Mailgun' },
              { value: 'postmark', label: 'Postmark' },
              { value: 'resend', label: 'Resend' },
              // { value: 'mailersend', label: 'MailerSend' },
            ]"
          />
        </div>

        <template v-if="form.mail.default == 'smtp'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('SMTP Host')"
              v-model="form.mail.mailers.smtp.host"
              :error="form.errors['mail.mailers.smtp.host'] ? form.errors['mail.mailers.smtp.host'].replace('mail.mailers.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <div class="flex gap-x-6">
              <div class="w-1/2">
                <Input
                  :label="$t('SMTP Port')"
                  v-model="form.mail.mailers.smtp.port"
                  :error="form.errors['mail.mailers.smtp.port'] ? form.errors['mail.mailers.smtp.port'].replace('mail.mailers.', '') : null"
                />
              </div>
              <div class="w-1/2">
                <AutoComplete
                  :json="true"
                  :label="$t('Encryption')"
                  v-model="form.mail.mailers.smtp.encryption"
                  :suggestions="[
                    { value: '', label: $t('None') },
                    { value: 'tls', label: $t('TLS') },
                    { value: 'ssl', label: $t('SSL') },
                  ]"
                  :error="
                    form.errors['mail.mailers.smtp.encryption']
                      ? form.errors['mail.mailers.smtp.encryption'].replace('mail.mailers.', '')
                      : null
                  "
                />
              </div>
            </div>
          </div>

          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('SMTP Username')"
              v-model="form.mail.mailers.smtp.username"
              :error="
                form.errors['mail.mailers.smtp.username'] ? form.errors['mail.mailers.smtp.username'].replace('mail.mailers.', '') : null
              "
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('SMTP Password')"
              v-model="form.mail.mailers.smtp.password"
              :error="
                form.errors['mail.mailers.smtp.password'] ? form.errors['mail.mailers.smtp.password'].replace('mail.mailers.', '') : null
              "
            />
          </div>
        </template>
        <template v-else-if="form.mail.default == 'ses'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('AWS SES Key')"
              v-model="form.services.ses.key"
              :error="form.errors['services.ses.key'] ? form.errors['services.ses.key'].replace('services.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('AWS SES Secret')"
              v-model="form.services.ses.secret"
              :error="form.errors['services.ses.secret'] ? form.errors['services.ses.secret'].replace('services.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('AWS SES Region')"
              v-model="form.services.ses.region"
              :error="form.errors['services.ses.region'] ? form.errors['services.ses.region'].replace('services.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3"></div>
        </template>
        <template v-else-if="form.mail.default == 'mailgun'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('Mailgun Domain')"
              v-model="form.services.mailgun.domain"
              :error="form.errors['services.mailgun.domain'] ? form.errors['services.mailgun.domain'].replace('services.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('Mailgun Secret')"
              v-model="form.services.mailgun.secret"
              :error="form.errors['services.mailgun.secret'] ? form.errors['services.mailgun.secret'].replace('services.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <Input
              :label="$t('Mailgun Endpoint')"
              v-model="form.services.mailgun.endpoint"
              :error="form.errors['services.mailgun.endpoint'] ? form.errors['services.mailgun.endpoint'].replace('services.', '') : null"
            />
          </div>
          <div class="col-span-6 sm:col-span-3"></div>
        </template>
        <template v-else-if="form.mail.default == 'postmark'">
          <div class="col-span-full">
            <Input
              :label="$t('Postmark Token')"
              v-model="form.services.postmark.token"
              :error="form.errors['services.postmark.token'] ? form.errors['services.postmark.token'].replace('services.', '') : null"
            />
          </div>
        </template>
        <template v-else-if="form.mail.default == 'resend'">
          <div class="col-span-full">
            <Input
              :label="$t('Resend Key')"
              v-model="form.services.resend.key"
              :error="form.errors['services.resend.key'] ? form.errors['services.resend.key'].replace('services.', '') : null"
            />
          </div>
        </template>
        <template v-else-if="form.mail.default == 'mailersend'">
          <div class="col-span-full">
            <Input
              :label="$t('MailerSend API Key')"
              v-model="form['mailersend-driver'].api_key"
              :error="form.errors['mailersend-driver.api_key'] ? form.errors['mailersend-driver.api_key'] : null"
            />
          </div>
        </template>
        <template v-else-if="form.mail.default == 'sendmail'">
          <div class="col-span-full">
            <Input
              :label="$t('SendMail Path')"
              v-model="form.mail.mailers.sendmail.path"
              :error="
                form.errors['mail.mailers.sendmail.path'] ? form.errors['mail.mailers.sendmail.path'].replace('mail.mailers.', '') : null
              "
            />
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
