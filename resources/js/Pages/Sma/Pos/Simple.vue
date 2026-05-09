<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import { PosHelper } from './PosHelper';
import { $can, calculate_item, searchItems } from '@/Core';
import { AutoComplete, BarcodeScanner, Input, Loading, ProductSearch } from '@/Components/Common';
import { ApplicationMark, DangerButton, DialogModal, Dropdown, DropdownLink, Modal, SecondaryButton } from '@/Components/Jet';
import VerifyPinCode from './Components/VerifyPinCode.vue';
import ChangeTable from './Components/ChangeTable.vue';
import { KeyBoard } from './Components/Keyboard';
import Icon from '@r/js/Components/Icon.vue';

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
  result,
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
  form.old_table_id = form.table_id;
  form.hall_id = data.hall_id;
  form.table_id = data.table_id;
  saveForm();
  saveOrder();
  router.reload({ only: ['sale'] });
  changeTableModal.value = false;
}
</script>

<template>
  <Head title="POS" />
  <div>
    <div class="flex h-screen min-h-full flex-1 grow flex-col self-stretch">
      <Loading v-if="loadingPage" class="z-20" />

      <!-- 3 column wrapper -->
      <div class="flex max-h-full w-full grow flex-col overflow-hidden lg:flex-row lg:items-stretch lg:justify-stretch">
        <!-- Left sidebar & main wrapper -->
        <div class="flex max-h-full max-w-full flex-1 grow flex-col print:hidden">
          <header
            class="flex h-16 items-center border-b border-gray-200 bg-white text-gray-700 lg:static lg:overflow-y-visible dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
          >
            <div class="w-full px-4">
              <div class="relative flex justify-between lg:gap-8">
                <div class="flex lg:static xl:col-span-2">
                  <div class="flex shrink-0 items-center">
                    <Link :href="route('dashboard')" class="h-8 w-auto focus:ring-0 focus:outline-hidden">
                      <ApplicationMark />
                    </Link>
                  </div>
                </div>
                <div class="flex-1">
                  <div
                    class="group z-20 flex items-center bg-white px-3 md:mx-auto md:max-w-3xl lg:mx-0 lg:max-w-none xl:px-0 dark:bg-gray-900 [&:has(input:focus)]:absolute [&:has(input:focus)]:inset-s-6 [&:has(input:focus)]:inset-e-2 [&:has(input:focus)]:top-0 [&:has(input:focus)]:-ms-6 [&:has(input:focus)]:-me-4 md:[&:has(input:focus)]:static md:[&:has(input:focus)]:mx-auto"
                  >
                    <button
                      type="button"
                      class="me-4 hidden bg-white group-[&:has(input:focus)]:inline-flex md:group-[&:has(input:focus)]:hidden dark:bg-gray-900"
                    >
                      <Icon name="x" class="size-5 text-gray-400" />
                    </button>
                    <div class="relative w-full">
                      <ProductSearch model="sale" type="pos" @add-item="addItem" />
                      <div
                        class="absolute inset-y-0 end-0 m-px flex items-center gap-2 rounded-md bg-white px-2 hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800"
                      >
                        <button
                          type="button"
                          :title="$t('Scan Barcode')"
                          class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-200"
                          @click="scannerOpen = true"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-7"
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
                        <button
                          type="button"
                          class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-200"
                          @click="() => (giftCardModal = true)"
                        >
                          <Icon name="gift-o" class="size-6" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div ref="menusContainer" class="flex items-center gap-2 lg:hidden">
                  <!-- Mobile menu button -->
                  <button
                    type="button"
                    @click="() => (showMobileMenu = true)"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset"
                    aria-expanded="false"
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
                    @click="showOrders = true"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset"
                  >
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">{{ $t('List Open Orders') }}</span>
                    <!-- <Icon name="squares" class="size-6" /> -->
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
                        d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"
                      />
                    </svg>
                  </button>
                  <button
                    type="button"
                    @click="showOrderDetails"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:hover:bg-gray-800"
                    aria-expanded="false"
                  >
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">{{ $t('View Orders') }}</span>
                    <!-- <Icon name="squares" class="size-6" /> -->
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
                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"
                      />
                    </svg>
                  </button>
                </div>
                <div class="hidden lg:flex lg:items-center lg:justify-end lg:gap-4">
                  <a
                    target="_blank"
                    :href="route('customer.view')"
                    class="relative shrink-0 rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  >
                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">{{ $t('Customer View') }}</span>
                    <Icon name="tablet-o" class="size-6" />
                  </a>
                  <Link
                    v-if="$can('read-sales')"
                    :href="route('sales.index')"
                    class="relative hidden shrink-0 rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden xl:block dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  >
                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">{{ $t('View Sales') }}</span>
                    <Icon name="bag-o" class="size-6" />
                  </Link>
                  <button
                    type="button"
                    @click="view_register = true"
                    class="relative shrink-0 rounded-md px-2 pt-1 pb-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  >
                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">{{ $t('Cash Register') }}</span>
                    <Icon name="register-o" class="mt-1 size-6" />
                  </button>
                  <button
                    type="button"
                    @click="showOrders = true"
                    class="relative shrink-0 rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  >
                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">{{ $t('List Open Orders') }}</span>
                    <Icon name="squares-o" class="size-6" />
                  </button>
                  <button
                    v-if="$page.props.settings?.restaurant == 1"
                    type="button"
                    @click="showQrOrders = true"
                    class="relative shrink-0 rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:outline-hidden dark:hover:bg-gray-800 dark:hover:text-gray-200"
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
                      <button class="flex items-center rounded-full p-1 transition duration-150 ease-in-out">
                        <img
                          :alt="$page.props.auth.user.name"
                          :src="$page.props.auth.user.profile_photo_url"
                          class="h-8 w-8 rounded-full object-cover xl:me-2"
                          v-if="$page.props.jetstream.managesProfilePhotos"
                        />
                        <span class="hidden items-center xl:inline-flex">
                          {{ $page.props.auth.user.name }}
                          <icons name="chevron-down" class="ms-2 -me-1 hidden xl:block" />
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
          </header>
          <div style="height: calc(100vh - 4rem)" class="flex max-h-full max-w-full flex-1 grow flex-col lg:flex-row">
            <div
              class="flex min-h-15 max-w-full flex-row gap-x-2 gap-y-1 overflow-auto border-b border-gray-200 bg-white px-2 text-gray-700 lg:h-full lg:w-56 lg:shrink-0 lg:flex-col lg:border-e lg:border-b-0 lg:px-4 lg:py-4 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
            >
              <template :key="c.id" v-for="c of categories">
                <button
                  type="button"
                  @click="getProducts(c.id)"
                  :class="c.id == cId ? 'bg-gray-100 dark:bg-gray-900' : ''"
                  class="group relative my-2 flex min-w-min items-center justify-center truncate overflow-hidden rounded-md px-6 py-1.5 pe-12 text-start text-sm font-bold whitespace-nowrap hover:-mx-2 hover:-my-1 hover:bg-gray-50 hover:px-8 hover:py-2.5 focus:ring-0 focus:outline-hidden lg:my-0 lg:justify-start lg:px-2 lg:hover:px-4 dark:hover:bg-gray-950"
                >
                  <img v-if="c.photo" :src="c.photo" alt="" class="me-2 h-6 w-6 rounded" />
                  {{ c.name }}

                  <span
                    class="absolute end-0 flex size-6 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-xs group-hover:end-2 dark:bg-gray-700"
                  >
                    {{ c.products_count > 99 ? '99+' : c.products_count }}
                  </span>
                </button>
                <div v-if="c.children && c.children.length" class="flex h-full ps-2 lg:h-auto lg:flex-wrap">
                  <button
                    :key="cc.id"
                    type="button"
                    v-for="cc of c.children"
                    @click="getProducts(cc.id)"
                    :class="cc.id == cId ? 'bg-gray-100 dark:bg-gray-900' : ''"
                    class="group relative my-2 flex w-full items-center justify-center rounded-md px-6 py-1.5 pe-10 text-start whitespace-nowrap hover:-mx-2 hover:-my-1 hover:bg-gray-100 hover:px-8 hover:py-2.5 focus:ring-0 focus:outline-hidden lg:my-0 lg:justify-start lg:px-2 lg:hover:ms-0 lg:hover:px-2 dark:hover:bg-gray-950"
                  >
                    <img v-if="cc.photo" :src="cc.photo" alt="" class="me-2 h-6 w-6" />
                    <span v-else v-html="'&#11153;'" class="me-2 text-xs"></span>
                    {{ cc.name }}

                    <span
                      class="absolute end-0 flex size-6 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-xs group-hover:end-1 dark:bg-gray-700"
                      >{{ cc.child_products_count > 99 ? '99+' : cc.child_products_count }}</span
                    >
                  </button>
                </div>
              </template>
            </div>

            <div class="relative flex-1 overflow-y-auto p-4 lg:flex-1">
              <Loading v-if="loading" />

              <div v-if="grid && grid.length" class="grid grid-cols-2 gap-4 sm:flex sm:flex-wrap sm:items-start sm:justify-start">
                <button
                  :key="p"
                  type="button"
                  v-for="p of grid"
                  @click="addProduct(p)"
                  :disabled="$page.props.settings.overselling != 1 && p.store_stock?.balance <= 0"
                  class="relative flex h-[180px] flex-col items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-white hover:drop-shadow-xl disabled:pointer-events-none disabled:opacity-50 sm:w-[140px] dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
                >
                  <img v-if="p.photo" :src="p.photo" alt="" class="h-[125px] w-full object-cover" />
                  <div class="flex grow items-center justify-center leading-snug">{{ p.name }}</div>
                  <div
                    class="absolute -top-px -right-px flex h-6 min-w-6 items-center justify-center rounded-tr-md rounded-bl-md bg-gray-100 px-1 text-xs font-bold dark:bg-gray-950"
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

        <div v-if="showOrder" class="fixed inset-0 z-0 bg-gray-500/70 backdrop-blur-xs lg:hidden dark:bg-gray-800/70"></div>
        <div
          :class="[
            showOrder ? 'fixed end-0 z-10 block h-screen overflow-auto lg:static lg:overflow-hidden' : 'hidden overflow-hidden',
            $page.props.settings.show_order_by_default == 1 ? 'w-full sm:w-96' : 'w-96',
          ]"
          class="max-h-full min-h-full shrink-0 border-s border-gray-200 bg-white lg:block dark:border-gray-800 dark:bg-gray-900 print:block print:h-full print:w-full print:border-0 print:lg:block"
        >
          <div ref="orderContainer" id="order-container" class="flex max-h-full min-h-full w-full flex-col">
            <div ref="orderDetails" id="order-details" class="flex items-start justify-between px-4 pt-2">
              <div class="grow font-semibold text-gray-900 dark:text-gray-100">
                <div v-if="$page.props.settings?.restaurant == 1 && form.table_id" class="flex items-center gap-1 leading-6">
                  <!-- {{ $t('Table') }}: -->
                  <button
                    type="button"
                    @click="changeTableModal = true"
                    class="group -mx-2 -my-1 flex items-center rounded-md bg-gray-100 px-2 py-1 text-sm transition-all hover:bg-gray-200 dark:bg-gray-900 dark:hover:bg-gray-800"
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
                <div v-else-if="$page.props.settings?.restaurant == 1 && form.reference_number" class="flex items-center gap-1 leading-6">
                  {{ $t('Reference') }}:
                  {{ form.reference_number || '' }}
                </div>
                <div v-else class="flex items-center gap-1 leading-6">
                  {{ $t('Order Details') }}
                  <div v-if="form.reference && form.reference != 'f'" class="text-xs">({{ form.reference }})</div>
                </div>
              </div>
              <div class="flex items-center gap-1 text-end">
                <div class="text-right">
                  <div class="text-xs">{{ $t('Order Number') }}</div>
                  <div class="text-xs font-bold">#{{ form.number }}</div>
                </div>
                <button
                  type="button"
                  v-if="showOrder"
                  @click="() => (showOrder = false)"
                  class="-me-2 -mt-1 block rounded-xs bg-gray-100 p-1 hover:bg-gray-200 lg:hidden dark:bg-gray-900 dark:hover:bg-gray-950"
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
            <div ref="orderCustomer" id="order-customer" class="px-4 pb-3">
              <div class="mb-px flex items-center gap-x-4 text-xs">
                <label for="customer">{{ $t('Customer') }}</label>
              </div>

              <div class="relative mt-px grid grid-cols-1 items-center">
                <AutoComplete
                  keyboard
                  :json="true"
                  size="small"
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
                  inputClass="border border-gray-200 dark:border-gray-700 focus rounded-md shadow-2xs print:border-0 print:shadow-none print:border-b print:px-0 print:rounded-none"
                />
                <div class="absolute inset-y-0 end-0 flex items-stretch gap-1 print:hidden">
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
                  <button
                    type="button"
                    @click="addCustomer()"
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
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="text-sm text-red-500" v-if="$page.props.errors?.customer_id">{{ $page.props.errors.customer_id }}</div>

              <div v-if="$page.props.settings.show_order_by_default == 1" class="mt-2 lg:hidden">
                <div class="relative flex-1">
                  <div class="flex items-center md:mx-auto md:max-w-3xl lg:mx-0 lg:max-w-none">
                    <ProductSearch model="sale" type="pos" id="product-search-order" @add-item="addItem" />
                    <div
                      class="absolute inset-y-0 end-0 m-px flex items-center gap-2 rounded-md px-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      <button
                        type="button"
                        :title="$t('Scan Barcode')"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-200"
                        @click="scannerOpen = true"
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
                      <button
                        type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-200"
                        @click="() => (giftCardModal = true)"
                      >
                        <Icon name="gift-o" class="size-6" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div
              id="order-items"
              ref="orderItems"
              class="flex min-h-[150px] w-full flex-col overflow-auto px-4 print:h-full! print:min-h-0 print:overflow-visible"
              :style="{ height: itemsHeight + 'px' }"
            >
              <ul v-if="$page.props.opened_register" role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                <template :key="item.id" v-for="item in form.items">
                  <FormItem :item="item" @edit="selectItem" @remove="removeItem" @update="quantityChanged" />
                </template>
              </ul>
            </div>
            <template v-if="form?.number">
              <div ref="orderSummary" id="order-summary" class="mx-2 mb-1">
                <OrderSummary :form="form" @discount="showOrderDiscount" />
              </div>
              <div ref="orderActions" id="order-actions" class="mx-2 flex items-center gap-2 py-1 print:hidden">
                <FormActions
                  @hold="holdOrder"
                  :loading="loadingPage"
                  @payment="showPaymentModal"
                  @print-bill="printBill = true"
                  @print-order="printOrder = true"
                  @delete-order="delete_order = form.number"
                />
              </div>
            </template>
          </div>
        </div>

        <!-- Mobile Menus -->
        <div v-if="showMobileMenu" class="fixed inset-0 z-0 bg-gray-500/70 backdrop-blur-xs lg:hidden dark:bg-gray-800/70"></div>
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
      </div>
    </div>

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

    <Modal :show="openItemModal" max-width="xl" @close="openItemModal = false" :overflow="true" :backdrop="false">
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

    <Modal :show="showOrders" @close="showOrders = false" maxWidth="xl" :closeable="false">
      <ShowOrders @loadOrder="loadOrder" @close="showOrders = false" @delete-order="id => (delete_order = id)" />
    </Modal>

    <Modal :show="showQrOrders" @close="showQrOrders = false" maxWidth="xl" :closeable="false">
      <ShowQrOrders @loadOrder="loadQrOrder" @close="showQrOrders = false" />
    </Modal>

    <Modal :show="printOrder" @close="() => (printOrder = false)" maxWidth="md" :closeable="true">
      <PrintOrder :form="form" :halls="halls" :sendPrint="sendToPPS" @close="() => (printOrder = false)" />
    </Modal>

    <Modal :show="printBill" @close="() => (printBill = false)" maxWidth="md" :closeable="true">
      <PrintBill :form="form" :halls="halls" :sendPrint="sendToPPS" @close="() => (printBill = false)" />
    </Modal>

    <Modal :show="showReceipt" @close="() => (showReceipt = false)" maxWidth="md" :closeable="true">
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

    <Modal :show="order_discount" @close="() => (order_discount = false)" maxWidth="md" :closeable="true">
      <OrderDiscount @apply="applyOrderDiscount" />
    </Modal>

    <Modal :show="addCustomerModal" @close="addCustomerModal = false" maxWidth="3xl" :closeable="true">
      <CustomerForm :keyboard="true" :pos="1" :countries="countries" :custom_fields="customer_fields" @close="addCustomerModal = false" />
    </Modal>

    <Modal :show="showCustomer" @close="showCustomer = false" maxWidth="lg" :closeable="true">
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

    <DialogModal :show="delete_order" @close="() => (delete_order = false)" maxWidth="sm">
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
