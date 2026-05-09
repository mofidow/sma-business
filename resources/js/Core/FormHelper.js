import { axios } from '@/Core';
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { notify } from 'notiwind';
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { route } from 'ziggy-js';
import { $can, calculate_item, check_promotions } from './helpers';

export const FormHelper = form => {
  let t;
  const page = usePage();
  const selectedItem = ref(null);
  const openItemModal = ref(false);
  const formName = ref(route ? route().current() + '.form' : '');

  watch(
    () => form.customer_id,
    () => {
      if (['pos.form', 'sales.create.form', 'quotations.create.form'].includes(formName.value)) {
        customerChanged();
      }
    }
  );

  onMounted(async () => {
    try {
      formName.value = route().current() + '.form';
    } catch (error) {
      console.log(error);
    }

    ({ t } = useI18n({}));
    if (formName.value.includes('.create') || formName.value.includes('pos')) {
      let saved_form = localStorage.getItem(formName.value);

      if (saved_form && dayjs().isSame(dayjs(saved_form._last_saved), 'day')) {
        saved_form = JSON.parse(saved_form);
        Object.keys(saved_form).map(k => {
          form[k] = saved_form[k];
        });
      }
    }

    syncSerials();
  });

  function resetForm() {
    localStorage.removeItem(formName.value);
    form.reset();
  }

  function saveForm(date) {
    if (formName.value.includes('.create') || formName.value.includes('pos')) {
      form.date_changed = date ? true : false;
      form._last_saved = dayjs().format('YYYY-MM-DD HH:mm:ss');
      localStorage.setItem(formName.value, JSON.stringify({ ...form, errors: {} }));
    }
  }

  function removeItem(item, force = false) {
    selectedItem.value = item?.product_id || selectedItem.value;

    if (!force && formName.value.includes('pos')) {
      if (page.props.settings.pin_code && (!$can('delete-orders') || !page.props.auth.user.roles.find(r => r.name == 'Super Admin'))) {
        page.props.pin_action = () => removeItem(item, true);
        page.props.ask_pin_code = true;
        return false;
      }

      if (!$can('delete-orders')) {
        notify({
          group: 'main',
          type: 'error',
          title: 'Error!',
          text: t('You do not have permission to delete order items.'),
        });
        return false;
      }
    }

    deleteSelectedItem();
  }

  function deleteSelectedItem() {
    form.items = form.items.filter(i => i.product_id != selectedItem.value);
    form.items = form.items.filter(i => i.promo_product_id != selectedItem.value);
    selectedItem.value = null;
    openItemModal.value = false;
    saveForm();
  }

  function selectItem(item) {
    selectedItem.value = { ...item };
    openItemModal.value = true;
  }

  async function updateItem() {
    const index = form.items.findIndex(i => i.product_id == selectedItem.value.product_id);
    form.items[index] = calculate_item({ ...selectedItem.value }, form.calculate_on);
    form.items = await check_promotions(form);
    selectedItem.value = null;
    openItemModal.value = false;
    saveForm();
  }

  async function customerChanged() {
    if (form.customer && (form.customer.customer_group || form.customer.price_group)) {
      // TODO: check price group

      if (form.customer.customer_group && form.customer.customer_group.discount) {
        form.items.map(product => {
          if (form.customer.customer_group.apply_as_discount == 1) {
            product.discount = form.customer.customer_group.discount + '%';
          } else {
            product.price = Number(product.product.price) - Number((product.product.price * form.customer.customer_group.discount) / 100);
          }
          selectedItem.value = product;
          updateItem();
        });
      }
    } else {
      form.items.map(product => {
        product.discount = 0;
        product.price = product.product.price;
        selectedItem.value = product;
        updateItem();
      });
    }
  }

  function syncSerials() {
    form.items?.forEach(item => {
      if (item.serials?.length && !item.available_serials?.length) {
        axios.post(route('search.serials'), { id: item.serials }).then(r => {
          item.available_serials = r.data;
        });
      }
    });
  }

  return { openItemModal, selectItem, selectedItem, saveForm, resetForm, removeItem, updateItem, customerChanged, syncSerials };
};
