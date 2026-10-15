@props([
    'variant' => 'full', // 'full', 'mark', 'inverse'
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 ' . $class]) }}>
    <!-- Geometrical Dawn Mark: Semicircle over horizontal horizon line -->
    <svg 
        class="w-6 h-6 text-brand shrink-0" 
        viewBox="0 0 28 28" 
        fill="none" 
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <!-- Horizon Line -->
        <line x1="3" y1="19" x2="25" y2="19" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" />
        <!-- Rising Dawn Sun (Semicircle) -->
        <path d="M6 19 A8 8 0 0 1 22 19 Z" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="1.75" />
        <!-- Subtle zenith dot -->
        <circle cx="14" cy="8" r="1.25" fill="currentColor" />
    </svg>

    @if($variant === 'full')
        <div class="flex flex-col leading-none">
            <span class="font-display font-semibold text-xl tracking-tight text-ink">Arunika</span>
            <span class="font-mono text-[9px] uppercase tracking-widest text-ink-muted mt-0.5">Learning OS</span>
        </div>
    @endif
</div>
