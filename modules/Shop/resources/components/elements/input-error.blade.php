@props(['for' => null, 'messages' => null])
@php
    $msgs = $messages ?? ($errors->has($for) ? $errors->get($for) : []);
@endphp
@if(count($msgs ?? []))
    @foreach($msgs as $message)
        <p {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400']) }}>{{ $message }}</p>
    @endforeach
@endif
