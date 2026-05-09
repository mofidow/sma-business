<script setup>
import { route } from 'ziggy-js';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

import { discount_keypress } from '@/Core';
import { SecondaryButton } from '@/Components/Jet';
import { AutoComplete, Input, Textarea } from '@/Components/Common';

const props = defineProps(['show', 'selectedItem', 'taxes']);
const emit = defineEmits(['close', 'update', 'remove', 'select:variant']);

const item = ref(null);

onMounted(() => {
  item.value = props.selectedItem;
});
</script>

<template>
  <div v-if="item">
    <div class="border-b border-gray-200 px-4 py-4 sm:px-6 dark:border-gray-700">
      <div class="sm:flex sm:items-baseline sm:justify-between">
        <div class="sm:w-0 sm:flex-1">
          <h1 class="text-focus text-base font-semibold">{{ $t('Edit {x}', { x: item.name }) }}</h1>
          <p class="text-mute mt-1 truncate text-sm">
            {{ $t('Please update the details below') }}
          </p>
        </div>
      </div>
    </div>
    <div>
      <div
        class="divide-y divide-gray-200 dark:divide-gray-700"
        v-if="item.product.has_variants == 1 && item.variations && item.variations.length"
      >
        <div v-for="(v, vi) in item.variations" :key="v.id" class="grid grid-cols-6 gap-6 p-6">
          <template v-if="v.code">
            <div class="text-mute col-span-full font-bold">
              {{ $t('Code') }}: <span class="text-focus">{{ v.code }}</span> ({{ $meta(v.meta) }})
            </div>
            <div class="col-span-6 sm:col-span-2">
              <Input keyboard type="number" :id="'item-quantity-v' + vi" :label="$t('Quantity')" v-model="item.variations[vi].quantity" />
            </div>
            <div v-if="item.product.unit?.subunits?.length" class="col-span-6 sm:col-span-4">
              <AutoComplete
                keyboard
                :json="true"
                value-key="id"
                id="item-unit"
                label-key="name"
                :searchable="false"
                :label="$t('Unit')"
                v-model="item.variations[vi].unit_id"
                :suggestions="[{ ...item.product.unit, subunits: null }, ...item.product.unit.subunits]"
                @change="
                  () => {
                    if (item.variations[vi].unit_id == item.product.unit.id) {
                      item.variations[vi].price = Number(item.product.price);
                    } else {
                      item.variations[vi].price = Number(
                        item.product.unit_prices?.find(p => p.unit_id == item.variations[vi].unit_id)?.price ||
                          convert_to_base_unit(item, item.variations[vi].unit_id, item.variations[vi].price || item.price)
                      );
                    }
                  }
                "
              />
            </div>
            <div class="col-span-6 sm:col-span-4">
              <Input
                keyboard
                type="number"
                :label="$t('Price')"
                :id="'item-price-v' + vi"
                v-model="item.variations[vi].price"
                :readonly="!item.product.can_edit_price"
              />
            </div>
            <div class="col-span-6 sm:col-span-2">
              <Input
                keyboard
                :label="$t('Discount')"
                :id="'item-discount-v' + vi"
                @keypress="discount_keypress"
                v-model="item.variations[vi].discount"
                @change="
                  e => {
                    let max_discount = usePage().props.settings?.max_discount || null;
                    if (item.variations[vi].discount.includes('%')) {
                      if (Number(item.variations[vi].discount.replace('%', '')) > Number(item.product.max_discount)) {
                        item.variations[vi].discount = Number(item.product.max_discount) + '%';
                        notify({
                          group: 'main',
                          type: 'error',
                          title: $t('You cannot apply discount more than {x}%', { x: Number(item.product.max_discount) }),
                        });
                      } else if (Number(item.variations[vi].discount.replace('%', '')) > Number(max_discount)) {
                        item.variations[vi].discount = Number(max_discount) + '%';
                        notify({
                          group: 'main',
                          type: 'error',
                          title: $t('You cannot apply discount more than {x}%', { x: Number(max_discount) }),
                        });
                      }
                    }
                  }
                "
              />
            </div>
            <div :class="item.product.unit?.subunits?.length ? 'col-span-full' : 'col-span-6 sm:col-span-4'">
              <AutoComplete
                keyboard
                :json="true"
                value-key="id"
                :multiple="true"
                label-key="name"
                :searchable="false"
                :suggestions="taxes"
                :label="$t('Taxes')"
                :id="'item-taxes-v' + vi"
                v-model="item.variations[vi].taxes"
                :disabled="!item.product.can_edit_taxes"
              />
            </div>
          </template>
          <template v-else>
            <div class="col-span-full">
              <SecondaryButton type="button" @click="() => emit('select:variant', item)">
                {{ $t('Select {x}', { x: $t('Variation') }) }}
              </SecondaryButton>
            </div>
          </template>
        </div>
      </div>
      <div v-else class="grid grid-cols-6 gap-6 p-6">
        <div class="col-span-6 sm:col-span-2">
          <Input keyboard type="number" id="item-quantity" :label="$t('Quantity')" v-model="item.quantity" />
        </div>
        <div v-if="item.product.unit?.subunits?.length" class="col-span-6 sm:col-span-4">
          <AutoComplete
            keyboard
            :json="true"
            value-key="id"
            id="item-unit"
            label-key="name"
            :searchable="false"
            :label="$t('Unit')"
            v-model="item.unit_id"
            :suggestions="[{ ...item.product.unit, subunits: null }, ...item.product.unit.subunits]"
            @change="
              () => {
                if (item.unit_id == item.product.unit.id) {
                  item.price = Number(item.product.price);
                } else {
                  item.price = Number(
                    item.product.unit_prices?.find(p => p.unit_id == item.unit_id)?.price ||
                      convert_to_base_unit(item, item.unit_id, item.price)
                  );
                }
              }
            "
          />
        </div>
        <div class="col-span-6 sm:col-span-4">
          <Input
            keyboard
            type="number"
            id="item-price"
            :label="$t('Price')"
            v-model="item.price"
            :readonly="!item.product.can_edit_price"
          />
        </div>
        <div class="col-span-6 sm:col-span-2">
          <Input
            keyboard
            id="item-discount"
            :label="$t('Discount')"
            v-model="item.discount"
            @keypress="discount_keypress"
            @change="
              e => {
                let max_discount = usePage().props.settings?.max_discount || null;
                if (item.discount.includes('%')) {
                  if (Number(item.discount.replace('%', '')) > Number(item.product.max_discount)) {
                    item.discount = Number(item.product.max_discount) + '%';
                    notify({
                      group: 'main',
                      type: 'error',
                      title: $t('You cannot apply discount more than {x}%', { x: Number(item.product.max_discount) }),
                    });
                  } else if (Number(item.discount.replace('%', '')) > Number(max_discount)) {
                    item.discount = Number(max_discount) + '%';
                    notify({
                      group: 'main',
                      type: 'error',
                      title: $t('You cannot apply discount more than {x}%', { x: Number(max_discount) }),
                    });
                  }
                }
              }
            "
          />
        </div>
        <div :class="item.product.unit?.subunits?.length ? 'col-span-full' : 'col-span-6 sm:col-span-4'">
          <AutoComplete
            keyboard
            :json="true"
            value-key="id"
            id="item-taxes"
            :multiple="true"
            label-key="name"
            :searchable="false"
            :suggestions="taxes"
            :label="$t('Taxes')"
            v-model="item.taxes"
            :disabled="!item.product.can_edit_taxes"
          />
        </div>
      </div>
      <div v-if="item.product.has_serials == 1" class="col-span-full mb-6 px-6">
        <AutoComplete
          keyboard
          :json="true"
          value-key="id"
          :multiple="true"
          label-key="number"
          :searchable="true"
          :label="$t('Serials')"
          v-model="item.serials"
          :max="Number(item.quantity) || 1"
          :suggestions="route('search.serials') + '?product_id=' + item.product_id"
          @change="serials => (item.available_serials = serials)"
        />
      </div>
      <div class="col-span-full mb-6 px-6">
        <Textarea keyboard id="item-comment" :label="$t('Comment')" v-model="item.comment" />
      </div>
      <div class="col-span-full flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
        <button type="button" @click="emit('remove', item)" class="focus rounded-md px-1 py-2.5 text-red-600 hover:text-red-500">
          {{ $t('Remove') }}
        </button>
        <button @click="emit('update', item)" class="btn-primary">{{ $t('Update') }}</button>
      </div>
    </div>
  </div>
</template>
