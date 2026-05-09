<script setup>
import { ref } from 'vue';
// import { router } from '@inertiajs/vue3';

import AppLayout from './AppLayout.vue';
import Topbar from '@/Layouts/Components/Topbar.vue';
import Sidebar from '@/Layouts/Components/Sidebar.vue';
import SelectStore from '@/Components/Common/SelectStore.vue';

defineProps({ title: String });
const isSidebarOpen = ref(false);

// const logout = () => {
//   router.post(route('logout'));
// };
</script>

<template>
  <AppLayout>
    <div class="flex min-h-screen flex-col items-stretch justify-stretch">
      <!-- <div v-show="isSidebarOpen" class="fixed inset-0 z-40" @click="isSidebarOpen = false" /> -->
      <Sidebar :open="isSidebarOpen" @close="isSidebarOpen = false" />

      <div class="main-contents flex grow flex-col items-stretch justify-stretch self-stretch xl:ps-64">
        <!-- Sticky search header -->
        <div
          class="top-bar-con sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-6 border-b px-4 sm:px-6 lg:px-8"
          :class="
            $page.props.settings.dark_topbar == 1
              ? 'dark border-gray-700 bg-gray-900 text-gray-300'
              : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900'
          "
        >
          <button type="button" @click="isSidebarOpen = true" class="text-focus -m-2.5 p-2.5 xl:hidden">
            <span class="sr-only">{{ $t('Open Sidebar') }}</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
              <path
                fill-rule="evenodd"
                d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z"
                clip-rule="evenodd"
              />
            </svg>
          </button>

          <Topbar />
        </div>

        <main class="flex grow flex-col items-stretch justify-stretch self-stretch bg-gray-50 dark:bg-gray-800">
          <SelectStore />
          <slot />
        </main>
      </div>
    </div>
  </AppLayout>
</template>
