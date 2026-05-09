import { createInertiaApp, Head, Link, router } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import Notifications, { notify } from 'notiwind';
import { createSSRApp, h } from 'vue';

import i18n from '@/Core/i18n';
import checkShortcutKeys from '@/Core/shortcuts';
import { ZiggyVue } from 'ziggy-js';

import { Icon } from '@/Components';
import mixin from '@/Core/mixin';
import Header from '@/Layouts/Components/Header.vue';

const appName = import.meta.env.VITE_APP_NAME || 'SMA';

createServer(
  page =>
    createInertiaApp({
      page,
      render: renderToString,
      title: title => `${title} - ${appName}`,
      resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: false });
        return pages[`./Pages/${name}.vue`];
      },
      setup({ App, props, plugin }) {
        router.on('success', event => {
          if (event.detail.page.props.flash?.message) {
            notify({
              group: 'main',
              type: 'success',
              title: 'Success!',
              text: event.detail.page.props.flash.message,
            });
          }
          if (event.detail.page.props.flash?.error) {
            notify({
              group: 'main',
              type: 'error',
              title: 'Failed!',
              text: event.detail.page.props.flash.error,
            });
          }
        });

        router.on('error', errors => {
          notify(
            {
              group: 'main',
              type: 'error',
              title: 'Failed!',
              text: Object.values(errors.detail.errors).join('<br />'),
            },
            5000
          );
        });

        return createSSRApp({
          render: () => h(App, props),
          mounted: () => {
            setTimeout(() => {
              document.addEventListener('keyup', checkShortcutKeys);
              document.getElementById('app-loading').style.display = 'none';
            }, 250);
          },
          beforeDestroy() {
            document.removeEventListener('keyup', checkShortcutKeys);
          },
        })
          .use(i18n)
          .use(plugin)
          .mixin(mixin)
          .use(ZiggyVue)
          .use(Notifications)
          .component('Head', Head)
          .component('Icon', Icon)
          .component('Link', Link)
          .component('Header', Header);
      },
    }),
  { cluster: true, port: 13721 }
);
