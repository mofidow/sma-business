<script setup>
import { PageSearch } from '@/Core/PageSearch';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Loading, Pagination } from '@/Components/Common';

defineOptions({ layout: AdminLayout });
defineProps(['pagination', 'supplier']);

const { filters, searching, sortBy } = PageSearch();
</script>

<template>
  <Head>
    <title>{{ $t('Supplier Statement') }}</title>
  </Head>
  <Header>
    {{ $t('Supplier Statement') }} ({{ supplier.company || supplier.name }})
    <template #subheading> {{ $t('Please review the data below') }} </template>
    <template #menu>
      <div class="flex items-center justify-center gap-4">
        <Link class="btn-primary" :href="route('suppliers.index')">
          {{ $t('List {x}', { x: $t('Suppliers') }) }}
        </Link>
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
                <th scope="col" class="text-focus py-3.5 ps-6 pe-3 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'created_at:asc' ? 'created_at:desc' : 'created_at:asc')"
                  >
                    {{ $t('Created at') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('created_at:')"
                      :name="filters.sort == 'created_at:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-start text-sm font-semibold">
                  <button
                    type="button"
                    class="flex items-center gap-2 whitespace-nowrap"
                    @click="sortBy(filters?.sort == 'description:asc' ? 'description:desc' : 'description:asc')"
                  >
                    {{ $t('Description') }}
                    <Icon
                      size="size-3 text-mute"
                      v-if="filters?.sort?.startsWith('description:')"
                      :name="filters.sort == 'description:desc' ? 'c-up' : 'c-down'"
                    />
                  </button>
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-end text-sm font-semibold whitespace-nowrap">
                  {{ $t('Debit') }}
                </th>
                <th scope="col" class="text-focus px-3 py-3.5 text-end text-sm font-semibold whitespace-nowrap">
                  {{ $t('Credit') }}
                </th>
                <th scope="col" class="text-focus py-3.5 ps-3 pe-6 text-end text-sm font-semibold whitespace-nowrap">
                  {{ $t('Balance') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
              <template v-if="pagination && pagination.data && pagination.data.length">
                <tr v-for="row in pagination.data" :key="row.id" :class="row.deleted_at ? 'bg-red-100 dark:bg-red-950' : ''">
                  <td class="py-4 ps-6 pe-3 text-sm whitespace-nowrap">
                    {{ $datetime(row.created_at) }}
                  </td>
                  <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="-my-2 line-clamp-2 min-w-64" v-html="row.description || ''"></div>
                  </td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap text-red-600 dark:text-red-400">
                    {{ row.value < 0 ? $currency(Math.abs(row.value)) : '-' }}
                  </td>
                  <td class="px-3 py-4 text-end text-sm whitespace-nowrap text-green-600 dark:text-green-400">
                    {{ row.value > 0 ? $currency(row.value) : '-' }}
                  </td>
                  <td
                    class="py-4 ps-3 pe-6 text-end text-sm font-semibold whitespace-nowrap"
                    :class="row.balance < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                  >
                    {{ $currency(row.balance) }}
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
