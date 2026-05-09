@php
  $model = str($attributes)->after('wire:model="')->after('wire:model.live="')->before('"');
  $error = $errors ? str((string) $errors)->contains($model) : false;
@endphp

<select {!! $attributes->merge(['class' => 'input' . ($error ? ' error' : '')]) !!}>
  {{ $slot }}
</select>
