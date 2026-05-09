<template>
  <img
    :alt="$page.props.settings.short_name || 'SMA'"
    :src="$page.props.settings.icon || '/img/sma-icon.svg'"
    class="h-full max-h-16 min-h-5 dark:hidden"
  />
  <img
    :alt="$page.props.settings.short_name || 'SMA'"
    :src="$page.props.settings.icon_dark || '/img/sma-icon-light.svg'"
    class="hidden h-full max-h-16 min-h-5 dark:block"
  />
</template>
