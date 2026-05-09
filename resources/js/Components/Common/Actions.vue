<script setup>
import { ref, watch } from 'vue';
import { DangerButton, DialogModal, SecondaryButton } from '@/Components/Jet';
import { Button } from '@/Components/Common';

const props = defineProps({
  row: { type: Object },
  record: { type: String },
  editRow: { type: Function },
  deleting: { type: Boolean },
  permission: { type: String },
  deleteRow: { type: Function },
  restoreRow: { type: Function },
  deleted: { type: [Boolean, Number] },
  restored: { type: [Boolean, Number] },
});

const confirm = ref(false);
const confirm_restore = ref(false);

watch(
  () => props.deleted,
  value => {
    if (value == props.row.id) {
      confirm.value = false;
    }
  }
);

watch(
  () => props.restored,
  value => {
    console.log(value);
    if (value == props.row.id) {
      confirm_restore.value = false;
    }
  }
);

function deleteRecord(row) {
  props.deleteRow(row);
}

function restoreRecord(row) {
  props.restoreRow(row);
}
</script>

<template>
  <div class="text-mute flex items-center gap-4">
    <button v-if="editRow && $can('update-' + permission)" type="button" class="link" @click="editRow(row)">
      <Icon name="edit-o" />
    </button>
    <button v-if="row.deleted_at && restoreRow && $can('delete-' + permission)" type="button" @click="confirm_restore = true">
      <Icon name="refresh" class="link" />
    </button>
    <button v-if="deleteRow && $can('delete-' + permission)" type="button" @click="confirm = true">
      <Icon v-if="row.deleted_at" name="trash" class="link-red" />
      <Icon v-else name="trash-o" class="link-red" />
    </button>

    <!-- Delete Confirmation Modal -->
    <DialogModal :show="confirm" @close="confirm = false" max-width="md" :backdrop="!deleting" :closeable="!deleting">
      <template #title>
        <span class="text-red-500"> {{ $t('Delete {x}', { x: record || $t('record') }) }}? </span>
      </template>

      <template #content>
        <p>
          {{ $t('Are you sure you want to delete the {record}?', { record: record || $t('record') }) }}
        </p>
        <div v-if="row.deleted_at" class="mt-4 font-extrabold text-red-500">
          <p>
            {{ $t('{x} will be deleted permanently from system.', { x: record || $t('Record') }) }}
          </p>
          <p class="mt-2">
            {{ $t('This action is not reversible!', { x: record || $t('Record') }) }}
          </p>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="confirm = false" :class="{ 'opacity-25': deleting }" :disabled="deleting">
          {{ $t('Cancel') }}
        </SecondaryButton>

        <DangerButton class="ms-3" @click="deleteRecord(row)" :class="{ 'opacity-25': deleting }" :disabled="deleting">
          {{ $t('Delete {x}', { x: record || $t('record') }) }}
        </DangerButton>
      </template>
    </DialogModal>

    <!-- Restore Confirmation Modal -->
    <DialogModal :show="confirm_restore" @close="confirm_restore = false" max-width="sm" :backdrop="!deleting" :closeable="!deleting">
      <template #title>
        <span class="text-red-500"> {{ $t('Restore {x}', { x: record || $t('record') }) }}? </span>
      </template>

      <template #content>
        <p>
          {{ $t('Are you sure you want to restore the {record}?', { record: record || $t('record') }) }}
        </p>
      </template>

      <template #footer>
        <SecondaryButton @click="confirm_restore = false" :class="{ 'opacity-25': deleting }" :disabled="deleting">
          {{ $t('Cancel') }}
        </SecondaryButton>

        <Button class="ms-3" @click="restoreRecord(row)" :class="{ 'opacity-25': deleting }" :disabled="deleting">
          {{ $t('Restore {x}', { x: record || $t('record') }) }}
        </Button>
      </template>
    </DialogModal>
  </div>
</template>
