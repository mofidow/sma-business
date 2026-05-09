<script setup>
import { ViewCustomFields } from '@/Components/Common';

defineProps(['current', 'custom_fields', 'editRow']);
</script>

<template>
  <span class="absolute end-14 top-4 inline-flex items-center">
    <button type="button" @click="() => editRow(current)" class="link -m-2 p-2">
      <Icon name="edit-o" class="size-5" />
    </button>
  </span>

  <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
    <div class="sm:flex sm:items-baseline sm:justify-between">
      <div class="sm:w-0 sm:flex-1">
        <h1 class="text-focus text-base font-semibold">
          {{ current?.name }}
          {{ current?.company ? ` (${current?.company})` : '' }}
        </h1>
        <p class="text-mute mt-1 truncate text-sm">
          {{ $t('Please view the details below') }}
        </p>
      </div>
    </div>
  </div>

  <div class="pb-2">
    <dl class="divide-y divide-gray-100 dark:divide-gray-700/50">
      <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Name') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">{{ current.name }}</dd>
      </div>
      <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Username') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">{{ current.username || '' }}</dd>
      </div>
      <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Phone') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">{{ current.phone || '' }}</dd>
      </div>
      <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Email') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">{{ current.email || '' }}</dd>
      </div>
      <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Role') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">{{ current.roles?.map(r => r.name).join(',') || '' }}</dd>
      </div>
      <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Award Point') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">{{ $number(current.points) }}</dd>
      </div>

      <!-- <div class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Address') }}</dt>
        <dd class="mt-1 text-sm/6 text-focus sm:col-span-2 sm:mt-0 flex gap-2">
          <span class="text-2xl -mt-0.5">{{ current?.country?.emoji }}</span>

          {{ $address(current) }}
        </dd>
      </div> -->
      <div v-if="current.extra_attributes" class="px-4 py-2.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Custom Fields') }}</dt>
        <dd class="text-focus mt-1 text-sm/6 sm:col-span-2 sm:mt-0">
          <ViewCustomFields :modal="false" :title="current.company || current.name" :extra_attributes="current.extra_attributes ?? []" />
        </dd>
      </div>
    </dl>
  </div>
</template>
