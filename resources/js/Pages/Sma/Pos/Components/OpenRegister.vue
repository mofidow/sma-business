<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { notify } from 'notiwind';
import { router, usePage } from '@inertiajs/vue3';

import { Modal } from '@/Components/Jet';
import { Input, LoadingButton } from '@/Components/Common';

const page = usePage();
defineProps(['show']);
const emit = defineEmits(['close']);

const loading = ref(false);
const cash_in_hand = ref(0);

async function openRegisterNow() {
  loading.value = true;
  await router.post(
    route('pos.register'),
    { cash_in_hand: cash_in_hand.value },
    {
      onSuccess: () => {
        emit('close');
      },
      onFinish: () => (loading.value = false),
    }
  );
}

function selectStore() {
  router.visit(route('stores.select', { store: page.props.opened_register.store_id }), {
    method: 'post',
    onSuccess: p => {
      if (p.props.opened_register && p.props.opened_register.store_id != p.props.selected_store) {
        notify({
          group: 'main',
          type: 'success',
          title: 'Success!',
          text: t('The store has been selected!'),
        });
        page.props.select_store = false;
        emit('close');
      }
    },
  });
}
</script>

<template>
  <Modal :show="show" maxWidth="sm" :closeable="false" :round="$page.props.settings?.pos_design == 'Modern'">
    <div v-if="page.props.opened_register" class="p-6">
      <div>
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Register already opened!') }}
        </h3>
        <p class="mt-1 text-sm">{{ $t('You already have opened register at {x}.', { x: page.props.opened_register.store.name }) }}</p>
      </div>

      <div class="mt-6 flex justify-between gap-4">
        <Link :href="route('dashboard')" class="link">{{ $t('Go to {x}', { x: $t('Dashboard') }) }}</Link>
        <button v-if="!page.props.auth.user.store_id && !page.props.order" type="button" @click="selectStore" class="link">
          {{ $t('Select {x}', { x: page.props.opened_register.store.name }) }}
        </button>
        <Link v-if="page.props.order" :href="route('orders.index')" class="link">
          {{ $t('Go to {x}', { x: $t('Orders') }) }}
        </Link>
      </div>
    </div>

    <div v-else>
      <div class="relative border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
          {{ $t('Open Register') }}
        </h3>
        <p class="mt-1 text-sm">{{ $t('Please type the cash in hand to open register.') }}</p>
      </div>

      <div
        class="bg-gray-100 p-6 dark:bg-gray-800"
        :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-b-2xl' : 'rounded-b-lg'"
      >
        <form @submit.prevent="openRegisterNow" autocomplete="off" class="flex flex-col items-stretch gap-4">
          <Input keyboard :placeholder="$t('Cash in hand')" type="number" step="1" v-model="cash_in_hand" id="register-cash-in-hand" />
          <LoadingButton class="justify-center" :loading="loading">{{ $t('Open') }}</LoadingButton>
          <Link :href="route('dashboard')" class="link">{{ $t('Go to {x}', { x: $t('Dashboard') }) }}</Link>
        </form>
      </div>
    </div>
  </Modal>
</template>
