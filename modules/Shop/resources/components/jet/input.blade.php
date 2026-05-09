@props(['disabled' => false])

@php
  $model = str($attributes)->after('wire:model="')->before('"');
  $error = str((string) $errors)->contains($model);
@endphp

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'input' . ($error ? ' error' : '')]) !!}>
