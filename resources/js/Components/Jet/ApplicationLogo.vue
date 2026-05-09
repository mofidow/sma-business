<template>
  <img
    :alt="$page.props.settings.name || 'SMA'"
    :src="$page.props.settings.logo || '/img/sma.svg'"
    class="h-full max-h-16 min-h-5 max-w-52 dark:hidden"
  />
  <img
    :alt="$page.props.settings.name || 'SMA'"
    :src="$page.props.settings.logo_dark || '/img/sma-light.svg'"
    class="hidden h-full max-h-16 min-h-5 max-w-52 dark:block"
  />
</template>
