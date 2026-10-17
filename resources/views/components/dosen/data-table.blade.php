@props([
    'headers' => [],
])

<div class="w-full overflow-hidden rounded-[8px] border border-line bg-surface shadow-2xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-text divide-y divide-line">
            @if (count($headers) > 0)
                <thead class="bg-slate-50 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="px-4 py-3 {{ is_array($header) && isset($header['align']) && $header['align'] === 'right' ? 'text-right' : 'text-left' }}">
                                {{ is_array($header) ? $header['label'] : $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-line/60 bg-surface">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
