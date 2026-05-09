import { useKeyboard } from './composable.js';

const { state } = useKeyboard();

const focusFn = event => {
  state.element = event.target;
  state.modelValue = event.target?.value;
  state.show = true;
};

const focusoutFn = () => {};

export const vKeyboard = {
  mounted(el) {
    el.addEventListener('focus', focusFn);
    el.addEventListener('focusout', focusoutFn);
  },

  unmounted(el) {
    el.removeEventListener('focus', focusFn);
    el.removeEventListener('focusout', focusoutFn);
  },
};
