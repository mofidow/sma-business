<script setup>
import { PageSearch } from '@/Core/PageSearch';
import { Dropdown } from '@/Components/Jet';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AutoComplete, Button, Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'product']);

const { filters, searching, searchNow } = PageSearch();
</script>

<template>
  <Head>
    <title>{{ $t('Product Tracks') }}</title>
  </Head>
  <Header>
    {{ $t('Product Tracks') }} ({{ product.name }})
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Button :href="route('products.index')">
          {{ $t('List {x}', { x: $t('Products') }) }}
        </Button>
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
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="text-focus w-16 py-3.5 ps-4 pe-3 text-start text-sm font-semibold whitespace-nowrap sm:ps-6 lg:ps-8">
                  {{ $t('Created at') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Description') }}
                </th>
                <th scope="col" class="text-focus w-24 px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Quantity') }}
                </th>
                <!-- <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-focus">
                  {{ $t('Variation') }}
                </th> -->
                <th v-if="!$page.props.selected_store" scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  {{ $t('Store') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id">
                  <td class="text-focus w-14 py-4 ps-4 pe-3 text-sm whitespace-nowrap sm:ps-6 lg:ps-8">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td class="px-3 py-4 text-sm font-semibold">
                    <div v-html="row.description" class="line-clamp-3"></div>
                  </td>
                  <td class="w-24 py-4 ps-3 pe-6 text-end text-sm whitespace-nowrap">{{ $number_qty(row.value) }}</td>
                  <!-- <td class="whitespace-nowrap px-3 py-4 text-sm">
                    {{ row.variation?.code || '' }}
                  </td> -->
                  <td v-if="!$page.props.selected_store" class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ row.store?.name || '' }}
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
  </div>
</template>
