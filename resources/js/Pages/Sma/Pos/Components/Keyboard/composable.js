import { reactive } from 'vue';

const state = reactive({
  show: false,
  element: null,
  modelValue: false,
});
export const useKeyboard = () => {
  const setState = state => {
    state.show = state.show;
    state.element = state.element;
    state.modelValue = state.modelValue;
  };

  return {
    state,
    setState,
  };
};
