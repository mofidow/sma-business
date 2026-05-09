<script setup>
import { useKeyboard } from './composable.js';
import { onMounted, ref, watch } from 'vue';

defineEmits(['change']);
const { state } = useKeyboard();
defineProps(['modelValue', 'element']);

const shift = ref(false);
const capslock = ref(false);
const input = ref(state.modelValue || '');

watch(
  () => state.element,
  newValue => {
    input.value = newValue.value || '';
  }
);

function cancel(e) {
  input.value = '';
  accept(e);
}

function accept() {
  dispatchEvents();
  state.element.blur();
  input.value = '';
  shift.value = false;
  capslock.value = false;
  changeState();

  const rightArrowKeyDownEvent = new KeyboardEvent('keydown', {
    key: 'ArrowRight',
    code: 'ArrowRight',
    keyCode: 39,
    which: 39,
    bubbles: true,
    cancelable: true,
  });
  state.element.dispatchEvent(rightArrowKeyDownEvent);
  state.show = false;
}

function arrowKeyPressed(key) {
  state.element.focus();
  const $keyCodes = { left: 37, up: 38, right: 39, down: 40 };
  const $codes = { left: 'ArrowLeft', up: 'ArrowUp', right: 'ArrowRight', down: 'ArrowDown' };
  const arrowKeyDownEvent = new KeyboardEvent('keydown', {
    key: $codes[key],
    code: $codes[key],
    keyCode: $keyCodes[key],
    which: $keyCodes[key],
    bubbles: true,
    cancelable: true,
  });
  state.element.dispatchEvent(arrowKeyDownEvent);
  dispatchFocusEvents();
}

function keyPressed(e) {
  state.element.focus();
  let value = e.target.innerText;

  if (value == ' ' || value == '&nbsp;' || e.target.classList.contains('space')) {
    value = ' ';
  }

  if (value == 'caps lock') {
    capslock.value = capslock.value === true ? false : true;
    shift.value = false;
    changeState();
    return false;
  }

  if (value == 'shift') {
    shift.value = shift.value === true ? false : true;
    capslock.value = false;
    changeState();
    return false;
  }

  if (value == 'delete') {
    input.value = input.value.slice(0, -1);
    // input.value = input.value.substr(0, input.value.length - 1);
    dispatchEvents();
    dispatchFocusEvents();
    return false;
  }

  if (value == 'tab') {
    value = '\t';
  }

  if (value == 'return') {
    value = '\n';
  }

  input.value += value;
  dispatchEvents();

  if (value == '\t') {
    const tabEvent = new KeyboardEvent('keydown', { key: 'Tab', code: 'Tab', keyCode: 9, which: 9, bubbles: true, cancelable: true });

    state.element.dispatchEvent(tabEvent);
  }

  if (value == '\n') {
    const enterKeyEvent = new KeyboardEvent('keypress', { key: 'Enter', code: 'Enter', keyCode: 13, which: 13, bubbles: true });
    state.element.dispatchEvent(enterKeyEvent);
  }

  if (shift.value === true) {
    shift.value = false;
    changeState();
  }

  dispatchFocusEvents();
}

function dispatchFocusEvents() {
  setTimeout(() => {
    const focusEvent = new Event('focus', {
      bubbles: true,
      cancelable: true,
    });
    state.element.dispatchEvent(focusEvent);
  }, 50);
}

function dispatchEvents() {
  state.modelValue = input.value;
  state.element.value = input.value;

  const inputEvent = new Event('input', {
    bubbles: true,
    cancelable: true,
  });
  state.element.dispatchEvent(inputEvent);

  const changeEvent = new Event('change', {
    bubbles: true,
    cancelable: true,
  });
  state.element.dispatchEvent(changeEvent);

  // dispatchFocusEvents();
}

function changeState() {
  if (shift.value === true || capslock.value === true) {
    document.querySelectorAll('.symbol').forEach(el => el.classList.add('pressed'));
    document.querySelectorAll('.letter').forEach(el => el.classList.add('uppercase'));
  } else {
    document.querySelectorAll('.symbol').forEach(el => el.classList.remove('pressed'));
    document.querySelectorAll('.letter').forEach(el => el.classList.remove('uppercase'));
  }
}

onMounted(() => {
  let offsetX, offsetY;
  let isDragging = false;
  const draggableHandle = document.getElementById('drag-handle');
  const draggableDiv = document.getElementById('keyboard-container');

  draggableDiv.addEventListener('mousedown', e => {
    isDragging = true;
    offsetX = e.clientX - draggableDiv.getBoundingClientRect().left;
    offsetY = e.clientY - draggableDiv.getBoundingClientRect().top;
    draggableHandle.style.cursor = 'grabbing';
  });

  document.addEventListener('mousemove', e => {
    if (!isDragging) return;
    draggableDiv.classList.remove('ltr:-translate-x-1/2 rtl:translate-x-1/2');
    draggableDiv.style.left = `${e.clientX - offsetX}px`;
    draggableDiv.style.top = `${e.clientY - offsetY}px`;
  });

  document.addEventListener('mouseup', () => {
    isDragging = false;
    draggableHandle.style.cursor = 'grab';
  });
});
</script>

<template>
  <Teleport to="body">
    <div
      id="keyboard-container"
      v-show="state.show && $page.props.settings.onscreen_keyboard == 1"
      class="fixed start-1/2 bottom-2 z-50 hidden h-64 max-w-3xl cursor-default items-center justify-center rounded-lg border border-gray-200 bg-gray-100 p-2.5 text-gray-700 lg:inline-flex ltr:-translate-x-1/2 rtl:translate-x-1/2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
    >
      <div id="keyboard" class="relative max-w-screen min-w-screen md:min-w-auto">
        <div
          id="drag-handle"
          class="absolute -top-7 -left-7 flex size-8 cursor-grab items-center justify-center rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="size-4"
          >
            <path d="M12 2v20" />
            <path d="m15 19-3 3-3-3" />
            <path d="m19 9 3 3-3 3" />
            <path d="M2 12h20" />
            <path d="m5 9-3 3 3 3" />
            <path d="m9 5 3-3 3 3" />
          </svg>
        </div>
        <ul
          class="flex items-center justify-center gap-1.5 pb-2 [&_li]:flex [&_li]:size-10 [&_li]:items-center [&_li]:justify-center [&_li]:rounded-md [&_li]:bg-white [&_li]:shadow dark:[&_li]:bg-gray-950 [&_li:hover]:relative [&_li:hover]:start-px [&_li:hover]:top-px [&_li:hover]:cursor-pointer"
        >
          <li @click="keyPressed" class="symbol"><span class="off">`</span><span class="on">~</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">1</span><span class="on">!</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">2</span><span class="on">@</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">3</span><span class="on">#</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">4</span><span class="on">$</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">5</span><span class="on">%</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">6</span><span class="on">^</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">7</span><span class="on">&amp;</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">8</span><span class="on">*</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">9</span><span class="on">(</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">0</span><span class="on">)</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">-</span><span class="on">_</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">=</span><span class="on">+</span></li>
          <li @click="keyPressed" class="delete">delete</li>
        </ul>

        <ul
          class="flex items-center justify-center gap-1.5 pb-2 [&_li]:flex [&_li]:size-10 [&_li]:items-center [&_li]:justify-center [&_li]:rounded-md [&_li]:bg-white [&_li]:shadow dark:[&_li]:bg-gray-950 [&_li:hover]:relative [&_li:hover]:start-px [&_li:hover]:top-px [&_li:hover]:cursor-pointer"
        >
          <li @click="keyPressed" class="tab">tab</li>
          <li @click="keyPressed" class="letter">q</li>
          <li @click="keyPressed" class="letter">w</li>
          <li @click="keyPressed" class="letter">e</li>
          <li @click="keyPressed" class="letter">r</li>
          <li @click="keyPressed" class="letter">t</li>
          <li @click="keyPressed" class="letter">y</li>
          <li @click="keyPressed" class="letter">u</li>
          <li @click="keyPressed" class="letter">i</li>
          <li @click="keyPressed" class="letter">o</li>
          <li @click="keyPressed" class="letter">p</li>
          <li @click="keyPressed" class="symbol"><span class="off">[</span><span class="on">{</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">]</span><span class="on">}</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">\</span><span class="on">|</span></li>
        </ul>

        <ul
          class="flex items-center justify-center gap-1.5 pb-2 [&_li]:flex [&_li]:size-10 [&_li]:items-center [&_li]:justify-center [&_li]:rounded-md [&_li]:bg-white [&_li]:shadow dark:[&_li]:bg-gray-950 [&_li:hover]:relative [&_li:hover]:start-px [&_li:hover]:top-px [&_li:hover]:cursor-pointer"
        >
          <li @click="keyPressed" class="capslock" :class="{ 'bg-primary-500! text-white': capslock }">caps lock</li>
          <li @click="keyPressed" class="letter">a</li>
          <li @click="keyPressed" class="letter">s</li>
          <li @click="keyPressed" class="letter">d</li>
          <li @click="keyPressed" class="letter">f</li>
          <li @click="keyPressed" class="letter">g</li>
          <li @click="keyPressed" class="letter">h</li>
          <li @click="keyPressed" class="letter">j</li>
          <li @click="keyPressed" class="letter">k</li>
          <li @click="keyPressed" class="letter">l</li>
          <li @click="keyPressed" class="symbol"><span class="off">;</span><span class="on">:</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">'</span><span class="on">&quot;</span></li>
          <li @click="keyPressed" class="return">return</li>
        </ul>

        <ul
          class="flex items-center justify-center gap-1.5 pb-2 [&_li]:flex [&_li]:size-10 [&_li]:items-center [&_li]:justify-center [&_li]:rounded-md [&_li]:bg-white [&_li]:shadow dark:[&_li]:bg-gray-950 [&_li:hover]:relative [&_li:hover]:start-px [&_li:hover]:top-px [&_li:hover]:cursor-pointer"
        >
          <li @click="keyPressed" class="left-shift" :class="{ 'bg-primary-500! text-white': shift }">shift</li>
          <li @click="keyPressed" class="letter">z</li>
          <li @click="keyPressed" class="letter">x</li>
          <li @click="keyPressed" class="letter">c</li>
          <li @click="keyPressed" class="letter">v</li>
          <li @click="keyPressed" class="letter">b</li>
          <li @click="keyPressed" class="letter">n</li>
          <li @click="keyPressed" class="letter">m</li>
          <li @click="keyPressed" class="symbol"><span class="off">,</span><span class="on">&lt;</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">.</span><span class="on">&gt;</span></li>
          <li @click="keyPressed" class="symbol"><span class="off">/</span><span class="on">?</span></li>
          <li @click="keyPressed" class="right-shift" :class="{ 'bg-primary-500! text-white': shift }">shift</li>
        </ul>

        <ul
          class="flex items-center justify-center gap-1.5 [&_li]:flex [&_li]:h-10 [&_li]:items-center [&_li]:justify-center [&_li]:rounded-md [&_li]:bg-white [&_li]:shadow dark:[&_li]:bg-gray-950 [&_li:hover]:relative [&_li:hover]:start-px [&_li:hover]:top-px [&_li:hover]:cursor-pointer"
        >
          <li @click="cancel" class="cancel size-10 bg-red-500! text-white!">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
            <span class="sr-only">{{ 'Cancel' }}</span>
          </li>
          <li @click="keyPressed" class="size-10">@</li>
          <li @click="keyPressed" class="size-10">.</li>
          <li @click="keyPressed" class="space flex-1">&nbsp;</li>
          <li @click="arrowKeyPressed('left')" class="size-10">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="size-4"
            >
              <path
                d="M13 9a1 1 0 0 1-1-1V5.061a1 1 0 0 0-1.811-.75l-6.835 6.836a1.207 1.207 0 0 0 0 1.707l6.835 6.835a1 1 0 0 0 1.811-.75V16a1 1 0 0 1 1-1h6a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1z"
              />
            </svg>
            <span class="sr-only">{{ 'LeftArrow' }}</span>
          </li>
          <li @click="arrowKeyPressed('up')" class="size-10">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="size-4"
            >
              <path
                d="M9 13a1 1 0 0 0-1-1H5.061a1 1 0 0 1-.75-1.811l6.836-6.835a1.207 1.207 0 0 1 1.707 0l6.835 6.835a1 1 0 0 1-.75 1.811H16a1 1 0 0 0-1 1v6a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1z"
              />
            </svg>
            <span class="sr-only">{{ 'UpArrow' }}</span>
          </li>
          <li @click="arrowKeyPressed('down')" class="size-10">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="size-4"
            >
              <path
                d="M15 11a1 1 0 0 0 1 1h2.939a1 1 0 0 1 .75 1.811l-6.835 6.836a1.207 1.207 0 0 1-1.707 0L4.31 13.81a1 1 0 0 1 .75-1.811H8a1 1 0 0 0 1-1V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1z"
              />
            </svg>
            <span class="sr-only">{{ 'DownArrow' }}</span>
          </li>
          <li @click="arrowKeyPressed('right')" class="size-10">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="size-4"
            >
              <path
                d="M11 9a1 1 0 0 0 1-1V5.061a1 1 0 0 1 1.811-.75l6.836 6.836a1.207 1.207 0 0 1 0 1.707l-6.836 6.835a1 1 0 0 1-1.811-.75V16a1 1 0 0 0-1-1H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1z"
              />
            </svg>
            <span class="sr-only">{{ 'RightArrow' }}</span>
          </li>
          <li @click="accept" class="accept w-20 bg-green-500! text-white!">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span class="sr-only">{{ 'Accept' }}</span>
          </li>
        </ul>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
#keyboard .tab,
#keyboard .delete {
  width: 70px;
}
#keyboard .capslock {
  width: 80px;
}
#keyboard .return {
  width: 77px;
}
#keyboard .left-shift {
  width: 95px;
}
#keyboard .right-shift {
  width: 109px;
}
.on {
  display: none;
}
.pressed .off {
  display: none;
}
.pressed .on {
  display: inline;
}
</style>
