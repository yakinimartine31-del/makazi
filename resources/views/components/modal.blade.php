@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidthClass = [
    'sm' => 'modal-sm',
    'md' => 'modal-md',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    '2xl' => '',
][$maxWidth] ?? '';
@endphp

<div class="modal fade" id="modal-{{ $name }}" tabindex="-1" aria-hidden="true" style="{{ $show ? 'display: block;' : 'display: none;' }}">
    <div class="modal-dialog {{ $maxWidthClass }}">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>
