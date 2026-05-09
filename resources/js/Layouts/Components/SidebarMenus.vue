<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { onMounted, nextTick, ref, watch } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';

import { $can } from '@/Core';
import { getMenus } from '@/Core/menus';
import Dropdown from '@/Components/Jet/Dropdown.vue';
// import ApplicationMark from '@/Components/Jet/ApplicationMark.vue';
import ApplicationLogo from '@/Components/Jet/ApplicationLogo.vue';

const page = usePage();
const { t, locale } = useI18n();
router.on('navigate', stateChanged);
const emits = defineEmits(['close']);

const menus = ref([]);
const menuState = ref({});
const currentRoute = ref(null);
const currentParent = ref(null);

watch(locale, async () => {
  menus.value = getMenus(t);
});

onMounted(async () => {
  try {
    currentRoute.value = route()?.current();
  } catch (e) {
    console.log(e);
  }
  menus.value = getMenus(t);
  menus.value.map(menu => {
    menu.menus?.map(m => {
      if (m.sub_menu && m.sub_menu.length) {
        menuState.value[m.name] = false;
      }
    });
  });
});

function stateChanged() {
  currentParent.value = null;
  currentRoute.value = route().current();
  Object.keys(menuState.value).map(k => (menuState.value[k] = false));

  //   if (page.props.settings.sidebar_dropdown == 1) {
  menus.value.map(menu => {
    menu.menus?.map(m => {
      if (m.sub_menu && m.sub_menu.length) {
        const cm = m.sub_menu.find(s => s.route == currentRoute.value);
        if (cm && cm.target) {
          currentParent.value = m.name;
          menuState.value[cm.target] = true;
          //   document.getElementById(cm.target).click();
          if (page.props.settings.sidebar_scroll_to_view == 1) {
            nextTick().then(() => {
              document.getElementById(cm.target)?.parentNode.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
          }
        }
      }
    });
  });
  //   }

  emits('close');
}

function isCurrentRoute(r, p) {
  const route_name = currentRoute.value.includes('.edit') ? currentRoute.value.split('.edit')[0] : currentRoute.value;

  if (p) {
    try {
      return route(route_name).includes(p);
    } catch {
      return route_name == p;
      //   return route_name.includes(p);
    }
  } else if (r) {
    try {
      return route(route_name) == route(r);
    } catch {}
  }

  return false;
}

function toggleMenu(e, key) {
  Object.keys(menuState.value).forEach(k => {
    if (k !== key && k !== currentParent.value) {
      menuState.value[k] = false;
    }
    // else if (ki == Object.keys(menuState.value).length - 1) {
    if (page.props.settings.sidebar_scroll_to_view == 1) {
      nextTick().then(() => {
        // setTimeout(() => {
        e.target.parentNode.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // }, 10);
      });
    }
    // }
  });
  menuState.value[key] = !menuState.value[key];
}
</script>

<template>
  <div
    id="side-bar"
    class="side-bar fixed inset-y-0 start-0 w-64"
    :class="page.props.settings.dark_sidebar == 1 ? 'dark bg-gray-900' : 'bg-white dark:bg-gray-900'"
  >
    <div class="absolute start-full top-0 z-20 flex w-16 justify-center pt-5 xl:hidden">
      <button type="button" class="-m-2.5 rounded-lg p-2.5" @click="$emit('close')">
        <span class="sr-only">{{ $t('Close sidebar') }}</span>
        <svg
          class="text-focus h-6 w-6"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          aria-hidden="true"
          data-slot="icon"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <nav class="relative z-20 flex h-full min-h-0 flex-col border-e border-gray-200 dark:border-gray-700">
      <div class="flex h-16 flex-col items-start justify-center border-b border-gray-200 px-4 dark:border-gray-700">
        <div
          class="flex w-full cursor-default items-center gap-2.5 rounded-lg px-1 text-start text-base/6 font-medium sm:text-sm dark:text-white"
        >
          <span data-slot="avatar" class="inline-grid h-12 shrink-0 align-middle">
            <ApplicationLogo />
          </span>
          <span class="sr-only">{{ $page.props.settings.name }}</span>
        </div>
        <!-- <Dropdown align="left" width="56">
          <template #trigger>
            <button
              class="cursor-default flex w-full items-center gap-2.5 rounded-lg px-1 py-2.5 text-start text-base/6 font-medium sm:py-2 sm:text-sm dark:text-white"
            >
              <span data-slot="avatar" class="inline-grid shrink-0 align-middle size-7">
                <ApplicationMark />
              </span>
              <span class="truncate">{{ $page.props.settings.name }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 text-mute">
                <path
                  fill-rule="evenodd"
                  d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
          </template>
          <template #content>
            <div>
              <Link
                :href="route('settings.index')"
                class="group cursor-default rounded-lg px-3.5 py-2.5 focus:outline-none sm:px-3 sm:py-1.5 text-start text-base/6 sm:text-sm/6 dark:text-white flex items-center gap-3"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                  <path
                    fill-rule="evenodd"
                    d="M6.955 1.45A.5.5 0 0 1 7.452 1h1.096a.5.5 0 0 1 .497.45l.17 1.699c.484.12.94.312 1.356.562l1.321-1.081a.5.5 0 0 1 .67.033l.774.775a.5.5 0 0 1 .034.67l-1.08 1.32c.25.417.44.873.561 1.357l1.699.17a.5.5 0 0 1 .45.497v1.096a.5.5 0 0 1-.45.497l-1.699.17c-.12.484-.312.94-.562 1.356l1.082 1.322a.5.5 0 0 1-.034.67l-.774.774a.5.5 0 0 1-.67.033l-1.322-1.08c-.416.25-.872.44-1.356.561l-.17 1.699a.5.5 0 0 1-.497.45H7.452a.5.5 0 0 1-.497-.45l-.17-1.699a4.973 4.973 0 0 1-1.356-.562L4.108 13.37a.5.5 0 0 1-.67-.033l-.774-.775a.5.5 0 0 1-.034-.67l1.08-1.32a4.971 4.971 0 0 1-.561-1.357l-1.699-.17A.5.5 0 0 1 1 8.548V7.452a.5.5 0 0 1 .45-.497l1.699-.17c.12-.484.312-.94.562-1.356L2.629 4.107a.5.5 0 0 1 .034-.67l.774-.774a.5.5 0 0 1 .67-.033L5.43 3.71a4.97 4.97 0 0 1 1.356-.561l.17-1.699ZM6 8c0 .538.212 1.026.558 1.385l.057.057a2 2 0 0 0 2.828-2.828l-.058-.056A2 2 0 0 0 6 8Z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rdm:" data-headlessui-state="">Settings</div>
              </Link>
              <div
                class="col-span-full mx-3.5 my-1 h-px border-0 bg-gray-950/5 sm:mx-3 dark:bg-white/10 forced-colors:bg-[CanvasText]"
                role="separator"
              ></div>
              <a
                class="group cursor-default rounded-lg px-3.5 py-2.5 focus:outline-none sm:px-3 sm:py-1.5 text-start text-base/6 sm:text-sm/6 dark:text-white flex items-center gap-3"
              >
                <span data-slot="avatar" slot="icon" class="inline-grid shrink-0 align-middle size-6">
                  <ApplicationMark class="size-full" />
                </span>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rdo:" data-headlessui-state="">Catalyst</div>
              </a>
              <div
                class="col-span-full mx-3.5 my-1 h-px border-0 bg-gray-950/5 sm:mx-3 dark:bg-white/10 forced-colors:bg-[CanvasText]"
                role="separator"
              ></div>
              <a
                class="group cursor-default rounded-lg px-3.5 py-2.5 focus:outline-none sm:px-3 sm:py-1.5 text-start text-base/6 sm:text-sm/6 dark:text-white flex items-center gap-3"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                  <path
                    d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z"
                  ></path>
                </svg>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rds:" data-headlessui-state="">New team</div>
              </a>
            </div>
          </template>
        </Dropdown> -->
      </div>

      <!-- Menus -->
      <div id="side-bar-menus" class="flex flex-1 flex-col gap-y-6 overflow-y-auto">
        <template v-for="menuSection in menus" :key="menuSection.name">
          <div v-if="menuSection.name == 'separator'" class="flex-1"></div>
          <template v-else-if="menuSection.name == 'support'">
            <div class="flex-1"></div>
            <div v-if="$page.props.settings.support_links == 1">
              <div data-slot="section" class="flex flex-col" :class="page.props.settings.sidebar_dropdown == 1 ? 'gap-0' : 'gap-1'">
                <h3 v-if="menuSection.heading" class="my-1 px-3 text-xs/6 font-medium text-gray-500 dark:text-gray-400">
                  {{ menuSection.heading }}
                </h3>
                <template v-for="mainMenu in menuSection.menus" :key="mainMenu.name">
                  <span
                    v-if="
                      (!mainMenu.sub_menu && (mainMenu.link || route().has(mainMenu.route))) ||
                      (mainMenu.module && $page.props[mainMenu.module])
                    "
                    class="relative"
                  >
                    <span
                      v-if="!mainMenu.link && isCurrentRoute(mainMenu.route)"
                      class="absolute inset-y-0 -start-0 w-0.5 rounded-full bg-gray-950 dark:bg-white"
                      style="transform: none; transform-origin: 50% 50% 0px"
                    >
                    </span>
                    <component
                      :is="mainMenu.link ? 'a' : Link"
                      :target="mainMenu.link ? '_blank' : '_self'"
                      :href="mainMenu.link || route(mainMenu.route)"
                      :class="
                        !mainMenu.link && isCurrentRoute(mainMenu.route) ? 'pointer-events-none bg-primary-100 dark:bg-primary-800' : ''
                      "
                      class="group flex w-full items-center gap-3 px-4 py-2.5 text-start text-base/6 font-medium hover:bg-primary-50 data-[active]:bg-gray-950/5 data-[hover]:bg-gray-950/5 data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 data-[slot=icon]:*:data-[active]:fill-gray-950 data-[slot=icon]:*:data-[current]:fill-gray-950 data-[slot=icon]:*:data-[hover]:fill-gray-950 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:hover:bg-primary-900 dark:data-[active]:bg-white/5 dark:data-[hover]:bg-white/5 dark:data-[slot=icon]:*:fill-gray-400 dark:data-[slot=icon]:*:data-[active]:fill-white dark:data-[slot=icon]:*:data-[current]:fill-white dark:data-[slot=icon]:*:data-[hover]:fill-white"
                    >
                      <span
                        class="absolute start-1/2 top-1/2 size-[max(100%,2.75rem)] -translate-y-1/2 ltr:-translate-x-1/2 rtl:translate-x-1/2 [@media(pointer:fine)]:hidden"
                        aria-hidden="true"
                      >
                      </span>
                      <Icon v-if="mainMenu.icon" :name="mainMenu.icon" />
                      <span class="flex-1 grow truncate">{{ mainMenu.label }}</span>
                      <Icon v-if="mainMenu.link" name="external" size="w-4 h-4 hidden group-hover:block" />
                    </component>
                  </span>
                </template>
              </div>
            </div>
          </template>
          <div v-else data-slot="section" class="flex flex-col" :class="page.props.settings.sidebar_dropdown == 1 ? 'gap-0' : 'gap-1'">
            <h3 v-if="menuSection.heading" class="my-1 px-3 text-xs/6 font-medium text-gray-500 dark:text-gray-400">
              {{ menuSection.heading }}
            </h3>
            <template v-for="mainMenu in menuSection.menus" :key="mainMenu.name">
              <span
                v-if="
                  (!mainMenu.sub_menu && (mainMenu.link || route().has(mainMenu.route))) ||
                  (!mainMenu.module && mainMenu.module && $page.props[mainMenu.module])
                "
                class="relative"
              >
                <span
                  v-if="!mainMenu.link && isCurrentRoute(mainMenu.route)"
                  class="absolute inset-y-0 -start-0 w-0.5 rounded-full bg-gray-950 dark:bg-white"
                  style="transform: none; transform-origin: 50% 50% 0px"
                >
                </span>
                <component
                  v-if="$can(mainMenu.permissions)"
                  :is="mainMenu.link ? 'a' : Link"
                  :target="mainMenu.link ? '_blank' : '_self'"
                  :href="mainMenu.link || route(mainMenu.route)"
                  :class="!mainMenu.link && isCurrentRoute(mainMenu.route) ? 'pointer-events-none bg-primary-100 dark:bg-primary-800' : ''"
                  class="group flex w-full items-center gap-3 px-4 py-2.5 text-start text-base/6 font-medium hover:bg-primary-50 data-[active]:bg-gray-950/5 data-[hover]:bg-gray-950/5 data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 data-[slot=icon]:*:data-[active]:fill-gray-950 data-[slot=icon]:*:data-[current]:fill-gray-950 data-[slot=icon]:*:data-[hover]:fill-gray-950 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:hover:bg-primary-900 dark:data-[active]:bg-white/5 dark:data-[hover]:bg-white/5 dark:data-[slot=icon]:*:fill-gray-400 dark:data-[slot=icon]:*:data-[active]:fill-white dark:data-[slot=icon]:*:data-[current]:fill-white dark:data-[slot=icon]:*:data-[hover]:fill-white"
                >
                  <span
                    class="absolute start-1/2 top-1/2 size-[max(100%,2.75rem)] -translate-y-1/2 ltr:-translate-x-1/2 rtl:translate-x-1/2 [@media(pointer:fine)]:hidden"
                    aria-hidden="true"
                  >
                  </span>
                  <Icon v-if="mainMenu.icon" :name="mainMenu.icon" />
                  <span class="flex-1 grow truncate">{{ mainMenu.label }}</span>
                  <Icon v-if="mainMenu.link" name="external" size="w-4 h-4 hidden group-hover:block" />
                </component>
              </span>

              <div
                v-else-if="
                  mainMenu.sub_menu &&
                  $can(mainMenu.permissions) &&
                  page.props.settings.sidebar_dropdown == 1 &&
                  (!mainMenu.module || (mainMenu.module && $page.props[mainMenu.module]))
                "
                class="overflow-hidden transition-all"
              >
                <div
                  class="relative z-10 border-t transition-all"
                  :class="[
                    menuState[mainMenu.name] ? 'border-gray-200 dark:border-gray-700' : 'border-transparent dark:border-transparent',
                    isCurrentRoute(mainMenu.route, mainMenu.route_prefix) ? 'bg-gray-50 dark:bg-gray-900' : '',
                  ]"
                >
                  <div
                    v-if="isCurrentRoute(mainMenu.route, mainMenu.route_prefix)"
                    class="absolute inset-y-0 -start-0 w-0.5 rounded-full bg-gray-950 dark:bg-white"
                    style="transform: none; transform-origin: 50% 50% 0px"
                  ></div>
                  <button
                    @click="e => toggleMenu(e, mainMenu.name)"
                    :class="
                      isCurrentRoute(mainMenu.route, mainMenu.route_prefix) || currentParent == mainMenu.name
                        ? 'bg-primary-50 dark:bg-primary-900'
                        : ''
                    "
                    :id="mainMenu.name"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-start text-base/6 font-medium hover:bg-primary-50 data-[active]:bg-gray-950/5 data-[hover]:bg-gray-950/5 data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 data-[slot=icon]:*:data-[active]:fill-gray-950 data-[slot=icon]:*:data-[current]:fill-gray-950 data-[slot=icon]:*:data-[hover]:fill-gray-950 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:hover:bg-primary-900 dark:data-[active]:bg-white/5 dark:data-[hover]:bg-white/5 dark:data-[slot=icon]:*:fill-gray-400 dark:data-[slot=icon]:*:data-[active]:fill-white dark:data-[slot=icon]:*:data-[current]:fill-white dark:data-[slot=icon]:*:data-[hover]:fill-white"
                  >
                    <span
                      class="absolute start-1/2 top-1/2 size-[max(100%,2.75rem)] -translate-y-1/2 ltr:-translate-x-1/2 rtl:translate-x-1/2 [@media(pointer:fine)]:hidden"
                      aria-hidden="true"
                    >
                    </span>
                    <Icon v-if="mainMenu.icon" :name="mainMenu.icon" />
                    <span class="flex-1 grow truncate">{{ mainMenu.label }}</span>
                    <Icon :name="menuState[mainMenu.name] ? 'c-up' : 'c-down'" size="w-4 h-4 text-mute" />
                  </button>

                  <!-- <Transition name="slide-down"> -->
                  <div
                    v-show="menuState[mainMenu.name]"
                    :class="menuState[mainMenu.name] ? 'h-auto' : 'h-0'"
                    class="z-1 -ms-5 border-b border-gray-200 ps-6 transition-all dark:border-gray-700"
                  >
                    <template :key="sub_menu.name" v-for="sub_menu in mainMenu.sub_menu">
                      <div v-if="sub_menu.name == 'separator'" class="mx-4 mb-1 pt-1"></div>
                      <span
                        v-else-if="!sub_menu.hidden && $can(sub_menu.permissions) && (sub_menu.link || route().has(sub_menu.route))"
                        class="relative"
                      >
                        <span
                          v-if="!sub_menu.link && isCurrentRoute(sub_menu.route)"
                          class="absolute -start-0.5 top-0 -bottom-px w-0.5 rounded-full bg-gray-950 dark:bg-white"
                          style="transform: none; transform-origin: 50% 50% 0px"
                        >
                        </span>
                        <component
                          :is="sub_menu.link ? 'a' : Link"
                          :target="sub_menu.link ? '_blank' : '_self'"
                          :href="sub_menu.link || route(sub_menu.route)"
                          :class="
                            !mainMenu.link && isCurrentRoute(sub_menu.route)
                              ? 'pointer-events-none bg-primary-100 dark:bg-primary-800'
                              : 'hover:bg-primary-50 dark:hover:bg-primary-900'
                          "
                          class="group flex w-full items-center gap-3 px-4 py-2.5 text-start text-base/6 font-medium data-[active]:bg-gray-950/5 data-[hover]:bg-gray-950/5 data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 data-[slot=icon]:*:data-[active]:fill-gray-950 data-[slot=icon]:*:data-[current]:fill-gray-950 data-[slot=icon]:*:data-[hover]:fill-gray-950 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:data-[active]:bg-white/5 dark:data-[hover]:bg-white/5 dark:data-[slot=icon]:*:fill-gray-400 dark:data-[slot=icon]:*:data-[active]:fill-white dark:data-[slot=icon]:*:data-[current]:fill-white dark:data-[slot=icon]:*:data-[hover]:fill-white"
                        >
                          <span
                            class="absolute start-1/2 top-1/2 size-[max(100%,2.75rem)] -translate-y-1/2 ltr:-translate-x-1/2 rtl:translate-x-1/2 [@media(pointer:fine)]:hidden"
                            aria-hidden="true"
                          >
                          </span>
                          <Icon v-if="sub_menu.icon" :name="sub_menu.icon" />
                          <span class="flex-1 grow truncate">{{ sub_menu.label }}</span>
                          <Icon v-if="sub_menu.link" name="external" size="w-4 h-4 hidden group-hover:block" />
                        </component>
                      </span>
                    </template>
                  </div>
                  <!-- </Transition> -->
                </div>
              </div>
              <div
                v-else-if="
                  mainMenu.sub_menu && $can(mainMenu.permissions) && (!mainMenu.module || (mainMenu.module && $page.props[mainMenu.module]))
                "
              >
                <div class="relative">
                  <div
                    v-if="isCurrentRoute(mainMenu.route, mainMenu.route_prefix)"
                    class="absolute inset-y-0 -start-0 w-0.5 rounded-full bg-gray-950 dark:bg-white"
                    style="transform: none; transform-origin: 50% 50% 0px"
                  ></div>
                  <div
                    :id="mainMenu.name"
                    :class="menuState[mainMenu.name] ? 'text-focus font-bold' : 'text-mute'"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-start text-base/6 data-[active]:bg-gray-950/5 data-[hover]:bg-gray-950/5 data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 data-[slot=icon]:*:data-[active]:fill-gray-950 data-[slot=icon]:*:data-[current]:fill-gray-950 data-[slot=icon]:*:data-[hover]:fill-gray-950 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:data-[active]:bg-white/5 dark:data-[hover]:bg-white/5 dark:data-[slot=icon]:*:fill-gray-400 dark:data-[slot=icon]:*:data-[active]:fill-white dark:data-[slot=icon]:*:data-[current]:fill-white dark:data-[slot=icon]:*:data-[hover]:fill-white"
                  >
                    <span
                      class="absolute start-1/2 top-1/2 size-[max(100%,2.75rem)] -translate-y-1/2 ltr:-translate-x-1/2 rtl:translate-x-1/2 [@media(pointer:fine)]:hidden"
                      aria-hidden="true"
                    >
                    </span>
                    <Icon v-if="mainMenu.icon" :name="mainMenu.icon" />
                    <span class="flex-1 grow truncate">{{ mainMenu.label }}</span>
                  </div>

                  <template :key="sub_menu.name" v-for="sub_menu in mainMenu.sub_menu">
                    <div v-if="sub_menu.name == 'separator'" class="mx-4 mb-1 pt-1"></div>
                    <div v-else-if="!sub_menu.hidden && (sub_menu.link || route().has(sub_menu.route))" class="relative">
                      <span
                        v-if="!sub_menu.link && isCurrentRoute(sub_menu.route)"
                        class="absolute inset-y-0 start-0.5 w-0.5 rounded-full bg-gray-950 dark:bg-white"
                        style="transform: none; transform-origin: 50% 50% 0px"
                      >
                      </span>

                      <component
                        v-if="$can(sub_menu.permissions)"
                        :is="sub_menu.link ? 'a' : Link"
                        :target="sub_menu.link ? '_blank' : '_self'"
                        :href="sub_menu.link || route(sub_menu.route)"
                        :class="
                          !mainMenu.link && isCurrentRoute(sub_menu.route)
                            ? 'pointer-events-none bg-primary-100 dark:bg-primary-800'
                            : 'hover:bg-primary-50 dark:hover:bg-primary-900'
                        "
                        class="group ms-0.5 flex w-full items-center gap-3 py-2.5 ps-6 pe-2 text-start text-base/6 font-medium data-[active]:bg-gray-950/5 data-[hover]:bg-gray-950/5 data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 data-[slot=icon]:*:data-[active]:fill-gray-950 data-[slot=icon]:*:data-[current]:fill-gray-950 data-[slot=icon]:*:data-[hover]:fill-gray-950 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:data-[active]:bg-white/5 dark:data-[hover]:bg-white/5 dark:data-[slot=icon]:*:fill-gray-400 dark:data-[slot=icon]:*:data-[active]:fill-white dark:data-[slot=icon]:*:data-[current]:fill-white dark:data-[slot=icon]:*:data-[hover]:fill-white"
                      >
                        <span
                          class="absolute start-1/2 top-1/2 size-[max(100%,2.75rem)] -translate-y-1/2 ltr:-translate-x-1/2 rtl:translate-x-1/2 [@media(pointer:fine)]:hidden"
                          aria-hidden="true"
                        >
                        </span>
                        <Icon v-if="sub_menu.icon" :name="sub_menu.icon" />
                        <span class="flex-1 grow truncate">{{ sub_menu.label }}</span>
                        <Icon v-if="mainMenu.link" name="external" size="w-4 h-4 hidden group-hover:block" />
                      </component>
                    </div>
                  </template>
                </div>
              </div>
            </template>
          </div>
        </template>
      </div>

      <!-- User Menus -->
      <!-- <div
        class="flex flex-col border-t border-gray-950/5 px-4 py-2 max-lg:hidden dark:border-white/5 [&>[data-slot=section]+[data-slot=section]]:mt-2.5"
      >
        <Dropdown align="left" valign="top" width="56">
          <template #trigger>
            <button
              type="button"
              class="flex w-full cursor-default items-center gap-3 rounded-lg px-2 py-2.5 text-start text-base/6 font-medium data-[slot=avatar]:*:-m-0.5 data-[slot=avatar]:*:size-7 data-[slot=avatar]:*:[--ring-opacity:10%] data-[slot=icon]:*:size-6 data-[slot=icon]:*:shrink-0 data-[slot=icon]:*:fill-gray-500 data-[slot=icon]:last:*:ms-auto data-[slot=icon]:last:*:size-5 sm:py-2 sm:text-sm/5 sm:data-[slot=avatar]:*:size-6 sm:data-[slot=icon]:*:size-5 sm:data-[slot=icon]:last:*:size-4 dark:text-white dark:data-[slot=icon]:*:fill-gray-400"
            >
              <span class="flex min-w-0 items-center gap-3">
                <span
                  data-slot="avatar"
                  class="inline-grid size-10 shrink-0 rounded-full align-middle outline -outline-offset-1 outline-black/[--ring-opacity] [--avatar-radius:20%] [--ring-opacity:20%] *:col-start-1 *:row-start-1 *:rounded-full dark:outline-white/[--ring-opacity]"
                >
                  <img class="size-full rounded" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                </span>
                <span class="min-w-0">
                  <span class="block truncate text-sm/5 font-medium dark:text-white">{{ $page.props.auth.user.name }}</span>
                  <span class="block truncate text-xs/5 font-normal text-gray-500 dark:text-gray-400">{{
                    $page.props.auth.user.email
                  }}</span>
                </span>
              </span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="text-mute size-5">
                <path
                  fill-rule="evenodd"
                  d="M11.78 9.78a.75.75 0 0 1-1.06 0L8 7.06 5.28 9.78a.75.75 0 0 1-1.06-1.06l3.25-3.25a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06Z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
          </template>

          <template #content>
            <div class="">
              <a
                class="group flex cursor-default gap-3 rounded-lg px-3.5 py-2.5 text-start text-base/6 focus:outline-none sm:px-3 sm:py-1.5 sm:text-sm/6 dark:text-white"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                  <path
                    fill-rule="evenodd"
                    d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-5-2a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM8 9c-1.825 0-3.422.977-4.295 2.437A5.49 5.49 0 0 0 8 13.5a5.49 5.49 0 0 0 4.294-2.063A4.997 4.997 0 0 0 8 9Z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rde:" data-headlessui-state="">My account</div>
              </a>
              <div
                class="col-span-full mx-3.5 my-1 h-px border-0 bg-gray-950/5 sm:mx-3 dark:bg-white/10 forced-colors:bg-[CanvasText]"
                role="separator"
              ></div>
              <a
                class="group flex cursor-default gap-3 rounded-lg px-3.5 py-2.5 text-start text-base/6 focus:outline-none sm:px-3 sm:py-1.5 sm:text-sm/6 dark:text-white"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                  <path
                    fill-rule="evenodd"
                    d="M8.5 1.709a.75.75 0 0 0-1 0 8.963 8.963 0 0 1-4.84 2.217.75.75 0 0 0-.654.72 10.499 10.499 0 0 0 5.647 9.672.75.75 0 0 0 .694-.001 10.499 10.499 0 0 0 5.647-9.672.75.75 0 0 0-.654-.719A8.963 8.963 0 0 1 8.5 1.71Zm2.34 5.504a.75.75 0 0 0-1.18-.926L7.394 9.17l-1.156-.99a.75.75 0 1 0-.976 1.138l1.75 1.5a.75.75 0 0 0 1.078-.106l2.75-3.5Z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rdg:" data-headlessui-state="">
                  Privacy policy
                </div>
              </a>
              <a
                class="group flex cursor-default gap-3 rounded-lg px-3.5 py-2.5 text-start text-base/6 focus:outline-none sm:px-3 sm:py-1.5 sm:text-sm/6 dark:text-white"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                  <path
                    d="M10.618 10.26c-.361.223-.618.598-.618 1.022 0 .226-.142.43-.36.49A6.006 6.006 0 0 1 8 12c-.569 0-1.12-.08-1.64-.227a.504.504 0 0 1-.36-.491c0-.424-.257-.799-.618-1.021a5 5 0 1 1 5.235 0ZM6.867 13.415a.75.75 0 1 0-.225 1.483 9.065 9.065 0 0 0 2.716 0 .75.75 0 1 0-.225-1.483 7.563 7.563 0 0 1-2.266 0Z"
                  ></path>
                </svg>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rdi:" data-headlessui-state="">
                  Share feedback
                </div>
              </a>
              <div
                class="col-span-full mx-3.5 my-1 h-px border-0 bg-gray-950/5 sm:mx-3 dark:bg-white/10 forced-colors:bg-[CanvasText]"
                role="separator"
              ></div>
              <a
                class="group flex cursor-default gap-3 rounded-lg px-3.5 py-2.5 text-start text-base/6 focus:outline-none sm:px-3 sm:py-1.5 sm:text-sm/6 dark:text-white"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                  <path
                    fill-rule="evenodd"
                    d="M2 4.75A2.75 2.75 0 0 1 4.75 2h3a2.75 2.75 0 0 1 2.75 2.75v.5a.75.75 0 0 1-1.5 0v-.5c0-.69-.56-1.25-1.25-1.25h-3c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h3c.69 0 1.25-.56 1.25-1.25v-.5a.75.75 0 0 1 1.5 0v.5A2.75 2.75 0 0 1 7.75 14h-3A2.75 2.75 0 0 1 2 11.25v-6.5Zm9.47.47a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06l-2.25 2.25a.75.75 0 1 1-1.06-1.06l.97-.97H5.25a.75.75 0 0 1 0-1.5h7.19l-.97-.97a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
                <div data-slot="label" class="col-start-2 row-start-1" id="headlessui-label-:rdk:" data-headlessui-state="">Sign out</div>
              </a>
            </div>
          </template>
        </Dropdown>
      </div> -->
    </nav>
  </div>
</template>
