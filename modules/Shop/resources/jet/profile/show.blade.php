<div>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    @if (Laravel\Fortify\Features::canUpdateProfileInformation())
      @livewire('shop.jet.update-profile-information-form')

      <x-shop::jet.section-border />
    @endif

    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
      <div class="mt-10 sm:mt-0">
        @livewire('shop.jet.update-password-form')
      </div>

      <x-shop::jet.section-border />
    @endif

    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
      <div class="mt-10 sm:mt-0">
        @livewire('shop.jet.two-factor-authentication-form')
      </div>

      <x-shop::jet.section-border />
    @endif

    <div class="mt-10 sm:mt-0">
      @livewire('shop.jet.logout-other-browser-sessions-form')
    </div>

    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
      <x-shop::jet.section-border />

      <div class="mt-10 sm:mt-0">
        @livewire('shop.jet.delete-user-form')
      </div>
    @endif
  </div>
</div>
