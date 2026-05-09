<script setup>
import { route } from 'ziggy-js';
import { onMounted, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

router.on('navigate', () => (currentRoute.value = route ? route()?.current() : null));

defineProps({ links: { type: Array } });
const currentRoute = ref(null);

onMounted(() => {
  try {
    currentRoute.value = route()?.current();
  } catch (e) {
    console.log(e);
  }
});

function changePage(event) {
  router.visit(route(event.target.value));
}
</script>
<template>
  <div class="px-6 pt-6 sm:pt-0" v-if="links && links.length">
    <div class="sm:hidden">
      <label for="tabs" class="sr-only">{{ $t('Select a tab') }}</label>
      <select
        id="tabs"
        name="tabs"
        @change="changePage"
        class="block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800"
      >
        <option v-for="link in links" :key="link.route" :value="link.route" :selected="link.route == currentRoute">{{ link.label }}</option>
      </select>
    </div>
    <div class="hidden sm:block">
      <div class="-mx-6 overflow-x-auto border-b border-gray-200 px-6 pb-px dark:border-gray-700">
        <nav class="-mb-px flex space-x-8 whitespace-nowrap" aria-label="Tabs">
          <template v-for="link in links" :key="link.route">
            <a
              v-if="link.external"
              :href="route(link.route)"
              class="group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium"
              :class="
                link.route == currentRoute
                  ? 'border-primary-500 text-primary-600'
                  : 'text-mute border-transparent hover:border-gray-300 hover:text-gray-700 dark:hover:border-gray-600 dark:hover:text-gray-300'
              "
            >
              <Icon
                v-if="link.icon"
                :name="link.icon"
                :size="
                  link.route == currentRoute
                    ? '-ms-0.5 me-2 size-5 text-primary-500'
                    : '-ms-0.5 me-2 size-5 text-gray-400 group-hover:text-gray-500'
                "
              />
              <span>{{ link.label }}</span>
            </a>
            <Link
              v-else
              :href="route(link.route)"
              class="group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium"
              :class="
                link.route == currentRoute
                  ? 'border-primary-500 text-primary-600'
                  : 'text-mute border-transparent hover:border-gray-300 hover:text-gray-700 dark:hover:border-gray-600 dark:hover:text-gray-300'
              "
            >
              <Icon
                v-if="link.icon"
                :name="link.icon"
                :size="
                  link.route == currentRoute
                    ? '-ms-0.5 me-2 size-5 text-primary-500'
                    : '-ms-0.5 me-2 size-5 text-gray-400 group-hover:text-gray-500'
                "
              />
              <span>{{ link.label }}</span>
            </Link>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>
