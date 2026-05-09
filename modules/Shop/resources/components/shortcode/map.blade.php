<div x-data="{ show: false, init() { setTimeout(() => { this.show = true; }, 500); } }" class="w-full flex items-center justify-center border dark:border-gray-700 rounded-md overflow-hidden"
  style="height:500px;">
  <x-shop::shared.loading-circle x-show="!show" />
  <iframe x-show="show" width="100%" height="500" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="display:none;"
    src="https://maps.google.com/maps?q={{ urlencode($address) }}&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
</div>
