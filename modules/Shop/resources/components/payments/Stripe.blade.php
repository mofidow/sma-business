@if (($payment_settings['gateway'] ?? null) == 'Stripe')
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
        <form autocomplete="off" id="stripe-payment-form">
          {{-- <x-shop::jet.label for="card-element" value="{{ __('Pay with Card') }}" /> --}}
          <div wire:ignore id="card-element" class="card-element"></div>
          <div id="card-errors" role="alert"></div>
          <div class="mt-4 payment-buttons">
            <x-shop::jet.button type="submit" class="w-full justify-center">
              {{ __('Submit') }}
            </x-shop::jet.button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
      var stripe = Stripe('{{ $payment_settings['services']['stripe']['key'] ?? '' }}');
      var elements = stripe.elements();

      var style = {
        base: {
          color: '#000000',
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSmoothing: 'antialiased',
          fontSize: '16px',
          '::placeholder': {
            color: '#9CA3AF'
          }
        },
        invalid: {
          color: '#DC2626',
          iconColor: '#DC2626'
        }
      };

      var card = elements.create('card', {
        style: style
      });
      card.mount('#card-element');

      card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
          displayError.textContent = event.error.message;
        } else {
          displayError.textContent = '';
        }
      });

      var form = document.getElementById('stripe-payment-form');
      form.addEventListener('submit', function(event) {
        event.preventDefault();
        stripe.createToken(card).then(function(result) {
          if (result.error) {
            var errorElement = document.getElementById('card-errors');
            errorElement.textContent = result.error.message;
          } else {
            console.log(result.token.id);

            // var hiddenInput = document.createElement('input');
            // hiddenInput.setAttribute('type', 'hidden');
            // hiddenInput.setAttribute('name', 'stripeToken');
            // hiddenInput.setAttribute('value', result.token.id);
            // form.appendChild(hiddenInput);
            // form.submit();
            @this.token = result.token.id;
            @this.pay();
          }
        });
      });
    </script>
  </div>
@endif
