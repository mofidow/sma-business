<script setup>
import { NumberInput, NumberInputRound } from '@/Components/Common';

defineProps(['item']);
const emit = defineEmits(['edit', 'update', 'remove']);
</script>

<template>
  <template v-if="$page.props.settings?.pos_design == 'Modern'">
    <li class="">
      <div class="mb-4 rounded-2xl bg-white p-4 shadow-xl dark:bg-gray-950">
        <div class="flex min-w-0 grow items-start gap-x-3">
          <img
            alt=""
            :src="item.product.photo"
            v-if="item.product?.photo"
            class="me-2 h-10 w-10 flex-none rounded-full bg-gray-100 dark:bg-gray-800"
          />
          <div class="w-full">
            <button
              type="button"
              @click="emit('edit', item)"
              class="flex items-center text-sm leading-6 font-bold hover:underline hover:underline-offset-4"
            >
              {{ item.name }}
            </button>
            <div v-if="item.serials?.length" class="text-mute truncate text-xs">
              {{ item.serials.length == 1 ? $t('Serial') : $t('Serials') }}:
              {{
                item.available_serials
                  ?.filter(s => item.serials.includes(s.id))
                  .slice(0, item.quantity)
                  .map(s => s.number)
                  .join(', ')
              }}
            </div>
            <template v-if="item.variations && item.variations.length">
              <template :key="variation.id" v-for="variation in item.variations">
                <div class="mt-3">
                  <div v-if="Object.keys(variation.meta).length" class="flex items-start gap-x-3">
                    {{ $meta(variation.meta) }}
                  </div>
                  <div v-else class="text-xs text-red-500">
                    {{ $t('Select {x}', { x: $t('Variation') }) }}
                  </div>
                </div>
                <div class="flex flex-wrap items-center gap-x-2 text-sm leading-6">
                  <span>{{ $t('Price') }}: {{ $currency(Number(item.net_price)) }} </span>
                  <span>{{ $t('Tax') }}: {{ $currency(Number(item.tax_amount)) }} </span>
                </div>
                <div class="mt-2 flex justify-end">
                  <NumberInputRound
                    :min="0"
                    :del-on="0"
                    v-model="variation.quantity"
                    @remove="emit('remove', variation)"
                    @change="
                      () => {
                        item.quantity = item.variations.reduce((a, v) => Number(v.quantity) + a, 0);
                        emit('update', item);
                      }
                    "
                    :max="variation.selected_store && variation.selected_store[0] ? variation.selected_store[0].pivot?.quantity : null"
                  />
                  <!-- <NumberInputRound
                    :min="0"
                    :del-on="0"
                    v-model="item.quantity"
                    @remove="emit('remove', item)"
                    @change="emit('update', item)"
                    :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
                  /> -->
                </div>
              </template>
            </template>
            <template v-else>
              <div class="flex flex-wrap items-center gap-x-2 text-sm leading-6">
                <span>{{ $t('Price') }}: {{ $currency(Number(item.net_price)) }} </span>
                <span>{{ $t('Tax') }}: {{ $currency(Number(item.tax_amount)) }} </span>
              </div>
              <div class="mt-2 flex justify-end">
                <NumberInputRound
                  :min="0"
                  :del-on="0"
                  v-model="item.quantity"
                  @remove="emit('remove', item)"
                  @change="emit('update', item)"
                  :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
                />
              </div>
            </template>
          </div>
        </div>
      </div>
    </li>
  </template>
  <template v-else>
    <template v-if="item.product?.has_variants == 1 && item.variations && item.variations.length">
      <li class="mb-1 flex flex-col justify-between gap-x-2 pt-2">
        <div class="min-w-0 grow">
          <div class="flex items-start gap-x-3">
            <button
              type="button"
              @click="emit('edit', item)"
              class="flex items-center text-sm leading-6 font-bold hover:underline hover:underline-offset-4"
            >
              <img alt="" :src="item.product.photo" v-if="item.product?.photo" class="me-2 h-6 w-6 flex-none rounded-full" />
              <div class="leading-tight ltr:text-left rtl:text-right">
                {{ item.name }}
              </div>
            </button>
            <div v-if="item.serials?.length" class="text-mute truncate text-xs">
              {{ item.serials.length == 1 ? $t('Serial') : $t('Serials') }}:
              {{
                item.available_serials
                  ?.filter(s => item.serials.includes(s.id))
                  .slice(0, item.quantity)
                  .map(s => s.number)
                  .join(', ')
              }}
            </div>
          </div>
        </div>
        <div :key="variation.id" v-for="variation in item.variations" class="-mt-1 flex items-end justify-between gap-x-2 border-none py-2">
          <span v-if="item.product?.photo" class="block w-6"></span>
          <div class="min-w-0 grow">
            <div v-if="Object.keys(variation.meta).length" class="flex items-start gap-x-3">
              {{ $meta(variation.meta) }}
            </div>
            <div v-else class="text-xs text-red-500">
              {{ $t('Select {x}', { x: $t('Variation') }) }}
            </div>

            <div class="text-sm leading-6 print:hidden">
              <span>
                {{ $t('Price') }}:
                {{ $currency(Number(variation.net_price)) }}
              </span>
              <span class="ms-1">
                {{ $t('Tax') }}:
                {{ $currency(Number(variation.tax_amount)) }}
              </span>
            </div>
            <!-- For Print only -->
            <div class="hidden text-end text-sm print:flex">
              <div class="w-[80px]">
                {{ $currency(item.price) }}
              </div>
              <div class="w-[80px]">
                {{ item.quantity }}
              </div>
              <div class="w-[120px]">
                {{ $currency(item.total) }}
              </div>
            </div>
          </div>
          <div class="mb-1 flex flex-none items-center gap-x-4 print:hidden">
            <NumberInput
              :min="0"
              :del-on="0"
              v-model="variation.quantity"
              @remove="emit('remove', variation)"
              @change="
                () => {
                  item.quantity = item.variations.reduce((a, v) => Number(v.quantity) + a, 0);
                  emit('update', item);
                }
              "
              :max="variation.selected_store && variation.selected_store[0] ? variation.selected_store[0].pivot?.quantity : null"
            />
          </div>
          <!-- For Print only -->
          <div class="hidden text-end text-sm print:flex">
            <div class="w-[80px]">
              {{ $currency(variation.price) }}
            </div>
            <div class="w-[80px]">
              {{ variation.quantity }}
            </div>
            <div class="w-[120px]">
              {{ $currency(variation.total) }}
            </div>
          </div>
        </div>
      </li>
    </template>
    <li v-else class="flex items-end justify-between gap-x-2 py-2">
      <div class="min-w-0 grow">
        <div class="flex items-start gap-x-3">
          <div v-if="item.type == 'Gift Card'" class="flex items-center">
            {{ item.name }} <span class="ms-1 text-xs font-bold">({{ item.code }})</span>
          </div>
          <button
            v-else
            type="button"
            @click="emit('edit', item)"
            class="flex items-center text-sm leading-6 font-bold hover:underline hover:underline-offset-4"
          >
            <img alt="" :src="item.product.photo" v-if="item.product?.photo" class="me-2 h-6 w-6 flex-none rounded-full" />
            <div class="leading-tight ltr:text-left rtl:text-right">
              {{ item.name }}
              <div v-if="item.product?.has_variants == 1" class="text-xs text-red-500">{{ $t('Add again to select variation') }}</div>
              <div v-if="item.serials?.length" class="text-mute truncate text-xs">
                {{ item.serials.length == 1 ? $t('Serial') : $t('Serials') }}:
                {{
                  item.available_serials
                    ?.filter(s => item.serials.includes(s.id))
                    .slice(0, item.quantity)
                    .map(s => s.number)
                    .join(', ')
                }}
              </div>
            </div>
          </button>
          <p
            v-if="item == 'Standard' && item.selected_store && item.selected_store[0]"
            class="mt-0.5 rounded-md bg-green-50 px-1.5 py-0.5 text-xs font-medium whitespace-nowrap text-green-700 ring-1 ring-green-600/20 ring-inset print:hidden"
          >
            {{ $decimal_qty(item.selected_store[0].pivot.quantity) }}
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-x-2 text-xs leading-6 print:hidden">
          <span v-if="item.product?.photo" class="block w-6"></span>
          <span>{{ $t('Price') }}: {{ $currency(Number(item.net_price)) }} </span>
          <span>{{ $t('Tax') }}: {{ $currency(Number(item.tax_amount)) }} </span>
        </div>
      </div>
      <div class="mb-1 flex flex-none items-center gap-x-4 print:hidden">
        <NumberInput
          :min="0"
          :del-on="0"
          v-model="item.quantity"
          @remove="emit('remove', item)"
          @change="emit('update', item)"
          :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
        />
      </div>
      <!-- For Print only -->
      <div class="hidden text-end text-sm print:flex">
        <div class="w-[80px]">
          {{ $currency(item.price) }}
        </div>
        <div class="w-[80px]">
          {{ item.quantity }}
        </div>
        <div class="w-[120px]">
          {{ $currency(item.total) }}
        </div>
      </div>
    </li>
  </template>
</template>
