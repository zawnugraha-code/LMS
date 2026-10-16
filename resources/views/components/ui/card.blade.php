@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-surface border border-line rounded-[8px] shadow-[0_1px_2px_rgba(20,33,61,0.04)] ' . $class]) }}>
    {{ $slot }}
</div>
