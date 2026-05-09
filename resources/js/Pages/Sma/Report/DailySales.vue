<script setup>
import { router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Modal } from '@/Components/Jet';
import { DateInput } from '@/Components/Common';

defineOptions({ layout: AdminLayout });

const show = ref(false);
const month = ref(null);
const dayData = ref(null);
const props = defineProps(['data', 'html', 'current_month', 'prev_month_link', 'next_month_link']);

month.value = props.current_month;
watch(month, v => monthChanged(v));

onMounted(async () => {
  document.querySelectorAll('.amount').forEach(item => {
    item.addEventListener('click', event => {
      dayData.value = props.data[event.target.dataset.date] || {};
      if (dayData.value.date) {
        show.value = true;
      }
    });
  });
});

const monthChanged = m => {
  router.visit(route('daily_sales.report', { month: m.month + 1, year: m.year }), { preserveScroll: true });
};
</script>

<template>
  <div>
    <Head :title="$t('Daily Sales')" />
    <Header>
      {{ $t('{x} Report', { x: $t('Daily Sales') }) }}
      <template #subheading> {{ $t('Please review the data below') }} </template>
    </Header>

    <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
      <div class="flow-root grow">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="mt-2 min-w-[640px] bg-white dark:bg-gray-900">
            <div class="daily-sales bg-white dark:bg-gray-900">
              <div class="flex items-center justify-between px-4 pt-4">
                <Link :href="prev_month_link" class="block rounded-sm p-2 hover:bg-gray-200 dark:hover:bg-gray-700">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                  </svg>
                </Link>
                <div class="xborder col-span-6 sm:col-span-3">
                  <DateInput label="" id="month" month-picker v-model="month" :clearable="false" />
                </div>

                <!-- <DateInput
                  v-model="month"
                  id="month"
                  label="Month"
                  @update:modelValue="monthChanged"
                  :showIcon="false"
                  format="MM/yyyy"
                  :modelValue="current_month"
                /> -->
                <!-- v-model="month" -->
                <!-- <input
                  id="month"
                  type="month"
                  name="month"
                  :value="current_month"
                  @change="monthChanged"
                  class="appearance-none border-0 w-auto min-w-0 max-w-full bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold py-1 px-2 focus:outline-hidden focus:ring-1 focus:ring-inset focus:ring-primary-500"
                /> -->
                <!-- <div class="flex items-center justify-center text-xl font-bold uppercase">{{ current_month }}</div> -->
                <Link :href="next_month_link" class="block rounded-sm p-2 hover:bg-gray-200 dark:hover:bg-gray-700">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                </Link>
              </div>
              <div v-html="html"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="show" :closeable="true" @close="show = false" maxWidth="sm">
      <div class="">
        <div class="border-b border-gray-200 px-6 py-4 text-lg font-bold capitalize dark:border-gray-700">
          {{ $date(dayData.date) }}
        </div>
        <!-- <p>{{ $t('Please review the day data below') }}</p> -->
        <div class="p-4 print:px-0">
          <div class="">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
              <tbody>
                <tr>
                  <td class="px-2 py-1.5">{{ $t('Total') }}</td>
                  <td class="text-right">{{ $number(dayData.total) }}</td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5">{{ $t('Tax') }}</td>
                  <td class="text-right">{{ $number(dayData.total_tax_amount) }}</td>
                </tr>
                <tr class="font-bold">
                  <td class="px-2 py-1.5">{{ $t('Grand Total') }}</td>
                  <td class="text-right">{{ $number(dayData.grand_total) }}</td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5">{{ $t('Received') }}</td>
                  <td class="text-right">{{ $number(dayData.paid) }}</td>
                </tr>
                <tr class="font-bold">
                  <td class="px-2 py-1.5">{{ $t('Balance') }}</td>
                  <td class="text-right">{{ $number(dayData.grand_total - dayData.paid) }}</td>
                </tr>
                <!-- <tr>
                  <td class="px-2 py-1.5"></td>
                  <td class="px-2 py-1.5"></td>
                </tr>
                <tr class="font-bold text-lg">
                  <td class="px-2 py-1.5">{{ $t('Total Payment') }}</td>
                  <td class="text-right">{{ $number(dayData.payment) }}</td>
                </tr> -->
              </tbody>
            </table>
          </div>
        </div>
        <div v-if="$super" class="flex flex-wrap justify-between border-t border-gray-200 px-5 py-4 font-bold dark:border-gray-700">
          <div>
            <div>{{ $t('Estimated Profit') }}</div>
            <div class="text-mute text-sm">({{ $t('after all payments') }})</div>
          </div>
          <div>{{ $number(Number(dayData.grand_total) - Number(dayData.cost)) }}</div>
        </div>
      </div>
    </Modal>
  </div>
</template>
