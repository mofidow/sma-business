import { onBeforeUnmount, onMounted } from 'vue';

export default function useClickOutside(component, callback, excludeComponent, excludeElement) {
  if (!component) {
    throw new Error('A target component has to be provided.');
  }

  if (!callback) {
    throw new Error('A callback has to be provided.');
  }

  if (excludeElement) {
    var element = document.getElementById(excludeElement);
  }

  const listener = event => {
    if (
      event.target === component.value ||
      event.composedPath().includes(component.value) ||
      event.target === excludeComponent.value ||
      event.composedPath().includes(excludeComponent.value) ||
      (excludeElement && (event.target === element || event.composedPath().includes(element)))
    ) {
      return;
    }
    if (typeof callback === 'function') {
      callback();
    }
  };

  onMounted(() => {
    window.addEventListener('click', listener);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('click', listener);
  });
}
