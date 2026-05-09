<script setup>
import { route } from 'ziggy-js';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Modal } from '@/Components/Jet';

defineOptions({ layout: AdminLayout });

const year = ref(null);
const show = ref(false);
const yearData = ref(null);
const props = defineProps(['data', 'html', 'current_year', 'prev_year_link', 'next_year_link']);

year.value = props.current_year;
onMounted(async () => {
  document.querySelectorAll('.amount').forEach(item => {
    item.addEventListener('click', event => {
      yearData.value = props.data[event.target.dataset.month] || {};
      if (yearData.value.month) {
        show.value = true;
      }
    });
  });
});

const yearChanged = e => {
  router.visit(route('monthly_sales.report', { year: e.target.value }), { preserveScroll: true });
};
</script>

<template>
  <div>
    <Head :title="$t('Monthly Sales')" />
    <Header>
      {{ $t('{x} Report', { x: $t('Monthly Sales') }) }}
      <template #subheading> {{ $t('Please review the data below') }} </template>
    </Header>

    <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
      <div class="flow-root grow">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="mt-2 min-w-[640px] bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between px-4 pt-4">
              <Link :href="prev_year_link" class="block rounded-sm p-2 hover:bg-gray-200 dark:hover:bg-gray-700">
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
              <input
                step="1"
                id="year"
                min="1900"
                max="2099"
                name="year"
                type="number"
                :value="current_year"
                @change="yearChanged"
                class="w-auto max-w-full min-w-20 appearance-none rounded-md border-0 bg-white px-2 py-1 text-center text-lg font-bold text-gray-700 focus:ring-1 focus:ring-primary-500 focus:outline-hidden focus:ring-inset dark:bg-gray-900 dark:text-gray-300"
              />
              <!-- <div class="flex items-center justify-center text-xl font-bold uppercase">{{ current_year }}</div> -->
              <Link :href="next_year_link" class="block rounded-sm p-2 hover:bg-gray-200 dark:hover:bg-gray-700">
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

    <Modal :show="show" :closeable="true" @close="show = false" maxWidth="sm">
      <div class="">
        <div class="border-b border-gray-200 px-6 py-4 text-lg font-bold capitalize dark:border-gray-700">
          {{ $month(new Date(yearData.year, yearData.month - 1)) }}
        </div>
        <!-- <p>{{ $t('Please review the year data below') }}</p> -->
        <div class="p-4 py-4 print:px-0">
          <div class="">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
              <tbody>
                <tr>
                  <td class="px-2 py-1.5">{{ $t('Total') }}</td>
                  <td class="text-right">{{ $number(yearData.total) }}</td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5">{{ $t('Tax') }}</td>
                  <td class="text-right">{{ $number(yearData.total_tax_amount) }}</td>
                </tr>
                <tr class="font-bold">
                  <td class="px-2 py-1.5">{{ $t('Grand Total') }}</td>
                  <td class="text-right">{{ $number(yearData.grand_total) }}</td>
                </tr>
                <tr>
                  <td class="px-2 py-1.5">{{ $t('Received') }}</td>
                  <td class="text-right">{{ $number(yearData.paid) }}</td>
                </tr>
                <tr class="font-bold">
                  <td class="px-2 py-1.5">{{ $t('Balance') }}</td>
                  <td class="text-right">{{ $number(yearData.grand_total - yearData.paid) }}</td>
                </tr>
                <!-- <tr>
                  <td class="px-2 py-1.5"></td>
                  <td class="px-2 py-1.5"></td>
                </tr>
                <tr class="font-bold text-lg">
                  <td class="px-2 py-1.5">{{ $t('Total Payment') }}</td>
                  <td class="text-right">{{ $number(yearData.payment) }}</td>
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
          <div>{{ $number(Number(yearData.grand_total) - Number(yearData.cost)) }}</div>
        </div>
      </div>
    </Modal>
  </div>
</template>
