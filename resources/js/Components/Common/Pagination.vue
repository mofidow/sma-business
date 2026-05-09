<template>
  <div v-if="meta && meta.total > 0">
    <div class="hidden items-center justify-between xl:flex">
      <div class="me-4 py-1.5">
        {{
          $t('Showing from {from} to {to} of total {total} records', {
            from: Math.ceil(meta.from / (half ? 2 : 1)),
            to: Math.ceil(meta.to / (half ? 2 : 1)),
            total: Math.ceil(meta.total / (half ? 2 : 1)),
          })
        }}
      </div>

      <div v-if="meta.last_page > 1" class="flex flex-wrap gap-1">
        <template v-for="(link, i) in meta.links" :key="'link_' + i">
          <template v-if="link.url === null">
            <div
              class="cursor-default rounded-md border border-gray-200 bg-white px-4 py-3 text-sm leading-4 text-gray-700 opacity-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
              v-html="
                link.label.replace('Next', '').replace('Previous', '').replace('&amp;laquo;', '&lang;').replace('&amp;raquo;', '&rang;')
              "
            />
          </template>
          <template v-else>
            <Link
              :href="link.url"
              :class="
                link.active ? 'border-primary-700 bg-primary-600 text-white' : 'bg-white text-gray-700 dark:bg-gray-950 dark:text-gray-300'
              "
              class="inline-block rounded-md border border-gray-200 px-4 py-3 text-sm leading-4 whitespace-nowrap hover:border-primary-700 hover:bg-primary-700 hover:text-white focus:border-primary-600 dark:border-gray-700"
            >
              <span
                v-html="
                  link.label.replace('Next', '').replace('Previous', '').replace('&amp;laquo;', '&lang;').replace('&amp;raquo;', '&rang;')
                "
              ></span>
            </Link>
          </template>
        </template>
      </div>
    </div>
    <div class="flex items-center justify-between xl:hidden">
      <Link
        v-if="links.prev"
        :href="links.prev"
        class="inline-block rounded-md border border-gray-200 px-4 py-3 text-sm leading-4 whitespace-nowrap dark:border-gray-700 print:hidden"
        :class="
          links.prev
            ? 'bg-white text-gray-700 hover:border-primary-700 hover:bg-primary-700 hover:text-white focus:border-primary-600 dark:bg-gray-950 dark:text-gray-300'
            : 'cursor-default bg-gray-100 dark:bg-gray-950'
        "
      >
        &Lang; <span class="ms-1 hidden sm:inline-block">{{ $t('Previous') }}</span>
      </Link>
      <button
        v-else
        type="button"
        class="inline-block cursor-default rounded-md border border-gray-200 bg-gray-100 px-4 py-3 text-sm leading-4 whitespace-nowrap opacity-25 dark:border-gray-700 dark:bg-gray-950 print:hidden"
      >
        &Lang; <span class="ms-1 hidden sm:inline-block">{{ $t('Previous') }}</span>
      </button>
      <div class="mx-3">
        {{
          $t('Showing from {from} to {to} of total {total} records', {
            from: Math.ceil(meta.from / (half ? 2 : 1)),
            to: Math.ceil(meta.to / (half ? 2 : 1)),
            total: Math.ceil(meta.total / (half ? 2 : 1)),
          })
        }}
      </div>
      <Link
        v-if="links.next"
        :href="links.next"
        class="inline-block rounded-md border border-gray-200 px-4 py-3 text-sm leading-4 whitespace-nowrap dark:border-gray-700 print:hidden"
        :class="
          links.next
            ? 'bg-white text-gray-700 hover:border-primary-700 hover:bg-primary-700 hover:text-white focus:border-primary-600 dark:bg-gray-950 dark:text-gray-300'
            : 'cursor-default bg-gray-100'
        "
      >
        <span class="me-1 hidden sm:inline-block">{{ $t('Next') }}</span> &Rang;
      </Link>
      <button
        v-else
        type="button"
        class="inline-block cursor-default rounded-md border border-gray-200 bg-gray-100 px-4 py-3 text-sm leading-4 whitespace-nowrap opacity-25 dark:border-gray-700 dark:bg-gray-950 print:hidden"
      >
        <span class="me-1 hidden sm:inline-block">{{ $t('Next') }}</span> &Rang;
      </button>
    </div>
  </div>
</template>

<script setup>
// import Button from './Button.vue';
// import { computed } from 'vue';
defineProps(['meta', 'links', 'half']);

// const pages = computed(() => {
//   if (props.meta.last_page > 9) {
//     const previous = props.links.shift();
//     const next = props.links.pop();
//     let links = props.links.slice(
//       props.meta.current_page > 4 ? props.meta.current_page - 4 : 0,
//       props.meta.last_page > props.meta.current_page + 3 ? props.meta.current_page + 3 : props.meta.last_page
//     );
//     links.unshift(previous);
//     links.push(next);
//     if (props.meta.current_page > 4) {
//       // console.log(props.meta.links[0]);
//       links.unshift({ ...props.meta.links[0], label: '&Lang;' });
//     }
//     if (props.meta.last_page > props.meta.current_page + 3) {
//       // console.log(props.meta.links[props.meta.links.length - 1]);
//       links.push({ ...props.meta.links[props.meta.links.length - 1], label: '&Rang;' });
//     }
//     return links;
//   }
//   return props.meta.links;
// });
</script>
