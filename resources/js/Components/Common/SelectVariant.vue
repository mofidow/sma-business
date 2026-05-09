<script setup>
import isEqual from 'lodash/isEqual';
import { usePage } from '@inertiajs/vue3';
import { onMounted, nextTick, ref, watch } from 'vue';

import { Modal } from '@/Components/Jet';
import { AutoComplete, Input } from '@/Components/Common';

const page = usePage();
const props = defineProps(['item', 'round']);
const emit = defineEmits(['close', 'update']);

const meta = ref({});
const show = ref(true);
const code = ref(null);
const selected = ref(null);
const localItem = ref(props.item);
// watch(selected, s => emit('update', { ...props.item, variation: s }), { deep: true });
watch(code, s => {
  selected.value = props.item?.product?.variations.find(v => v.code.toLowerCase() == s.toLowerCase());
  update();
});
watch(
  meta,
  s => {
    selected.value = props.item?.product?.variations.find(v => isEqual(v.meta, s));
    update();
  },
  { deep: true }
);

onMounted(async () => {
  await nextTick();
  setTimeout(() => {
    document.getElementById('variation-code')?.focus();
  }, 100);
});

function update() {
  if (selected.value) {
    const variation = props.item.variations.find(v => v.id == selected.value.id);
    if (variation) {
      localItem.value.variations = props.item.variations.map(v => (v.id == variation.id ? { ...v, quantity: v.quantity + 1 } : v));
      selected.value = variation;
    } else {
      localItem.value.variations = props.item.variations.map(v =>
        v.id
          ? v
          : {
              ...selected.value,
              quantity: 1,
              taxes: props.item.taxes,
              unit_id: props.item.unit_id,
              tax_included: props.item.tax_included == 1,
              cost: selected.value.cost || props.item.cost,
              price: selected.value.price || props.item.price,
            }
      );
    }

    localItem.value.variations = localItem.value.variations.filter(v => v.id);
    localItem.value.quantity = localItem.value.variations.reduce((a, v) => Number(v.quantity) + a, 0);

    meta.value = {};
    code.value = null;
    selected.value = null;

    emit('update', localItem.value);
  }
}
</script>

<template>
  <Modal :show="show" max-width="md" @close="$emit('close')" :overflow="true" :round="round">
    <div class="p-6">
      <div class="flex items-center justify-start gap-4">
        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
          <Icon name="bolt" size="size-5 text-primary-600" />
        </div>
        <div class="flex-1 grow">
          <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
            {{ $t('Select {x}', { x: $t('Variant') }) }}
          </h3>
          <p class="text-sm">
            {{ $t('Please select the variant to add item to order list.') }}
          </p>
        </div>
      </div>
      <div class="mt-6 grid grid-cols-1 gap-6 sm:mt-8 sm:grid-cols-2">
        <div class="col-span-full">
          <Input label="" v-model="code" id="variation-code" :placeholder="$t('Scan barcode or type the variation code')" />
        </div>
        <template v-for="(variant, vi) in localItem.product.variants" :key="vi">
          <div>
            <AutoComplete id="type" :json="true" :label="$t(variant.name)" :suggestions="variant.options" v-model="meta[variant.name]" />
          </div>
        </template>
        <div v-if="selected" class="col-span-full rounded-lg border border-gray-200 dark:border-gray-700">
          <dl class="divide-y divide-gray-200 dark:divide-gray-700">
            <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm/6 font-medium text-gray-900">{{ $t('Code') }}</dt>
              <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ selected.code }}</dd>
            </div>
            <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm/6 font-medium text-gray-900">{{ $t('Quantity') }}</dt>
              <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">
                {{ $number(selected.stocks.find(s => s.store_id == page.props.selected_store)?.balance) }}
              </dd>
            </div>
            <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm/6 font-medium text-gray-900">{{ $t('Cost') }}</dt>
              <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ selected.cost ? $number(selected.cost) : '' }}</dd>
            </div>
            <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm/6 font-medium text-gray-900">{{ $t('Price') }}</dt>
              <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ selected.price ? $number(selected.price) : '' }}</dd>
            </div>
            <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm/6 font-medium text-gray-900">{{ $t('Weight') }}</dt>
              <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ selected.weight ? $number(selected.weight) : '' }}</dd>
            </div>
            <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm/6 font-medium text-gray-900">{{ $t('Dimensions') }}</dt>
              <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ selected.dimensions }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </Modal>
</template>
