@props(['for'])

@error($for)
  <p {{ $attributes->merge(['class' => 'input-error']) }}>
    {{ str($message)->replace('form.', '')->replace('shop ', '')->replace(' id ', ' ') }}</p>
@enderror
