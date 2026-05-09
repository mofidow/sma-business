<script setup>
import { ref } from 'vue';
import { Modal } from '@/Components/Jet';
import { $date, isValidDate } from '@/Core/helpers';

const show = ref(false);
defineProps({
  title: String,
  line: Boolean,
  modal: { type: Boolean, default: true },
  extra_attributes: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div v-if="Object.keys(extra_attributes).length" class="">
    <template v-if="modal">
      <button type="button" @click="() => (show = true)" class="-my-1 rounded-md border border-gray-200 px-2 py-0.5 dark:border-gray-700">
        {{ $t('View') }}
      </button>
      <Modal :show="show" maxWidth="lg" :closeable="true" @close="() => (show = false)">
        <div class="border-b border-gray-200 p-4 font-bold dark:border-gray-700">
          {{ $t('Custom Fields') }} {{ title ? ' (' + title + ')' : '' }}
        </div>
        <div class="m-6 flow-root">
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full rounded-md border border-gray-200 align-middle dark:border-gray-700">
              <table class="min-w-full">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <template v-for="key in Object.keys(extra_attributes)" :key="key">
                    <tr>
                      <td class="w-1/3 py-2 ps-4 pe-3 text-end text-sm">{{ key }}</td>
                      <td class="py-2 ps-3 pe-4 text-sm font-bold">
                        {{
                          Array.isArray(extra_attributes[key])
                            ? extra_attributes[key].join(', ')
                            : isValidDate(extra_attributes[key])
                              ? $date(extra_attributes[key])
                              : extra_attributes[key] || ''
                        }}
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </Modal>
    </template>
    <template v-else>
      <div>
        <template v-for="key in Object.keys(extra_attributes)" :key="key">
          <div>
            <div v-if="line" class="text-mute text-sm">{{ key }}</div>
            <template v-else>
              <span class="text-mute">{{ key }}</span
              >:
            </template>
            {{
              Array.isArray(extra_attributes[key])
                ? extra_attributes[key].join(', ')
                : isValidDate(extra_attributes[key])
                  ? $date(extra_attributes[key])
                  : extra_attributes[key] || ''
            }}
          </div>
        </template>
      </div>
    </template>
  </div>
</template>
