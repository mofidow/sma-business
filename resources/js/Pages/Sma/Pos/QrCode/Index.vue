<script setup>
import { ref, nextTick, onMounted } from 'vue';
import QRCode from 'qrcode';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/Common';
import { route } from 'ziggy-js';
import { axios } from '@/Core';

defineOptions({ layout: AdminLayout });
const props = defineProps({ halls: Array, app_name: String });

const generateQrCode = async (tableId, url) => {
  await nextTick();
  const canvas = document.getElementById('qr-' + tableId);
  if (canvas) {
    await QRCode.toCanvas(canvas, url, {
      width: 200,
      margin: 2,
      color: { dark: '#000000', light: '#ffffff' },
    });
  }
};

const generateAllQrCodes = async () => {
  for (const hall of props.halls) {
    for (const table of hall.tables) {
      await generateQrCode(table.id, table.menu_url);
    }
  }
};

onMounted(() => {
  generateAllQrCodes();
});

const printHall = async hall => {
  const qrImages = [];
  for (const table of hall.tables) {
    const dataUrl = await QRCode.toDataURL(table.menu_url, { width: 300, margin: 2 });
    qrImages.push({ table, dataUrl });
  }

  const cardsHtml = qrImages
    .map(
      ({ table, dataUrl }) => `
      <div class="card">
        <h3>${table.name}</h3>
        <p>${hall.name} &middot; ${table.seats} seats</p>
        <img src="${dataUrl}" alt="QR Code">
        <div class="scan-text">Scan to Order</div>
      </div>
    `
    )
    .join('');

  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>${props.app_name} - ${hall.name} QR Codes</title>
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .card { border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; text-align: center; page-break-inside: avoid; }
        .card h3 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .card p { font-size: 13px; color: #6b7280; margin-bottom: 12px; }
        .card img { max-width: 180px; margin: 0 auto 12px; display: block; }
        .card .scan-text { font-size: 14px; font-weight: 600; color: #2563eb; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { font-size: 22px; font-weight: 700; }
        .header p { font-size: 14px; color: #6b7280; }
        @media print { .grid { grid-template-columns: repeat(2, 1fr); } }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>${props.app_name}</h1>
        <p>${hall.name}</p>
      </div>
      <div class="grid">${cardsHtml}</div>
    </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 500);
};

const printTable = async table => {
  const hallName = props.halls.find(h => h.tables.some(t => t.id === table.id))?.name || '';
  const dataUrl = await QRCode.toDataURL(table.menu_url, { width: 300, margin: 2 });

  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>${props.app_name} - ${table.name}</title>
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { text-align: center; padding: 40px; }
        .card h2 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .card p { font-size: 14px; color: #6b7280; margin-bottom: 16px; }
        .card img { max-width: 300px; margin: 0 auto 16px; display: block; }
        .card .scan-text { font-size: 18px; font-weight: 600; color: #2563eb; }
      </style>
    </head>
    <body>
      <div class="card">
        <h2>${table.name}</h2>
        <p>${hallName} &middot; ${props.app_name}</p>
        <img src="${dataUrl}" alt="QR Code">
        <div class="scan-text">Scan to Order</div>
      </div>
    </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 500);
};

const regenerateToken = async table => {
  try {
    const { data } = await axios.post(route('pos.qr-codes.regenerate', table.id));
    if (data.success) {
      table.qr_token = data.qr_token;
      table.menu_url = data.menu_url;
      await generateQrCode(table.id, data.menu_url);
    }
  } catch (e) {
    console.error('Failed to regenerate QR code', e);
  }
};
</script>

<template>
  <Head>
    <title>{{ $t('QR Codes') }}</title>
  </Head>
  <Header class="print:hidden">
    {{ $t('QR Codes') }}
    <template #subheading> {{ $t('Generate and print QR codes for restaurant tables') }} </template>
  </Header>

  <div class="p-4">
    <div
      v-if="halls.length === 0"
      class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800"
    >
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="1.5"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
      </svg>
      <p class="mt-4 text-gray-500 dark:text-gray-400">
        {{ $t('No halls or tables found. Please create halls and tables first.') }}
      </p>
    </div>

    <div v-for="hall in halls" :key="hall.id" class="mb-6">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ hall.name }}</h2>
        <Button v-if="hall.tables.length > 0" @click="printHall(hall)" class="gap-2 print:hidden">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
            />
          </svg>
          {{ $t('Print All') }}
        </Button>
      </div>

      <div
        v-if="hall.tables.length === 0"
        class="rounded-lg border border-gray-200 bg-white p-6 text-center text-gray-400 dark:border-gray-700 dark:bg-gray-800"
      >
        {{ $t('No tables in this hall') }}
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <div
          v-for="table in hall.tables"
          :key="table.id"
          class="overflow-hidden rounded-xl border border-gray-200 bg-white text-center dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="rounded-t-xl border-b border-gray-100 px-4 py-3 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-gray-100">{{ table.name }}</h3>
            <p class="text-xs text-gray-500">{{ table.seats }} {{ $t('seats') }}</p>
          </div>

          <div class="flex items-center justify-center rounded-b-xl bg-white p-4">
            <canvas :id="'qr-' + table.id" class="rounded"></canvas>
          </div>

          <div
            class="flex items-center justify-center gap-2 rounded-b-xl border-t border-gray-100 px-4 py-3 dark:border-gray-700 print:hidden"
          >
            <button
              @click="printTable(table)"
              type="button"
              :title="$t('Print')"
              class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                />
              </svg>
              {{ $t('Print') }}
            </button>
            <button
              @click="regenerateToken(table)"
              type="button"
              :title="$t('Regenerate')"
              class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
              {{ $t('Regenerate') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
