<script setup>
import { onMounted, ref } from 'vue';
import dayjs from 'dayjs';
import { route } from 'ziggy-js';
import { notify } from 'notiwind';
import { useI18n } from 'vue-i18n';
import debounce from 'lodash/debounce';
import { router, useForm, usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { $extras, calculate_item, discount_keypress, convert_to_base_unit, searchItems } from '@/Core';

import { axios, FormHelper, VariationSelection } from '@/Core';
// import { FormHelper } from '@/Core/FormHelper';
// import { VariationSelection } from '@/Core/VariationSelection';
import { ActionMessage, FormSection, Modal, SecondaryButton } from '@/Components/Jet';
import {
  AutoComplete,
  Attachments,
  BarcodeScanner,
  CustomFields,
  DateInput,
  FileInput,
  Input,
  LoadingButton,
  ProductSearch,
  Textarea,
} from '@/Components/Common';

const page = usePage();
const { t } = useI18n();
const scannerOpen = ref(false);
defineOptions({ layout: AdminLayout });
const props = defineProps(['current', 'custom_fields', 'stores', 'taxes', 'sale_id', 'purchase_id']);

const form = useForm({
  _method: props.current?.id ? 'put' : 'post',
  calculate_on: props.current?.type == 'Purchase' ? 'cost' : 'price',

  type: props.current?.type,
  type_ref: props.current?.type_ref,
  sale_id: props.current?.sale_id,
  purchase_id: props.current?.purchase_id,
  date: dayjs(props.current?.date).format('YYYY-MM-DD'),
  reference: props.current?.reference,
  customer_id: props.current?.customer_id,
  supplier_id: props.current?.supplier_id,
  details: props.current?.details,
  attachments: null,
  return_payment_amount: props.current?.return_payment_amount || null,
  return_payment_method: props.current?.return_payment_method || 'Cash',
  items:
    props.current && props.current.items && props.current.items.length
      ? props.current.items.map(i => {
          const item = calculate_item(
            {
              ...i,
              id: i.id,
              discount: i.discount,
              name: i.product.name,
              cost: Number(i.cost),
              price: Number(i.price),
              product_id: i.product_id,
              quantity: Number(i.quantity),
              old_quantity: Number(i.quantity),
              sale_item_id: i.sale_item_id,
              purchase_item_id: i.purchase_item_id,
              taxes: i.taxes?.map(t => t.id) || [],
              tax_included: i.product.tax_included == 1,
              variations: i.variations.map(v => ({
                ...v,
                discount: v.pivot.discount,
                cost: Number(v.pivot.cost),
                price: Number(v.pivot.price),
                quantity: Number(v.pivot.quantity),
                old_quantity: Number(v.pivot.quantity),
                tax_included: i.product.tax_included == 1,
                taxes: v.pivot.taxes?.map(t => Number(t)) || [],
              })),
            },
            props.current?.type == 'Purchase' ? 'cost' : 'price'
          );

          return item;
        })
      : [],
  extra_attributes: props.current ? $extras(props.custom_fields, props.current.extra_attributes) : $extras(props.custom_fields),
});

const { currentItem, emptyVariation, variantModal, SelectVariant } = VariationSelection();
const { openItemModal, selectItem, selectedItem, removeItem, resetForm, saveForm, updateItem } = FormHelper(form);

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

    currentItem.value = calculate_item(
      {
        ...item,
        id: null,
        quantity: 1,
        cost: Number(product.cost),
        price: Number(product.price),
        taxes: product.taxes.map(t => t.id),
        tax_included: product.tax_included == 1,
        product_id: product.id,
        unit_id: product.unit_id,
        product,
      },
      form.calculate_on
    );

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

const getSale = debounce(() => {
  if (form.type_ref) {
    axios.post(route('search.sale'), { ref: form.type_ref }).then(res => {
      if (res.data?.id) {
        const sale = res.data;
        form.purchase_id = null;
        form.supplier_id = null;
        form.sale_id = sale.id;
        form.customer_id = sale.customer_id;
        form.items = sale.items.map(i => {
          const item = calculate_item(
            {
              ...i,
              id: null,
              sale_item_id: i.id,
              discount: i.discount,
              name: i.product.name,
              cost: Number(i.cost),
              price: Number(i.price),
              product_id: i.product_id,
              quantity: Number(i.quantity),
              max_quantity: Number(i.quantity),
              taxes: i.taxes?.map(t => t.id) || [],
              tax_included: i.product.tax_included == 1,
              variations: i.variations.map(v => ({
                ...v,
                discount: v.pivot.discount,
                cost: Number(v.pivot.cost),
                price: Number(v.pivot.price),
                quantity: Number(v.pivot.quantity),
                max_quantity: Number(v.pivot.quantity),
                tax_included: i.product.tax_included == 1,
                taxes: v.pivot.taxes?.map(t => Number(t)) || [],
              })),
            },
            'price'
          );

          return item;
        });
        // document.getElementById('product-search')?.focus();
        notify({ group: 'main', type: 'success', title: t('Sale items are loaded, please choose only return ones.') });
        saveForm();
      } else {
        notify({ group: 'main', type: 'error', title: t('Sale not found with No./ref {x}.', { x: form.type_ref }) });
      }
    });
  }
}, 250);

const getPurchase = debounce(() => {
  if (form.type_ref) {
    axios.post(route('search.purchase'), { ref: form.type_ref }).then(res => {
      if (res.data?.id) {
        const purchase = res.data;
        form.sale_id = null;
        form.customer_id = null;
        form.purchase_id = purchase.id;
        form.supplier_id = purchase.supplier_id;
        form.items = purchase.items.map(i => {
          const item = calculate_item(
            {
              ...i,
              id: null,
              purchase_item_id: i.id,
              discount: i.discount,
              name: i.product.name,
              cost: Number(i.cost),
              // price: Number(i.price),
              product_id: i.product_id,
              quantity: Number(i.quantity),
              max_quantity: Number(i.quantity),
              taxes: i.taxes?.map(t => t.id) || [],
              tax_included: i.product.tax_included == 1,
              variations: i.variations.map(v => ({
                ...v,
                discount: v.pivot.discount,
                cost: Number(v.pivot.cost),
                // price: Number(v.pivot.price),
                quantity: Number(v.pivot.quantity),
                max_quantity: Number(v.pivot.quantity),
                tax_included: i.product.tax_included == 1,
                taxes: v.pivot.taxes?.map(t => Number(t)) || [],
              })),
            },
            'cost'
          );

          return item;
        });
        // document.getElementById('product-search')?.focus();
        notify({ group: 'main', type: 'success', title: t('Purchase items are loaded, please choose only return ones.') });
        saveForm();
      } else {
        notify({ group: 'main', type: 'error', title: t('Purchase not found with No./ref {x}.', { x: form.type_ref }) });
      }
    });
  }
}, 250);

onMounted(() => {
  if (props.sale_id) {
    form.type = 'Sale';
    form.calculate_on = 'price';
    form.type_ref = props.sale_id;
    getSale();
  } else if (props.purchase_id) {
    form.type = 'Purchase';
    form.calculate_on = 'cost';
    form.type_ref = props.purchase_id;
    getPurchase();
  }
});

async function handleBarcodeScan(code) {
  scannerOpen.value = false;
  const results = await searchItems(code, 'return_order', true);
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
        cost: i.cost,
        price: i.price,
        taxes: i.taxes,
        quantity: i.quantity,
        unit_id: i.unit_id,
        discount: i.discount,
        product_id: i.product_id,
        sale_item_id: i.sale_item_id,
        old_quantity: i.old_quantity,
        purchase_item_id: i.purchase_item_id,
        variations:
          i.variations && i.variations.length
            ? i.variations.map(v => ({
                id: v.id,
                cost: v.cost,
                price: v.price,
                taxes: v.taxes,
                unit_id: v.unit_id,
                discount: v.discount,
                quantity: v.quantity,
                old_quantity: v.old_quantity,
              }))
            : null,
      }));

      try {
        form.date = form.date ? dayjs(form.date).format('YYYY-MM-DD') : null;
      } catch {}
      if (!form.return_payment_amount || Number(form.return_payment_amount) <= 0) {
        form.return_payment_amount = null;
        form.return_payment_method = null;
      }
      return form;
    })
    .post(props.current?.id ? route('return_orders.update', props.current.id) : route('return_orders.store'), {
      forceFormData: true,
      onSuccess: () => {
        resetForm();
        if (listing) {
          router.get(route('return_orders.index'));
        }
      },
    });
}
</script>

<template>
  <Head>
    <title>{{ current?.id ? $t('Edit {x}', { x: $t('Return Order') }) : $t('Add {x}', { x: $t('Return Order') }) }}</title>
  </Head>

  <div class="px-0 pt-6 pb-0 sm:px-6 sm:py-8 lg:px-8">
    <FormSection @submitted="handleSubmit">
      <template #title>
        {{ current?.id ? $t('Edit {x}', { x: $t('Return Order') }) : $t('Add {x}', { x: $t('Return Order') }) }}
      </template>

      <template #description>
        <div class="block w-full sm:flex sm:items-start sm:justify-between lg:block">
          <div>
            {{
              $t('Please fill the form below to {action} {record}.', {
                record: $t('return_order'),
                action: current?.id ? $t('edit') : $t('add'),
              })
            }}
          </div>
          <div class="me-3 mt-6 sm:mt-0 lg:mt-6">
            <Link class="link" :href="route('return_orders.index')">{{ $t('List {x}', { x: $t('Return Orders') }) }}</Link>
          </div>
        </div>
      </template>

      <template #form>
        <!-- Type -->
        <div class="col-span-6 sm:col-span-3">
          <AutoComplete
            id="type"
            :json="true"
            :label="$t('Type')"
            v-model="form.type"
            @change="
              () => {
                form.calculate_on = form.type == 'Purchase' ? 'cost' : 'price';
                saveForm();
              }
            "
            :error="form.errors.type"
            :suggestions="[
              { value: 'Sale', label: $t('Return Order for Sale') },
              { value: 'Purchase', label: $t('Return Order for Purchase') },
            ]"
          />
        </div>
        <div class="col-span-6 sm:col-span-3"></div>

        <!-- Date -->
        <div class="col-span-6 sm:col-span-3">
          <DateInput type="date" id="date" @change="saveForm" :label="$t('Date')" v-model="form.date" :error="form.errors.date" />
        </div>
        <!-- Reference -->
        <div class="col-span-6 sm:col-span-3">
          <Input id="reference" @change="saveForm" :label="$t('Reference')" v-model="form.reference" :error="form.errors.reference" />
        </div>

        <template v-if="form.type == 'Sale'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              @change="getSale"
              v-model="form.type_ref"
              :label="$t('Sale No/Ref')"
              @keyup.prevent.enter="getSale"
              :error="$page.props.errors.type_ref"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              valueKey="id"
              id="customer_id"
              labelKey="company"
              @change="saveForm"
              :searchable="true"
              :label="$t('Customer')"
              v-model="form.customer_id"
              :suggestions="route('search.customers')"
              :error="$page.props.errors.customer_id"
            />
          </div>
        </template>

        <template v-if="form.type == 'Purchase'">
          <div class="col-span-6 sm:col-span-3">
            <Input
              @change="getPurchase"
              v-model="form.type_ref"
              :label="$t('Purchase No/Ref')"
              @keyup.prevent.enter="getPurchase"
              :error="$page.props.errors.type_ref"
            />
          </div>
          <!-- Supplier -->
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              valueKey="id"
              id="supplier_id"
              labelKey="company"
              @change="saveForm"
              :searchable="true"
              :label="$t('Supplier')"
              v-model="form.supplier_id"
              :suggestions="route('search.suppliers')"
              :error="$page.props.errors.supplier_id"
            />
          </div>
        </template>

        <div class="col-span-6 sm:col-span-3">
          <Input type="number" v-model="form.surcharge" :error="$page.props.errors.surcharge" :label="$t('Surcharge')" />
        </div>

        <!-- Attachments -->
        <div class="col-span-6 sm:col-span-3">
          <FileInput
            multiple
            id="attachments"
            :label="$t('Attachments')"
            v-model="form.attachments"
            :error="form.errors.attachments"
            :accept="$page.props.settings?.attachment_exts || '.jpg,.png,.pdf,.xlsx,.docx,.zip'"
          />
        </div>

        <div v-if="current && current.attachments && current.attachments.length" class="col-span-full">
          <Attachments :attachments="current.attachments || []" />
        </div>

        <div class="col-span-full">
          <div class="flex items-start gap-2">
            <div class="flex-1">
              <ProductSearch model="return_order" :type="form.type" @add-item="addItem" />
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

          <div v-if="form.items && form.items.length" class="overflow-x-auto">
            <h4 class="mt-6 pb-2 text-lg font-bold">{{ $t('Return Order Items') }}</h4>
            <table class="w-full min-w-[500px] divide-y divide-gray-200 dark:divide-gray-700">
              <thead>
                <tr>
                  <th class="text-mute">#</th>
                  <th class="text-mute text-start">{{ $t('Product') }}</th>
                  <th class="text-mute">{{ form.type == 'Purchase' ? $t('Cost') : $t('Price') }}</th>
                  <th class="text-mute">{{ $t('Quantity') }}</th>
                  <th class="text-mute" v-if="page.props.settings.show_tax == 1">{{ $t('Tax') }}</th>
                  <th class="text-mute">{{ $t('Total') }}</th>
                  <!-- <th><Icon name="trash-o" /></th> -->
                </tr>
              </thead>
              <template v-for="(item, index) of form.items" :key="item.code">
                <tbody v-if="item.product.has_variants == 1 && item.variations && item.variations.length">
                  <!-- Item product has variants -->
                  <tr>
                    <td class="w-8 px-2 pt-4 pb-2">{{ index + 1 }}.</td>
                    <td class="p-2 font-bold">
                      <button type="button" @click="selectItem(item)" class="link min-w-56 text-start">{{ item.name }}</button>
                    </td>
                    <td class="p-2"></td>
                    <td class="w-36 p-2">
                      <!-- <Input label="" type="number" :readonly="true" v-model="item.quantity" :placeholder="$t('Quantity')" /> -->
                    </td>
                    <th class="text-mute" v-if="page.props.settings.show_tax == 1"></th>
                    <th class="text-mute text-end">
                      <!-- {{ $number(item.variations.reduce((a, v) => Number(v.total) + a, 0)) }} -->
                    </th>
                    <!-- <td class="w-6 p-2 text-end">
                      <button type="button" @click="removeItem(item)" class="py-2.5 px-1 rounded-md focus text-red-600 hover:text-red-500">
                        <Icon name="trash-o" size="size-5" />
                      </button>
                    </td> -->
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
                      <td class="w-24 p-2 text-end">
                        {{
                          form.type == 'Purchase'
                            ? $number(page.props.settings.show_tax == 1 ? variation.net_cost : variation.unit_cost)
                            : $number(page.props.settings.show_tax == 1 ? variation.net_price : variation.unit_price)
                        }}
                      </td>
                      <td class="w-36 p-2">
                        <div class="relative">
                          <Input
                            :min="0"
                            label=""
                            :del-on="0"
                            type="number"
                            @change="
                              () => {
                                item.quantity = item.variations.reduce((a, v) => Number(v.quantity) + a, 0);
                                calculate_item(item, form.calculate_on);
                                saveForm();
                              }
                            "
                            v-model="variation.quantity"
                            :placeholder="$t('Quantity')"
                            :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
                          />
                          <template v-if="variation.unit_id">
                            <template v-if="item.product?.unit?.id == variation.unit_id">
                              <span class="text-mute absolute end-2 top-0 py-2">{{ item.product.unit.code }}</span>
                            </template>
                            <template v-else>
                              <span class="text-mute absolute end-2 top-0 py-2">{{
                                item.product.unit?.subunits?.find(s => s.id == variation.unit_id).code
                              }}</span>
                            </template>
                          </template>
                        </div>
                      </td>
                      <td v-if="page.props.settings.show_tax == 1" class="w-20 p-2 text-end">{{ $number(variation.tax_amount) }}</td>
                      <td class="w-24 p-2 text-end">{{ $number(variation.total) }}</td>
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
                    <td class="p-2">
                      <button type="button" @click="selectItem(item)" class="link min-w-56 text-start">{{ item.name }}</button>
                    </td>
                    <td class="w-24 p-2 text-end">
                      {{
                        form.type == 'Purchase'
                          ? $number(page.props.settings.show_tax == 1 ? item.net_cost : item.unit_cost)
                          : $number(page.props.settings.show_tax == 1 ? item.net_price : item.unit_price)
                      }}
                    </td>
                    <td class="w-36 p-2">
                      <div class="relative">
                        <Input
                          :min="0"
                          label=""
                          :del-on="0"
                          type="number"
                          v-model="item.quantity"
                          :placeholder="$t('Quantity')"
                          @change="
                            () => {
                              selectedItem = item;
                              updateItem();
                            }
                          "
                          :max="item.selected_store && item.selected_store[0] ? item.selected_store[0].pivot?.quantity : null"
                        />
                        <template v-if="item.unit_id">
                          <template v-if="item.product?.unit?.id == item.unit_id">
                            <span class="text-mute absolute end-2 top-0 py-2">{{ item.product.unit.code }}</span>
                          </template>
                          <template v-else>
                            <span class="text-mute absolute end-2 top-0 py-2">{{
                              item.product.unit?.subunits?.find(s => s.id == item.unit_id).code
                            }}</span>
                          </template>
                        </template>
                      </div>
                    </td>
                    <td v-if="page.props.settings.show_tax == 1" class="w-20 p-2 text-end">{{ $number(item.tax_amount) }}</td>
                    <td class="w-24 p-2 text-end">{{ $number(item.total) }}</td>
                    <!-- <td class="w-6 p-2 text-end">
                      <button type="button" @click="removeItem(item)" class="py-2.5 px-1 rounded-md focus text-red-600 hover:text-red-500">
                        <Icon name="trash-o" size="size-5" />
                      </button>
                    </td> -->
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
              <tfoot class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="page.props.settings.show_tax == 1">
                  <th colspan="3" class="p-2 text-end text-lg font-bold">{{ $t('Total') }}</th>
                  <th class="p-2 text-end text-lg font-bold">
                    <!-- {{ form.items.length }} -->
                    {{ $decimal_qty(form.items.reduce((a, i) => Number(i.quantity) + a, 0)) }}
                  </th>
                  <th class="p-2 text-end text-lg font-bold">
                    {{ $decimal(form.items.reduce((a, i) => Number(i.total_tax_amount) + a, 0)) }}
                  </th>
                  <th class="p-2 text-end text-lg font-bold">{{ $decimal(form.items.reduce((a, i) => Number(i.total) + a, 0)) }}</th>
                  <th></th>
                </tr>
                <template v-else>
                  <template
                    v-if="
                      $decimal(
                        form.items.reduce((a, i) => Number(i.total_tax_amount) + a, 0),
                        true
                      ) > 0
                    "
                  >
                    <tr>
                      <th colspan="4" class="p-2 text-end text-lg font-bold">{{ $t('Subtotal') }}</th>
                      <th class="p-2 text-end text-lg font-bold">
                        {{ $decimal(form.items.reduce((a, i) => Number(i.subtotal) + a, 0)) }}
                      </th>
                    </tr>
                    <tr>
                      <th colspan="4" class="p-2 text-end text-lg font-bold">{{ $t('Tax') }}</th>
                      <th class="p-2 text-end text-lg font-bold">
                        {{ $decimal(form.items.reduce((a, i) => Number(i.total_tax_amount) + a, 0)) }}
                      </th>
                    </tr>
                  </template>
                  <template v-else-if="page.props.settings.show_zero_taxes == 1">
                    <tr>
                      <th colspan="4" class="p-2 text-end text-lg font-bold">{{ $t('Tax') }}</th>
                      <th class="p-2 text-end text-lg font-bold">
                        {{ $decimal(form.items.reduce((a, i) => Number(i.total_tax_amount) + a, 0)) }}
                      </th>
                    </tr>
                  </template>
                  <tr>
                    <th colspan="4" class="p-2 text-end text-lg font-bold">{{ $t('Total') }}</th>
                    <th class="p-2 text-end text-lg font-bold">
                      {{ $decimal(form.items.reduce((a, i) => Number(i.total) + a, 0)) }}
                    </th>
                    <th></th>
                  </tr>
                </template>
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

          <!-- Return Payment -->
          <template v-if="(form.type == 'Sale' && form.sale_id) || (form.type == 'Purchase' && form.purchase_id)">
            <div class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
              <h4 class="mb-3 text-sm font-semibold">{{ $t('Return Payment') }}</h4>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Input
                  type="number"
                  :min="0"
                  :label="$t('Refund Amount')"
                  v-model="form.return_payment_amount"
                  :placeholder="$t('Leave empty if no refund')"
                  :error="form.errors.return_payment_amount"
                />
                <AutoComplete
                  :json="true"
                  :label="$t('Payment Method')"
                  v-model="form.return_payment_method"
                  :error="form.errors.return_payment_method"
                  :suggestions="
                    [
                      ...['Cash', 'Credit Card', 'Card Terminal', 'Others'],
                      ...($page.props.settings?.payment?.static_payment_methods || []),
                    ].map(m => ({ value: m, label: m }))
                  "
                />
              </div>
            </div>
          </template>

          <Textarea @change="saveForm" :label="$t('Details')" v-model="form.details" :error="$page.props.errors.details" />
        </div>
      </template>

      <template #actions>
        <div class="w-full">
          <div v-if="form.progress" class="-mx-6 -mt-6 pb-1.5">
            <progress
              max="100"
              :value="form.progress?.percentage"
              class="h-0.5 w-full appearance-none rounded-md bg-gray-200 dark:bg-gray-700"
            >
              {{ form.progress?.percentage }}%
            </progress>
          </div>

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
          form.items = form.items.map(i => (i.product_id == currentItem.product_id ? calculate_item(currentItem, form.calculate_on) : i));
          variantModal = false;
        }
      "
      @update="
        item => {
          item.variations = item.variations.filter(v => v.id);
          form.items = form.items.map(i => (i.product_id == item.product_id ? calculate_item(item, form.calculate_on) : i));
          saveForm();
          variantModal = false;
        }
      "
    />
  </div>
  <Modal :show="openItemModal" max-width="xl" @close="openItemModal = false" :overflow="true" :backdrop="false">
    <!-- <EditRow :item="item" @close="openItemModal = false" /> -->
    <div v-if="selectedItem">
      <div class="border-b border-gray-200 px-4 py-4 sm:px-6 dark:border-gray-700">
        <div class="sm:flex sm:items-baseline sm:justify-between">
          <div class="sm:w-0 sm:flex-1">
            <h1 class="text-focus text-base font-semibold">{{ $t('Edit {x}', { x: selectedItem.name }) }}</h1>
            <p class="text-mute mt-1 truncate text-sm">
              {{ $t('Please update the details below') }}
            </p>
          </div>
        </div>
      </div>
      <div>
        <div
          class="divide-y divide-gray-200 dark:divide-gray-700"
          v-if="selectedItem.product.has_variants == 1 && selectedItem.variations && selectedItem.variations.length"
        >
          <div v-for="(v, vi) in selectedItem.variations" :key="v.id" class="grid grid-cols-6 gap-6 p-6">
            <template v-if="v.code">
              <div class="text-mute col-span-full font-bold">
                {{ $t('Code') }}: <span class="text-focus">{{ v.code }}</span> ({{ $meta(v.meta) }})
              </div>
              <div class="col-span-6 sm:col-span-2">
                <Input type="number" :id="'item-quantity-v' + vi" :label="$t('Quantity')" v-model="selectedItem.variations[vi].quantity" />
              </div>
              <div v-if="selectedItem.product.unit?.subunits?.length" class="col-span-6 sm:col-span-4">
                <AutoComplete
                  :json="true"
                  value-key="id"
                  id="item-unit"
                  label-key="name"
                  :searchable="false"
                  :label="$t('Unit')"
                  v-model="selectedItem.variations[vi].unit_id"
                  :suggestions="[{ ...selectedItem.product.unit, subunits: null }, ...selectedItem.product.unit.subunits]"
                  @change="
                    () => {
                      if (selectedItem.variations[vi].unit_id == selectedItem.product.unit.id) {
                        selectedItem.variations[vi].price = Number(selectedItem.product.price);
                      } else {
                        selectedItem.variations[vi].price = Number(
                          selectedItem.product.unit_prices?.find(p => p.unit_id == selectedItem.variations[vi].unit_id)?.price ||
                            convert_to_base_unit(
                              selectedItem,
                              selectedItem.variations[vi].unit_id,
                              selectedItem.variations[vi].price || selectedItem.price
                            )
                        );
                      }
                    }
                  "
                />
              </div>
              <div class="col-span-6 sm:col-span-4">
                <Input
                  type="number"
                  :id="'item-cost-v' + vi"
                  v-model="selectedItem.variations[vi].cost"
                  :readonly="!selectedItem.product.can_edit_price"
                  :label="form.type == 'Purchase' ? $t('Cost') : $t('Price')"
                />
              </div>
              <div class="col-span-6 sm:col-span-2">
                <Input
                  :label="$t('Discount')"
                  :id="'item-discount-v' + vi"
                  @keypress="discount_keypress"
                  v-model="selectedItem.variations[vi].discount"
                  @change="
                    e => {
                      let max_discount = usePage().props.settings?.max_discount || null;
                      if (selectedItem.variations[vi].discount.includes('%')) {
                        if (Number(selectedItem.variations[vi].discount.replace('%', '')) > Number(selectedItem.product.max_discount)) {
                          selectedItem.variations[vi].discount = Number(selectedItem.product.max_discount) + '%';
                          notify({
                            group: 'main',
                            type: 'error',
                            title: $t('You cannot apply discount more than {x}%', { x: Number(selectedItem.product.max_discount) }),
                          });
                        } else if (Number(selectedItem.variations[vi].discount.replace('%', '')) > Number(max_discount)) {
                          selectedItem.variations[vi].discount = Number(max_discount) + '%';
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
              <div :class="selectedItem.product.unit?.subunits?.length ? 'col-span-full' : 'col-span-6 sm:col-span-4'">
                <AutoComplete
                  :json="true"
                  value-key="id"
                  :multiple="true"
                  label-key="name"
                  :searchable="false"
                  :suggestions="taxes"
                  :label="$t('Taxes')"
                  :id="'item-taxes-v' + vi"
                  v-model="selectedItem.variations[vi].taxes"
                  :disabled="!selectedItem.product.can_edit_taxes"
                />
              </div>
            </template>
            <template v-else>
              <div class="col-span-full">
                <SecondaryButton
                  type="button"
                  @click="
                    () => {
                      currentItem = selectedItem;
                      variantModal = true;
                    }
                  "
                >
                  {{ $t('Select {x}', { x: $t('Variation') }) }}
                </SecondaryButton>
              </div>
            </template>
          </div>
        </div>
        <div v-else class="grid grid-cols-6 gap-6 p-6">
          <div class="col-span-6 sm:col-span-2">
            <Input type="number" id="item-quantity" :label="$t('Quantity')" v-model="selectedItem.quantity" />
          </div>
          <div v-if="selectedItem.product.unit?.subunits?.length" class="col-span-6 sm:col-span-4">
            <AutoComplete
              :json="true"
              value-key="id"
              id="item-unit"
              label-key="name"
              :searchable="false"
              :label="$t('Unit')"
              v-model="selectedItem.unit_id"
              :suggestions="[{ ...selectedItem.product.unit, subunits: null }, ...selectedItem.product.unit.subunits]"
              @change="
                () => {
                  if (selectedItem.unit_id == selectedItem.product.unit.id) {
                    selectedItem.price = Number(selectedItem.product.price);
                  } else {
                    selectedItem.price = Number(
                      selectedItem.product.unit_prices?.find(p => p.unit_id == selectedItem.unit_id)?.price ||
                        convert_to_base_unit(selectedItem, selectedItem.unit_id, selectedItem.price)
                    );
                  }
                }
              "
            />
          </div>
          <div class="col-span-6 sm:col-span-4">
            <Input
              type="number"
              id="item-cost"
              v-model="selectedItem.cost"
              :readonly="!selectedItem.product.can_edit_price"
              :label="form.type == 'Purchase' ? $t('Cost') : $t('Price')"
            />
          </div>
          <div class="col-span-6 sm:col-span-2">
            <Input
              id="item-discount"
              :label="$t('Discount')"
              v-model="selectedItem.discount"
              @keypress="discount_keypress"
              @change="
                e => {
                  let max_discount = usePage().props.settings?.max_discount || null;
                  if (selectedItem.discount.includes('%')) {
                    if (Number(selectedItem.discount.replace('%', '')) > Number(selectedItem.product.max_discount)) {
                      selectedItem.discount = Number(selectedItem.product.max_discount) + '%';
                      notify({
                        group: 'main',
                        type: 'error',
                        title: $t('You cannot apply discount more than {x}%', { x: Number(selectedItem.product.max_discount) }),
                      });
                    } else if (Number(selectedItem.discount.replace('%', '')) > Number(max_discount)) {
                      selectedItem.discount = Number(max_discount) + '%';
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
          <div :class="selectedItem.product.unit?.subunits?.length ? 'col-span-full' : 'col-span-6 sm:col-span-4'">
            <AutoComplete
              :json="true"
              value-key="id"
              id="item-taxes"
              :multiple="true"
              label-key="name"
              :searchable="false"
              :suggestions="taxes"
              :label="$t('Taxes')"
              v-model="selectedItem.taxes"
              :disabled="!selectedItem.product.can_edit_taxes"
            />
          </div>
        </div>
        <div class="col-span-full flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
          <button type="button" @click="removeItem(selectedItem)" class="focus rounded-md px-1 py-2.5 text-red-600 hover:text-red-500">
            {{ $t('Remove') }}
          </button>
          <button @click="updateItem" class="btn-primary">{{ $t('Update') }}</button>
        </div>
      </div>
    </div>
  </Modal>

  <BarcodeScanner :show="scannerOpen" @scanned="handleBarcodeScan" @close="scannerOpen = false" />
</template>
