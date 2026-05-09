@props(['action' => 'auth'])
@if ($shop_settings->g_recaptcha)
  <div>
    @php
      $recapcha_key = $shop_settings->g_recaptcha_key; // config('services.recaptcha.site_key');
    @endphp
    @push('scripts')
      <script src="https://www.google.com/recaptcha/api.js?render={{ $recapcha_key }}"></script>
    @endpush
    <div x-on:recaptcha.window="execute" x-data="{
        execute() {
            grecaptcha.ready((e) => {
                grecaptcha.execute('{{ $recapcha_key }}', { action: '{{ $action }}' })
                    .then((token) => {
                        this.$refs.recaptchaToken.value = token;
                        this.$el.closest('form').submit();
                    });
            });
        }
    }">
      <input type="hidden" name="captcha" x-ref="recaptchaToken" value="">
    </div>
  </div>
@endif
