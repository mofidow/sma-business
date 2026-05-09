<script setup>
import { route } from 'ziggy-js';
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { $extras, searchItems } from '@/Core';

import { FormHelper } from '@/Core/FormHelper';
import { VariationSelection } from '@/Core/VariationSelection';
import { ActionMessage, FormSection, SecondaryButton } from '@/Components/Jet';
import { AutoComplete, BarcodeScanner, CustomFields, Input, LoadingButton, ProductSearch, Textarea } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
const props = defineProps(['current', 'custom_fields', 'stores']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',

  to_store_id: props.current?.to_store_id,
  reference: props.current?.reference,
  details: props.current?.details,
  items:
    props.current && props.current.items
      ? props.current.items.map(i => ({
          ...i,
          id: i.id,
          name: i.product.name,
          product_id: i.product_id,
          quantity: Number(i.quantity),
          old_quantity: Number(i.quantity),
          variations: i.variations.map(v => ({ ...v, quantity: Number(v.pivot.quantity), old_quantity: Number(v.pivot.quantity) })),
        }))
      : [],
  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const { removeItem, resetForm, saveForm } = FormHelper(form);
const { currentItem, deleteVariation, emptyVariation, variantModal, SelectVariant } = VariationSelection();
const scannerOpen = ref(false);

async function addItem(item) {
  if (form.items.find(i => i.product_id == item.id)) {
    form.items = form.items.map(i => {
      if (i.product_id == item.id) {
        if (i.product.has_variants == 1) {
          i.variations = [...i.variations, emptyVariation(i.product)];
          currentItem.value = i;
          variantModal.value = true;
        } else {
          i.quantity++;
        }
      }
      return i;
    });
  } else {
    const product = await axios.get(route('products.show', item.id)).then(r => r.data);
    currentItem.value = { ...item, id: null, quantity: 1, product_id: item.id, product };
    if (product.has_variants == 1) {
      currentItem.value.variations = [emptyVariation(product)];
      variantModal.value = true;
    }
    form.items = [...form.items, { ...currentItem.value }];
  }

  //   if (currentItem.value.product.has_variants == 1) {
  //     variantModal.value = true;
  //   }

  saveForm();
}

async function handleBarcodeScan(code) {
  scannerOpen.value = false;
  const results = await searchItems(code, 'transfer', true);
  if (results && results.length) {
    await addItem(results[0]);
  }
}

function handleSubmit(e, listing = false) {
  form.errors = {};
  form
    .transform(data => {
      const form = { ...data };
      form.items = form.items.map(i => ({
        id: i.id,
        quantity: i.quantity,
        product_id: i.product_id,
        old_quantity: i.old_quantity,
        variations:
          i.variations && i.variations.length
            ? i.variations.map(v => ({ id: v.id, quantity: Number(v.quantity), old_quantity: v.old_quantity }))
            : null,
      }));

      return form;
    })
    .post(props.current?.id ? route('transfers.update', props.current.id) : route('transfers.store'), {
      onSuccess: () => {
        resetForm();
        if (listing) {
          router.get(route('transfers.index'));
        }
      },
    });
}
</script>

<template>
  <Head>
    <title>{{ current?.id ? $t('Edit {x}', { x: $t('Transfer') }) : $t('Add {x}', { x: $t('Transfer') }) }}</title>
  </Head>

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="handleSubmit">
      <template #title>
        {{ current?.id ? $t('Edit {x}', { x: $t('Transfer') }) : $t('Add {x}', { x: $t('Transfer') }) }}
      </template>

      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          <div>
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('transfer'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </div>
          <div class="me-3 mt-6 sm:mt-0 lg:mt-6">
            <Link class="link" :href="route('transfers.index')">{{ $t('List {x}', { x: $t('Transfers') }) }}</Link>
          </div>
        </div>
      </template>

      <template #form>
        <!-- To -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            :json="true"
            value-key="id"
            label-key="name"
            id="to_store_id"
            :label="$t('To')"
            @change="saveForm"
            v-model="form.to_store_id"
            :error="form.errors.to_store_id"
            :suggestions="stores.filter(s => s.id != $page.props.selected_store)"
          />
        </div>
        <!-- Reference -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="reference" @change="saveForm" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
        </div>

        <div class="col-span-full">
          <div class="flex items-start gap-2">
            <div class="flex-1">
              <ProductSearch model="transfer" @add-item="addItem" />
            </div>
            <button
              type="button"
              @click="scannerOpen = true"
              :title="$t('Scan Barcode')"
              class="mb-px inline-flex items-center rounded-md border border-gray-300 bg-gray-100 p-2.5 text-gray-600 hover:bg-gray-200 hover:text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-gray-200"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M7 7h2v2H7zm0 4h2v2H7zm4-4h2v2h-2zm0 4h2v2h-2zm4-4h2v2h-2zm0 4h2v2h-2z"
                />
              </svg>
            </button>
          </div>

          <!-- <div class="relative">
            <Input label="" v-model="search" id="product-search" :placeholder="$t('Scan barcode or type to search')" />
            <div
              v-if="search && result && result.length"
              class="absolute top-full end-0 start-0 z-10 mt-2 max-h-96 overflow-y-auto rounded-md bg-white py-1 ring-1 ring-gray-200 dark:bg-gray-700 dark:ring-gray-700"
            >
              <button
                :key="p.id"
                type="button"
                v-for="p of result"
                @click="addItem(p)"
                :disabled="$page.props.settings.overselling != 1 && p.store_stock?.balance <= 0"
                class="flex w-full items-center justify-between gap-x-8 gap-y-2 px-4 py-1.5 text-start hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-gray-900"
              >
                {{ p.name }}
                <span class="text-mute text-sm">{{
                  p.store_stock?.balance ? $t('In Stock') + ': ' + $decimal_qty(p.store_stock.balance) : ''
                }}</span>
              </button>
            </div>
          </div> -->

          <div v-if="form.items && form.items.length" class="border-b border-gray-200 dark:border-gray-700">
            <h4 class="mt-6 border-b border-gray-200 pb-1 text-lg font-bold dark:border-gray-700">{{ $t('Transfer Items') }}</h4>
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
              <template v-for="(item, index) of form.items" :key="item.code">
                <tbody v-if="item.product.has_variants == 1 && item.variations && item.variations.length">
                  <!-- Item product has variants -->
                  <tr>
                    <td class="w-8 px-2 pt-4 pb-2">{{ index + 1 }}.</td>
                    <td class="p-2 font-bold">{{ item.name }}</td>
                    <!-- <td class="p-2"></td> -->
                    <td class="w-36 p-2">
                      <!-- <Input label="" type="number" :readonly="true" v-model="item.quantity" :placeholder="$t('Quantity')" /> -->
                    </td>
                    <td class="w-6 p-2 text-end">
                      <!-- <button type="button" @click="removeItem(item)" class="py-2.5 px-1 rounded-md focus text-red-600 hover:text-red-500">
                        <Icon name="trash-o" size="size-5" />
                      </button> -->
                    </td>
                  </tr>

                  <template v-for="(variation, vi) in item.variations" :key="variation.id">
                    <tr
                      :class="
                        form.errors &&
                        (form.errors['items.' + index + '.variations.' + vi + '.id'] ||
                          form.errors['items.' + index + '.variations.' + vi + '.quantity'])
                          ? 'bg-red-100 dark:bg-red-900'
                          : ''
                      "
                    >
                      <!-- Item product has variants -->
                      <td class="w-8 p-2"></td>
                      <td class="p-2">
                        {{ $meta(variation.meta) }}
                      </td>
                      <!-- <td class="p-2">
                      <AutoComplete
                        label=""
                        :json="true"
                        valueKey="id"
                        labelKey="code"
                        @change="saveForm"
                        v-model="variation.id"
                        :placeholder="$t('Variation')"
                        :suggestions="item.product.variations"
                      />
                    </td> -->
                      <td class="w-36 p-2">
                        <Input
                          :min="0"
                          label=""
                          :del-on="0"
                          type="number"
                          @change="
                            () => {
                              item.quantity = item.variations.reduce((a, v) => Number(v.quantity) + a, 0);
                              saveForm();
                            }
                          "
                          v-model="variation.quantity"
                          :placeholder="$t('Quantity')"
                          :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
                        />
                      </td>
                      <td class="w-6 p-2 text-end">
                        <button
                          type="button"
                          @click="deleteVariation(variation, item)"
                          class="focus rounded-md px-1 py-2.5 text-red-600 hover:text-red-500"
                        >
                          <Icon name="trash-o" size="size-5" />
                        </button>
                      </td>
                    </tr>
                    <template
                      v-if="
                        form.errors &&
                        (form.errors['items.' + index + '.variations.' + vi + '.id'] ||
                          form.errors['items.' + index + '.variations.' + vi + '.quantity'])
                      "
                    >
                      <tr class="bg-red-100 dark:bg-red-900">
                        <td colspan="100%">
                          <div class="px-4 py-1 text-sm text-red-500">
                            <div v-if="form.errors['items.' + index + '.variations.' + vi + '.id']">
                              {{
                                form.errors['items.' + index + '.variations.' + vi + '.id']
                                  .toString()

                                  .replace(
                                    'items.' + index + '.variations.' + vi + '.id',
                                    $t('item') + ' #' + (index + 1) + ' ' + $t('variation') + ' #' + (vi + 1)
                                  )
                              }}
                            </div>
                            <div v-if="form.errors['items.' + index + '.variations.' + vi + '.quantity']">
                              {{
                                form.errors['items.' + index + '.variations.' + vi + '.quantity']
                                  .toString()

                                  .replace(
                                    'items.' + index + '.variations.' + vi + '.quantity',
                                    $t('item') + ' #' + (index + 1) + ' ' + $t('variation') + ' #' + (vi + 1) + ' ' + $t('quantity')
                                  )
                                  .split('when')[0]
                              }}
                            </div>
                          </div>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
                <tbody
                  v-else
                  :class="
                    form.errors && (form.errors['items.' + index + '.quantity'] || form.errors['items.' + index + '.variations'])
                      ? 'bg-red-100 dark:bg-red-800'
                      : ''
                  "
                >
                  <tr>
                    <!-- Item product doesn't have variants -->
                    <td class="w-8 p-2">{{ index + 1 }}.</td>
                    <td class="p-2">{{ item.name }}</td>
                    <!-- <td class="p-2"></td> -->
                    <td class="w-36 p-2">
                      <Input
                        :min="0"
                        label=""
                        :del-on="0"
                        type="number"
                        @change="saveForm"
                        v-model="item.quantity"
                        :placeholder="$t('Quantity')"
                        :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
                      />
                    </td>
                    <td class="w-6 p-2 text-end">
                      <button type="button" @click="removeItem(item)" class="focus rounded-md px-1 py-2.5 text-red-600 hover:text-red-500">
                        <Icon name="trash-o" size="size-5" />
                      </button>
                    </td>
                  </tr>
                  <template
                    v-if="form.errors && (form.errors['items.' + index + '.quantity'] || form.errors['items.' + index + '.variations'])"
                  >
                    <tr>
                      <td colspan="100%">
                        <div class="px-4 py-1 text-sm text-red-500">
                          <div v-if="form.errors['items.' + index + '.quantity']">
                            {{
                              form.errors['items.' + index + '.quantity']
                                .toString()
                                .replace('items.' + index + '.quantity', $t('item') + ' #' + (index + 1) + ' ' + $t('quantity'))
                            }}
                          </div>
                          <div v-if="form.errors['items.' + index + '.variations']">
                            {{
                              form.errors['items.' + index + '.variations']
                                .toString()
                                .replace('items.' + index + '.variations', $t('item') + ' #' + (index + 1) + ' ' + $t('variations'))
                            }}
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </template>
              <tfoot>
                <tr>
                  <th colspan="2" class="p-2 text-end text-lg font-bold">{{ $t('Total quantity') }}</th>
                  <th class="p-2 text-end text-lg font-bold">{{ $decimal(form.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
          <label
            v-else
            for="product-search"
            class="mt-4 block w-full rounded-md bg-gray-100 px-4 py-2 text-primary-600 dark:bg-gray-800 dark:text-primary-400"
          >
            {{ $t('Please add at least one product.') }}
          </label>
        </div>
        <div class="col-span-full flex flex-col gap-6">
          <CustomFields :errors="form.errors" :custom_fields="custom_fields" :extra_attributes="form.extra_attributes" @update="saveForm" />
          <Textarea @change="saveForm" :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
        </div>
      </template>

      <template #actions>
        <div class="flex w-full items-center justify-between">
          <SecondaryButton type="button" @click="resetForm" class="me-3">{{ $t('Reset') }}</SecondaryButton>

          <div class="flex items-center justify-end">
            <button
              type="button"
              v-if="current"
              class="btn-secondary"
              :loading="form.processing"
              :disabled="form.processing"
              @click="e => handleSubmit(e, true)"
            >
              {{ $t('Save & go to listing') }}
            </button>

            <ActionMessage :on="form.recentlySuccessful" class="ms-3 me-3"> {{ $t('Saved.') }} </ActionMessage>

            <LoadingButton type="button" @click="handleSubmit" :class="{ 'opacity-25': form.processing }" :loading="form.processing">
              {{ $t('Save') }}
            </LoadingButton>
          </div>
        </div>
      </template>
    </FormSection>

    <SelectVariant
      v-if="variantModal"
      :item="currentItem"
      :show="variantModal"
      @close="
        () => {
          currentItem.variations = currentItem.variations.filter(v => v.id);
          form.items = form.items.map(i => (i.product_id == currentItem.product_id ? currentItem : i));
          variantModal = false;
        }
      "
      @update="
        item => {
          item.variations = item.variations.filter(v => v.id);
          form.items = form.items.map(i => (i.product_id == item.product_id ? item : i));
          saveForm();
          variantModal = false;
        }
      "
    />
  </div>

  <BarcodeScanner :show="scannerOpen" @scanned="handleBarcodeScan" @close="scannerOpen = false" />
</template>
