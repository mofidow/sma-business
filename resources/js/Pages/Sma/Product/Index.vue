<script setup>
import { onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

import QuickView from './QuickView.vue';
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dropdown, DropdownLink, Modal } from '@/Components/Jet';
import { Actions, AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

const page = usePage();
defineOptions({ layout: AdminLayout });
const props = defineProps(['pagination', 'taxes', 'selected_store', 'stores']);

const view = ref(false);
const photo = ref(null);
const current = ref(null);
const deleted = ref(false);
const restored = ref(false);
const deleting = ref(false);
const { filters, searching, searchNow, sortBy } = PageSearch();

onMounted(() => {
  if (props.selected_store) {
    filters.value.store = page.props.filters.store || props.selected_store;
  }
});

function viewRow(row) {
  current.value = row;
  view.value = true;
}

function editRow(row) {
  router.visit(route('products.edit', { product: row.id }));
}

function deleteRow(row) {
  deleted.value = false;
  deleting.value = true;
  router.delete(route('products.destroy', row.id), {
    preserveScroll: true,
    onSuccess: () => (deleted.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}

async function restoreRow(row) {
  restored.value = false;
  deleting.value = true;
  router.put(route('products.restore', row.id), {
    preserveScroll: true,
    onSuccess: () => (restored.value = row.id),
    onFinish: () => (deleting.value = false),
  });
}
</script>

<template>
  <Head>
    <title>{{ $t('Products') }}</title>
  </Head>
  <Header>
    {{ $t('Products') }}
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button v-if="$can('create-products')" :href="route('products.create')">
          {{ $t('Add {x}', { x: $t('Product') }) }}
        </Button>
        <template v-if="$can(['import-products', 'export-products'])">
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
                <DropdownLink v-if="route().has('products.import') && $can('import-products')" :href="route('products.import')">
                  {{ $t('Import {x}', { x: $t('Products') }) }}
                </DropdownLink>
                <DropdownLink as="a" v-if="route().has('products.export') && $can('export-products')" :href="route('products.export')">
                  {{ $t('Export {x}', { x: $t('Products') }) }}
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
              <!-- Store -->
              <div class="mb-4">
                <AutoComplete
                  :json="true"
                  id="store_id"
                  :clearable="true"
                  :searchable="false"
                  :label="$t('Store')"
                  @change="searchNow"
                  :suggestions="stores"
                  v-model="filters.store"
                />
              </div>
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
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-center text-sm font-semibold sm:ps-6 lg:ps-8">
                  <span class="sr-only">{{ $t('Photo') }}</span>
                  <Icon name="photo" size="size-5 m-auto" />
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
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
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'code:asc' ? 'code:desc' : 'code:asc')"
                  >
                    {{ $t('Code') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('code:')"
                      :name="filters.sort == 'code:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'type:asc' ? 'type:desc' : 'type:asc')"
                  >
                    {{ $t('Type') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('type:')"
                      :name="filters.sort == 'type:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'brand.name:asc' ? 'brand.name:desc' : 'brand.name:asc')"
                  >
                    {{ $t('Brand') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('brand.name:')"
                      :name="filters.sort == 'brand.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'category.name:asc' ? 'category.name:desc' : 'category.name:asc')"
                  >
                    {{ $t('Category') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('category.name:')"
                      :name="filters.sort == 'category.name:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'supplier.company:asc' ? 'supplier.company:desc' : 'supplier.company:asc')"
                  >
                    {{ $t('Supplier') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('supplier.company:')"
                      :name="filters.sort == 'supplier.company:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Quantity') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Unit') }}
                </th>
                <th v-if="$can('show-cost')" scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'cost:asc' ? 'cost:desc' : 'cost:asc')"
                  >
                    {{ $t('Cost') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('cost:')"
                      :name="filters.sort == 'cost:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'price:asc' ? 'price:desc' : 'price:asc')"
                  >
                    {{ $t('Price') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('price:')"
                      :name="filters.sort == 'price:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">{{ $t('Taxes') }}</th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'rack_location:asc' ? 'rack_location:desc' : 'rack_location:asc')"
                  >
                    {{ $t('Rack Location') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('rack_location:')"
                      :name="filters.sort == 'rack_location:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="relative w-16 py-3.5 ps-3 pe-4 sm:pe-6 lg:pe-8">
                  <span class="sr-only">{{ $t('Actions') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm font-medium whitespace-nowrap sm:ps-6 lg:ps-8">
                    <button
                      type="button"
                      v-if="row.photo"
                      @click="photo = row.photo"
                      class="-my-4 flex h-8 w-8 items-center justify-center"
                    >
                      <img alt="" :src="row.photo" class="max-h-full max-w-full rounded-sm" />
                    </button>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm font-semibold whitespace-nowrap">{{ row.name }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.code }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.type }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.brand?.name || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.category?.name }}</td>
                  <td v-if="$can('show-cost')" @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.supplier?.company || row.supplier?.name || '' }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{
                      row.dont_track_stock || row.type != 'Standard'
                        ? ''
                        : $number_qty(
                            filters?.store
                              ? row.stocks?.find(s => s.store_id == filters.store)?.balance
                              : row.stocks?.reduce((a, s) => Number(s.balance) + a, 0)
                          )
                    }}
                  </td>
                  <td v-if="$can('show-cost')" @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.unit?.name || '' }}
                  </td>
                  <td v-if="$can('show-cost')" @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(row.cost) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-end text-sm whitespace-nowrap">
                    {{ $currency(selected_store ? row.stores?.find(s => s.id == selected_store)?.pivot?.price || row.price : row.price) }}
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">
                    {{
                      selected_store || filters.store
                        ? row.stores
                            ?.find(s => s.id == selected_store || filters.store)
                            ?.pivot?.taxes?.taxes?.map(t => t.name)
                            ?.join(', ') || row.taxes?.map(t => t.name)?.join(', ')
                        : row.taxes?.map(t => t.name)?.join(', ') || ''
                    }}
                  </td>
                  <!-- <td @click="viewRow(row)" class="cursor-pointer whitespace-nowrap px-3 py-4 text-sm w-16">
                    <span v-html="$boolean(row.tax_included, true)"></span>
                  </td> -->
                  <td @click="viewRow(row)" class="cursor-pointer px-3 py-4 text-sm whitespace-nowrap">{{ row.rack_location || '' }}</td>
                  <!-- <td @click="viewRow(row)" class="cursor-pointer whitespace-nowrap px-3 py-4 text-sm">{{ row.secondary_name || '' }}</td>
                  <td @click="viewRow(row)" class="cursor-pointer whitespace-nowrap px-3 py-4 text-sm w-16">
                    <span v-html="$boolean(row.featured, true)"></span>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer whitespace-nowrap px-3 py-4 text-sm w-16">
                    <span v-html="$boolean(row.hide_in_pos, true)"></span>
                  </td>
                  <td @click="viewRow(row)" class="cursor-pointer whitespace-nowrap px-3 py-4 text-sm w-16">
                    <span v-html="$boolean(row.hide_in_shop, true)"></span>
                  </td> -->
                  <td
                    :class="{ deleted: row.deleted_at }"
                    class="relative w-16 py-4 ps-3 pe-4 text-end text-sm font-medium whitespace-nowrap sm:pe-6 lg:pe-8"
                  >
                    <div class="text-mute flex items-center justify-end gap-4">
                      <Link
                        class="link"
                        :href="route('products.track', { product: row.id })"
                        v-if="!row.dont_track_stock && row.type == 'Standard'"
                      >
                        <Icon name="d-arrows" size="size-5" />
                      </Link>
                      <Actions
                        :row="row"
                        :editRow="editRow"
                        :deleted="deleted"
                        :restored="restored"
                        :deleting="deleting"
                        permission="products"
                        :deleteRow="deleteRow"
                        :restoreRow="restoreRow"
                        :record="$t('Product')"
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

    <Modal :show="view" max-width="3xl" @close="view = false">
      <QuickView :current="current" @close="view = false" :editRow="editRow" />
    </Modal>

    <Modal :show="photo" max-width="2xl" :transparent="true" @close="() => (photo = null)">
      <div class="flex items-center justify-center">
        <img alt="" :src="photo" class="h-full max-h-screen min-h-24 w-full max-w-full rounded-md" />
      </div>
    </Modal>
  </div>
</template>
