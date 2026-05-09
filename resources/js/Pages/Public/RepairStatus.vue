<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/Common';
import AppLayout from '@/Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
  result: Object,
  hash: String,
});

const form = useForm({
  hash: props.hash || '',
});

function handleSearch() {
  form.get(route('repair-status.index'), {
    preserveScroll: true,
  });
}

function getStatusColor(status) {
  const colors = {
    pending: 'bg-gray-100 text-gray-800 border-gray-300',
    in_progress: 'bg-primary-100 text-primary-800 border-primary-300',
    waiting_parts: 'bg-yellow-100 text-yellow-800 border-yellow-300',
    completed: 'bg-green-100 text-green-800 border-green-300',
    delivered: 'bg-green-100 text-green-800 border-green-300',
    cancelled: 'bg-red-100 text-red-800 border-red-300',
  };
  return colors[status] || colors.pending;
}

function getPriorityColor(priority) {
  const colors = {
    low: 'bg-gray-100 text-gray-600 border-gray-300',
    normal: 'bg-primary-100 text-primary-600 border-primary-300',
    high: 'bg-orange-100 text-orange-600 border-orange-300',
    urgent: 'bg-red-100 text-red-600 border-red-300',
  };
  return colors[priority] || colors.normal;
}

function getStatusProgress(status) {
  const progress = {
    pending: 10,
    in_progress: 40,
    waiting_parts: 60,
    completed: 90,
    delivered: 100,
    cancelled: 0,
  };
  return progress[status] || 0;
}
</script>

<template>
  <Head>
    <title>{{ $t('Check Repair Status') }}</title>
  </Head>

  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8 dark:bg-gray-900 print:items-start">
    <div class="w-full max-w-2xl space-y-8">
      <!-- Header -->
      <div class="text-center print:hidden">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">
          {{ $t('Check Repair Status') }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          {{ $t('Enter your repair order secret hash') }}
        </p>
      </div>

      <!-- Search Form -->
      <div class="mt-8 rounded-lg bg-white p-6 shadow-md dark:bg-gray-800 print:hidden">
        <form @submit.prevent="handleSearch" class="space-y-4">
          <div>
            <Input
              id="hash"
              autofocus
              type="text"
              v-model="form.hash"
              :label="$t('Secret')"
              :error="form.errors.hash"
              :placeholder="$t('Enter your repair order secret hash')"
            />
          </div>

          <Button
            type="submit"
            class="w-full justify-center"
            :disabled="form.processing || !form.hash"
            :class="{ 'opacity-25': form.processing }"
          >
            {{ $t('Check Status') }}
          </Button>
        </form>
      </div>

      <!-- Search Result -->
      <div v-if="result" class="mt-6 rounded-lg bg-white p-6 shadow-md dark:bg-gray-800 print:mt-0 print:pt-0 print:shadow-none">
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ result.reference }}
              </h2>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ result.service_type?.name }}
              </p>
            </div>
            <div class="flex gap-2">
              <span
                :class="getPriorityColor(result.priority)"
                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold capitalize"
              >
                {{ $t(result.priority) }}
              </span>
              <span
                :class="getStatusColor(result.status)"
                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold capitalize"
              >
                {{ $t(result.status.replace('_', ' ')) }}
              </span>
            </div>
          </div>

          <!-- Progress Bar -->
          <div v-if="result.status !== 'cancelled'" class="mt-4">
            <div class="relative pt-1">
              <div class="mb-2 flex items-center justify-between">
                <div>
                  <span class="inline-block text-xs font-semibold text-primary-600 dark:text-primary-400">
                    {{ $t('Progress') }}
                  </span>
                </div>
                <div class="text-right">
                  <span class="inline-block text-xs font-semibold text-primary-600 dark:text-primary-400">
                    {{ getStatusProgress(result.status) }}%
                  </span>
                </div>
              </div>
              <div class="print:exact mb-4 flex h-2 overflow-hidden rounded bg-primary-200! text-xs dark:bg-primary-900">
                <div
                  :style="{ width: getStatusProgress(result.status) + '%' }"
                  class="flex flex-col justify-center rounded-l bg-primary-500! text-center whitespace-nowrap text-white shadow-none transition-all duration-500"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="space-y-4">
          <div v-if="result.serial_no">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Serial Number') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ result.serial_no }}</dd>
          </div>

          <div v-if="result.problem_description">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Problem') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ result.problem_description }}</dd>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Received Date') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $date(result.received_date) }}</dd>
            </div>

            <div v-if="result.due_date">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Estimated Completion') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $date(result.due_date) }}</dd>
            </div>

            <div v-if="result.completed_date">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Completed Date') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $date(result.completed_date) }}</dd>
            </div>

            <div v-if="result.price">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Price') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $currency(result.price) }}</dd>
            </div>
          </div>

          <div v-if="result.customer_notes">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $t('Notes') }}</dt>
            <dd class="mt-1 text-sm whitespace-pre-line text-gray-900 dark:text-white">{{ result.customer_notes }}</dd>
          </div>

          <!-- Store Contact -->
          <div v-if="result.store" class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $t('Contact Information') }}</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <p v-if="result.store.name" class="font-medium text-gray-900 dark:text-white">{{ result.store.name }}</p>
              <p v-if="result.store.phone">{{ $t('Phone') }}: {{ result.store.phone }}</p>
              <p v-if="result.store.email">{{ $t('Email') }}: {{ result.store.email }}</p>
              <p v-if="result.store.address" class="mt-1">{{ result.store.address }}</p>
            </div>
          </div>

          <!-- Status Messages -->
          <div v-if="result.status === 'completed'" class="mt-4 rounded-md bg-green-50 p-4 dark:bg-green-950">
            <div class="flex">
              <div class="shrink-0">
                <Icon name="tick" class="size-5 text-green-400" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                  {{ $t('Your device is ready for pickup!') }}
                </p>
                <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                  {{ $t('Please contact us to arrange pickup at your convenience.') }}
                </p>
              </div>
            </div>
          </div>

          <div v-if="result.status === 'delivered'" class="mt-4 rounded-md bg-primary-50 p-4 dark:bg-primary-950">
            <div class="flex">
              <div class="shrink-0">
                <Icon name="check-badge" class="size-5 text-primary-400" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-primary-800 dark:text-primary-200">
                  {{ $t('Your device has been delivered!') }}
                </p>
                <p class="mt-1 text-sm text-primary-700 dark:text-primary-300">
                  {{ $t('Thank you for choosing our service.') }}
                </p>
              </div>
            </div>
          </div>

          <div v-if="result.status === 'waiting_parts'" class="mt-4 rounded-md bg-yellow-50 p-4 dark:bg-yellow-950">
            <div class="flex">
              <div class="shrink-0">
                <Icon name="info" class="size-5 text-yellow-400" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                  {{ $t('Waiting for parts') }}
                </p>
                <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                  {{ $t('We are waiting for replacement parts to arrive. We will notify you once work resumes.') }}
                </p>
              </div>
            </div>
          </div>

          <div v-if="result.status === 'cancelled'" class="mt-4 rounded-md bg-red-50 p-4 dark:bg-red-950">
            <div class="flex">
              <div class="shrink-0">
                <Icon name="x" class="size-5 text-red-400" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                  {{ $t('This repair order has been cancelled') }}
                </p>
                <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                  {{ $t('Please contact us for more information.') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Result Message -->
      <div v-else-if="search && !result" class="mt-6 rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
        <div class="text-center">
          <Icon name="search" class="mx-auto size-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ $t('No results found') }}</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $t('Please check your reference or serial number and try again.') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
