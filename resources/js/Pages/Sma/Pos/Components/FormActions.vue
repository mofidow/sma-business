<script setup>
defineProps(['loading']);
const emit = defineEmits(['hold', 'print-order', 'print-bill', 'payment']);
</script>

<template>
  <button
    type="button"
    @click="emit('delete-order')"
    class="group bg-red-500 p-2 text-sm font-semibold text-white shadow-2xs hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500"
    :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-full shadow-lg' : 'rounded-md'"
  >
    <Icon name="trash-o" className="group-hover:hidden group-hover:opacity-0 opacity-100 transition size-5" />
    <Icon name="trash" className="hidden group-hover:block group-hover:opacity-100 opacity-0 transition size-5" />
  </button>
  <button
    type="button"
    @click="emit('print-order')"
    class="bg-gray-800 px-3 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-gray-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-800"
    :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-full shadow-lg' : 'rounded-md'"
  >
    {{ $t('Order') }}
  </button>
  <button
    type="button"
    @click="emit('print-bill')"
    class="bg-gray-800 px-3 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-gray-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-800"
    :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-full shadow-lg' : 'rounded-md'"
  >
    {{ $t('Bill') }}
  </button>
  <button
    type="button"
    :disabled="loading"
    @click="emit('hold')"
    class="bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
    :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-full shadow-lg' : 'rounded-md'"
  >
    {{ $t('Hold') }}
  </button>
  <button
    type="button"
    @click="emit('payment')"
    class="grow bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-green-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
    :class="$page.props.settings?.pos_design == 'Modern' ? 'rounded-full shadow-lg' : 'rounded-md'"
  >
    {{ $t('Payment') }}
  </button>
</template>
