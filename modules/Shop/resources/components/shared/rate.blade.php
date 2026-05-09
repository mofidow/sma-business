@props(['can' => false])

<div x-data="{
      rating: 0,
      hoverRating: 0,
      ratings: [{'amount': 1, 'label':'{{ __('Terrible') }}'}, {'amount': 2, 'label':'{{ __('Bad') }}'}, {'amount': 3, 'label':'{{ __('Okay') }}'}, {'amount': 4, 'label':'{{ __('Good') }}'}, {'amount': 5, 'label':'{{ __('Great') }}'}],
      rate(amount) { this.rating = this.rating == amount ? 0 : this.rating = amount; this.fireTagsUpdateEvent(); },
      init() {
        this.hoverRating = parseInt(this.$el.parentNode.getAttribute('data-rating'));
        this.rate(parseInt(this.$el.parentNode.getAttribute('data-rating')));
      },
      currentLabel() {
        let r = this.rating;
        if (this.hoverRating != this.rating) { r = this.hoverRating; }
        let i = this.ratings.findIndex(e => e.amount == r);
        return (i >= 0) ? this.ratings[i].label : '';
      },
      fireTagsUpdateEvent() {
        this.$el.dispatchEvent(new CustomEvent('rating-update', { detail: { value: this.rating }, bubbles: true }));
      },
  }"
  class="flex flex-col items-center justify-center {{ $can ? 'space-y-2 rounded m-2 w-72 p-3 border dark:border-gray-700 mx-auto' : '' }}">
  <div class="flex space-x-0">
    <template x-for="(star, index) in ratings" :key="index">
      @if ($can)
        <button type="button" @click="rate(star.amount)" @mouseover="hoverRating = star.amount" @mouseleave="hoverRating = rating"
          aria-hidden="true" :title="star.label"
          class="rounded-sm text-gray-400 fill-current focus:outline-none focus:shadow-outline p-1 w-12 m-0 {{ $can ? 'cursor-pointer' : '' }}"
          :class=" {'text-gray-600': hoverRating>= star.amount, 'text-yellow-400': rating >= star.amount && hoverRating >= star.amount}">
          <x-shop::elements.icon name="star" class="w-15 transition duration-150" />
        </button>
      @else
        <div
          class="rounded-sm text-gray-400 fill-current focus:outline-none focus:shadow-outline p-0 m-0 {{ $can ? 'cursor-pointer' : '' }}"
          :class=" {'text-gray-600': hoverRating>= star.amount, 'text-yellow-400': rating >= star.amount && hoverRating >= star.amount}">
          <x-shop::elements.icon name="star" class="w-6 transition duration-150" />
        </div>
      @endif
    </template>
  </div>
  @if ($can)
    <div class="p-2">
      <template x-if="rating || hoverRating">
        <p x-text="currentLabel()"></p>
      </template>
      <template x-if="!rating && !hoverRating">
        <p>{{ __('Please Rate!') }}</p>
      </template>
    </div>
  @endif
</div>
