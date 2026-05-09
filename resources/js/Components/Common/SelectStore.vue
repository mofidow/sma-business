<script setup>
import { watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

import { Modal } from '@/Components/Jet';

const page = usePage();

watch(
  () => page.props.flash,
  () => {
    page.props.select_store = page.props.flash?.select_store ? true : false;
  }
);

function hide() {
  page.props.select_store = false;
}

function selectStore(id) {
  router.visit(route('stores.select', { id }), {
    method: 'post',
    onSuccess: () => (page.props.select_store = false),
  });
}
</script>

<template>
  <Modal :show="page.props.select_store" max-width="md" @close="hide">
    <div class="p-6">
      <div class="flex items-center justify-start gap-4">
        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
          <Icon name="store-o" size="size-5 text-primary-600" />
        </div>
        <div class="flex-1 grow">
          <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
            {{ $t('Select {x}', { x: $t('Store') }) }}
          </h3>
          <p class="text-sm">
            {{ $t('Please select a store first!') }}
          </p>
        </div>
      </div>
      <div class="mt-6 grid grid-cols-2 gap-6 sm:mt-8">
        <template v-for="store in page.props.available_stores" :key="store.id">
          <button
            v-if="store.id == page.props.selected_store"
            @click="selectStore(0)"
            class="yellow:bg-yellow-800 yellow:text-yellow-100 yellow:hover:bg-yellow-700 focus inline-flex w-full justify-center rounded-lg bg-yellow-100 px-3 py-2 text-sm font-semibold text-yellow-900 shadow-xs hover:bg-yellow-300"
          >
            {{ $t('Unselect {x}', { x: store.name }) }}
          </button>
          <button
            v-else
            @click="selectStore(store.id)"
            class="focus inline-flex w-full justify-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs hover:bg-gray-300 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-700"
          >
            {{ $t('Select {x}', { x: store.name }) }}
          </button>
        </template>
      </div>
    </div>
  </Modal>
</template>
