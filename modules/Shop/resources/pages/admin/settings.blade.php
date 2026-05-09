<div class="mx-auto max-w-7xl sm:px-6 py-8 sm:py-16 lg:px-8" x-data="{
    errors: {},
    form: {{ json_encode($settings) }},
    selectedSlide: 0,
    imageFieldName: null,
    selectedFooterSection: 0,
    addSlide() {
        if (this.form.shop_slider.length < 8) {
            this.form.shop_slider.push({ image: '', bg_image: '', heading: '', description: '', button_text: '', button_link: '' });
        }
    },
    removeSlider() {
        if (this.form.shop_slider.length > 1) {
            this.form.shop_slider.splice(this.selectedSlide, 1);
            this.selectedSlide = Math.max(0, this.selectedSlide - 1);
        }
    },
    addFooterSection() {
        if (this.form.shop_footer.sections.length < 4) {
            this.form.shop_footer.sections.push({ title: '', menus: [] });
            this.selectedFooterSection = this.form.shop_footer.sections.length - 1;
        }
    },
    removeFooterSection() {
        if (this.form.shop_footer.sections.length > 1) {
            this.form.shop_footer.sections.splice(this.selectedFooterSection, 1);
            this.selectedFooterSection = Math.max(0, this.selectedFooterSection - 1);
        }
    },
    errorMessage(key) {
        return this.errors[key] ? this.errors[key].join(', ').replace(key, key.split('.').slice(-1)) : '';
    },
    saveSettings() {
        {{-- console.log('Saving settings:', this.form); --}}
        this.$wire.settings = this.form;
        this.errors = [];
        this.$wire.save().then(res => {
            if (res && res.original.success === false) {
                this.$dispatch('notify', { content: res.original.message ? res.original.message.replace('settings.general.', '').replace('settings.seo.', '').replace('settings.shop cta.', '').replace('settings.pages menu.', '').replace('settings.shop_footer.', '') : 'Failed to save settings.', type: 'error' });
                this.errors = res.original.errors || {}
            }
            window.scrollTo(0, 0);
        }).catch(error => {
            console.error('Error saving settings:', error);
            this.$dispatch('notify', { content: '{{ __('Failed to save settings!') }}', type: 'error' });
        });
    },
    async uploadFile(event, fieldName) {
        this.imageFieldName = fieldName;
        var fileX = event.target.files[0];
        {{-- console.log('Uploading file:', fileX, this.selectedSlide, fieldName, this.imageFieldName); --}}
        @this.upload('currentFile', fileX, (uploadedFilename) => {
            this.$wire.uploadFile();
        });
    },
}"
  @uploaded.window="(e) => {
    {{-- console.log('EV File uploaded:', e.detail ? e.detail[0] : null); --}}
    if (e.detail && e.detail[0]) {
        if (imageFieldName == 'slider_image') {
            form.shop_slider[selectedSlide].image = e.detail[0].url;
        } else if (imageFieldName == 'slider_bg_image') {
            form.shop_slider[selectedSlide].bg_image = e.detail[0].url;
        } else if (imageFieldName == 'cta_bg_image') {
            form.shop_cta.bg_image = e.detail[0].url;
        }
    }
}">
  <form @submit.prevent="saveSettings">
    <x-shop::jet.form-section>
      <x-slot name="title">{{ __('Shop Settings') }}</x-slot>
      <x-slot name="description">{{ __('Please update setting as you desire') }}</x-slot>

      <x-slot name="form">
        <div class="col-span-full text-xs text-red-600" x-show="Object.keys(errors).length > 0">
          <div x-html="Object.entries(errors).map(([key, value]) => `<div>${value}</div>`).join('')" class="font-mono whitespace-pre-wrap">
          </div>
        </div>

        <div class="col-span-full flex items-end justify-between">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">{{ __('General Settings') }}</x-slot>
            <x-slot name="description">{{ __('Please update setting for shop') }}</x-slot>
          </x-shop::jet.section-title>
        </div>

        <!-- Shop Name -->
        <div class="col-span-full sm:col-span-3">
          <label for="shop_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Name') }}
          </label>
          <input type="text" class="input" id="shop_name" x-model="form.general.name" />
          <span class="text-sm text-red-600" x-show="errors['settings.general.name']" x-text="errorMessage('settings.general.name')"></span>
        </div>

        <!-- Store-->
        <div class="col-span-full sm:col-span-3">
          <label for="shop_store" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Store') }}
          </label>
          <x-shop::jet.select class="input" id="shop_store" x-model="form.general.store_id">
            <option value="">{{ __('Select Store') }}</option>
            @foreach ($stores as $store)
              <option value="{{ $store->id }}" {{ $store->id == ($settings['general']['store_id'] ?? null) ? 'selected' : '' }}>
                {{ $store->name }}
              </option>
            @endforeach
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.store_id']"
            x-text="errorMessage('settings.general.store_id')"></span>
        </div>

        <!-- Phone -->
        <div class="col-span-full sm:col-span-3">
          <label for="shop_phone" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Phone') }}
          </label>
          <input type="text" class="input" id="shop_phone" x-model="form.general.phone" />
          <span class="text-sm text-red-600" x-show="errors['settings.general.phone']"
            x-text="errorMessage('settings.general.phone')"></span>
        </div>

        <!-- Email -->
        <div class="col-span-full sm:col-span-3">
          <label for="shop_email" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Email') }}
          </label>
          <input type="text" class="input" id="shop_email" x-model="form.general.email" />
          <span class="text-sm text-red-600" x-show="errors['settings.general.email']"
            x-text="errorMessage('settings.general.email')"></span>
        </div>

        <!-- Shop Mode -->
        <div class="col-span-full sm:col-span-3">
          <label for="shop_mode" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Shop Mode') }}
          </label>
          <x-shop::jet.select class="input" id="shop_mode" x-model="form.general.shop_mode">
            <option value="">{{ __('Select Shop Mode') }}</option>
            <option value="public">{{ __('Public') }}</option>
            <option value="private">{{ __('Private') }}</option>
            <option value="maintenance">{{ __('Maintenance') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.shop_mode']"
            x-text="errorMessage('settings.general.shop_mode')"></span>
        </div>

        <!-- Hide Price -->
        <div class="col-span-full sm:col-span-3">
          <label for="hide_price" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Hide Price') }}
          </label>
          <x-shop::jet.select class="input" id="hide_price" x-model="form.general.hide_price">
            <option value="0">{{ __('No') }}</option>
            <option value="1">{{ __('Yes') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.hide_price']"
            x-text="errorMessage('settings.general.hide_price')"></span>
        </div>

        <!-- Disable Cart -->
        <div class="col-span-full sm:col-span-3">
          <label for="disable_cart" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Disable Cart') }}
          </label>
          <x-shop::jet.select class="input" id="disable_cart" x-model="form.general.disable_cart">
            <option value="0">{{ __('No') }}</option>
            <option value="1">{{ __('Yes') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.disable_cart']"
            x-text="errorMessage('settings.general.disable_cart')"></span>
        </div>

        <!-- Guest Checkout -->
        <div class="col-span-full sm:col-span-3">
          <label for="guest_checkout" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Guest Checkout') }}
          </label>
          <x-shop::jet.select class="input" id="guest_checkout" x-model="form.general.guest_checkout">
            <option value="0">{{ __('No') }}</option>
            <option value="1">{{ __('Yes') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.guest_checkout']"
            x-text="errorMessage('settings.general.guest_checkout')"></span>
        </div>

        <!-- Products Per Page -->
        <div class="col-span-full sm:col-span-3">
          <label for="products_per_page" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Products Per Page') }}
          </label>
          <input type="number" class="input" id="products_per_page" x-model="form.general.products_per_page" min="10"
            max="50" />
          <span class="text-sm text-red-600" x-show="errors['settings.general.products_per_page']"
            x-text="errorMessage('settings.general.products_per_page')"></span>
        </div>

        <!-- Max Unpaid Orders -->
        <div class="col-span-full sm:col-span-3">
          <label for="max_unpaid_orders" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Max Unpaid Orders') }}
          </label>
          <input type="number" class="input" id="max_unpaid_orders" x-model="form.general.max_unpaid_orders" min="1"
            max="10" />
          <span class="text-sm text-red-600" x-show="errors['settings.general.max_unpaid_orders']"
            x-text="errorMessage('settings.general.max_unpaid_orders')"></span>
        </div>

        <!-- User Registration -->
        <div class="col-span-full sm:col-span-3">
          <label for="user_registration" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('User Registration') }}
          </label>
          <x-shop::jet.select class="input" id="user_registration" x-model="form.general.user_registration">
            <option value="0">{{ __('Disable') }}</option>
            <option value="1">{{ __('Enable') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.user_registration']"
            x-text="errorMessage('settings.general.user_registration')"></span>
        </div>

        <!-- New Account Email Confirmation -->
        <div class="col-span-full sm:col-span-3">
          <label for="new_account_email_confirmation" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('New Account Email Confirmation') }}
          </label>
          <x-shop::jet.select class="input" id="new_account_email_confirmation" x-model="form.general.new_account_email_confirmation">
            <option value="0">{{ __('Disable') }}</option>
            <option value="1">{{ __('Enable') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.general.new_account_email_confirmation']"
            x-text="errorMessage('settings.general.new_account_email_confirmation')"></span>
        </div>

        {{-- <!-- Captcha -->
        <div class="col-span-full sm:col-span-3">
          <label for="captcha" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Captcha') }}
          </label>
          <x-shop::jet.select class="input" id="captcha" x-model="form.captcha">
            <option value="0">{{ __('Disable') }}</option>
            <option value="local">{{ __('Local') }}</option>
            <option value="recaptcha">{{ __('Google Recaptcha') }}</option>
            <option value="turnstile">{{ __('CloudFlare Turnstile') }}</option>
          </x-shop::jet.select>
          <span class="text-sm text-red-600" x-show="errors['settings.captcha']"
            x-text="errorMessage('settings.captcha')"></span>
        </div>

        <!-- Recaptcha Key -->
        <div x-show="form.captcha === 'recaptcha'" class="col-span-full sm:col-span-3">
          <label for="recaptcha_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Recaptcha Key') }}
          </label>
          <input type="text" class="input" id="recaptcha_key" x-model="form.recaptcha_key" />
          <span class="text-sm text-red-600" x-show="errors['settings.recaptcha_key']"
            x-text="errorMessage('settings.recaptcha_key')"></span>
        </div>

        <!-- Recaptcha Secret -->
        <div x-show="form.captcha === 'recaptcha'" class="col-span-full sm:col-span-3">
          <label for="recaptcha_secret" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Recaptcha Secret') }}
          </label>
          <input type="text" class="input" id="recaptcha_secret" x-model="form.recaptcha_secret" />
          <span class="text-sm text-red-600" x-show="errors['settings.recaptcha_secret']"
            x-text="errorMessage('settings.recaptcha_secret')"></span>
        </div>

        <!-- Turnstile Key -->
        <div x-show="form.captcha === 'turnstile'" class="col-span-full sm:col-span-3">
          <label for="turnstile_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Turnstile Key') }}
          </label>
          <input type="text" class="input" id="turnstile_key" x-model="form.turnstile_key" />
          <span class="text-sm text-red-600" x-show="errors['settings.turnstile_key']"
            x-text="errorMessage('settings.turnstile_key')"></span>
        </div>

        <!-- Turnstile Secret -->
        <div x-show="form.captcha === 'turnstile'" class="col-span-full sm:col-span-3">
          <label for="turnstile_secret" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Turnstile Secret') }}
          </label>
          <input type="text" class="input" id="turnstile_secret" x-model="form.turnstile_secret" />
          <span class="text-sm text-red-600" x-show="errors['settings.turnstile_secret']"
            x-text="errorMessage('settings.turnstile_secret')"></span>
        </div> --}}

        <div class="col-span-full flex items-end justify-between border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">{{ __('SEO Settings') }}</x-slot>
            <x-slot name="description">{{ __('Please update setting for SEO') }}</x-slot>
          </x-shop::jet.section-title>
        </div>

        <!-- Shop Title -->
        <div class="col-span-full sm:col-span-3">
          <label for="shop_title" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Homepage Title') }}
          </label>
          <input type="text" class="input" id="shop_title" x-model="form.seo.title" />
          <span class="text-sm text-red-600" x-show="errors['settings.seo.title']" x-text="errorMessage('settings.seo.title')"></span>
        </div>

        <!-- Products Title -->
        <div class="col-span-full sm:col-span-3">
          <label for="products_title" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Products Title') }}
          </label>
          <input type="text" class="input" id="products_title" x-model="form.seo.products_title" />
          <span class="text-sm text-red-600" x-show="errors['settings.seo.products_title']"
            x-text="errorMessage('settings.seo.products_title')"></span>
        </div>

        <!-- Home Description -->
        <div class="col-span-full">
          <label for="description" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Homepage Description') }}
          </label>
          <textarea class="input" id="description" x-model="form.seo.description"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.seo.description']"
            x-text="errorMessage('settings.seo.description')"></span>
        </div>

        <!-- Products Description -->
        <div class="col-span-full">
          <label for="products_description" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
            {{ __('Products Page Description') }}
          </label>
          <textarea class="input mt-1" id="products_description" x-model="form.seo.products_description"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.seo.products_description']"
            x-text="errorMessage('settings.seo.products_description')"></span>
        </div>

        <div class="col-span-full flex items-end justify-between border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">{{ __('Banner Settings') }}</x-slot>
            <x-slot name="description">{{ __('Please update setting for banner') }}</x-slot>
          </x-shop::jet.section-title>
        </div>

        <!-- Notification -->
        <div class="col-span-full">
          <label for="notification" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Message') }}
          </label>
          <textarea class="input" id="notification" x-model="form.notification.message"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.notification.message']"
            x-text="errorMessage('settings.notification.message')"></span>
        </div>

        <!-- Button Text -->
        <div class="col-span-full sm:col-span-3">
          <label for="button_text" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Button Text') }}
          </label>
          <input type="text" class="input" id="button_text" x-model="form.notification.button_text" />
          <span class="text-sm text-red-600" x-show="errors['settings.notification.button_text']"
            x-text="errorMessage('settings.notification.button_text')"></span>
        </div>

        <!-- Button Link -->
        <div class="col-span-full sm:col-span-3">
          <label for="button_link" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Button Link') }}
          </label>
          <input type="text" class="input" id="button_link" x-model="form.notification.button_link" />
          <span class="text-sm text-red-600" x-show="errors['settings.notification.button_link']"
            x-text="errorMessage('settings.notification.button_link')"></span>
        </div>


        <div class="col-span-full flex items-end justify-between border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">{{ __('Slider Settings') }}</x-slot>
            <x-slot name="description">{{ __('Please update setting for homepage slider') }}</x-slot>
          </x-shop::jet.section-title>
          <div>
            <x-shop::jet.button type="button" x-show="form.shop_slider.length < 8" @click="addSlide">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </x-shop::jet.button>
          </div>
        </div>

        <div class="border-b border-gray-200 dark:border-gray-700 -mt-3 -mx-6 px-6 overflow-x-auto col-span-full pb-px">
          <nav class="-mb-px flex space-x-8 whitespace-nowrap" aria-label="Tabs">
            <template x-for="(slider, index) in form.shop_slider" :key="index">
              <button type="button" @click="selectedSlide = index"
                class="group x-focus inline-flex gap-x-4 items-center border-b-2 px-1 py-4 text-sm font-medium"
                :class="index == selectedSlide ?
                    'border-primary-500 text-primary-600' :
                    'border-transparent text-mute hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300'">
                <span>{{ __('Slide') }} <span x-text="index + 1"></span></span>
              </button>
            </template>
            {{-- @forelse ($settings['shop_slider'] as $slider)
            <button type="button" @click="selectedSlide = {{ $loop->index }}"
              class="group x-focus inline-flex gap-x-4 items-center border-b-2 px-1 py-4 text-sm font-medium"
              :class="{{ $loop->index }} == selectedSlide ?
                  'border-primary-500 text-primary-600' :
                  'border-transparent text-mute hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300'">
              <span>{{ __('Slide {x}', ['x' => $loop->iteration]) }}</span>
            </button>
          @empty
          @endforelse --}}
          </nav>
        </div>


        <div
          class="flex items-center justify-between col-span-full border-b border-gray-200 dark:border-gray-700 -mt-6 py-3 -mx-6 px-6 bg-gray-50 dark:bg-gray-950">
          <div>
            <h4 class="text-sm font-bold"><span x-text="selectedSlide+1"></span>. {{ __('Please update slide details below') }}</h4>
            <p class="text-sm">{{ __('Please use same image dimensions and content length for best results.') }}</p>
          </div>
          <button type="button" v-if="form.shop_slider.length > 1" class="x-focus text-gray-500 hover:text-red-500"
            @click="removeSlider">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
              class="size-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="col-span-6 sm:col-span-3 flex items-end gap-3">
          <img alt="" :id="'slide_image_' + selectedSlide"
            class="max-h-16 max-w-16 rounded-md border border-gray-200 dark:border-gray-700" :src="form.shop_slider[selectedSlide].image"
            x-show="form.shop_slider[selectedSlide].image && (typeof form.shop_slider[selectedSlide].image === 'string' || form.shop_slider[selectedSlide].image instanceof String)" />

          <div class="flex-1">
            <label :for="'image_' + selectedSlide" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
              {{ __('Image') }}
            </label>
            <input type="file" class="input" accept="image/*" :id="'image_' + selectedSlide"
              @change="uploadFile($event, 'slider_image')" />
            {{-- @change="e => ($wire.settings.shop_slider[selectedSlide].image = e.target.files[0])" /> --}}
            <span class="text-sm text-red-600" x-show="errors['settings.shop_slider[selectedSlide].image']"
              x-text="errorMessage('settings.shop_slider[selectedSlide].image')"></span>
          </div>
        </div>

        <div class="col-span-6 sm:col-span-3 flex items-end gap-3">
          <img alt="" :id="'slide_bg_image_' + selectedSlide"
            class="max-h-16 max-w-16 rounded-md border border-gray-200 dark:border-gray-700"
            :src="form.shop_slider[selectedSlide].bg_image" x-show="form.shop_slider[selectedSlide].bg_image" />

          <div class="flex-1">
            <label :for="'bg_image_' + selectedSlide" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
              {{ __('Background Image') }}
            </label>
            <input type="file" class="input" accept="image/*" :id="'bg_image_' + selectedSlide"
              @change="uploadFile($event, 'slider_bg_image')" />
            {{-- @change="e => (form.shop_slider[selectedSlide].bg_image = e.target.files[0])" /> --}}
            <span class="text-sm text-red-600" x-show="errors['settings.shop_slider[selectedSlide].bg_image']"
              x-text="errorMessage('settings.shop_slider[selectedSlide].bg_image')"></span>
          </div>
        </div>

        <!-- Slider Heading -->
        <div class="col-span-full">
          <label :for="'heading_' + selectedSlide" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Heading') }}
          </label>
          <input type="text" class="input" :id="'heading_' + selectedSlide" x-model="form.shop_slider[selectedSlide].heading" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_slider[selectedSlide].heading']"
            x-text="errorMessage('settings.shop_slider[selectedSlide].heading')"></span>
        </div>

        <!-- Slider Description -->
        <div class="col-span-full">
          <label :for="'description_' + selectedSlide" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Description') }}
          </label>
          <textarea class="input mt-1" :id="'description_' + selectedSlide" x-model="form.shop_slider[selectedSlide].description"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.shop_slider[selectedSlide].description']"
            x-text="errorMessage('settings.shop_slider[selectedSlide].description')"></span>
        </div>

        <!-- Slider Button Text -->
        <div class="col-span-full sm:col-span-3">
          <label :for="'button_text_' + selectedSlide" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Button Text') }}
          </label>
          <input type="text" class="input" :id="'button_text_' + selectedSlide"
            x-model="form.shop_slider[selectedSlide].button_text" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_slider[selectedSlide].button_text']"
            x-text="errorMessage('settings.shop_slider[selectedSlide].button_text')"></span>
        </div>
        <!-- Slider Button Link -->
        <div class="col-span-full sm:col-span-3">
          <label :for="'button_link_' + selectedSlide" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Button Link') }}
          </label>
          <input type="text" class="input" :id="'button_link_' + selectedSlide"
            x-model="form.shop_slider[selectedSlide].button_link" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_slider[selectedSlide].button_link']"
            x-text="errorMessage('settings.shop_slider[selectedSlide].button_link')"></span>
        </div>

        <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 flex items-end justify-between">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">
              {{ __('Pages Menu Settings') }}
            </x-slot>
            <x-slot name="description">
              {{ __('Please update setting for pages menu in header') }}
            </x-slot>
          </x-shop::jet.section-title>
          <div class=shrink-0>
            <x-shop::jet.button type="button" @click="form.page_menus.push({ label: '', link: '' })">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </x-shop::jet.button>
          </div>
        </div>

        <template x-for="(menu, pi) in form.page_menus" :key="pi">
          <div class="col-span-full items-end grid grid-cols-6 gap-6">
            {{-- Menu Label --}}
            <div class="col-span-full sm:col-span-3">
              <div class="flex items-center justify-between">
                <label :for="'menu_label_' + pi" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                  {{ __('Menu Label') }}
                </label>
                <button type="button" @click="form.page_menus.splice(pi, 1)" class="x-focus link-danger">
                  {{ __('Remove') }}
                </button>
              </div>
              <input type="text" class="input" :id="'menu_label_' + pi" x-model="form.page_menus[pi].label" />
              <span class="text-sm text-red-600" x-show="errors['settings.page_menus.' + pi + '.label']"
                x-text="errorMessage('settings.page_menus.' + pi + '.label')"></span>
            </div>

            {{-- Menu Link --}}
            <div class="col-span-full sm:col-span-3">
              <label :for="'menu_link_' + pi" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Menu Link') }}
              </label>
              <input type="text" class="input" :id="'menu_link_' + pi" x-model="form.page_menus[pi].link" />
              <span class="text-sm text-red-600" x-show="errors['settings.page_menus.' + pi + '.link']"
                x-text="errorMessage('settings.page_menus.' + pi + '.link')"></span>
            </div>

            {{-- Remove Button --}}
            {{-- <div class="col-span-6 sm:col-span-1">
              <button type="button" @click="form.page_menus.splice(pi, 1)" class="btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                  class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div> --}}
          </div>
        </template>

        <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">
              {{ __('Call to Action Settings') }}
            </x-slot>
            <x-slot name="description">
              {{ __('Please update setting for homepage call to action section') }}
            </x-slot>
          </x-shop::jet.section-title>
        </div>

        <div class="col-span-6 flex items-end gap-3">
          <img alt="" id="cta_bg_image" class="max-h-16 max-w-16 rounded-md border border-gray-200 dark:border-gray-700"
            :src="form.shop_cta.bg_image" x-show="form.shop_cta.bg_image" />

          <div class="flex-1">
            <label for="cta_bg_image" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
              {{ __('Background Image') }}
            </label>
            <input type="file" class="input" accept="image/*" id="cta_bg_image" @change="uploadFile($event, 'cta_bg_image')" />
            <span class="text-sm text-red-600" x-show="errors['settings.shop_cta.bg_image']"
              x-text="errorMessage('settings.shop_cta.bg_image')"></span>
          </div>
        </div>

        <!-- CTA Heading -->
        <div class="col-span-full">
          <label for="cta_heading" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Heading') }}
          </label>
          <input type="text" class="input" id="cta_heading" x-model="form.shop_cta.heading" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_cta.heading']"
            x-text="errorMessage('settings.shop_cta.heading')"></span>
        </div>

        <!-- CTA Description -->
        <div class="col-span-full">
          <label for="cta_description" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Description') }}
          </label>
          <textarea class="input mt-1" id="cta_description" x-model="form.shop_cta.description"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.shop_cta.description']"
            x-text="errorMessage('settings.shop_cta.description')"></span>
        </div>

        <!-- CTA Button Text -->
        <div class="col-span-full sm:col-span-3">
          <label for="cta_button_text" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Button Text') }}
          </label>
          <input type="text" class="input" id="cta_button_text" x-model="form.shop_cta.button_text" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_cta.button_text']"
            x-text="errorMessage('settings.shop_cta.button_text')"></span>
        </div>
        <!-- CTA Button Link -->
        <div class="col-span-full sm:col-span-3">
          <label for="cta_button_link" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Button Link') }}
          </label>
          <input type="text" class="input" id="cta_button_link" x-model="form.shop_cta.button_link" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_cta.button_link']"
            x-text="errorMessage('settings.shop_cta.button_link')"></span>
        </div>

        <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6  flex items-end justify-between">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">
              {{ __('Footer Menus') }}
            </x-slot>
            <x-slot name="description">
              {{ __('Please update menus for footer section') }}
            </x-slot>
          </x-shop::jet.section-title>
          <div class=shrink-0>
            <x-shop::jet.button type="button" @click="addFooterSection" x-show="form.shop_footer.sections.length < 4">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </x-shop::jet.button>
          </div>
        </div>

        <div class="border-b -mt-3 border-gray-200 dark:border-gray-700 -mx-6 px-6 overflow-x-auto col-span-full pb-px">
          <nav class="-mb-px flex space-x-8 whitespace-nowrap" aria-label="Tabs">
            <template x-for="(section, sectionIndex) in form.shop_footer.sections" :key="sectionIndex">
              <button type="button" @click="selectedFooterSection = sectionIndex"
                class="group x-focus inline-flex gap-x-4 items-center border-b-2 px-1 py-4 text-sm font-medium"
                :class="sectionIndex == selectedFooterSection ?
                    'border-primary-500 text-primary-600' :
                    'border-transparent text-mute hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300'">
                <span>{{ __('Section') }} <span x-text="sectionIndex + 1"></span></span>
                <!-- <Icon name="x" size="size-4" @click.stop="form.shop_footer.sections.splice(sectionIndex, 1)" /> -->
              </button>
            </template>
          </nav>
        </div>

        <div
          class="flex items-center justify-between col-span-full border-b border-gray-200 dark:border-gray-700 -mt-6 py-3 -mx-6 px-6 bg-gray-50 dark:bg-gray-950">
          <div>
            <h4 class="text-sm font-bold"><span x-text="selectedFooterSection + 1"></span>. {{ __('Please update section menus below') }}
            </h4>
          </div>
          <div>
            {{-- <button type="button" class="me-4 btn-primary"
              @click="form.shop_footer.sections[selectedFooterSection].menus.push({ label: '', link: '' })">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </button> --}}
            <button type="button" v-if="form.shop_slider.length > 1" class="x-focus text-gray-500 hover:text-red-500"
              @click="removeFooterSection">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div class="col-span-full">
          <div class="flex items-center justify-between">
            <label :for="'title_' + selectedFooterSection" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
              {{ __('Section Title') }}
            </label>
            <button type="button" class="x-focus link"
              @click="form.shop_footer.sections[selectedFooterSection].menus.push({ label: '', link: '' })">
              {{ __('Add Menu') }}
              </svg>
            </button>
          </div>
          <input type="text" class="input" :id="'title_' + selectedFooterSection"
            x-model="form.shop_footer.sections[selectedFooterSection].title" />
          <span class="text-sm text-red-600" x-show="errors['settings.shop_footer.sections.'+selectedFooterSection+'.title']"
            x-text="errorMessage('settings.shop_footer.sections.'+selectedFooterSection+'.title')"></span>
        </div>

        <template x-for="(menu, mi) in form.shop_footer.sections[selectedFooterSection].menus" :key="mi">
          <div class="col-span-full items-end grid grid-cols-6 gap-6">
            {{-- Menu Label --}}
            <div class="col-span-full sm:col-span-3">
              <div class="flex items-center justify-between">
                <label :for="'label_' + mi" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                  {{ __('Menu Label') }}
                </label>
                <button type="button" @click="form.shop_footer.sections[selectedFooterSection].menus.splice(mi, 1)"
                  class="x-focus link-danger">
                  {{ __('Remove') }}
                </button>
              </div>
              <input type="text" class="input" :id="'label_' + mi"
                x-model="form.shop_footer.sections[selectedFooterSection].menus[mi].label" />
              <span class="text-sm text-red-600" x-show="errors['settings.shop_footer.sections[selectedFooterSection].menus[mi].label']"
                x-text="errorMessage('settings.shop_footer.sections[selectedFooterSection].menus[mi].label')"></span>
            </div>

            {{-- Menu Link --}}
            <div class="col-span-full sm:col-span-3">
              <label :for="'link_' + mi" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Menu Link') }}
              </label>
              <input type="text" class="input" :id="'link_' + mi"
                x-model="form.shop_footer.sections[selectedFooterSection].menus[mi].link" />
              <span class="text-sm text-red-600" x-show="errors['settings.shop_footer.sections[selectedFooterSection].menus[mi].link']"
                x-text="errorMessage('settings.shop_footer.sections[selectedFooterSection].menus[mi].link')"></span>
            </div>
          </div>
        </template>


        <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">
              {{ __('Social Links') }}
            </x-slot>
            <x-slot name="description">
              {{ __('Please update links for social media pages') }}
            </x-slot>
          </x-shop::jet.section-title>
        </div>

        {{-- Facebook --}}
        <div class="col-span-full sm:col-span-3">
          <label for="facebook" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Facebook') }}
          </label>
          <input type="text" class="input" id="facebook" x-model="form.social_links.facebook" />
          <span class="text-sm text-red-600" x-show="errors['settings.social_links.facebook']"
            x-text="errorMessage('settings.social_links.facebook')"></span>
        </div>

        {{-- Twitter/x --}}
        <div class="col-span-full sm:col-span-3">
          <label for="twitter" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Twitter') }}
          </label>
          <input type="text" class="input" id="twitter" x-model="form.social_links.twitter" />
          <span class="text-sm text-red-600" x-show="errors['settings.social_links.twitter']"
            x-text="errorMessage('settings.social_links.twitter')"></span>
        </div>

        {{-- Instagram --}}
        <div class="col-span-full sm:col-span-3">
          <label for="instagram" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Instagram') }}
          </label>
          <input type="text" class="input" id="instagram" x-model="form.social_links.instagram" />
          <span class="text-sm text-red-600" x-show="errors['settings.social_links.instagram']"
            x-text="errorMessage('settings.social_links.instagram')"></span>
        </div>

        {{-- LinkedIn --}}
        <div class="col-span-full sm:col-span-3">
          <label for="linkedin" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('LinkedIn') }}
          </label>
          <input type="text" class="input" id="linkedin" x-model="form.social_links.linkedin" />
          <span class="text-sm text-red-600" x-show="errors['settings.social_links.linkedin']"
            x-text="errorMessage('settings.social_links.linkedin')"></span>
        </div>

        {{-- YouTube --}}
        <div class="col-span-full sm:col-span-3">
          <label for="youtube" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('YouTube') }}
          </label>
          <input type="text" class="input" id="youtube" x-model="form.social_links.youtube" />
          <span class="text-sm text-red-600" x-show="errors['settings.social_links.youtube']"
            x-text="errorMessage('settings.social_links.youtube')"></span>
        </div>

        {{-- Pinterest --}}
        <div class="col-span-full sm:col-span-3">
          <label for="pinterest" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Pinterest') }}
          </label>
          <input type="text" class="input" id="pinterest" x-model="form.social_links.pinterest" />
          <span class="text-sm text-red-600" x-show="errors['settings.social_links.pinterest']"
            x-text="errorMessage('settings.social_links.pinterest')"></span>
        </div>

        <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">
              {{ __('Other Shop Settings') }}
            </x-slot>
            <x-slot name="description">
              {{ __('Please update shop settings below') }}
            </x-slot>
          </x-shop::jet.section-title>
        </div>

        <div class="col-span-full flex flex-col gap-2 rounded-md p-4 border border-gray-200 dark:border-gray-700 overflow-x-auto">
          <h4 class="text-sm font-bold mb-3">{{ __('Miscellaneous Settings') }}</h4>

          <div>
            <label for="newsletter_input" class="flex items-center">
              <input type="checkbox" id="newsletter_input" name="newsletter_input" x-model="form.newsletter_input" value="1"
                {{ ($settings['newsletter_input'] ?? null) == 1 ? 'checked' : '' }}
                class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800" />
              <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Display newsletter subscription') }}</span>
            </label>
          </div>

          <div>
            <label for="brands_article" class="flex items-center">
              <input type="checkbox" id="brands_article" name="brands_article" x-model="form.brands_article" value="1"
                {{ ($settings['brands_article'] ?? null) == 1 ? 'checked' : '' }}
                class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800" />
              <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Display feature product on brands menu') }}</span>
            </label>
          </div>
        </div>

        <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-6 -mx-6 px-6 ">
          <x-shop::jet.section-title class="flex-1">
            <x-slot name="title">
              {{ __('Shipping & Return Policies') }}
            </x-slot>
            <x-slot name="description">
              {{ __('Please update product shipping and return policies below') }}
            </x-slot>
          </x-shop::jet.section-title>
        </div>

        <!-- Shipping Policy -->
        <div class="col-span-full">
          <label for="shipping_policy" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Shipping Policy') }}
          </label>
          <textarea class="input" rows="6" id="shipping_policy" x-model="form.shipping_policy"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.shipping_policy']"
            x-text="errorMessage('settings.shipping_policy')"></span>
        </div>

        <!-- Return Policy -->
        <div class="col-span-full">
          <label for="return_policy" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
            {{ __('Return Policy') }}
          </label>
          <textarea class="input" rows="6" id="return_policy" x-model="form.return_policy"></textarea>
          <span class="text-sm text-red-600" x-show="errors['settings.return_policy']"
            x-text="errorMessage('settings.return_policy')"></span>
        </div>

      </x-slot>

      <x-slot name="actions">
        <x-shop::jet.button class="ms-4 btn-md">
          {{ __('Save') }}
          </x-jet.button>
      </x-slot>
    </x-shop::jet.form-section>
  </form>
</div>
