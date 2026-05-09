<script setup>
import { onMounted, ref } from 'vue';

const emit = defineEmits(['update:modelValue']);

const canvas = ref(null);
const isDrawing = ref(false);
const isEmpty = ref(true);
let ctx = null;
let lastX = 0;
let lastY = 0;

onMounted(() => {
  ctx = canvas.value.getContext('2d');
  ctx.strokeStyle = '#1a1a1a';
  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);
});

const resizeCanvas = () => {
  const rect = canvas.value.parentElement.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;
  canvas.value.width = rect.width * dpr;
  canvas.value.height = 160 * dpr;
  canvas.value.style.width = rect.width + 'px';
  canvas.value.style.height = '160px';
  ctx.scale(dpr, dpr);
  ctx.strokeStyle = '#1a1a1a';
  ctx.lineWidth = 2;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
};

const getPos = e => {
  const rect = canvas.value.getBoundingClientRect();
  if (e.touches) {
    return {
      x: e.touches[0].clientX - rect.left,
      y: e.touches[0].clientY - rect.top,
    };
  }
  return {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top,
  };
};

const startDraw = e => {
  e.preventDefault();
  isDrawing.value = true;
  const pos = getPos(e);
  lastX = pos.x;
  lastY = pos.y;
  ctx.beginPath();
  ctx.moveTo(lastX, lastY);
};

const draw = e => {
  if (!isDrawing.value) {
    return;
  }
  e.preventDefault();
  isEmpty.value = false;
  const pos = getPos(e);
  ctx.lineTo(pos.x, pos.y);
  ctx.stroke();
  lastX = pos.x;
  lastY = pos.y;
};

const stopDraw = e => {
  if (!isDrawing.value) {
    return;
  }
  e.preventDefault();
  isDrawing.value = false;
  ctx.closePath();
  if (!isEmpty.value) {
    emit('update:modelValue', canvas.value.toDataURL('image/png'));
  }
};

const clearSignature = () => {
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
  isEmpty.value = true;
  emit('update:modelValue', null);
};

defineExpose({ clearSignature, isEmpty });
</script>

<template>
  <div>
    <div class="relative overflow-hidden rounded-md border-2 border-dashed border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-50">
      <canvas
        ref="canvas"
        class="block touch-none"
        @mousedown="startDraw"
        @mousemove="draw"
        @mouseup="stopDraw"
        @mouseleave="stopDraw"
        @touchstart="startDraw"
        @touchmove="draw"
        @touchend="stopDraw"
      />
      <div v-if="isEmpty" class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-gray-400">
        <slot name="placeholder">{{ $t('Sign here') }}</slot>
      </div>
    </div>
    <div class="text-end">
      <button type="button" @click="clearSignature" class="mt-1 text-xs text-gray-500 underline hover:text-gray-700">
        <slot name="clear">{{ $t('Clear') }}</slot>
      </button>
    </div>
  </div>
</template>
