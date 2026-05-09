<script setup>
import { ViewCustomFields } from '@/Components/Common';

defineProps(['current', 'custom_fields', 'editRow']);
</script>

<template>
  <span class="absolute end-12 top-4 inline-flex items-center sm:end-14">
    <button v-if="editRow" type="button" @click="() => editRow(current)" class="link -m-2 p-2">
      <Icon name="edit-o" class="size-5" />
    </button>
  </span>

  <div class="border-b border-gray-200 px-4 py-4 sm:px-6 dark:border-gray-700">
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
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Name') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.name }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Company') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.company || '' }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Phone') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.phone || '' }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Email') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.email || '' }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Award Point') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ $number(current.points) }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Due Amount') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ $currency(current.balance) }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Due Limit') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.due_limit ? $number(current.due_limit) : '' }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Customer Group') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.customer_group?.name || '' }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Price Group') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">{{ current.price_group?.name || '' }}</dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Address') }}</dt>
        <dd class="text-focus col-span-2 mt-0 flex gap-2 text-sm/6">
          <span class="-mt-0.5 text-2xl">{{ current?.country?.emoji }}</span>

          {{ $address(current) }}
        </dd>
      </div>
      <div class="grid grid-cols-3 gap-4 px-4 py-2.5 sm:px-6">
        <dt class="text-sm font-medium">{{ $t('Custom Fields') }}</dt>
        <dd class="text-focus col-span-2 mt-0 text-sm/6">
          <ViewCustomFields :modal="false" :title="current.company || current.name" :extra_attributes="current.extra_attributes" />
        </dd>
      </div>
    </dl>
  </div>
</template>
