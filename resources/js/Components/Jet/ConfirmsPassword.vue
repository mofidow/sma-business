<script setup>
import { ref, reactive, nextTick } from 'vue';
import DialogModal from './DialogModal.vue';
import InputError from './InputError.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import TextInput from './TextInput.vue';

const emit = defineEmits(['confirmed']);

defineProps({
  title: { type: String },
  content: { type: String },
  button: { type: String },
});

const confirmingPassword = ref(false);

const form = reactive({
  password: '',
  error: '',
  processing: false,
});

const passwordInput = ref(null);

const startConfirmingPassword = () => {
  axios.get(route('password.confirmation')).then(response => {
    if (response.data.confirmed) {
      emit('confirmed');
    } else {
      confirmingPassword.value = true;

      setTimeout(() => passwordInput.value.focus(), 250);
    }
  });
};

const confirmPassword = () => {
  form.processing = true;

  axios
    .post(route('password.confirm'), {
      password: form.password,
    })
    .then(() => {
      form.processing = false;

      closeModal();
      nextTick().then(() => emit('confirmed'));
    })
    .catch(error => {
      form.processing = false;
      form.error = error.response.data.errors.password[0];
      passwordInput.value.focus();
    });
};

const closeModal = () => {
  confirmingPassword.value = false;
  form.password = '';
  form.error = '';
};
</script>

<template>
  <span>
    <span @click="startConfirmingPassword">
      <slot />
    </span>

    <DialogModal :show="confirmingPassword" @close="closeModal" maxWidth="md">
      <template #title>
        {{ title || $t('Confirm Password') }}
      </template>

      <template #content>
        {{ content || $t('For your security, please confirm your password to continue.') }}

        <div class="mt-4">
          <TextInput
            type="password"
            ref="passwordInput"
            placeholder="Password"
            v-model="form.password"
            class="mt-1 block w-full"
            autocomplete="current-password"
            @keyup.enter="confirmPassword"
          />

          <InputError :message="form.error" class="mt-2" />
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> {{ $t('Cancel') }} </SecondaryButton>

        <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="confirmPassword">
          {{ button || $t('Confirm') }}
        </PrimaryButton>
      </template>
    </DialogModal>
  </span>
</template>
