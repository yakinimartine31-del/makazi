@props(['align' => 'right', 'width' => '48'])

@php
$alignmentClasses = match ($align) {
    'left' => 'dropdown-menu-left',
    default => 'dropdown-menu-right',
};

$width = match ($width) {
    '48' => 'dropdown-menu',
    default => 'dropdown-menu',
};
@endphp

<div class="dropdown">
    <div>
        {{ $trigger }}
    </div>

    <div class="{{ $width }} {{ $alignmentClasses }}" style="position: absolute;">
        <div class="dropdown-menu show">
            {{ $content }}
        </div>
    </div>
</div>
