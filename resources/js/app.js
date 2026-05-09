import '../css/app.css';
import './bootstrap';

import { createInertiaApp, Head, Link, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Notifications, { notify } from 'notiwind';
import { createSSRApp, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { route, ZiggyVue } from 'ziggy-js';

import { Icon } from '@/Components';
import i18n, { loadLocaleMessages } from '@/Core/i18n';
import mixin from '@/Core/mixin';
import checkShortcutKeys from '@/Core/shortcuts';
import Layout from '@/Layouts/AppLayout.vue';
import Header from '@/Layouts/Components/Header.vue';

const appName = import.meta.env.VITE_APP_NAME || 'SMA';

createInertiaApp({
  title: title => `${title} - ${appName}`,
  //   resolve: name => {
  //     const pages = import.meta.glob('./Pages/**/*.vue', { eager: false });
  //     return pages[`./Pages/${name}.vue`]();
  //   },
  resolve: name => {
    const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue', { eager: false }));
    page.then(module => (module.default.layout = module.default.layout || Layout));
    return page;
  },
  async setup({ el, App, props, plugin }) {
    const currentLocale = window.Locale || 'en';
    await loadLocaleMessages('en');
    if (currentLocale !== 'en') {
      await loadLocaleMessages(currentLocale);
    }

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

    let app = createSSRApp({
      setup() {
        const { t } = useI18n();
        return { t };
      },
      render: () => h(App, props),
      mounted: () => {
        setTimeout(() => {
          document.addEventListener('keyup', checkShortcutKeys);
          document.getElementById('app-loading').style.display = 'none';
        }, 250);
        router.on('httpException', event => {
          event.preventDefault();
          window.location.reload();
          //   const response = event.detail.response;
          //   window.location.href = response.request.responseURL;
        });
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

    app.config.globalProperties.$route = route;
    app.config.globalProperties.$t = i18n.global.t;
    return app.mount(el);
  },
  // setup({ el, App, props, plugin }) {
  //   return createApp({ render: () => h(App, props) })
  //     .use(plugin)
  //     .use(ZiggyVue)
  //     .component('Haader', Haader)
  //     .mount(el);
  // },
  progress: {
    color: '#4B5563',
    showSpinner: true,
  },
});
