<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import { PosHelper } from './PosHelper';
import { $can, calculate_item, searchItems } from '@/Core';
import VerifyPinCode from './Components/VerifyPinCode.vue';
import ChangeTable from './Components/ChangeTable.vue';
import { KeyBoard, vKeyboard } from './Components/Keyboard';
import { AutoComplete, BarcodeScanner, Loading, ProductSearch } from '@/Components/Common';
import { DangerButton, DialogModal, Dropdown, DropdownLink, Modal, SecondaryButton } from '@/Components/Jet';

const props = defineProps([
  'taxes',
  'categories',
  'countries',
  'custom_fields',
  'customer_fields',
  'payment_fields',
  'customer',
  'sale',
  'order',
  'products',
  'halls',
]);

const {
  grid,
  cId,
  search,
  receipt,
  loading,
  customer,
  finalize,
  itemsHeight,
  showOrder,
  orderItems,
  printBill,
  printOrder,
  showOrders,
  showQrOrders,
  qrOrdersCount,
  loadingPage,
  orderActions,
  orderDetails,
  orderSummary,
  showReceipt,
  delete_order,
  giftCardModal,
  orderCustomer,
  showCustomer,
  menusContainer,
  open_register,
  orderContainer,
  view_register,
  order_discount,
  showMobileMenu,
  addCustomerModal,
  mobileMenuContainer,
  form,
  currentItem,
  variantModal,
  SelectVariant,
  openItemModal,
  selectItem,
  selectedItem,
  saveForm,
  removeItem,
  openOrder,
  holdOrder,
  saveOrder,
  showPaymentModal,
  loadOrder,
  showOrderDiscount,
  applyOrderDiscount,
  updatedOrderItem,
  showOrderDetails,
  addItem,
  addProduct,
  quantityChanged,
  deleteOrder,
  loadQrOrder,
  getProducts,
  showCustomerDetails,
  addCustomer,
  handleSubmit,

  CustomerForm,
  ViewCustomer,
  FormItem,
  OpenOrder,
  PrintBill,
  PrintOrder,
  ShowOrders,
  ShowQrOrders,
  AddGiftCard,
  FormActions,
  FinalizeSale,
  OpenRegister,
  OrderSummary,
  PrintReceipt,
  OrderDiscount,
  UpdateFormItem,
  RegisterDetails,
  playSuccessSound,

  sendToPPS,
} = PosHelper(props);

const currentCategory = ref(null);
const show_categories_modal = ref(false);
const scannerOpen = ref(false);
const changeTableModal = ref(false);

async function handleBarcodeScan(code) {
  scannerOpen.value = false;
  const results = await searchItems(code, 'sale', true);
  if (results && results.length) {
    await addItem(results[0]);
  }
}

function handleTableChange(data) {
  form.hall_id = data.hall_id;
  form.table_id = data.table_id;
  saveForm();
  saveOrder();
  router.reload({ only: ['halls'] });
  changeTableModal.value = false;
}

function showChildCategories(c) {
  currentCategory.value = c;
  show_categories_modal.value = true;
}
</script>

<template>
  <Head title="POS" />

  <div>
    <div v-if="showOrder" class="fixed inset-0 z-1 bg-gray-500/70 backdrop-blur-xs lg:hidden dark:bg-gray-800/70"></div>
    <aside
      :class="[showOrder ? 'z-10' : 'hidden lg:block', $page.props.settings.show_order_by_default == 1 ? 'w-full sm:w-96' : 'w-96']"
      class="fixed inset-y-0 end-0 overflow-y-auto border-s border-transparent bg-gray-100 lg:shadow-2xl xl:shadow-lg dark:border-gray-800 dark:bg-gray-800 print:block print:w-full print:border-0"
    >
      <div ref="orderContainer" id="order-container" class="flex max-h-full min-h-full w-full flex-col">
        <div ref="orderDetails" id="order-details" class="flex items-start justify-between px-4 pt-4">
          <div class="grow leading-6 font-semibold text-gray-900 dark:text-gray-100">
            <div v-if="$page.props.settings?.restaurant == 1 && form.table_id" class="flex items-center gap-1">
              <!-- {{ $t('Table') }}: -->
              <button
                type="button"
                @click="changeTableModal = true"
                class="group -mx-2 -my-1 flex items-center rounded-md bg-gray-100 px-2 py-1 text-sm transition-all hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-900"
              >
                {{
                  halls.find(h => h.id == form.hall_id)?.tables?.find(t => t.id == form.table_id)?.name +
                    ' (' +
                    halls.find(h => h.id == form.hall_id)?.name +
                    ')' || ''
                }}
                <Icon name="edit" class="invisible size-4! transition-all group-hover:visible group-hover:ms-2" />
              </button>
            </div>
            <div v-else-if="$page.props.settings?.restaurant == 1 && form.reference_number" class="flex items-center gap-1">
              {{ $t('Reference') }}:
              {{ form.reference_number || '' }}
            </div>
            <div v-else class="flex items-center gap-1">
              {{ $t('Order Details') }}
              <div v-if="form.reference && form.reference != 'f'" class="text-xs">({{ form.reference }})</div>
            </div>
          </div>
          <div class="flex items-stretch gap-2 text-end">
            <div class="text-right">
              <div class="text-xs">{{ $t('Order Number') }}</div>
              <div class="text-xs font-bold">#{{ form.number }}</div>
            </div>
            <button
              type="button"
              v-if="showOrder"
              @click="() => (showOrder = false)"
              class="-me-2 block rounded-md p-1 hover:bg-gray-200 lg:hidden dark:hover:bg-gray-950"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="h-5 w-5"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <div ref="orderCustomer" id="order-customer" class="px-4 pt-2 pb-6">
          <!-- <div class="flex items-center gap-x-4 text-xs mb-px">
            <label for="customer">{{ $t('Customer') }}</label>
          </div> -->

          <div class="relative mt-px grid grid-cols-1 items-center">
            <AutoComplete
              keyboard
              :json="true"
              valueKey="id"
              :hideIcon="true"
              id="customer_id"
              labelKey="company"
              :searchable="true"
              @change="
                e => {
                  form.customer = e;
                  saveForm();
                }
              "
              v-model="form.customer_id"
              :selected="form.customer_id"
              :suggestions="route('search.customers')"
              inputClass="focus rounded-full shadow-xl border-0 print:shadow-none print:border-b print:px-0 print:rounded-none"
            />
            <div class="absolute inset-y-0 end-2 flex items-stretch gap-1 print:hidden">
              <button
                type="button"
                @click="showCustomerDetails()"
                class="inline-flex items-center rounded-xs px-1 text-gray-400 focus:ring-inset"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="h-6 w-6"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                  />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </button>
              <button type="button" @click="addCustomer()" class="inline-flex items-center rounded-xs px-1 text-gray-400 focus:ring-inset">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="h-6 w-6"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
              </button>
            </div>
          </div>
          <div class="text-sm text-red-500" v-if="$page.props.errors?.customer_id">{{ $page.props.errors.customer_id }}</div>

          <div v-if="$page.props.settings.show_order_by_default == 1" class="mt-4 lg:hidden">
            <div class="relative w-full">
              <input
                v-keyboard
                type="text"
                v-model="search"
                id="product-search-order"
                :placeholder="$t('Type to search products')"
                class="focus block w-full rounded-full border-gray-300 bg-white px-4 py-2 text-base shadow-xl placeholder:text-gray-400 sm:text-sm/6 dark:border-gray-700 dark:bg-gray-800"
              />

              <div
                v-if="showOrder && search && result && result.length"
                class="absolute start-0 end-0 top-full z-20 mt-2 rounded-md bg-white py-2 ring-1 ring-gray-200 dark:bg-gray-700 dark:ring-gray-700"
              >
                <button
                  :key="p.code"
                  type="button"
                  v-for="p of result"
                  @click="addItem(p)"
                  class="w-full px-4 py-1 text-start hover:bg-gray-100 dark:hover:bg-gray-900"
                >
                  {{ p.name }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div
          id="order-items"
          ref="orderItems"
          class="flex min-h-[150px] w-full flex-col overflow-auto px-4 pb-4 print:h-full! print:min-h-0 print:overflow-visible"
          :style="{ height: itemsHeight + 'px' }"
        >
          <ul v-if="$page.props.opened_register" role="list" class="">
            <template :key="item.id" v-for="item in form.items">
              <FormItem :item="item" @edit="selectItem" @remove="removeItem" @update="quantityChanged" />
            </template>
          </ul>
        </div>
        <div ref="orderSummary" id="order-summary" class="bg-white p-4 dark:bg-gray-950">
          <OrderSummary :form="form" @discount="showOrderDiscount" />
        </div>
        <div ref="orderActions" id="order-actions" class="flex items-center gap-2 bg-white px-4 pb-4 dark:bg-gray-950 print:hidden">
          <FormActions
            @hold="holdOrder"
            :loading="loadingPage"
            @payment="showPaymentModal"
            @print-bill="printBill = true"
            @print-order="printOrder = true"
            @delete-order="delete_order = form.number"
          />
        </div>
      </div>
    </aside>

    <!-- Mobile Menus -->
    <div v-if="showMobileMenu" class="fixed inset-0 z-10 bg-gray-500/70 backdrop-blur-xs lg:hidden dark:bg-gray-800/70"></div>
    <div
      :class="
        showMobileMenu
          ? 'fixed start-0 z-10 flex h-screen items-stretch justify-stretch overflow-auto lg:static lg:hidden lg:overflow-hidden'
          : 'hidden overflow-hidden'
      "
      class="block max-h-full min-h-full w-56 shrink-0 border-s border-gray-200 bg-white shadow-lg lg:hidden dark:border-gray-700 dark:bg-gray-950 print:hidden"
    >
      <div ref="mobileMenuContainer" class="flex w-full items-stretch justify-stretch p-4">
        <div class="flex flex-1 flex-col" aria-label="Sidebar">
          <ul role="list" class="-mx-2 space-y-1">
            <li>
              <button
                type="button"
                @click="showOrders = true"
                class="group flex w-full gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
              >
                {{ $t('View Open Orders') }}
              </button>
            </li>
            <li v-if="$page.props.settings?.restaurant == 1">
              <button
                type="button"
                @click="showQrOrders = true"
                class="group flex w-full items-center gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
              >
                {{ $t('QR Code Orders') }}
                <span
                  v-if="qrOrdersCount > 0"
                  class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                >
                  {{ qrOrdersCount }}
                </span>
              </button>
            </li>
            <li>
              <button
                type="button"
                @click="view_register = true"
                class="group flex w-full gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
              >
                {{ $t('View Register Details') }}
              </button>
            </li>
          </ul>
          <ul role="list" class="-mx-2 mt-auto space-y-1">
            <li>
              <a
                :href="route('dashboard')"
                class="group flex gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
                >{{ $t('Dashboard') }}</a
              >
            </li>
            <li>
              <a
                :href="route('settings.pos')"
                class="group flex gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
                >{{ $t('POS Settings') }}</a
              >
            </li>
            <li>
              <a
                :href="route('orders.index')"
                class="group flex gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
                >{{ $t('POS Orders') }}</a
              >
            </li>
            <li>
              <a
                :href="route('sales.index')"
                class="group flex gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
                >{{ $t('List {x}', { x: $t('Sales') }) }}</a
              >
            </li>
            <li>
              <a
                target="_blank"
                :href="route('customer.view')"
                class="group flex gap-x-3 rounded-md p-2 ps-3 text-sm leading-6 font-semibold hover:bg-gray-50 dark:hover:bg-gray-950"
                >{{ $t('Customer View') }}</a
              >
            </li>
          </ul>
        </div>
      </div>
    </div>

    <main class="min-h-screen bg-gray-50 dark:bg-gray-950 print:hidden">
      <div class="lg:pe-96">
        <!-- Main area -->
        <div class="px-4 pt-4 pb-10 sm:px-6 lg:px-8 lg:py-6">
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Link
                :href="route('dashboard')"
                class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
              >
                <Icon name="home-o" class="size-6" />
              </Link>
              <h4 class="hidden font-bold md:block">{{ $t('Categories') }}</h4>
            </div>
            <div class="flex items-center gap-2">
              <div class="relative hidden w-full max-w-56 sm:block">
                <ProductSearch rounded model="sale" type="pos" @add-item="addItem" />
              </div>
              <button
                type="button"
                @click="scannerOpen = true"
                :title="$t('Scan Barcode')"
                class="relative hidden items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset sm:inline-flex dark:hover:bg-gray-800 dark:hover:text-gray-200"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="size-6"
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
              <div ref="menusContainer" class="flex items-center lg:hidden lg:gap-2 xl:gap-4">
                <!-- Mobile menu button -->
                <button
                  type="button"
                  @click="() => (showMobileMenu = true)"
                  class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-0.5"></span>
                  <span class="sr-only">{{ $t('Open menu') }}</span>

                  <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                  </svg>
                  <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                <button
                  type="button"
                  @click="showOrderDetails"
                  class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-0.5"></span>
                  <span class="sr-only">{{ $t('View {x}', { x: $t('Order') }) }}</span>
                  <Icon name="cart-o" class="size-6" />
                </button>
              </div>
              <div class="relative z-1 flex lg:items-center lg:justify-end lg:gap-2 xl:gap-4">
                <button
                  type="button"
                  class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  @click="() => (giftCardModal = true)"
                >
                  <Icon name="gift-o" class="size-6" />
                </button>
                <a
                  target="_blank"
                  :href="route('customer.view')"
                  class="relative hidden items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset lg:inline-flex dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">{{ $t('Customer View') }}</span>
                  <Icon name="tablet-o" class="size-6" />
                </a>
                <Link
                  v-if="$can('read-sales')"
                  :href="route('sales.index')"
                  class="relative hidden items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset xl:inline-flex dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">{{ $t('View Sales') }}</span>
                  <Icon name="bag-o" class="size-6" />
                </Link>
                <button
                  type="button"
                  @click="view_register = true"
                  class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">{{ $t('Cash Register') }}</span>
                  <Icon name="register-o" class="mt-1 size-6" />
                </button>
                <button
                  type="button"
                  @click="showOrders = true"
                  class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">{{ $t('List Open Orders') }}</span>
                  <Icon name="squares-o" class="size-6" />
                </button>
                <button
                  v-if="$page.props.settings?.restaurant == 1"
                  type="button"
                  @click="showQrOrders = true"
                  class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">{{ $t('QR Code Orders') }}</span>
                  <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                    />
                  </svg>
                  <span
                    v-if="qrOrdersCount > 0"
                    class="absolute -end-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                  >
                    {{ qrOrdersCount }}
                  </span>
                </button>
                <Link
                  v-if="$can('settings')"
                  :href="route('settings.pos')"
                  class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">{{ $t('POS Settings') }}</span>
                  <Icon name="cog-o" class="size-6" />
                </Link>

                <Dropdown align="right" width="48">
                  <template #trigger>
                    <button class="flex h-10 w-10 items-center rounded-full p-1 transition duration-150 ease-in-out">
                      <img
                        :alt="$page.props.auth.user.name"
                        :src="$page.props.auth.user.profile_photo_url"
                        class="h-8 w-8 rounded-full object-cover xl:me-2"
                        v-if="$page.props.jetstream.managesProfilePhotos"
                      />
                      <span class="sr-only">
                        {{ $page.props.auth.user.name }}
                        <!-- <icons name="chevron-down" class="ms-2 -me-1 hidden xl:block" /> -->
                      </span>
                    </button>
                  </template>

                  <template #content>
                    <div class="text-left">
                      <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ $t('Manage Account') }}
                      </div>
                      <DropdownLink :href="route('profile.show')">
                        {{ $t('Profile') }}
                      </DropdownLink>
                      <DropdownLink :href="route('api-tokens.index')" v-if="$page.props.jetstream.hasApiFeatures">
                        {{ $t('API Tokens') }}
                      </DropdownLink>

                      <div class="border-t border-gray-100 dark:border-gray-700"></div>
                      <form @submit.prevent="logout">
                        <DropdownLink as="button">
                          {{ $t('Log Out') }}
                        </DropdownLink>
                      </form>
                    </div>
                  </template>
                </Dropdown>
              </div>
            </div>
          </div>
          <div class="-mx-4 sm:-mx-6 lg:-mx-8">
            <div class="min-w-full overflow-x-auto px-4 sm:px-6 lg:px-8">
              <div class="inline-flex gap-4 pb-8">
                <template :key="c.id" v-for="c of categories">
                  <button
                    type="button"
                    @click="showChildCategories(c)"
                    v-if="c.children && c.children.length"
                    :class="c.id == cId || c.children.find(sc => sc.id == cId) ? 'font-bold' : ''"
                    class="relative flex w-24 flex-col items-center justify-center gap-2 rounded-2xl bg-white p-4 text-sm shadow-lg hover:shadow-xl focus:ring-0 focus:outline-hidden dark:bg-gray-800 dark:hover:bg-gray-700"
                  >
                    <img v-if="c.photo" :src="c.photo" alt="" class="h-8 w-8 rounded" />
                    <div class="line-clamp-3">{{ c.name }}</div>
                    <span
                      class="absolute end-0 top-0 flex size-6 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-xs group-hover:end-2 dark:bg-gray-700"
                      >{{ c.products_count > 99 ? '99+' : c.products_count }}</span
                    >
                  </button>
                  <button
                    v-else
                    type="button"
                    @click="getProducts(c.id)"
                    :class="c.id == cId ? 'font-bold' : ''"
                    class="relative flex w-24 flex-col items-center justify-center gap-2 rounded-2xl bg-white p-4 text-sm shadow-lg hover:shadow-xl focus:ring-0 focus:outline-hidden dark:bg-gray-800 dark:hover:bg-gray-700"
                  >
                    <img v-if="c.photo" :src="c.photo" alt="" class="h-8 w-8 rounded" />
                    <div class="line-clamp-3">{{ c.name }}</div>

                    <span
                      class="absolute end-0 top-0 flex size-6 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-xs group-hover:end-2 dark:bg-gray-700"
                      >{{ c.products_count > 99 ? '99+' : c.products_count }}</span
                    >
                  </button>
                </template>
              </div>
            </div>
          </div>
          <div class="z-0">
            <div class="mt-4 flex items-center justify-between gap-4">
              <h4 class="font-bold">{{ $t('Products') }}</h4>
              <div class="flex items-center gap-2">
                <div class="relative z-10 block max-w-56 sm:hidden">
                  <ProductSearch rounded model="sale" type="pos" id="product-search-order" @add-item="addItem" />
                </div>
                <button
                  type="button"
                  @click="scannerOpen = true"
                  :title="$t('Scan Barcode')"
                  class="relative block inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset sm:hidden dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="size-6"
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
            </div>
            <Loading v-if="loading" />
            <div v-if="grid && grid.length" class="mt-8 flex flex-wrap items-start justify-start gap-x-4 gap-y-8">
              <button
                :key="p"
                type="button"
                v-for="p of grid"
                @click="addProduct(p)"
                :disabled="$page.props.settings.overselling != 1 && p.store_stock?.balance <= 0"
                class="group relative z-0 mt-12 flex h-[100px] w-[100px] flex-col justify-end hover:drop-shadow-xl disabled:pointer-events-none disabled:opacity-50 sm:w-[150px]"
              >
                <div
                  class="absolute start-1/2 top-0 h-16 w-16 -translate-y-1/2 overflow-hidden rounded-full bg-white p-0 shadow-md sm:h-24 sm:w-24 ltr:ltr:-translate-x-1/2 rtl:translate-x-1/2 dark:bg-gray-800 dark:group-hover:bg-gray-700"
                >
                  <img v-if="p.photo" :src="p.photo" alt="" class="h-full w-full rounded-full object-contain object-center" />
                </div>
                <div class="min-h-24 overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800 dark:group-hover:bg-gray-700">
                  <div class="mt-10 flex min-h-10 items-center justify-center px-1 text-sm leading-tight sm:mt-12">
                    {{ p.name }}
                    <!-- <div
                      class="absolute top-1 -right-px flex h-6 min-w-7 items-center justify-center rounded-tl-xs rounded-tr-2xl rounded-bl-2xl bg-gray-100 px-1 text-xs font-bold dark:bg-gray-950"
                    >
                      {{ $decimal_qty(p.store_stock?.balance) }}
                    </div> -->
                  </div>
                </div>
                <div
                  class="absolute start-1/2 -top-15 flex h-6 min-w-7 items-center justify-center rounded-full bg-white px-1 text-xs font-bold shadow ltr:-translate-x-1/2 rtl:translate-x-1/2 dark:bg-gray-800"
                >
                  {{ $decimal_qty(p.store_stock?.balance || 0) }}
                </div>
              </button>
            </div>
            <div v-else class="text-mute flex h-full w-full items-center justify-center">
              {{ $t('There is no product to display') }}
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div>
    <SelectVariant
      v-if="variantModal"
      :item="currentItem"
      :show="variantModal"
      :round="$page.props.settings?.pos_design == 'Modern'"
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
          if ($page.props.settings?.play_sound == 1) {
            playSuccessSound();
          }
        }
      "
    />

    <OpenOrder
      :halls="halls"
      @close="openOrder"
      :loadOrder="loadOrder"
      :show="!form.reference"
      @showOpenOrders="
        () => {
          form.reference = 'f';
          showOrders = true;
        }
      "
      @viewRegisterDetails="
        () => {
          form.reference = 'f';
          view_register = true;
        }
      "
    />

    <Modal :round="true" max-width="xl" :show="show_categories_modal" @close="show_categories_modal = false">
      <div class="print:hidden">
        <div class="border-b border-gray-200 p-4 sm:px-6 dark:border-gray-700">
          <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
            {{ $t('Select {x}', { x: $t('Category') }) }}
          </h3>
          <p class="mt-1 text-sm">{{ $t('Please select the category to load products') }}</p>
        </div>

        <!-- {{ JSON.stringify(currentCategory) }} -->
        <div class="flex flex-col items-center bg-gray-100 p-8 dark:bg-gray-950">
          <div class="inline-flex gap-4">
            <button
              type="button"
              @click="
                () => {
                  getProducts(currentCategory.id);
                  show_categories_modal = false;
                }
              "
              class="relative flex w-28 flex-col items-stretch justify-stretch gap-2 rounded-2xl bg-white p-4 text-sm font-bold shadow-lg hover:shadow-xl focus:ring-0 focus:outline-hidden dark:bg-gray-800 dark:hover:bg-gray-700"
            >
              <img v-if="currentCategory.photo" :src="currentCategory.photo" alt="" class="mx-auto h-8 w-8" />
              {{ currentCategory.name }}
              <div class="text-xs">({{ $t('Main') }})</div>
              <span
                class="absolute end-0 top-0 flex size-6 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-xs group-hover:end-2 dark:bg-gray-700"
                >{{ currentCategory.products_count > 99 ? '99+' : currentCategory.products_count }}</span
              >
            </button>
            <template :key="cc.id" v-for="cc of currentCategory.children">
              <button
                type="button"
                @click="
                  () => {
                    getProducts(cc.id);
                    show_categories_modal = false;
                  }
                "
                class="relative flex w-28 flex-col items-stretch justify-stretch gap-2 rounded-2xl bg-white p-4 text-sm shadow-lg hover:shadow-xl focus:ring-0 focus:outline-hidden dark:bg-gray-800 dark:hover:bg-gray-700"
              >
                <img v-if="cc.photo" :src="cc.photo" alt="" class="mx-auto h-8 w-8 rounded" />
                {{ cc.name }}
                <div class="text-xs">({{ $t('Sub') }})</div>
                <span
                  class="absolute end-0 top-0 flex size-6 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-xs group-hover:end-2 dark:bg-gray-700"
                  >{{ cc.child_products_count > 99 ? '99+' : cc.child_products_count }}</span
                >
              </button>
            </template>
          </div>
        </div>
      </div>
    </Modal>

    <Modal :show="openItemModal" max-width="xl" @close="openItemModal = false" :overflow="true" :backdrop="false" :round="true">
      <UpdateFormItem
        :taxes="taxes"
        :selectedItem="selectedItem"
        @remove="i => removeItem(i)"
        @close="openItemModal = false"
        @update="i => updatedOrderItem(i)"
        @select:variant="
          item => {
            currentItem = item;
            variantModal = true;
          }
        "
      />
    </Modal>

    <Modal :show="showOrders" @close="showOrders = false" maxWidth="xl" :closeable="false" :round="true">
      <ShowOrders @loadOrder="loadOrder" @close="showOrders = false" @delete-order="id => (delete_order = id)" />
    </Modal>

    <Modal :show="showQrOrders" @close="showQrOrders = false" maxWidth="xl" :closeable="false" :round="true">
      <ShowQrOrders @loadOrder="loadQrOrder" @close="showQrOrders = false" />
    </Modal>

    <Modal :show="printOrder" @close="() => (printOrder = false)" maxWidth="md" :closeable="true" :round="true">
      <PrintOrder :form="form" :halls="halls" :sendPrint="sendToPPS" @close="() => (printOrder = false)" />
    </Modal>

    <Modal :show="printBill" @close="() => (printBill = false)" maxWidth="md" :closeable="true" :round="true">
      <PrintBill :form="form" :halls="halls" :sendPrint="sendToPPS" @close="() => (printBill = false)" />
    </Modal>

    <Modal :show="showReceipt" @close="() => (showReceipt = false)" maxWidth="md" :closeable="true" :round="true">
      <PrintReceipt :receipt="receipt" :halls="halls" :sendPrint="sendToPPS" @close="() => (showReceipt = false)" />
    </Modal>

    <FinalizeSale
      :form="form"
      :show="finalize"
      :saveForm="saveForm"
      @finalize="handleSubmit"
      @close="finalize = false"
      :custom_fields="custom_fields"
      :payment_fields="payment_fields"
    />

    <Modal :show="order_discount" @close="() => (order_discount = false)" maxWidth="md" :closeable="true" :round="true">
      <OrderDiscount @apply="applyOrderDiscount" />
    </Modal>

    <Modal :show="addCustomerModal" @close="addCustomerModal = false" maxWidth="3xl" :closeable="true" :round="true">
      <CustomerForm :keyboard="true" :pos="1" :countries="countries" :custom_fields="customer_fields" @close="addCustomerModal = false" />
    </Modal>

    <Modal :show="showCustomer" @close="showCustomer = false" maxWidth="lg" :closeable="true" :round="true">
      <template v-if="customer">
        <ViewCustomer :current="customer" @close="showCustomer = false" />
      </template>
    </Modal>

    <RegisterDetails :show="view_register" @close="view_register = false" />

    <AddGiftCard
      :show="giftCardModal"
      @close="
        item => {
          item && addItem(item);
          giftCardModal = false;
        }
      "
    />

    <OpenRegister :show="open_register" @close="open_register = false" />

    <DialogModal :show="delete_order" @close="() => (delete_order = false)" maxWidth="sm" :round="true">
      <template #title>
        <span v-if="property"> {{ $t('Delete {x}', { x: row[property] }) }}</span>
        <span v-else>{{ $t('Delete Order') }} {{ form?.number }}</span>
      </template>
      <template #content>
        <p>{{ $t('Please confirm that you would like to delete the record?') }}</p>
      </template>
      <template #footer>
        <SecondaryButton @click="() => (delete_order = false)" class="me-4"> {{ $t('Cancel') }} </SecondaryButton>
        <DangerButton @click="deleteOrder(delete_order)"> {{ $t('Yes, delete') }} </DangerButton>
      </template>
    </DialogModal>

    <VerifyPinCode />
    <KeyBoard />

    <BarcodeScanner :show="scannerOpen" @scanned="handleBarcodeScan" @close="scannerOpen = false" />

    <ChangeTable
      :halls="halls"
      :show="changeTableModal"
      @close="changeTableModal = false"
      @changed="handleTableChange"
      :currentHallId="form.hall_id"
      :currentTableId="form.table_id"
    />
  </div>
</template>
