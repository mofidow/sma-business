<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

import Form from './Form.vue';
import View from './View.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserForm from '@/Pages/Sma/People/User/Form.vue';
import PaymentForm from '@/Pages/Sma/Order/Payment/Form.vue';
import { Dropdown, DropdownLink, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'countries', 'customer_groups', 'price_groups', 'custom_fields', 'payment_fields']);

const add = ref(false);
const user = ref(null);
const view = ref(false);
const current = ref(null);
const payment = ref(false);
const deleted = ref(false);
const deleting = ref(false);
const add_user = ref(false);
const show_users = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

onMounted(() => {
  const query_params = new Proxy(new URLSearchParams(window.location.search), {
    get: (searchParams, prop) => searchParams.get(prop),
  });
  if (query_params.create == 1) {
    add.value = true;
  }
});

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function showPaymentModal(row) {
  current.value = row;
  payment.value = true;
}

function editRow(row) {
  current.value = row;
  add.value = true;
}

function deleteRow(row) {
  deleting.value = true;
  router.delete(route('customers.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}

function hideForm() {
  if (!view.value) {
    current.value = null;
  }
  add.value = false;
}

function showUsers(row) {
  current.value = row;
  show_users.value = true;
}

function editUser(row) {
  user.value = row;
  add_user.value = true;
}
</script>

<template>
  <Head>
    <title>{{ $t('Customers') }}</title>
  </Head>
  <Header>
    {{ $t('Customers') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-customers')" type="button" @click="add = true">
          {{ $t('Add {x}', { x: $t('Customer') }) }}
        </Button>
        <template v-if="$can(['import-customers', 'export-customers'])">
          <Dropdown align="right" width="40" :auto-close="false">
            <template #trigger>
              <button class="-m-2 flex items-center rounded-md p-2.5 transition duration-150 ease-in-out">
                <Icon name="v-arrows" size="size-6" />
                <span class="sr-only">{{ $t('Import/Export') }}</span>
              </button>
            </template>

            <template #content>
              <div>
                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">{{ $t('Import/Export') }}</div>
                <DropdownLink v-if="route().has('customers.import') && $can('import-customers')" :href="route('customers.import')">
                  {{ $t('Import {x}', { x: $t('Customers') }) }}
                </DropdownLink>
                <DropdownLink as="a" v-if="route().has('customers.export') && $can('export-customers')" :href="route('customers.export')">
                  {{ $t('Export {x}', { x: $t('Customers') }) }}
                </DropdownLink>
              </div>
            </template>
          </Dropdown>
        </template>
        <Dropdown align="right" width="56" :auto-close="false">
          <template #trigger>
            <button class="-m-2 flex items-center rounded-md p-2.5 transition duration-150 ease-in-out">
              <Icon name="funnel-o" size="size-5" />
              <span class="sr-only">{{ $t('Show Filters') }}</span>
            </button>
          </template>

          <template #content>
            <div class="px-4 py-2">
              <div>
                <AutoComplete
                  :json="true"
                  @change="searchNow"
                  :label="$t('Trashed')"
                  v-model="filters.trashed"
                  :placeholder="$t('With Trashed')"
                  :suggestions="[
                    { value: 'not', label: $t('Not Trashed') },
                    { value: 'with', label: $t('With Trashed') },
                    { value: 'only', label: $t('Only Trashed') },
                  ]"
                />
              </div>
            </div>
          </template>
        </Dropdown>
      </div>
    </template>
  </Header>

  <div class="relative flex grow flex-col items-stretch justify-stretch self-stretch bg-white px-4 sm:px-6 lg:px-8 dark:bg-gray-800">
    <Loading v-if="searching" circle-size="w-10 h-10" />
    <div class="flow-root grow">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="my-2 inline-block min-w-full border-b border-gray-200 align-middle dark:border-gray-700">
          <table class="fixed-actions min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold whitespace-nowrap sm:ps-6 lg:ps-8">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'company:asc' ? 'company:desc' : 'company:asc')"
                  >
                    {{ $t('Company') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('company:')"
                      :name="filters.sort == 'company:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'name:asc' ? 'name:desc' : 'name:asc')"
                  >
                    {{ $t('Name') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('name:')"
                      :name="filters.sort == 'name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'phone:asc' ? 'phone:desc' : 'phone:asc')"
                  >
                    {{ $t('Phone') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('phone:')"
                      :name="filters.sort == 'phone:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'email:asc' ? 'email:desc' : 'email:asc')"
                  >
                    {{ $t('Email') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('email:')"
                      :name="filters.sort == 'email:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold whitespace-nowrap">
                  {{ $t('Points') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="mx-auto flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'balance:asc' ? 'balance:desc' : 'balance:asc')"
                  >
                    {{ $t('Due') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('balance:')"
                      :name="filters.sort == 'balance:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-center text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'due_limit:asc' ? 'due_limit:desc' : 'due_limit:asc')"
                  >
                    {{ $t('Due Limit') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('due_limit:')"
                      :name="filters.sort == 'due_limit:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'customer_group.name:asc' ? 'customer_group.name:desc' : 'customer_group.name:asc')"
                  >
                    {{ $t('Customer Group') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('customer_group.name:')"
                      :name="filters.sort == 'customer_group.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'price_group.name:asc' ? 'price_group.name:desc' : 'price_group.name:asc')"
                  >
                    {{ $t('Price Group') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('price_group.name:')"
                      :name="filters.sort == 'price_group.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Address') }}
                </th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td @click="viewRow(row)" class="cursor-pointer py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.company || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.name }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.phone || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.email || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $number(row.points, null, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }}
                  </td>
                  <td v-if="$can('create-payments')" class="px-3 py-4 text-end text-sm whitespace-nowrap">
                    <button class="link" type="button" @click="showPaymentModal(row)" v-if="$page.props.selected_store">
                      <template v-if="row.balance < 0">{{ $t('Adv') }}: {{ $currency(0 - row.balance) }}</template>
                      <template v-else>{{ $currency(row.balance) }}</template>
                    </button>
                    <template v-else>
                      <template v-if="row.balance < 0">{{ $t('Adv') }}: {{ $currency(0 - row.balance) }}</template>
                      <template v-else>{{ $currency(row.balance) }}</template>
                    </template>
                  </td>
                  <td v-else @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    <template v-if="row.balance < 0">{{ $t('Adv') }}: {{ $currency(0 - row.balance) }}</template>
                    <template v-else>{{ $currency(row.balance) }}</template>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ row.due_limit ? $number(row.due_limit) : '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.customer_group?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.price_group?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm">
                    <div class="-my-2 line-clamp-2 max-w-xs min-w-64">
                      {{ $address(row) }}
                    </div>
                  </td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <div class="text-mute flex items-center gap-4">
                      <Link :href="route('customers.statement', row.id)" class="link">
                        <Icon name="statement" class="size-5" />
                      </Link>
                      <button type="button" @click="showUsers(row)" class="link">
                        <Icon name="users" class="size-5" />
                      </button>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :deleting="deleting"
                        :deleteRow="deleteRow"
                        permission="customers"
                        :record="$t('Customer')"
                      />
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="100%">
                  <div class="text-mute py-3.5 ps-4 pe-3 text-sm font-light whitespace-nowrap sm:ps-2 lg:ps-4">
                    {{ $t('There is no data to display!') }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
      <Pagination class="mx-4 mt-auto py-2 text-sm sm:mx-6" :meta="pagination.meta" :links="pagination.links" />
    </div>

    <Modal :show="view" :backdrop="false" max-width="3xl" :closeable="true" @close="view = false">
      <View :current="current" @close="view = false" :editRow="editRow" />
    </Modal>

    <Modal :show="add" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="hideForm">
      <Form
        @done="hideForm"
        @close="hideForm"
        :current="current"
        :countries="countries"
        :price_groups="price_groups"
        :custom_fields="custom_fields"
        :customer_groups="customer_groups"
      />
    </Modal>

    <Modal :show="payment" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="payment = false">
      <PaymentForm :current="null" :customer="current" :custom_fields="payment_fields" @close="payment = false" @done="payment = false" />
    </Modal>

    <Modal :show="show_users" :backdrop="false" :overflow="true" max-width="3xl" :closeable="true" @close="show_users = false">
      <div>
        <span class="absolute end-12 top-5 inline-flex items-center sm:end-14">
          <button type="button" @click="add_user = true" class="link -m-2 flex items-center gap-1 rounded-md border px-1 py-0.5">
            <Icon name="add" class="size-5" /> {{ $t('Add {x}', { x: $t('User') }) }}
          </button>
        </span>
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <div class="sm:flex sm:items-baseline sm:justify-between">
            <div class="sm:w-0 sm:flex-1">
              <h1 class="text-focus text-base font-semibold">
                {{ $t('List {x}', { x: $t('Users') }) }}
                {{ current ? ' (' + current.name + ')' : '' }}
              </h1>
              <p class="text-mute mt-1 truncate text-sm">
                {{ $t('Please view the customer users below.') }}
              </p>
            </div>
          </div>
        </div>
        <div class="overflow-x-auto pb-6">
          <div v-if="!current.users?.length" class="px-6 pt-6">
            <p class="text-mute">{{ $t('No users found for this customer.') }}</p>
          </div>
          <table v-else class="fixed-actions min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus py-3.5 ps-4 pe-3 text-start text-sm font-semibold whitespace-nowrap sm:ps-6 lg:ps-8">
                  {{ $t('Name') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Phone') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Username') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Email') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Active') }}
                </th>

                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Impersonate') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold whitespace-nowrap">
                  {{ $t('Created at') }}
                </th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="current.users?.length">
                <tr v-for="row in current.users" :key="row.id">
                  <td class="py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ row.name }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.phone || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.username || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">{{ row.email || '' }}</td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center justify-center" v-html="$boolean(row.active, true)" />
                  </td>

                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center justify-center" v-html="$boolean(row.can_be_impersonated, true)" />
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8">
                    <div class="text-mute flex items-center gap-4">
                      <Link :href="route('impersonate', row.id)" class="link">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="size-5"
                        >
                          <path d="m16 11 2 2 4-4" />
                          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                          <circle cx="9" cy="7" r="4" />
                        </svg>
                      </Link>
                      <button type="button" @click="editUser(row)" class="link">
                        <Icon name="edit" class="size-5" />
                      </button>
                      <Actions :row="row" permission="users" :deleting="deleting" :record="$t('User')" :deleteRow="deleteRow" />
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </Modal>

    <Modal :show="add_user" :backdrop="false" :overflow="true" max-width="2xl" :closeable="true" @close="add_user = false">
      <UserForm :current="user" :customer="current" @done="add_user = false" @close="add_user = false" :custom_fields="custom_fields" />
    </Modal>
  </div>
</template>
