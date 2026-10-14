@props(['active'])

@php
$classes = ($active ?? false)
    ? 'nav-link active font-weight-bold'
    : 'nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
