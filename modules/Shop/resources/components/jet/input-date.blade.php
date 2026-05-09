@props(['disabled' => false, 'value' => now()->format('Y-m-d')])

@once
  @section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  @endsection
@endonce

<div x-data="{
    init() {
        this.value = '{{ $value }}';
        let picker = flatpickr(this.$refs.picker, {
            mode: 'date',
            dateFormat: 'Y-m-d',
            onChange: (date, dateString) => {
                console.log('Date changed:', date, dateString);
                {{-- this.value = dateString; --}}
            }
        })

        this.$watch('value', () => picker.setDate(this.value))
    },
}">
  <input x-ref="picker" id="picker" type="text" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'input']) !!}>
</div>
