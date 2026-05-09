<script setup>
import { Head } from '@inertiajs/vue3';
import { Notification, NotificationGroup } from 'notiwind';

defineProps({ title: String });
</script>

<template>
  <div class="">
    <Head :title="title" />

    <main class="bg-gray-50 dark:bg-gray-800">
      <slot />
    </main>

    <!-- Page Heading -->
    <!-- <template v-if="$page.props.is_impersonating">
      <div class="fixed bottom-1 start-1 z-20 text-sm">
        <div class="max-w-62 rounded-md bg-yellow-100 px-4 py-2 dark:bg-yellow-950">
          {{ $t('You are impersonating as {x}', { x: $page.props.acting_as_user?.company || $page.props.acting_as_user?.name }) }}
          <Link
            :href="route('impersonate.stop')"
            class="relative mt-2.5 flex items-center gap-2 rounded-full text-sm font-bold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <span class="sr-only">{{ $t('Stop Impersonating') }}</span>
            <svg fill="none" class="size-4" stroke-width="1.5" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $t('Stop Impersonating') }}
          </Link>
        </div>
      </div>
    </template> -->

    <NotificationGroup group="main">
      <div class="pointer-events-none fixed inset-0 z-99 flex items-end px-4 py-6 sm:items-start sm:p-6 print:hidden">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
          <Notification
            v-slot="{ notifications, close }"
            enter="transform ease-out duration-300 transition"
            enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to="translate-y-0 opacity-100 sm:translate-x-0"
            leave="transition ease-in duration-500"
            leave-from="opacity-100"
            leave-to="opacity-0"
            move="transition duration-500"
            move-delay="delay-300"
          >
            <div
              :key="notification.id"
              v-for="notification in notifications"
              :class="
                notification.type === 'success'
                  ? 'bg-green-100'
                  : notification.type === 'error'
                    ? 'bg-red-100'
                    : 'bg-white dark:bg-gray-950'
              "
              class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
            >
              <div class="p-4">
                <div v-if="notification.type === 'success'" class="flex items-start text-green-600">
                  <div class="shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <div class="ms-3 w-0 flex-1 pt-0.5">
                    <p v-if="notification.title" class="mb-1 text-sm font-medium">{{ notification.title }}</p>
                    <p class="text-sm" v-html="notification.text"></p>
                  </div>
                  <div class="ms-4 flex shrink-0">
                    <button
                      type="button"
                      @click="close(notification.id)"
                      class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:ring-0 focus:outline-hidden"
                    >
                      <span class="sr-only">{{ $t('Close') }}</span>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                          d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
                <div v-if="notification.type === 'error'" class="flex items-start text-red-600">
                  <div class="shrink-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="h-6 w-6"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                      />
                    </svg>
                  </div>
                  <div class="ms-3 w-0 flex-1 pt-0.5">
                    <p v-if="notification.title" class="mb-1 text-sm font-medium">{{ notification.title }}</p>
                    <p class="text-sm" v-html="notification.text"></p>
                  </div>
                  <div class="ms-4 flex shrink-0">
                    <button
                      type="button"
                      @click="close(notification.id)"
                      class="inline-flex rounded-md text-gray-500 hover:text-gray-600 focus:ring-0 focus:outline-hidden"
                    >
                      <span class="sr-only">{{ $t('Close') }}</span>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                          d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </Notification>
        </div>
      </div>
    </NotificationGroup>
    <NotificationGroup group="mobile">
      <div class="pointer-events-none fixed inset-0 z-99 flex items-start px-4 py-6 sm:p-6 lg:hidden print:hidden">
        <div class="flex w-full flex-col items-end space-y-4">
          <Notification
            v-slot="{ notifications, close }"
            enter="transform ease-out duration-300 transition"
            enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to="translate-y-0 opacity-100 sm:translate-x-0"
            leave="transition ease-in duration-500"
            leave-from="opacity-100"
            leave-to="opacity-0"
            move="transition duration-500"
            move-delay="delay-300"
          >
            <div
              :key="notification.id"
              v-for="notification in notifications"
              :class="
                notification.type === 'success'
                  ? 'bg-green-100'
                  : notification.type === 'error'
                    ? 'bg-red-100'
                    : 'bg-white dark:bg-gray-950'
              "
              class="pointer-events-auto w-full max-w-[350px] overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
            >
              <div class="p-4">
                <div v-if="notification.type === 'success'" class="flex items-start text-green-600">
                  <div class="shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <div class="ms-3 w-0 flex-1 pt-0.5">
                    <p v-if="notification.title" class="mb-1 text-sm font-medium">{{ notification.title }}</p>
                    <p class="text-sm" v-html="notification.text"></p>
                  </div>
                  <div class="ms-4 flex shrink-0">
                    <button
                      type="button"
                      @click="close(notification.id)"
                      class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:ring-0 focus:outline-hidden"
                    >
                      <span class="sr-only">{{ $t('Close') }}</span>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                          d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
                <div v-if="notification.type === 'error'" class="flex items-start text-red-600">
                  <div class="shrink-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="h-6 w-6"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                      />
                    </svg>
                  </div>
                  <div class="ms-3 w-0 flex-1 pt-0.5">
                    <p v-if="notification.title" class="mb-1 text-sm font-medium">{{ notification.title }}</p>
                    <p class="text-sm" v-html="notification.text"></p>
                  </div>
                  <div class="ms-4 flex shrink-0">
                    <button
                      type="button"
                      @click="close(notification.id)"
                      class="inline-flex rounded-md text-gray-500 hover:text-gray-600 focus:ring-0 focus:outline-hidden"
                    >
                      <span class="sr-only">{{ $t('Close') }}</span>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                          d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </Notification>
        </div>
      </div>
    </NotificationGroup>
  </div>
</template>
