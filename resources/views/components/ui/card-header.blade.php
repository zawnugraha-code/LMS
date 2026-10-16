@props([
    'title' => '',
    'icon' => null,
    'action' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'px-5 py-3.5 border-b border-line flex items-center justify-between gap-3 ' . $class]) }}>
    <div class="flex items-center gap-2.5 min-w-0">
        @if($icon)
            <x-icon :name="$icon" :size="18" class="text-primary shrink-0" />
        @endif
        @if($title)
            <h3 class="text-sm sm:text-[15px] font-semibold text-text tracking-tight truncate">{{ $title }}</h3>
        @endif
        {{ $slot }}
    </div>

    @if($action)
        <div class="shrink-0 flex items-center text-xs">
            {!! $action !!}
        </div>
    @endif
</div>
