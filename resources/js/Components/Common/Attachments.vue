<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { DialogModal, DangerButton, SecondaryButton } from '@/Components/Jet';

const modal = ref(false);
const selected = ref(null);

defineProps({
  attachments: {
    type: Array,
  },
});

const destroy = () => {
  router.delete(route('sma.attachments.destroy', selected.value.id), {
    onFinish: closeModal,
  });
};

const openModal = attachment => {
  modal.value = true;
  selected.value = attachment;
};

const closeModal = () => {
  modal.value = false;
  selected.value = null;
};
</script>

<template>
  <div v-if="attachments.length">
    <p class="mb-1">{{ $t('Attachments') }}</p>
    <ul role="list" class="divide-y divide-gray-200 rounded-md border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
      <li v-for="attachment in attachments" :key="attachment.id" class="flex items-center justify-between py-4 ps-4 pe-5 text-sm leading-6">
        <div class="flex w-0 flex-1 items-center">
          <svg class="h-5 w-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path
              fill-rule="evenodd"
              d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z"
              clip-rule="evenodd"
            />
          </svg>
          <div class="ms-4 flex min-w-0 flex-1 gap-2">
            <span class="truncate font-medium">{{ attachment.title }}</span>
            <!-- <span class="shrink-0 text-gray-400">{{ attachment.filesize }}</span> -->
          </div>
        </div>
        <div class="ms-4 flex shrink-0 items-center gap-2">
          <a :href="route('sma.attachments.download', attachment.uuid)" class="link font-medium">{{ $t('Download') }}</a>
          <button
            type="button"
            @click="openModal(attachment)"
            v-if="$can('delete-attachments')"
            class="font-medium text-red-600 hover:text-red-500"
          >
            {{ $t('Delete') }}
          </button>
        </div>
      </li>
    </ul>
    <DialogModal :show="modal" @close="closeModal" maxWidth="sm">
      <template #title> {{ $t('Delete {x}', { x: $t('attachment') }) }} </template>
      <template #content>
        <p>{{ $t('Please confirm that you would like to delete the record?') }}</p>
        <p class="mt-2 text-red-500">{{ $t('This action will delete the data permanently.') }}</p>
      </template>
      <template #footer>
        <SecondaryButton @click="closeModal"> {{ $t('Cancel') }} </SecondaryButton>
        <DangerButton @click="destroy"> {{ $t('Yes, delete') }} </DangerButton>
      </template>
    </DialogModal>
  </div>
</template>
