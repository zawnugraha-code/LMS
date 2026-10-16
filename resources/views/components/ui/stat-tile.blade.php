@props([
    'value' => '',
    'label' => '',
    'sub' => null,
    'subColor' => 'text-text-muted', // or text-success
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-tile border border-line/60 rounded-[8px] p-3.5 flex flex-col justify-between ' . $class]) }}>
    <div>
        <div class="text-2xl font-bold text-text tabular-nums tracking-tight leading-none mb-1">
            {{ $value }}
        </div>
        <div class="text-xs font-semibold text-text">
            {{ $label }}
        </div>
    </div>

    @if($sub)
        <div class="text-[11px] {{ $subColor }} mt-2 flex items-center gap-1 font-medium">
            {!! $sub !!}
        </div>
    @endif
</div>
