<div class="pt-6 prose-none">
  <x-shop::jet.section-title>
    <x-slot name="title">
      {{ __('Send us message') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill the form below to send us message.') }}
    </x-slot>
  </x-shop::jet.section-title>

  <div class="mt-6">
    <form action="{{ route('shop.message') }}" method="POST" class="grid grid-cols-1 gap-6 sm:grid-cols-2" autocomplete="off">
      @csrf

      @if (session('success'))
        <div class="col-span-full mb-4 font-medium text-sm text-green-600 dark:text-green-400">
          {{ session('success') }}
        </div>
      @endif

      <div>
        <x-shop::jet.label for="name" value="{{ __('Name') }}" />
        <x-shop::jet.input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
        <x-shop::jet.input-error for="name" class="prose-error mt-0" />
      </div>

      <div>
        <x-shop::jet.label for="subject" value="{{ __('Subject') }}" />
        <x-shop::jet.input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required />
        <x-shop::jet.input-error for="subject" class="prose-error mt-0" />
      </div>

      <div>
        <x-shop::jet.label for="email" value="{{ __('Email') }}" />
        <x-shop::jet.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
        <x-shop::jet.input-error for="email" class="prose-error mt-0" />
      </div>

      <div>
        <x-shop::jet.label for="phone" value="{{ __('Phone') }}" />
        <x-shop::jet.input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" />
        <x-shop::jet.input-error for="phone" class="prose-error mt-0" />
      </div>

      <div class="col-span-full">
        <x-shop::jet.label for="message" value="{{ __('Message') }}" />
        <div class="mt-1">
          <textarea id="message" name="message" rows="4" required
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus-default rounded-md shadow-sm block mt-1 w-full">{{ old('message') }}</textarea>
          <x-shop::jet.input-error for="message" class="prose-error mt-0" />
        </div>
      </div>

      <div class="col-span-full flex justify-end">
        <button type="submit"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          {{ __('Submit') }}
        </button>
      </div>
    </form>
  </div>
</div>
