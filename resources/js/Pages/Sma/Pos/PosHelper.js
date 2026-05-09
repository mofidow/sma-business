import { axios } from '@/Core';
import { useForm, usePage } from '@inertiajs/vue3';
import { default as daxios } from 'axios';
import dayjs from 'dayjs';
import debounce from 'lodash/debounce';
import { notify } from 'notiwind';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { route } from 'ziggy-js';

// import Input from '@/Components/TextInput.vue';
// import NumberInput from '@/Shared/NumberInput.vue';

import { FormHelper } from '@/Core/FormHelper';
import { VariationSelection } from '@/Core/VariationSelection';
import CustomerForm from '@/Pages/Sma/People/Customer/Form.vue';
import ViewCustomer from '@/Pages/Sma/People/Customer/View.vue';

import AddGiftCard from './Components/AddGiftCard.vue';
import FinalizeSale from './Components/FinalizeSale.vue';
import FormActions from './Components/FormActions.vue';
import FormItem from './Components/FormItem.vue';
import OpenOrder from './Components/OpenOrder.vue';
import OpenRegister from './Components/OpenRegister.vue';
import OrderDiscount from './Components/OrderDiscount.vue';
import OrderSummary from './Components/OrderSummary.vue';
import PrintBill from './Components/PrintBill.vue';
import PrintOrder from './Components/PrintOrder.vue';
import PrintReceipt from './Components/PrintReceipt.vue';
import RegisterDetails from './Components/RegisterDetails.vue';
import ShowOrders from './Components/ShowOrders.vue';
import ShowQrOrders from './Components/ShowQrOrders.vue';
import UpdateFormItem from './Components/UpdateFormItem.vue';

import { $extras, $random, calculate_item, check_promotions, searchItems, useClickOutside } from '@/Core';

export const PosHelper = props => {
  const page = usePage();
  const { t } = useI18n({});

  const grid = ref([]);
  const cId = ref(null);
  const search = ref('');
  // const orders = ref([]);
  const result = ref([]);
  const errors = ref({});
  // const reference = ref('');
  // const saving = ref(false);
  const receipt = ref(null);
  const loading = ref(false);
  const customer = ref(null);
  // const register = ref(null);
  const finalize = ref(false);
  const itemsHeight = ref(200);
  const showOrder = ref(false);
  const orderItems = ref(null);
  const printBill = ref(false);
  const printOrder = ref(false);
  const showOrders = ref(false);
  const loadingPage = ref(false);
  const orderActions = ref(null);
  const orderDetails = ref(null);
  const orderSummary = ref(null);
  // const selectedItem = ref(null);
  const showReceipt = ref(false);
  const delete_order = ref(false);
  const giftCardModal = ref(null);
  const orderCustomer = ref(null);
  const showCustomer = ref(false);
  const closeRegister = ref(false);
  const loadingOrders = ref(false);
  const menusContainer = ref(null);
  const open_register = ref(false);
  const orderContainer = ref(null);
  const showOrderItem = ref(false);
  const view_register = ref(false);
  const order_discount = ref(false);
  const showMobileMenu = ref(false);
  const closingRegister = ref(false);
  //   const loadingProducts = ref(false);
  const loadingRegister = ref(false);
  // const totalPaymentsAmount = ref(0);
  const addCustomerModal = ref(false);
  const mobileMenuContainer = ref(null);
  const showQrOrders = ref(false);
  const qrOrdersCount = ref(0);
  let qrPollInterval = null;
  // const order = ref({ customer_id: null, items: [] });

  const form = useForm({
    _method: 'post',
    calculate_on: 'price',

    items: [],
    date: dayjs().format('YYYY-MM-DD'),
    customer_id: props.customer?.id || page.props.settings.default_customer,
    customer: props.customer || null,

    details: null,
    due_date: null,
    reference: null,
    attachments: null,
    extra_attributes: props.custom_fields ? $extras(props.custom_fields) : [],
  });

  const { currentItem, deleteVariation, emptyVariation, variantModal, SelectVariant } = VariationSelection();
  const { openItemModal, selectItem, selectedItem, removeItem, resetForm, saveForm, updateItem, syncSerials } = FormHelper(form);

  watch(
    () => props.customer,
    customer => {
      if (customer) {
        form.customer = customer;
        form.customer_id = customer.id;
        saveForm();
      }
    }
  );
  watch(
    () => props.sale,
    sale => {
      if (sale) {
        resetForm();
        if (page.props.settings.auto_open_order == 1) {
          let details = {
            tendered: 0,
            discount: 0,
            reference: getNewReference(),
            user_id: page.props.auth.user.id,
            customer_id: page.props.settings.default_customer,
            payments: [{ amount: null, method: 'Cash', method_data: {} }],
            number: dayjs().format('YYMD') + '-' + String($random(0, 9999)).padStart(4, '0'),
          };
          let no = localStorage.getItem('pos_order_number');
          localStorage.setItem('pos_order_number', Number(no) + 1);
          openOrder(details);
        } else {
          form.reference = 'f';
        }
        receipt.value = sale;
        showReceipt.value = true;
      }
    }
  );
  useClickOutside(
    orderContainer,
    () => {
      if (page.props.settings.show_order_by_default != 1) {
        showOrder.value = false;
      }
    },
    menusContainer
  );
  useClickOutside(mobileMenuContainer, () => (showMobileMenu.value = false), menusContainer);

  let socket = null;
  let mediaQueryList = null;
  onMounted(async () => {
    let pond = localStorage.getItem('pos_order_number_date');
    if (!dayjs().isSame(pond, 'day')) {
      localStorage.setItem('pos_order_number', 1);
      localStorage.setItem('pos_order_number_date', dayjs().format('YYYY-MM-DD'));
    }

    if (page.props.settings.show_order_by_default == 1) {
      showOrder.value = true;
    }

    if (page.props.open_register) {
      open_register.value = true;
      await nextTick();
      setTimeout(() => {
        document.getElementById('register-cash-in-hand').focus();
        document.getElementById('register-cash-in-hand').select();
      }, 500);
    }

    if (page.props.opened_register && page.props.opened_register.store_id != page.props.selected_store) {
      notify(
        {
          group: 'main',
          type: 'error',
          title: 'Error!',
          text: t('You have already opened register at another store.!'),
        },
        10000
      );
      open_register.value = true;
    }

    grid.value = props.products ? [...props.products] : [];
    cId.value = page.props.settings?.default_category;
    window.addEventListener('resize', orderItemsHeight);

    //   if (props.customer) {
    //     form.customer_id = props.customer.id;
    //     saveForm();
    //   }

    let saved_order = localStorage.getItem('pos.form');
    if (saved_order) {
      let order = JSON.parse(saved_order);
      if (!order.store_id || order.store_id == page.props.selected_store) {
        Object.keys(order).forEach(k => (form[k] = order[k]));
      }
    }

    if (props.customer) {
      form.customer = props.customer;
      form.customer_id = props.customer.id;
      saveForm();
    }

    if (!form.number) {
      form.customer_id = page.props.settings.default_customer;
      form.number = dayjs().format('YYMD') + '-' + String($random(0, 9999)).padStart(4, '0');
      saveForm();
    }
    setTimeout(() => (orderItems.value.scrollTop = orderItems.value.scrollHeight), 250);
    if (!form.reference || form.reference == 'f') {
      await nextTick();
      document.getElementById('order-reference')?.focus();
      document.getElementById('order-reference')?.select();
    }

    if (props.sale) {
      // resetForm();
      form.reference = 'f';
      receipt.value = props.sale;
      showReceipt.value = true;
    }

    if (props.order) {
      loadOrder(props.order);
    }

    await nextTick();
    orderItemsHeight();
    document.getElementById('product-search')?.focus();

    if (window.matchMedia) {
      mediaQueryList = window.matchMedia('print');
      mediaQueryList.addEventListener('change', e => {
        if (e.matches) {
          beforePrint();
        } else {
          afterPrint();
        }
      });
    }
    window.onafterprint = afterPrint;
    window.onbeforeprint = beforePrint;

    if (page.props.settings?.pos_server == 1) {
      connectPPS();
    }

    if (page.props.settings?.restaurant == 1) {
      pollQrOrders();
      qrPollInterval = setInterval(pollQrOrders, 30000);
    }
  });

  onBeforeUnmount(() => {
    window.removeEventListener('resize', orderItemsHeight);
    if (form.reference && form.reference != 'f' && form.number) {
      holdOrder();
      saveForm();
    }

    if (qrPollInterval) {
      clearInterval(qrPollInterval);
    }

    if (mediaQueryList) {
      mediaQueryList.removeEventListener('change', e => {
        if (e.matches) {
          beforePrint();
        } else {
          afterPrint();
        }
      });
    }

    if (socket) {
      socket.close();
    }
  });

  function beforePrint() {
    // console.log('beforePrint');
  }
  function afterPrint() {
    if (page.props.settings?.print_dialog == 1) {
      printOrder.value = false;
      printBill.value = false;
      showReceipt.value = false;
    }
  }

  function connectPPS() {
    if (!socket || socket.readyState === WebSocket.CLOSED) {
      socket = new WebSocket('ws://localhost:6441');

      socket.onopen = () => {
        console.log('WebSocket connection opened.');
      };

      socket.onmessage = event => {
        let data = {};
        try {
          data = JSON.parse(event.data);
        } catch {}

        if (data.type && data.type == 'error') {
          notify({ group: 'main', type: data.type, title: data.message });
        } else if (event.data != 'Connected to POS Print Server') {
          notify({ group: 'main', type: 'success', title: 'Success!', text: event.data });
        }
      };

      socket.onerror = error => {
        console.error('WebSocket error:', error);
      };

      socket.onclose = () => {
        notify({
          group: 'main',
          type: 'error',
          title: 'Error!',
          text: t('Connection closed, please make sure POS server is running.'),
        });
      };
    }
  }

  function sendToPPS(data) {
    if (!socket) {
      connectPPS();
    }

    if (socket && socket.readyState === WebSocket.OPEN) {
      socket.send(JSON.stringify(data));
    }
  }

  function openOrder(details) {
    if (details && details.reference) {
      Object.keys(details).map(k => (form[k] = details[k]));
      saveForm();
    } else {
      form.reset();
      form.number = null;
      form.reference = 'f';
    }
    orderItemsHeight();
  }

  function playErrorSound() {
    const audio = new Audio('/sounds/error.mp3');
    audio.play();
  }

  function playSuccessSound() {
    const audio = new Audio('/sounds/success.mp3');
    audio.play();
  }

  function orderItemsHeight() {
    // if (form.reference && form.reference != 'f' && form.number) {
    itemsHeight.value =
      (orderContainer.value?.clientHeight || 0) -
      ((orderDetails.value?.clientHeight || 0) +
        (orderCustomer.value?.clientHeight || 0) +
        (orderSummary.value?.clientHeight || 0) +
        (orderActions.value?.clientHeight || 0)) +
      8;
    orderItems.value.scrollTop = orderItems.value.scrollHeight;
    // }
  }

  function getNewReference() {
    let no = localStorage.getItem('pos_order_number');
    if (!no) {
      no = 1;
      localStorage.setItem('pos_order_number', no);
    }

    return 'Order ' + dayjs().format('M.D') + '.' + (no < 10 ? '00' + no : no < 100 ? '0' + no : no).toString();
  }

  async function holdOrder(e, dont_hide = false) {
    if (page.props.opened_register && form.reference && form.reference != 'f' && form.number && form.items?.length) {
      loadingPage.value = true;
      form.total_items = form.items.length;
      form.total = form.items.reduce((a, i) => Number(i.total) + a, 0);
      form.total_quantity = form.items.reduce((a, i) => Number(i.quantity) + a, 0);
      await axios
        .post(route('pos.orders.store'), form)
        .then(() => {
          if (dont_hide) {
            loadingPage.value = false;
            return;
          }
          //   form = {};
          form.reset();
          form.items = [];
          if (page.props.settings.auto_open_order == 1 && !page.props.settings?.restaurant == 1) {
            form.tendered = 0;
            form.discount = 0;
            form.reference = getNewReference();
            form.user_id = page.props.auth.user.id;
            form.customer_id = page.props.settings.default_customer;
            form.payments = [{ amount: null, method: 'Cash', method_data: {} }];

            let no = localStorage.getItem('pos_order_number');
            localStorage.setItem('pos_order_number', Number(no) + 1);
          } else {
            form.hall_id = '';
            form.table_id = '';
            form.reference = '';
            form.reference_number = '';
          }

          form.customer_id = page.props.settings.default_customer;
          form.number = dayjs().format('YYMD') + '-' + String($random(0, 9999)).padStart(4, '0');
          saveForm();
          loadingPage.value = false;
          document.getElementById('order-reference')?.focus();
          document.getElementById('order-reference')?.select();
          notify({
            group: 'main',
            type: 'success',
            title: 'Success!',
            text: t('The order has been saved!'),
          });
        })
        .catch(() => {
          loadingPage.value = false;
        });
    }
  }

  async function saveOrder(xalert = false) {
    // loadingPage.value = true;
    await axios
      .post(route('pos.orders.store'), form)
      .then(() => {
        if (xalert) {
          notify({
            group: 'main',
            type: 'success',
            title: 'Success!',
            text: t('The order has been saved!'),
          });
        }
      })
      .catch(() => {
        // loadingPage.value = false;
      });
  }

  async function showPaymentModal() {
    let hasItem = form.items && form.items.length;
    if (hasItem) {
      saveForm();
      holdOrder({}, true);
    }
    finalize.value = true;
    if (hasItem) {
      await nextTick();
      if (document.getElementById('tender-amount')) {
        document.getElementById('tender-amount').focus();
        document.getElementById('tender-amount').select();
      }
    }
  }

  function print() {
    window.print();
  }

  async function loadOrder(selected) {
    if (form && form.reference && form.number && form.items && form.items.length) {
      await holdOrder();
    }
    showOrders.value = false;
    loadingPage.value = true;

    if (!selected.store_id || selected.store_id == page.props.selected_store) {
      Object.keys(selected.data).map(k => (form[k] = selected.data[k]));
      form.hall_id = selected.hall_id;
      form.table = selected.table;
      form.store_id = selected.store_id;
      form.order_id = selected.id;
      saveForm();
      syncSerials();
    } else {
      form.reset();
      form.number = null;
      form.reference = 'f';

      open_register.value = true;
    }
    loadingPage.value = false;
    await nextTick();
    orderItemsHeight();
  }

  async function showOrderDiscount() {
    order_discount.value = true;
    await nextTick();
    document.getElementById('order-discount')?.focus();
  }

  async function applyOrderDiscount(discount) {
    if (!discount.includes('%')) {
      discount += '%';
    }
    form.items = form.items.map(p => {
      let max_discount = page.props.settings?.max_discount || null;
      if (Number(discount.replace('%', '')) > Number(p.product.max_discount)) {
        p.discount = Number(p.product.max_discount) + '%';
        if (p.variations && p.variations.length) {
          p.variations = p.variations.map(v => {
            v.discount = p.discount;
            return v;
          });
        }
        notify({
          group: 'main',
          type: 'error',
          title: t('You cannot apply discount more than {x}%', { x: Number(p.product.max_discount) }),
        });
      } else if (Number(discount.replace('%', '')) > Number(max_discount)) {
        p.discount = Number(max_discount) + '%';
        if (p.variations && p.variations.length) {
          p.variations = p.variations.map(v => {
            v.discount = p.discount;
            return v;
          });
        }
        notify({
          group: 'main',
          type: 'error',
          title: t('You cannot apply discount more than {x}%', { x: Number(max_discount) }),
        });
      } else {
        p.discount = discount;
        if (p.variations && p.variations.length) {
          p.variations = p.variations.map(v => {
            v.discount = p.discount;
            return v;
          });
        }
      }

      return calculate_item(p);
    });

    saveForm();
    order_discount.value = false;
  }

  async function updatedOrderItem(item) {
    selectedItem.value = { ...item };
    updateItem();
    openItemModal.value = false;
  }

  async function showOrderDetails() {
    showOrder.value = true;
    await nextTick();
    orderItemsHeight();
  }

  const searchProducts = debounce(async (query, exact = false) => {
    if (query) {
      result.value = await searchItems(query, 'sale', exact);

      if (result.value.length == 1) {
        addItem(result.value[0]);
      }
    }
  }, 300);

  async function addItem(item) {
    if (form.reference == 'f') {
      form.reference = '';
      await nextTick();
      document.getElementById('order-reference')?.focus();
      document.getElementById('order-reference')?.select();
      return false;
    }

    if (form.items.find(i => i.product_id == item.id)) {
      form.items = form.items.map(i => {
        if (i.product_id == item.id) {
          if (i.product.has_variants == 1) {
            i.variations = [...i.variations, emptyVariation(i.product)];
            currentItem.value = i;
            variantModal.value = true;
          } else {
            i.quantity++;
          }
        }
        return i;
      });
    } else {
      let product = await axios.get(route('products.show', { product: item.id, with: 'promotions' })).then(r => r.data);

      // Set store price and taxes
      const selected_data = product.stores?.find(s => s.id == page.props.selected_store);
      product.price = selected_data?.pivot?.price ?? product.price;
      product.taxes = selected_data?.pivot?.taxes?.length ? selected_data?.pivot.taxes : product.taxes;

      if (form.customer && (form.customer.customer_group || form.customer.price_group)) {
        // check price group

        if (form.customer.customer_group && form.customer.customer_group.discount) {
          if (form.customer.customer_group.apply_as_discount == 1) {
            product.discount = form.customer.customer_group.discount + '%';
          } else {
            product.price = Number(product.price) - Number((product.price * form.customer.customer_group.discount) / 100);
          }
        }
      }

      currentItem.value = calculate_item({
        ...item,
        id: null,
        quantity: 1,
        cost: Number(product.cost),
        price: Number(product.price),
        taxes: product.taxes.map(t => t.id),
        tax_included: product.tax_included == 1,
        product_id: product.id,
        unit_id: product.unit_id,
        discount: product.discount || 0,
        serials: [],
        available_serials: [],
        product,
      });

      if (product.has_variants == 1) {
        currentItem.value.variations = [emptyVariation(product)];
        variantModal.value = true;
      }
      if (product.has_serials == 1) {
        await axios
          .post(route('search.serials'), {
            product_id: product.id,
            limit: currentItem.value.quantity,
          })
          .then(({ data }) => (currentItem.value.available_serials = data));
        currentItem.value.serials = currentItem.value.available_serials?.map(s => s.id);
      }
      form.items = [...form.items, { ...currentItem.value }];
    }
    //   if (currentItem.value.product.has_variants == 1) {
    //     variantModal.value = true;
    //   }

    form.items = await check_promotions(form);

    saveForm();
    await nextTick();
    search.value = '';
    orderItemsHeight();
    holdOrder({}, true);
    if (!variantModal.value && page.props.settings?.play_sound == 1) {
      playSuccessSound();
    } else if (variantModal.value) {
      playErrorSound();
    }
    document.getElementById('product-search')?.focus();
    notify({ group: 'mobile', type: 'success', text: t('{x} added to order.', { x: item.name }) }, 2000);
    if (page.props.settings.show_order_by_default == 1) {
      showOrder.value = true;
      await nextTick();
      orderItemsHeight();
      document.getElementById('product-search-order')?.focus();
    }
  }

  async function addProduct(p) {
    if (form.reference == 'f') {
      form.reference = '';
      await nextTick();
      document.getElementById('order-reference')?.focus();
      document.getElementById('order-reference')?.select();
      return false;
    }
    searchProducts(p.code, true);
  }

  async function quantityChanged(p) {
    if (p.quantity < 0) {
      p.quantity = 0;
      return false;
    }

    selectedItem.value = p;
    updateItem(p);
  }

  async function deleteOrder(id) {
    // if (!force) {
    //   if (page.props.settings.pin_code && (!$can('delete-orders') || !page.props.auth.user.roles.find(r => r.name == 'Super Admin'))) {
    //     page.props.pin_action = () => deleteOrder(id, true);
    //     page.props.ask_pin_code = true;
    //     return false;
    //   }
    //   if (!$can('delete-orders')) {
    //     notify({
    //       group: 'main',
    //       type: 'error',
    //       title: 'Error!',
    //       text: t('You do not have permission to delete orders.'),
    //     });
    //     return false;
    //   }
    // }

    loading.value = true;
    await daxios
      .delete(route('pos.orders.destroy', { order: id }))
      .then(res => {
        if (res.data.success) {
          showOrders.value = false;
          delete_order.value = false;
          // resetForm();
          notify({
            group: 'main',
            type: 'success',
            title: 'Success!',
            text: t('The order has been deleted!'),
          });
        }
      })
      .catch(err => {
        if (err.response && err.response.status == 403) {
          notify({
            group: 'main',
            type: 'error',
            title: 'Error!',
            text: err.response.data?.message || t('You do not have permission to delete orders.'),
          });
        }
      })
      .finally(() => {
        if (id == form.order_id || id == form.number || id == form.reference) {
          form.reset();
          form.items = [];
          form.number = null;
          form.reference = 'f';
          showOrders.value = false;
          delete_order.value = false;
          saveForm();
        }
        loading.value = false;
      });
  }

  async function pollQrOrders() {
    try {
      const res = await axios.get(route('pos.qr-orders.count'));
      const newCount = res.data.count;
      if (newCount > qrOrdersCount.value && qrOrdersCount.value !== null) {
        playSuccessSound();
        notify(
          {
            group: 'main',
            type: 'success',
            title: t('New QR Order!'),
            text: t('{x} pending QR order(s)', { x: newCount }),
          },
          5000
        );
      }
      qrOrdersCount.value = newCount;
    } catch {}
  }

  function loadQrOrder(order) {
    if (order && order.data) {
      loadOrder(order);
    }
    showQrOrders.value = false;
  }

  function getProducts(category, force = false) {
    if (cId.value != category || force) {
      cId.value = category;
      loading.value = true;
      axios
        .get(route('pos.products', { category }))
        .then(res => (grid.value = res.data))
        .catch()
        .finally(() => (loading.value = false));
    }
  }

  async function showCustomerDetails() {
    if (customer.value?.id != form.customer_id) {
      axios
        .get(route('customers.show', form.customer_id))
        .then(res => (customer.value = res.data))
        .catch();
    }
    showCustomer.value = true;
  }

  async function addCustomer() {
    addCustomerModal.value = true;
    await nextTick();
    document.getElementById('customer-name')?.focus();
  }

  function handleSubmit(e) {
    form.errors = {};
    if (e && e.number) {
      Object.keys(e).map(k => (form[k] = e[k]));
    }

    form
      .transform(data => {
        let form = { ...data };
        form.hall_id = e.hall_id;
        form.table_id = e.table_id;
        form.payments = e.payments;
        form.tendered = e.tendered;
        form.order_number = e.number;
        form.order_reference = e.reference;
        form.change_returned = e.change_returned;
        form.reference_number = e.reference_number;
        form.reference = null;

        form.items = form.items.map(i => ({
          id: i.id,
          price: i.price,
          taxes: i.taxes,
          comment: i.comment,
          quantity: i.quantity,
          unit_id: i.unit_id,
          discount: i.discount,
          product_id: i.product_id,
          old_quantity: i.old_quantity,
          cost: isNaN(Number(i.cost)) ? null : Number(i.cost),
          serials: i.serials && i.serials.length ? i.serials : null,
          type: i.type,
          expiry_date: i.expiry_date,
          customer_id: i.customer_id,
          variations:
            i.variations && i.variations.length
              ? i.variations.map(v => ({
                  id: v.id,
                  cost: v.cost,
                  price: v.price,
                  taxes: v.taxes,
                  unit_id: v.unit_id,
                  discount: v.discount,
                  quantity: Number(v.quantity),
                  old_quantity: v.old_quantity,
                }))
              : null,
        }));

        try {
          form.date = form.date ? dayjs(form.date).format('YYYY-MM-DD') : null;
          // form.date = form.date.toString().includes('T') ? form.date.toString().split('T')[0] : form.date;
        } catch {}
        try {
          form.due_date = form.due_date ? dayjs(form.due_date).format('YYYY-MM-DD') : null;
        } catch {}
        return form;
      })
      .post(route('sales.store', { to_pos: 1 }), {
        forceFormData: true,
        onSuccess: page => {
          form.reset();
          form.items = [];
          form.number = null;
          form.reference = 'f';
          finalize.value = false;

          if (page.props.settings.auto_open_order == 1) {
            let details = {
              tendered: 0,
              discount: 0,
              reference: getNewReference(),
              user_id: page.props.auth.user.id,
              customer_id: page.props.settings.default_customer,
              payments: [{ amount: null, method: 'Cash', method_data: {} }],
              number: dayjs().format('YYMD') + '-' + String($random(0, 9999)).padStart(4, '0'),
            };
            let no = localStorage.getItem('pos_order_number');
            localStorage.setItem('pos_order_number', Number(no) + 1);
            openOrder(details);
            getProducts(cId.value, true);
          }

          if (page.props.flash.sale) {
            showReceiptToPrint(page.props.flash.sale);
          }
        },
      });
  }

  return {
    grid,
    cId,
    search,
    result,
    errors,
    receipt,
    loading,
    customer,
    finalize,
    itemsHeight,
    showOrder,
    orderItems,
    printBill,
    printOrder,
    showOrders,
    showQrOrders,
    qrOrdersCount,
    loadingPage,
    orderActions,
    orderDetails,
    orderSummary,
    showReceipt,
    delete_order,
    giftCardModal,
    orderCustomer,
    showCustomer,
    closeRegister,
    loadingOrders,
    menusContainer,
    open_register,
    orderContainer,
    showOrderItem,
    view_register,
    order_discount,
    showMobileMenu,
    closingRegister,
    loadingRegister,
    addCustomerModal,
    mobileMenuContainer,
    form,
    currentItem,
    deleteVariation,
    emptyVariation,
    variantModal,
    SelectVariant,
    openItemModal,
    selectItem,
    selectedItem,
    saveForm,
    resetForm,
    removeItem,
    updateItem,
    openOrder,
    orderItemsHeight,
    holdOrder,
    saveOrder,
    showPaymentModal,
    print,
    loadOrder,
    showOrderDiscount,
    applyOrderDiscount,
    updatedOrderItem,
    showOrderDetails,
    searchProducts,
    addItem,
    addProduct,
    quantityChanged,
    deleteOrder,
    loadQrOrder,
    getProducts,
    showCustomerDetails,
    addCustomer,
    handleSubmit,

    CustomerForm,
    ViewCustomer,
    FormItem,
    OpenOrder,
    PrintBill,
    PrintOrder,
    ShowOrders,
    ShowQrOrders,
    AddGiftCard,
    FormActions,
    FinalizeSale,
    OpenRegister,
    OrderSummary,
    PrintReceipt,
    OrderDiscount,
    UpdateFormItem,
    RegisterDetails,
    playSuccessSound,
    playErrorSound,
    connectPPS,
    sendToPPS,
  };
};
