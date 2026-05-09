<script>
export default {
  data() {
    return { currentLang: this.$page.props.language };
  },
  methods: {
    getFlag() {
      return LANGUAGES.find(l => l.value == this.$root.$i18n.locale)?.flag || 'US';
    },
    async changeLang(language) {
      await loadLocaleMessages(language, { force: true });
      router.visit(route('language', { language }), {
        onFinish: () => {
          window.Locale = language;
          this.currentLang = language;
          document.querySelector('html').setAttribute('lang', language);
          document.querySelector('html').setAttribute('dir', getDocumentDirection(language));
          this.$root.$i18n.locale = language;
        },
      });
    },
    toggleRTL() {
      router.visit(route('language', { language: 'toggle_rtl' }), {
        onFinish: () => {
          const html = document.querySelector('html');
          const currentDir = html.getAttribute('dir');
          const newDir = currentDir === 'ltr' ? 'rtl' : 'ltr';
          html.setAttribute('dir', newDir);
        },
      });
    },
  },
};
</script>

<script setup>
import { route } from 'ziggy-js';
import { onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

import { $can } from '@/Core';
import { LANGUAGES, getDocumentDirection, loadLocaleMessages } from '@/Core/i18n';
import { Alerts } from '@/Components/Common';
import { Dropdown, DropdownLink, Modal, ThemeSwitch } from '@/Components/Jet';

const page = usePage();
const currentRoute = ref(null);
router.on('navigate', () => (currentRoute.value = route ? route()?.current() : null));

const showAlerts = ref(false);

onMounted(() => {
  try {
    currentRoute.value = route().current();
  } catch (e) {
    console.log(e);
  }
});

function isCurrentRoute(r) {
  try {
    return route(currentRoute.value) == route(r);
  } catch {}
}

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div id="top-bar" class="top-bar flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
    <!-- <template v-if="page.props.auth.user.store_id">
      <div class="flex items-center gap-2 py-1.5 font-bold">
        <Icon name="store" size="size-5 -ms-1" />
        {{ page.props.auth.user.store.name }}
      </div>
    </template>
    <template v-else>
      <div class="flex items-center">
        <button type="button" @click="page.props.select_store = true" :class="page.props.selected_store ? 'btn-primary' : ''">
          <span class="flex items-center gap-2">
            <span v-if="page.props.selected_store" class="flex items-center gap-2 font-bold">
              <Icon name="store" size="size-5 -ms-1" />
              <span class="hidden lg:inline-block">{{
                page.props.available_stores.find(s => s.id == page.props.selected_store)?.name
              }}</span>
            </span>
            <Icon v-else name="store-o" size="size-5" />
          </span>
        </button>
      </div>
    </template> -->

    <div class="flex flex-1">
      <label for="search-field" class="sr-only">{{ $t('Search') }}</label>
      <div class="relative w-full">
        <Icon name="search" size="pointer-events-none absolute inset-y-0 start-0 h-full w-5 text-mute" />
        <input
          type="search"
          name="search"
          id="search-field"
          v-model="page.props.filters.search"
          :placeholder="$t('Search') + '...'"
          class="text-focus block h-full w-full border-0 bg-transparent py-0 ps-8 pe-0 focus:ring-0 sm:text-sm"
        />
      </div>
    </div>

    <div class="flex items-center gap-x-6">
      <Link
        :href="route('dashboard')"
        class="-m-2.5 hidden p-2.5 md:block"
        :class="
          isCurrentRoute('dashboard')
            ? 'text-primary-600 dark:text-primary-400'
            : 'text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300'
        "
      >
        <span class="sr-only">{{ $t('Dashboard') }}</span>
        <Icon name="home-o" size="size-5" />
      </Link>
      <a
        :href="route('shop.home')"
        class="group -m-2 hidden rounded-md p-2 shadow-xs hover:shadow md:block dark:border-gray-700 dark:shadow-gray-500/50"
        v-if="page.props.shop_module && route().has('shop.home')"
        :class="
          isCurrentRoute('shop.home')
            ? 'text-primary-600 dark:text-primary-400'
            : 'text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300'
        "
      >
        <span class="sr-only">{{ $t('Shop') }}</span>
        <Icon name="cart" size="size-5 hidden group-hover:block" />
        <Icon name="cart-o" size="size-5 group-hover:hidden" />
      </a>

      <template v-if="page.props.auth.user.store_id && !page.props.selected_store">
        <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400">
          <Icon name="store" size="size-5" />
          <span class="hidden sm:block">{{ page.props.auth.user.store.name }}</span>
        </div>
      </template>
      <template v-else-if="page.props.auth.user?.employee == 1">
        <div class="flex items-center text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
          <button
            type="button"
            @click="page.props.select_store = true"
            :class="
              page.props.selected_store
                ? 'text-success-600 dark:text-success-400'
                : 'text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300'
            "
          >
            <span class="flex items-center gap-2">
              <span v-if="page.props.selected_store" class="flex items-center gap-2 font-bold">
                <Icon name="store" size="size-5" />
                <!-- <span class="hidden lg:inline-block">{{
                  page.props.available_stores.find(s => s.id == page.props.selected_store)?.name
                }}</span> -->
              </span>
              <Icon v-else name="store-o" size="size-5" />
            </span>
          </button>
        </div>
      </template>

      <Link
        class="-m-2.5 p-2.5"
        :href="route('pos')"
        v-if="$can('read-pos') && route().has('pos')"
        :class="
          isCurrentRoute('pos')
            ? 'text-primary-600 dark:text-primary-400'
            : 'text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300'
        "
      >
        <span class="sr-only">{{ $t('POS') }}</span>
        <Icon name="pos-o" size="size-5" />
      </Link>

      <div class="hidden text-gray-500 hover:text-gray-600 md:block dark:text-gray-400 dark:hover:text-gray-300">
        <ThemeSwitch />
      </div>

      <div class="hidden md:block">
        <Dropdown
          align="left"
          width="40"
          contentClasses="max-h[calc(100vh-5rem)] bg-white dark:bg-gray-700 py-1 overflow-y-auto scroll-contain"
        >
          <template #trigger>
            <button class="focus flex items-center rounded-md border-2 border-transparent px-2 py-1 transition duration-150 ease-in-out">
              <span class="text-xl" v-html="getFlag().replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"></span>
            </button>
          </template>

          <template #content>
            <template v-for="lang in LANGUAGES" :key="lang.value">
              <DropdownLink as="button" @click="changeLang(lang.value)">
                <div
                  class="-my-1 me-2 text-lg"
                  v-html="lang.flag.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"
                ></div>
                {{ lang.label }}
              </DropdownLink>
            </template>
            <div class="my-1 border-b border-gray-100 dark:border-gray-600"></div>
            <DropdownLink as="button" @click="toggleRTL()">
              <span class="flex items-center gap-2" v-if="$page.props.rtl_support === '1' || $page.props.rtl_support === '0'">
                <svg
                  v-if="$page.props.rtl_support === '1'"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-4"
                >
                  <path d="M21 5H3" />
                  <path d="M21 12H9" />
                  <path d="M21 19H7" />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-4"
                >
                  <path d="M21 5H3" />
                  <path d="M15 12H3" />
                  <path d="M17 19H3" />
                </svg>
                {{ $page.props.rtl_support == '1' ? $t('Disable RTL') : $t('Enable RTL') }}
              </span>
              <span v-else class="flex items-center gap-2">
                <svg
                  v-if="$page.props.settings?.rtl_support == '1'"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-4"
                >
                  <path d="M21 5H3" />
                  <path d="M21 12H9" />
                  <path d="M21 19H7" />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-4"
                >
                  <path d="M21 5H3" />
                  <path d="M15 12H3" />
                  <path d="M17 19H3" />
                </svg>
                {{ $page.props.settings?.rtl_support == '1' ? $t('Disable RTL') : $t('Enable RTL') }}
              </span>
            </DropdownLink>
          </template>
        </Dropdown>
      </div>

      <button
        type="button"
        @click="showAlerts = true"
        v-if="$page.props.auth.user?.employee == 1 && !$page.props.is_impersonating"
        class="-m-2.5 p-2.5 text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
      >
        <span class="sr-only">{{ $t('View notifications') }}</span>
        <Icon name="bell-o" size="size-5" />
      </button>

      <template v-if="$page.props.is_impersonating">
        <Link
          :href="route('impersonate.stop')"
          class="-m-2.5 p-2.5 text-yellow-800 hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-200"
        >
          <svg fill="none" class="size-5" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </Link>
      </template>

      <Link
        v-if="$can('settings')"
        :href="route('settings.index')"
        class="-m-2.5 hidden p-2.5 md:block"
        :class="
          isCurrentRoute('settings.index')
            ? 'text-primary-600 dark:text-primary-400'
            : 'text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300'
        "
      >
        <span class="sr-only">{{ $t('Settings') }}</span>
        <Icon name="cog-o" size="size-5" />
      </Link>

      <!-- Separator -->
      <!-- <div class="hidden lg:block lg:h-6 lg:w-px" aria-hidden="true"></div> -->

      <!-- Profile dropdown -->
      <div class="relative z-20">
        <Dropdown align="right" width="40">
          <template #trigger>
            <button type="button" class="-m-1.5 flex items-center p-1.5">
              <span class="sr-only">{{ $t('Open user menu') }}</span>
              <span class="flex h-8 w-8 items-center overflow-hidden rounded-full">
                <img
                  class="max-h-full max-w-full object-cover"
                  :src="page.props.auth.user.profile_photo_url"
                  :alt="page.props.auth.user.name"
                />
              </span>
              <span class="hidden lg:flex lg:items-center">
                <span class="ms-3 text-sm leading-6 font-semibold" aria-hidden="true">{{ page.props.auth.user.name }}</span>
                <svg class="ms-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                  <path
                    fill-rule="evenodd"
                    d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </button>
          </template>

          <template #content>
            <div>
              <!-- Impersonation -->
              <DropdownLink v-if="$page.props.is_impersonating" :href="route('impersonate.stop')">
                {{ $t('Stop Impersonating') }}
              </DropdownLink>

              <!-- Account Management -->
              <div class="block px-4 py-2 text-xs text-gray-400">{{ $t('Manage Account') }}</div>

              <DropdownLink :href="route('profile.show')"> {{ $t('Profile') }} </DropdownLink>

              <DropdownLink v-if="page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                {{ $t('API Tokens') }}
              </DropdownLink>

              <div class="border-t border-gray-200 border-gray-300 dark:border-gray-600" />

              <!-- Authentication -->
              <form @submit.prevent="logout">
                <DropdownLink as="button"> {{ $t('Log Out') }} </DropdownLink>
              </form>
            </div>
          </template>
        </Dropdown>
      </div>
    </div>
    <Modal :show="showAlerts" :backdrop="false" max-width="2xl" :closeable="true" @close="showAlerts = false">
      <Alerts @close="showAlerts = false" />
    </Modal>
  </div>
</template>
