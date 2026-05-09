<script setup>
defineProps(['form']);
const emit = defineEmits(['discount']);
</script>

<template>
  <div
    class="flex flex-col gap-1 print:border-t print:shadow-none"
    :class="$page.props.settings?.pos_design == 'Modern' ? '' : 'rounded-md bg-gray-100 px-3 py-2 shadow-inner dark:bg-gray-800'"
  >
    <div class="flex items-center justify-between text-xs">
      {{ $t('Items') }}
      <span> {{ form.items.length }} ({{ $decimal_qty(form.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}) </span>
    </div>
    <div class="flex items-center justify-between text-xs">
      {{ $t('Subtotal') }} <span>{{ $currency(form.items.reduce((a, i) => Number($decimal(i.subtotal)) + a, 0)) }}</span>
    </div>
    <div class="flex items-center justify-between text-xs">
      <button type="button" class="link" @click="emit('discount')">
        {{ $t('Discount') }}
      </button>
      <span>{{ $currency(form.items.reduce((a, i) => Number($decimal(i.total_discount_amount)) + a, 0)) }}</span>
    </div>
    <div class="flex items-center justify-between text-xs">
      {{ $t('Tax') }} <span>{{ $currency(form.items.reduce((a, i) => Number($decimal(i.total_tax_amount)) + a, 0)) }}</span>
    </div>
    <div class="mt-1 border-t-2 border-dashed dark:border-gray-500 print:border-solid"></div>
    <div class="flex items-center justify-between text-sm font-bold">
      {{ $t('Payable') }}
      <span class="text-lg font-bold">{{ $currency(form.items.reduce((a, i) => Number($decimal(i.total)) + a, 0)) }}</span>
    </div>
  </div>
</template>
