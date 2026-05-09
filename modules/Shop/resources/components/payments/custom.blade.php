@if ($fields && count($fields) > 0)
  <div>
    <div>
      <div class="mb-6">
        <x-shop::jet.section-title>
          <x-slot name="title">{{ __('Pay with Card') }}</x-slot>
          <x-slot name="description">
            {{ __('Please fill the card details and submit the form.') }}
          </x-slot>
        </x-shop::jet.section-title>
      </div>

      <div class="mb-4">
        <form autocomplete="off" id="custom-payment-form" wire:submit.prevent="pay">
          @foreach ($fields as $key => $value)
            <div class="mb-4">
              <x-shop::jet.label for="{{ $key }}" value="{{ $value['label'] }}" />
              <x-shop::jet.input id="{{ $key }}" type="{{ $value['type'] }}" @class([
                  'block w-full mt-1 py-2 sm:text-sm',
                  'error' => $errors->has('form.' . $key),
              ])
                wire:model.defer="form.{{ $key }}" autocomplete="off" />
              <x-shop::jet.input-error for="form.{{ $key }}" class="mt-1" />
            </div>
          @endforeach

          <div class="mt-4 payment-buttons">
            <x-shop::jet.button type="submit" class="w-full justify-center">
              {{ __('Submit') }}
            </x-shop::jet.button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endif
