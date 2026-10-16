@props([
    'src' => null,
    'name' => 'User',
    'size' => 32,
    'class' => '',
])

@php
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    if (empty($initials)) {
        $initials = 'U';
    }

    $sizeStyle = "width: {$size}px; height: {$size}px;";
@endphp

<div
    {{ $attributes->merge(['class' => 'relative rounded-full overflow-hidden shrink-0 select-none bg-navy-700 text-white font-medium flex items-center justify-center ' . $class]) }}
    style="{{ $sizeStyle }}"
>
    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $name }}"
            class="w-full h-full object-cover"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />
        <span class="hidden w-full h-full items-center justify-center text-xs font-semibold">
            {{ $initials }}
        </span>
    @else
        <span class="text-xs font-semibold tracking-wider">
            {{ $initials }}
        </span>
    @endif
</div>
