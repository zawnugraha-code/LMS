@extends('layouts.app')

@section('title', 'Notifikasi — Arunika LMS')
@section('page_breadcrumb', 'Notifikasi')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman Notifikasi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-navy-900 text-white flex items-center justify-center shrink-0">
                <x-icon name="bell" :size="20" class="text-white" />
            </div>
            <div>
                <h1 class="text-xl font-bold text-text tracking-tight">Semua Notifikasi</h1>
                <p class="text-xs text-text-muted mt-0.5">Seluruh pembaruan tugas, pengumuman perkuliahan, dan aktivitas sistem.</p>
            </div>
        </div>

        <form action="{{ route('notifications.mark_all_read') }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-[6px] bg-surface hover:bg-canvas border border-line text-xs font-medium text-text transition-colors flex items-center gap-1.5 cursor-pointer">
                <x-icon name="check-circle" :size="14" class="text-text-muted" />
                <span>Tandai Semua Sudah Dibaca</span>
            </button>
        </form>
    </div>

    <!-- Kontainer Kartu Daftar Notifikasi -->
    <div class="bg-surface border border-line rounded-[10px] shadow-2xs divide-y divide-line/70 overflow-hidden">
        @forelse($notifications ?? [] as $item)
            <div class="p-4 sm:p-5 flex items-start justify-between gap-4 hover:bg-canvas transition-colors {{ !$item['is_read'] ? 'bg-blue-50/30' : '' }}">
                <div class="flex items-start gap-3.5 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-blue-50 text-primary flex items-center justify-center shrink-0 mt-0.5">
                        <x-icon :name="$item['icon'] ?? 'bell'" :size="18" />
                    </div>
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ $item['action_url'] ?? '#' }}" class="font-semibold text-xs text-text hover:text-primary transition-colors">
                                {{ $item['title'] }}
                            </a>
                            @if(!$item['is_read'])
                                <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                            @endif
                        </div>
                        <p class="text-xs text-text-muted leading-relaxed">
                            {{ $item['message'] }}
                        </p>
                        <span class="text-[11px] text-text-subtle block">
                            {{ \App\Helpers\ArunikaDate::format($item['created_at']) }} &middot; {{ \App\Helpers\ArunikaDate::diffForHumans($item['created_at']) }}
                        </span>
                    </div>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    @if(!$item['is_read'])
                        <form action="{{ route('notifications.mark_as_read', $item['id']) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-[11px] text-text-subtle hover:text-primary cursor-pointer" title="Tandai dibaca">
                                <x-icon name="check" :size="14" />
                            </button>
                        </form>
                    @endif
                    <a href="{{ $item['action_url'] ?? '#' }}" class="px-2.5 py-1 rounded-[4px] bg-canvas hover:bg-tile border border-line text-[11px] font-medium text-text transition-colors">
                        Buka &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-text-muted text-xs space-y-2">
                <div class="w-12 h-12 rounded-full bg-canvas mx-auto flex items-center justify-center text-text-subtle">
                    <x-icon name="bell" :size="24" />
                </div>
                <p class="font-medium text-text">Belum ada notifikasi</p>
                <p class="text-text-muted">Pemberitahuan akademik baru akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
