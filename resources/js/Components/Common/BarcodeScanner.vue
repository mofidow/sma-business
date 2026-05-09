<script setup>
import { useI18n } from 'vue-i18n';
import { ref, watch, onUnmounted, nextTick } from 'vue';
import { BrowserMultiFormatReader } from '@zxing/browser';
import { BarcodeFormat, DecodeHintType, NotFoundException } from '@zxing/library';
import { Modal } from '@/Components/Jet';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['scanned', 'close']);

const { t } = useI18n({});

const video = ref(null);
const error = ref(null);
const timedOut = ref(false);
const cameras = ref([]);
const selectedCamera = ref(null);
const scanning = ref(false);
const timeLeft = ref(30);

const SCAN_TIMEOUT = 30; // seconds

let reader = null;
let controls = null;
let timeoutHandle = null;
let countdownInterval = null;
// Flag to prevent the selectedCamera watcher from double-starting during initial load
let skipCameraWatch = false;

// Limit to 1D formats used by the app + TRY_HARDER for reliable mobile scanning
const hints = new Map();
hints.set(DecodeHintType.POSSIBLE_FORMATS, [
  BarcodeFormat.CODE_128,
  BarcodeFormat.CODE_39,
  BarcodeFormat.EAN_8,
  BarcodeFormat.EAN_13,
  BarcodeFormat.UPC_A,
  BarcodeFormat.UPC_E,
]);
hints.set(DecodeHintType.TRY_HARDER, true);

const clearScanTimeout = () => {
  if (timeoutHandle !== null) {
    clearTimeout(timeoutHandle);
    timeoutHandle = null;
  }
  if (countdownInterval !== null) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
};

const startScanTimeout = () => {
  clearScanTimeout();
  timedOut.value = false;
  timeLeft.value = SCAN_TIMEOUT;

  countdownInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value -= 1;
    }
  }, 1000);

  timeoutHandle = setTimeout(() => {
    clearInterval(countdownInterval);
    countdownInterval = null;
    timedOut.value = true;
    stopScanning();
  }, SCAN_TIMEOUT * 1000);
};

const stopScanning = () => {
  clearScanTimeout();
  if (controls) {
    try {
      controls.stop();
    } catch (_) {}
    controls = null;
  }
  if (video.value && video.value.srcObject) {
    video.value.srcObject.getTracks().forEach(track => track.stop());
    video.value.srcObject = null;
  }
  scanning.value = false;
};

const startScanning = async cameraId => {
  stopScanning();
  error.value = null;
  timedOut.value = false;
  scanning.value = true;

  if (!video.value) {
    error.value = t('Camera could not be initialized. Please try again.');
    scanning.value = false;
    return;
  }

  // Low resolution + capped framerate to reduce CPU load and speed up decode
  const constraints = {
    audio: false,
    video: {
      ...(cameraId ? { deviceId: { exact: cameraId } } : { facingMode: 'environment' }),
      width: { ideal: 640, max: 640 },
      height: { ideal: 480, max: 480 },
      frameRate: { ideal: 15, max: 20 },
    },
  };

  try {
    reader = new BrowserMultiFormatReader(hints);
    controls = await reader.decodeFromConstraints(constraints, video.value, (result, err) => {
      if (result) {
        const code = result.getText();
        stopScanning();
        emit('scanned', code);
        emit('close');
        return;
      }
      if (err && !(err instanceof NotFoundException)) {
        // Unexpected decode error — surface it so the user knows
        error.value = t('Scanner error. Please close and try again.');
        stopScanning();
      }
      // NotFoundException is thrown every frame when no barcode is visible — ignore it
    });
    scanning.value = true;
    startScanTimeout();
  } catch (e) {
    scanning.value = false;
    if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
      error.value = t('Camera permission denied. Please allow camera access and try again.');
    } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
      error.value = t('No camera found on this device.');
    } else {
      error.value = t('Could not start camera: :message', { message: e.message });
    }
  }
};

const retry = () => {
  timedOut.value = false;
  error.value = null;
  startScanning(selectedCamera.value);
};

const loadCameras = async () => {
  try {
    // Request permission so device labels are populated, then release the stream immediately
    // to avoid blocking decodeFromConstraints from opening the same camera.
    const permStream = await navigator.mediaDevices.getUserMedia({ video: true });
    permStream.getTracks().forEach(track => track.stop());

    const devices = await BrowserMultiFormatReader.listVideoInputDevices();
    cameras.value = devices;

    // Set flag so selectedCamera watcher does not double-start during initial load
    skipCameraWatch = true;
    const rear = devices.find(d => /back|rear|environment/i.test(d.label));
    selectedCamera.value = rear ? rear.deviceId : (devices[0]?.deviceId ?? null);
    skipCameraWatch = false;
  } catch (e) {
    if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
      error.value = t('Camera permission denied. Please allow camera access and try again.');
    } else {
      error.value = t('No camera found on this device.');
    }
  }
};

watch(
  () => props.show,
  async visible => {
    if (visible) {
      error.value = null;
      timedOut.value = false;
      await loadCameras();
      if (!error.value) {
        await nextTick();
        startScanning(selectedCamera.value);
      }
    } else {
      stopScanning();
      cameras.value = [];
      selectedCamera.value = null;
      error.value = null;
      timedOut.value = false;
    }
  }
);

// Re-start scanning only when the user manually picks a different camera from the dropdown
watch(selectedCamera, id => {
  if (skipCameraWatch) return;
  if (id && props.show) {
    startScanning(id);
  }
});

onUnmounted(() => {
  stopScanning();
});

const close = () => {
  stopScanning();
  emit('close');
};
</script>

<template>
  <Modal :show="show" max-width="md" @close="close">
    <div class="p-4">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ t('Scan Barcode') }}</h3>
      </div>

      <!-- Camera selector -->
      <div v-if="cameras.length > 1" class="mb-3">
        <select
          v-model="selectedCamera"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-blue-300 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
        >
          <option v-for="cam in cameras" :key="cam.deviceId" :value="cam.deviceId">
            {{ cam.label || t('Camera :n', { n: cameras.indexOf(cam) + 1 }) }}
          </option>
        </select>
      </div>

      <!-- Error state -->
      <div v-if="error" class="rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
        {{ error }}
      </div>

      <!-- Timeout state -->
      <div v-else-if="timedOut" class="rounded-md bg-yellow-50 p-6 text-center dark:bg-yellow-900/30">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="mx-auto mb-3 h-10 w-10 text-yellow-500"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
          />
        </svg>
        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ t('No barcode detected') }}</p>
        <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">{{ t('Make sure the barcode is well-lit and fully visible.') }}</p>
        <button
          type="button"
          @click="retry"
          class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          {{ t('Try Again') }}
        </button>
      </div>

      <!-- Video preview -->
      <div v-else class="relative aspect-video overflow-hidden rounded-md bg-black">
        <video ref="video" class="h-full w-full object-cover" autoplay muted playsinline />
        <!-- Scanning guide overlay -->
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
          <div class="relative h-1/3 w-2/3 rounded border-2 border-blue-400">
            <span class="absolute -top-5 right-0 left-0 text-center text-xs text-blue-300">
              {{ scanning ? t('Point camera at barcode') : t('Starting camera...') }}
            </span>
            <!-- Corner accents -->
            <div class="absolute -top-px -left-px h-4 w-4 rounded-tl border-t-4 border-l-4 border-blue-400"></div>
            <div class="absolute -top-px -right-px h-4 w-4 rounded-tr border-t-4 border-r-4 border-blue-400"></div>
            <div class="absolute -bottom-px -left-px h-4 w-4 rounded-bl border-b-4 border-l-4 border-blue-400"></div>
            <div class="absolute -right-px -bottom-px h-4 w-4 rounded-br border-r-4 border-b-4 border-blue-400"></div>
          </div>
        </div>
        <!-- Countdown progress bar -->
        <div class="absolute right-0 bottom-0 left-0 h-1 bg-white/20">
          <div class="h-full bg-blue-400 transition-all duration-1000 ease-linear" :style="{ width: (timeLeft / 30) * 100 + '%' }"></div>
        </div>
      </div>

      <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">{{ t('Barcode will be detected automatically') }}</p>
    </div>
  </Modal>
</template>
