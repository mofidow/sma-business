<script setup>
import axios from 'axios';
import { ref, onMounted, nextTick } from 'vue';

import { PosHelper } from './PosHelper';
import { Loading } from '@/Components/Common';
import { ApplicationMark } from '@/Components/Jet';
import { $number_qty } from '@r/js/Core';

const props = defineProps(['pos_settings']);
const { orderItems, OrderSummary } = PosHelper(props);

const form = ref({});
const loading = ref(true);
const customer = ref(null);

onMounted(() => {
  form.value = JSON.parse(localStorage.getItem('pos.form') || '{}');
  if (form.value.customer_id && customer.value?.id != form.value.customer_id) {
    getCustomer();
  }
  loading.value = false;
  nextTick(() => {
    document.getElementById('order-items').scrollTop = document.getElementById('order-items').scrollHeight;
  });

  window.addEventListener('storage', event => {
    if (event.key === 'pos.form') {
      form.value = JSON.parse(event.newValue || '{}');
      if (form.value.customer_id && customer.value?.id != form.value.customer_id) {
        getCustomer();
      }
      nextTick(() => {
        setTimeout(() => {
          document.getElementById('order-items').scrollTop = document.getElementById('order-items').scrollHeight;
        }, 300);
      });
    }
  });
});

function getCustomer() {
  if (form.value.customer) {
    customer.value = form.value.customer;
    return;
  }
  if (form.value.customer_id) {
    axios
      .post(route('search.customers', { id: form.value.customer_id }))
      .then(response => {
        if (response.data.length == 1) {
          customer.value = response.data[0];
        }
      })
      .catch(error => {
        console.error('Error fetching customer:', error);
      });
  }
}
</script>

<template>
  <Head title="POS - Customer View" />
  <div>
    <div class="flex h-screen min-h-full flex-1 grow flex-col self-stretch">
      <Loading v-if="loadingPage" class="z-20" />

      <!-- 3 column wrapper -->
      <div class="flex max-h-full w-full grow items-stretch justify-stretch overflow-hidden">
        <!-- Left sidebar & main wrapper -->
        <div class="hidden max-h-full max-w-full flex-1 grow flex-col sm:flex print:hidden">
          <header
            v-if="$page.props.settings?.customer_view_show_header == 1"
            class="flex h-16 items-center border-b border-gray-200 bg-white text-gray-700 lg:static lg:overflow-y-visible dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
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
                <div class="flex-1"></div>
              </div>
            </div>
          </header>
          <div style="height: calc(100vh - 4rem)" class="flex max-h-full max-w-full flex-1 grow">
            <div class="relative flex-1 overflow-y-auto p-4">
              <Loading v-if="loading" />

              <div class="flex h-full w-full items-center justify-center text-center">
                <div>
                  <h1 v-if="pos_settings.customer_view_heading" class="mb-2 text-2xl font-bold">
                    {{ pos_settings.customer_view_heading }}
                  </h1>
                  <div class="text-mute font-light whitespace-pre-line">
                    <div v-html="pos_settings?.customer_view_message || $t('You can cover this area with advertisements.')"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- <div class="fixed z-0 inset-0 bg-gray-500/70 dark:bg-gray-800/70 backdrop-blur-xs lg:hidden"></div> -->
        <div
          class="static end-0 z-10 block h-screen max-h-full min-h-full w-full shrink-0 overflow-hidden border-s border-gray-200 bg-white sm:w-96 dark:border-gray-800 dark:bg-gray-950 print:block print:h-full print:w-full print:border-0"
        >
          <div ref="orderContainer" id="order-container" class="flex max-h-full min-h-full w-full flex-col">
            <div ref="orderDetails" id="order-details" class="flex items-start justify-between px-4 pt-2">
              <div class="flex grow items-center gap-1 leading-6 font-semibold text-gray-900 dark:text-gray-100">
                {{ $t('Order Details') }}
                <div v-if="form.reference && form.reference != 'f'" class="text-xs">({{ form.reference }})</div>
              </div>
              <div class="flex items-center gap-1 text-end">
                <div class="text-right">
                  <div class="text-xs">{{ $t('Order Number') }}</div>
                  <div class="text-xs font-bold">#{{ form.number }}</div>
                </div>
              </div>
            </div>
            <div ref="orderCustomer" id="order-customer" class="px-4 pb-3">
              <div class="mb-px flex items-center gap-x-4 text-xs">
                <label for="customer">{{ $t('Customer') }}:</label>
              </div>

              <div class="relative mt-px grid grid-cols-1 items-center font-bold">{{ customer?.company || customer?.name }}</div>
            </div>
            <div
              id="order-items"
              ref="orderItems"
              style="height: calc(100vh - 230px)"
              v-if="form.items && form.items.length"
              class="scroll-box flex min-h-[150px] w-full flex-col overflow-auto print:h-full print:min-h-0 print:overflow-visible"
            >
              <table class="divide-y divide-gray-200 dark:divide-gray-700">
                <template :key="item.id" v-for="item in form.items">
                  <tbody>
                    <template v-if="item.product.has_variants == 1 && item.variations && item.variations.length">
                      <tr>
                        <td class="py-1.5 ps-4 pe-2 align-top">
                          <div class="flex">
                            <img alt="" :src="item.product.photo" v-if="item.product.photo" class="me-2 size-9 flex-none rounded-md" />
                            <div class="flex flex-col text-sm font-bold">
                              {{ item.name }}
                              <div>{{ item.code }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-1.5 text-end align-top font-bold"></td>
                        <td class="py-1.5 ps-2 pe-4 text-end align-top"></td>
                      </tr>
                      <template :key="variation.id" v-for="variation in item.variations">
                        <tr>
                          <td class="ps-4 pr-2 pb-1.5 align-top">
                            <div class="flex">
                              <div alt="" v-if="item.product.photo" class="me-2 w-9 flex-none rounded-md" />
                              {{ $meta(variation.meta) }}
                            </div>
                          </td>
                          <td class="px-2 pb-1.5 text-end align-top font-bold">{{ $number_qty(variation.quantity) }} x</td>
                          <td class="ps-2 pr-4 pb-1.5 text-end align-top font-bold">
                            {{ $number(Number(variation.net_price)) }}
                          </td>
                        </tr>
                      </template>
                    </template>
                    <template v-else>
                      <tr>
                        <td class="py-1.5 ps-4 pe-2 align-top">
                          <div class="flex">
                            <img alt="" :src="item.product.photo" v-if="item.product.photo" class="me-2 size-9 flex-none rounded-md" />
                            <div class="flex flex-col text-sm font-bold">
                              {{ item.name }}
                              <div>{{ item.code }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-1.5 text-end align-top font-bold">{{ $number_qty(item.quantity) }} x</td>
                        <td class="py-1.5 ps-2 pe-4 text-end align-top font-bold">{{ $number(item.net_price) }}</td>
                      </tr>
                    </template>
                  </tbody>
                </template>
              </table>
            </div>

            <div v-if="form.items?.length" class="mt-auto p-2">
              <OrderSummary :form="form" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
