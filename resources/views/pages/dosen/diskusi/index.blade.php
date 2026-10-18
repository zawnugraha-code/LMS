@extends('layouts.dosen')

@section('title', 'Forum Diskusi — Portal Dosen Arunika')
@section('page_breadcrumb', 'Diskusi')

@section('content')
<div class="space-y-6" x-data="{ modalBuat: false }">
    
    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="message-square" :size="20" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Diskusi
                </h1>
                <p class="text-xs sm:text-[13px] text-text-muted mt-0.5 leading-relaxed">
                    Berinteraksi dengan mahasiswa melalui forum diskusi, tanya jawab, dan pengumuman kelas.
                </p>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-check" :size="16" class="text-emerald-600 shrink-0" />
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-alert" :size="16" class="text-red-600 shrink-0" />
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-red-600 hover:text-red-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif

    <!-- 2. SPLIT-PANE 3-KOLOM (Screenshot 3: Kiri 3/12, Tengah 6/12, Kanan 3/12) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- ========================================== -->
        <!-- PANE KIRI: DAFTAR KELAS & NAVIGASI         -->
        <!-- ========================================== -->
        <div class="lg:col-span-4 xl:col-span-3 min-w-0 space-y-3">
            <div class="bg-surface border border-line rounded-[8px] p-3 shadow-xs">
                
                <!-- Search Mata Kuliah Input -->
                <form action="{{ route('dosen.diskusi.index') }}" method="GET" class="relative mb-2.5">
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-text-muted">
                        <x-icon name="search" :size="13" />
                    </span>
                    <input 
                        type="text" 
                        name="search_class" 
                        value="{{ $searchClass }}"
                        placeholder="Cari mata kuliah..." 
                        class="w-full pl-7 pr-2.5 py-1.5 bg-canvas/70 border border-line rounded-[6px] text-xs text-text placeholder:text-text-muted/60 focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                    >
                </form>

                <!-- Filter Dropdown Button: Semua Kelas -->
                <div class="flex items-center justify-between px-1 py-1 mb-2 text-xs text-text-muted">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Daftar Kelas</span>
                    <span class="inline-flex items-center gap-1 text-[11px] text-[#2563EB] font-medium">
                        <x-icon name="layers" :size="12" />
                        <span>{{ count($classes) }} Kelas</span>
                    </span>
                </div>

                <!-- Vertical Class Items List -->
                <div class="space-y-1.5 max-h-[600px] overflow-y-auto overflow-x-hidden pr-0.5">
                    @foreach ($classes as $c)
                        @php
                            $isActiveClass = $c['id'] === $selectedClass->id;
                        @endphp
                        <a 
                            href="{{ route('dosen.diskusi.index', ['class_id' => $c['id']]) }}"
                            class="flex items-center justify-between p-2 rounded-[6px] border transition-colors {{ $isActiveClass ? 'bg-blue-50/70 border-blue-200 shadow-2xs' : 'bg-surface hover:bg-slate-50 border-line/70' }}"
                        >
                            <div class="flex items-center gap-2 min-w-0 flex-1 pr-2">
                                <div class="w-7 h-7 rounded-[6px] {{ $isActiveClass ? 'bg-blue-100 text-[#2563EB]' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                                    <x-icon name="layers" :size="14" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-text truncate leading-tight">
                                        {{ $c['code'] }} - {{ $c['name'] }}
                                    </p>
                                    <p class="text-[10px] text-text-muted mt-0.5 truncate">
                                        {{ $c['section'] }} • {{ $c['students_count'] }} mhs
                                    </p>
                                </div>
                            </div>

                            <!-- Right Badges: Red (Unread) & Blue (Total) -->
                            <div class="shrink-0">
                                @if ($c['unread_count'] > 0)
                                    <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center leading-none">
                                        {{ $c['unread_count'] }}
                                    </span>
                                @else
                                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-400 text-[10px] font-medium flex items-center justify-center leading-none">
                                        0
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!-- PANE TENGAH: DAFTAR THREAD DISKUSI         -->
        <!-- ========================================== -->
        <div class="lg:col-span-8 xl:col-span-6 min-w-0 space-y-4">
            <div class="bg-surface border border-line rounded-[8px] shadow-xs overflow-hidden">
                
                <!-- 1. Header Kelas Terpilih + Tombol "+ Buat Diskusi Baru" -->
                <div class="p-4 border-b border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-9 h-9 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                            <x-icon name="layers" :size="18" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-bold text-text truncate tracking-tight">
                                {{ $selectedClass->course?->code }} - {{ $selectedClass->course?->name }}
                            </h2>
                            <p class="text-[11px] text-text-muted mt-0.5">
                                {{ $selectedClass->section }} • {{ $selectedClass->enrollments->count() > 0 ? $selectedClass->enrollments->count() : 32 }} mahasiswa
                            </p>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        @click="modalBuat = true"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-medium rounded-[6px] transition-colors shadow-xs shrink-0 cursor-pointer"
                    >
                        <x-icon name="plus" :size="14" />
                        <span>Buat Diskusi Baru</span>
                    </button>
                </div>

                <!-- 2. Category Tabs & Search Bar -->
                <div class="px-4 py-2.5 border-b border-line bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-2.5 text-xs">
                    
                    <!-- Tabs Kategori (Rapi tanpa horizontal scrollbar) -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a 
                            href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => 'semua', 'search' => $search, 'sort' => $sort]) }}"
                            class="px-2.5 py-1 rounded-[5px] font-medium transition-colors shrink-0 {{ $activeKategori === 'semua' ? 'bg-[#2563EB] text-white shadow-2xs' : 'bg-white border border-line text-text-muted hover:text-text hover:bg-slate-50' }}"
                        >
                            Semua
                        </a>
                        <a 
                            href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => 'tanya_jawab', 'search' => $search, 'sort' => $sort]) }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[5px] font-medium transition-colors shrink-0 {{ in_array($activeKategori, ['tanya_jawab', 'tanya-jawab']) ? 'bg-[#2563EB] text-white shadow-2xs' : 'bg-white border border-line text-text-muted hover:text-text hover:bg-slate-50' }}"
                        >
                            <x-icon name="help-circle" :size="12" />
                            <span>Tanya Jawab</span>
                        </a>
                        <a 
                            href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => 'pengumuman', 'search' => $search, 'sort' => $sort]) }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[5px] font-medium transition-colors shrink-0 {{ $activeKategori === 'pengumuman' ? 'bg-[#2563EB] text-white shadow-2xs' : 'bg-white border border-line text-text-muted hover:text-text hover:bg-slate-50' }}"
                        >
                            <x-icon name="megaphone" :size="12" />
                            <span>Pengumuman</span>
                        </a>
                        <a 
                            href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => 'diskusi_umum', 'search' => $search, 'sort' => $sort]) }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[5px] font-medium transition-colors shrink-0 {{ in_array($activeKategori, ['diskusi_umum', 'diskusi-umum', 'diskusi']) ? 'bg-[#2563EB] text-white shadow-2xs' : 'bg-white border border-line text-text-muted hover:text-text hover:bg-slate-50' }}"
                        >
                            <x-icon name="message-circle" :size="12" />
                            <span>Diskusi Umum</span>
                        </a>
                    </div>

                    <!-- Search Input & Sort Dropdown -->
                    <div class="flex items-center gap-2 shrink-0">
                        <form action="{{ route('dosen.diskusi.index') }}" method="GET" class="relative w-36 sm:w-44">
                            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                            <input type="hidden" name="kategori" value="{{ $activeKategori }}">
                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-text-muted">
                                <x-icon name="search" :size="12" />
                            </span>
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ $search }}"
                                placeholder="Cari diskusi..." 
                                class="w-full pl-6 pr-2 py-1 bg-white border border-line rounded-[5px] text-[11px] text-text placeholder:text-text-muted/60 focus:outline-hidden focus:border-[#2563EB]"
                            >
                        </form>

                        <div class="relative" x-data="{ sortOpen: false }">
                            <button 
                                @click="sortOpen = !sortOpen" 
                                type="button" 
                                class="inline-flex items-center gap-1 px-2 py-1 bg-white border border-line rounded-[5px] text-[11px] font-medium text-text hover:bg-slate-50 cursor-pointer shadow-2xs shrink-0"
                            >
                                <span>{{ $sort === 'terpopuler' ? 'Terpopuler' : ($sort === 'belum_dijawab' ? 'Belum Dijawab' : 'Terbaru') }}</span>
                                <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                            </button>
                            <div 
                                x-show="sortOpen" 
                                @click.away="sortOpen = false" 
                                x-cloak
                                class="absolute right-0 mt-1 w-32 bg-surface rounded-[6px] border border-line shadow-lg py-1 z-20 text-[11px]"
                            >
                                <a href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => $activeKategori, 'search' => $search, 'sort' => 'terbaru']) }}" class="block px-3 py-1.5 hover:bg-slate-50">Terbaru</a>
                                <a href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => $activeKategori, 'search' => $search, 'sort' => 'terpopuler']) }}" class="block px-3 py-1.5 hover:bg-slate-50">Terpopuler</a>
                                <a href="{{ route('dosen.diskusi.index', ['class_id' => $selectedClass->id, 'kategori' => $activeKategori, 'search' => $search, 'sort' => 'belum_dijawab']) }}" class="block px-3 py-1.5 hover:bg-slate-50">Belum Dijawab</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Thread List Items (Cards) -->
                <div class="divide-y divide-line">
                    @forelse ($threads as $thread)
                        <div class="p-4 hover:bg-slate-50/60 transition-colors flex items-start justify-between gap-3 group">
                            
                            <!-- Left: Author Avatar + Thread Content -->
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <!-- Avatar -->
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 overflow-hidden border border-line">
                                    @if ($thread->user?->avatar_path)
                                        <img src="{{ $thread->user->avatar_path }}" alt="{{ $thread->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($thread->user?->name ?? 'M', 0, 1)) }}
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    
                                    <!-- Title + Pin/Lock badges + Unread badge -->
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if (! $thread->is_read)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-blue-600 text-white tracking-wide">
                                                BARU
                                            </span>
                                        @endif
                                        @if ($thread->is_pinned)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-[4px] text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <x-icon name="pin" :size="10" />
                                                <span>Disematkan</span>
                                            </span>
                                        @endif
                                        @if ($thread->is_locked)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-[4px] text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <x-icon name="lock" :size="10" />
                                                <span>Terkunci</span>
                                            </span>
                                        @endif
                                        <a 
                                            href="{{ route('dosen.diskusi.show', $thread->id) }}"
                                            class="text-xs font-bold text-text hover:text-[#2563EB] transition-colors leading-snug line-clamp-1"
                                        >
                                            {{ $thread->title }}
                                        </a>
                                    </div>

                                    <!-- Content Snippet -->
                                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed line-clamp-2">
                                        {{ Str::limit(strip_tags($thread->content), 140) }}
                                    </p>

                                    <!-- Category Pill + Author Info Footer -->
                                    <div class="mt-2.5 flex items-center gap-2 flex-wrap text-[11px]">
                                        <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-semibold border {{ $thread->badge_class }}">
                                            {{ $thread->badge_label }}
                                        </span>
                                        <span class="text-text-subtle text-[10px] inline-flex items-center gap-1">
                                            <x-icon name="message-circle" :size="11" />
                                            <span>{{ $thread->replies_count }} balasan</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Star Favorite + Author & Time + Chevron Arrow -->
                            <div class="flex items-center gap-2.5 shrink-0 text-right">
                                <!-- Star Favorite Toggle Form -->
                                <form action="{{ route('dosen.diskusi.favorite', $thread->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        title="{{ $thread->is_favorited ? 'Hapus favorit' : 'Tandai favorit' }}"
                                        class="p-1 rounded hover:bg-slate-100 transition-colors cursor-pointer {{ $thread->is_favorited ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400' }}"
                                    >
                                        <x-icon name="star" :size="15" class="{{ $thread->is_favorited ? 'fill-amber-400 text-amber-500' : '' }}" />
                                    </button>
                                </form>

                                <div class="hidden sm:block text-[11px] text-text-muted">
                                    <p class="font-medium text-text leading-tight">{{ $thread->user?->name ?? 'Pengguna' }}</p>
                                    <p class="text-[10px] text-text-subtle mt-0.5">{{ $thread->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}</p>
                                </div>
                                <a 
                                    href="{{ route('dosen.diskusi.show', $thread->id) }}"
                                    class="text-text-muted group-hover:text-[#2563EB] transition-colors p-1"
                                >
                                    <x-icon name="chevron-right" :size="16" />
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="py-12 text-center text-text-muted">
                            <x-icon name="message-square-off" :size="28" class="mx-auto mb-2 text-text-muted/60" />
                            <p class="text-xs font-medium">Belum ada diskusi pada kategori ini.</p>
                            <button 
                                type="button" 
                                @click="modalBuat = true"
                                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#2563EB] text-white text-xs font-medium rounded-[6px] hover:bg-blue-700 cursor-pointer"
                            >
                                <x-icon name="plus" :size="13" />
                                <span>Buat Topik Pertama</span>
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Pagination -->
                <div class="px-4 py-3 border-t border-line flex items-center justify-between text-xs text-text-muted bg-white">
                    <p>Menampilkan {{ $threads->firstItem() ?? 0 }}-{{ $threads->lastItem() ?? 0 }} dari {{ $threads->total() }} diskusi</p>
                    <div class="flex items-center gap-1">
                        @if ($threads->onFirstPage())
                            <span class="w-6 h-6 rounded-[4px] border border-line flex items-center justify-center text-text-muted opacity-50 cursor-not-allowed">
                                <x-icon name="chevron-left" :size="12" />
                            </span>
                        @else
                            <a href="{{ $threads->previousPageUrl() }}" class="w-6 h-6 rounded-[4px] border border-line flex items-center justify-center text-text-muted hover:bg-slate-50">
                                <x-icon name="chevron-left" :size="12" />
                            </a>
                        @endif

                        <span class="w-6 h-6 rounded-[4px] bg-[#2563EB] text-white flex items-center justify-center font-medium text-[11px]">
                            {{ $threads->currentPage() }}
                        </span>

                        @if ($threads->hasMorePages())
                            <a href="{{ $threads->nextPageUrl() }}" class="w-6 h-6 rounded-[4px] border border-line flex items-center justify-center text-text-muted hover:bg-slate-50">
                                <x-icon name="chevron-right" :size="12" />
                            </a>
                        @else
                            <span class="w-6 h-6 rounded-[4px] border border-line flex items-center justify-center text-text-muted opacity-50 cursor-not-allowed">
                                <x-icon name="chevron-right" :size="12" />
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- PANE KANAN: 3 WIDGET INFORMASI             -->
        <!-- ========================================== -->
        <div class="lg:col-span-12 xl:col-span-3 min-w-0">
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-1 gap-4">
                
                <!-- Widget 1: Statistik Diskusi (Grid 2x2) -->
                <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-line mb-3 gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <x-icon name="bar-chart-2" :size="15" class="text-text-muted shrink-0" />
                            <h3 class="text-xs font-bold text-text tracking-tight truncate">Statistik Diskusi</h3>
                        </div>
                        <a href="#semua" class="text-[11px] font-medium text-[#2563EB] hover:underline shrink-0">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        
                        <!-- 1. Total Diskusi -->
                        <div class="bg-blue-50/50 border border-blue-100/80 rounded-[6px] p-2.5 flex items-center justify-between">
                            <div class="min-w-0 pr-1">
                                <p class="text-[10px] font-medium text-text-muted uppercase tracking-wider truncate">Total</p>
                                <p class="text-base font-bold text-navy-900 mt-0.5">{{ $stats['total'] }}</p>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-blue-100/70 text-[#2563EB] flex items-center justify-center shrink-0">
                                <x-icon name="message-square" :size="13" />
                            </div>
                        </div>

                        <!-- 2. Tanya Jawab -->
                        <div class="bg-blue-50/50 border border-blue-100/80 rounded-[6px] p-2.5 flex items-center justify-between">
                            <div class="min-w-0 pr-1">
                                <p class="text-[10px] font-medium text-text-muted uppercase tracking-wider truncate">Tanya Jawab</p>
                                <p class="text-base font-bold text-[#2563EB] mt-0.5">{{ $stats['tanya_jawab'] }}</p>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-blue-100/70 text-[#2563EB] flex items-center justify-center shrink-0">
                                <x-icon name="help-circle" :size="13" />
                            </div>
                        </div>

                        <!-- 3. Pengumuman -->
                        <div class="bg-emerald-50/50 border border-emerald-100/80 rounded-[6px] p-2.5 flex items-center justify-between">
                            <div class="min-w-0 pr-1">
                                <p class="text-[10px] font-medium text-emerald-800 uppercase tracking-wider truncate">Pengumuman</p>
                                <p class="text-base font-bold text-emerald-600 mt-0.5">{{ $stats['pengumuman'] }}</p>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-emerald-100/70 text-emerald-600 flex items-center justify-center shrink-0">
                                <x-icon name="megaphone" :size="13" />
                            </div>
                        </div>

                        <!-- 4. Diskusi Umum -->
                        <div class="bg-purple-50/50 border border-purple-100/80 rounded-[6px] p-2.5 flex items-center justify-between">
                            <div class="min-w-0 pr-1">
                                <p class="text-[10px] font-medium text-purple-800 uppercase tracking-wider truncate">Umum</p>
                                <p class="text-base font-bold text-purple-600 mt-0.5">{{ $stats['diskusi_umum'] }}</p>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-purple-100/70 text-purple-600 flex items-center justify-center shrink-0">
                                <x-icon name="users" :size="13" />
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Widget 2: Diskusi Terbaru -->
                <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-line mb-3 gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <x-icon name="clock" :size="15" class="text-text-muted shrink-0" />
                            <h3 class="text-xs font-bold text-text tracking-tight truncate">Diskusi Terbaru</h3>
                        </div>
                        <a href="#terbaru" class="text-[11px] font-medium text-[#2563EB] hover:underline shrink-0">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="space-y-2">
                        @foreach ($recentThreads as $rThread)
                            <a 
                                href="{{ route('dosen.diskusi.show', $rThread->id) }}"
                                class="flex items-center justify-between gap-2 p-1.5 rounded-[6px] hover:bg-slate-50 transition-colors group"
                            >
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0 overflow-hidden border border-line">
                                        @if ($rThread->user?->avatar_path)
                                            <img src="{{ $rThread->user->avatar_path }}" alt="{{ $rThread->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($rThread->user?->name ?? 'M', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-text group-hover:text-[#2563EB] transition-colors truncate leading-tight">
                                            {{ $rThread->title }}
                                        </p>
                                        <p class="text-[10px] text-text-subtle mt-0.5 truncate">
                                            {{ $rThread->academicClass?->course?->code ?? 'IF2101' }} • {{ $rThread->created_at->locale('id')->isoFormat('D MMM, HH:mm') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Replies count -->
                                <div class="flex items-center gap-1 text-[10px] text-text-muted shrink-0 pl-1">
                                    <x-icon name="message-square" :size="11" />
                                    <span class="font-semibold">{{ $rThread->replies_count }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Widget 3: Pengumuman -->
                <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-line mb-3 gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <x-icon name="megaphone" :size="15" class="text-text-muted shrink-0" />
                            <h3 class="text-xs font-bold text-text tracking-tight truncate">Pengumuman</h3>
                        </div>
                        <a href="{{ route('dosen.dashboard') }}" class="text-[11px] font-medium text-[#2563EB] hover:underline shrink-0">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="space-y-2">
                        @foreach ($announcements as $anc)
                            <div class="flex items-start gap-2.5 p-2 bg-blue-50/40 border border-blue-100 rounded-[6px]">
                                <div class="w-6 h-6 rounded-[4px] bg-blue-100/70 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5">
                                    <x-icon name="file-text" :size="12" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-text leading-snug line-clamp-2">
                                        {{ $anc['title'] }}
                                    </p>
                                    <p class="text-[10px] text-text-muted mt-1">
                                        {{ $anc['date'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- 3. Modal Alpine: + Buat Diskusi Baru -->
    <div 
        x-show="modalBuat" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.away="modalBuat = false"
            class="bg-surface border border-line rounded-[8px] max-w-lg w-full shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150"
        >
            <!-- Header Modal -->
            <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center border border-blue-100">
                        <x-icon name="message-square" :size="16" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-text tracking-tight">Buat Diskusi Baru</h3>
                        <p class="text-[11px] text-text-muted">Kelas {{ $selectedClass->course?->code }} - {{ $selectedClass->section }}</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="modalBuat = false"
                    class="text-text-muted hover:text-text cursor-pointer"
                >
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <!-- Form Buat Diskusi -->
            <form action="{{ route('dosen.diskusi.store', $selectedClass->id) }}" method="POST">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    
                    <!-- Judul Diskusi -->
                    <div class="space-y-1">
                        <label for="thread_title" class="font-medium text-text">
                            Judul Diskusi <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="thread_title" 
                            name="title" 
                            required 
                            placeholder="Contoh: Pertanyaan seputar Tugas 2 dan Implementasi Git"
                            class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                        >
                    </div>

                    <!-- Kategori -->
                    <div class="space-y-1">
                        <label for="thread_kategori" class="font-medium text-text">
                            Kategori Diskusi <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="thread_kategori" 
                            name="kategori" 
                            required
                            class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                        >
                            <option value="tanya_jawab">Tanya Jawab (Diskusi interaktif seputar materi/tugas)</option>
                            <option value="pengumuman">Pengumuman (Pemberitahuan resmi dosen)</option>
                            <option value="diskusi_umum">Diskusi Umum (Pembahasan santai dan kerja kelompok)</option>
                        </select>
                    </div>

                    <!-- Isi Pesan -->
                    <div class="space-y-1">
                        <label for="thread_content" class="font-medium text-text">
                            Isi Pesan / Pembahasan <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="thread_content" 
                            name="content" 
                            rows="4" 
                            required 
                            placeholder="Tuliskan detail pertanyaan, penjelasan, atau pengumuman Anda secara lengkap..."
                            class="w-full px-3 py-2 bg-surface border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                        ></textarea>
                    </div>

                    <!-- Checkboxes Opsi Dosen -->
                    <div class="space-y-2 pt-2 border-t border-line">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_pinned" value="1" class="rounded-[4px] border-line text-[#2563EB] focus:ring-[#2563EB]">
                            <span class="text-xs text-text">Sematkan diskusi ini di posisi teratas (Pinned)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_locked" value="1" class="rounded-[4px] border-line text-[#2563EB] focus:ring-[#2563EB]">
                            <span class="text-xs text-text">Kunci balasan (Khusus pengumuman satu arah)</span>
                        </label>
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="px-5 py-3 border-t border-line bg-slate-50/50 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="modalBuat = false"
                        class="px-3.5 py-2 bg-surface hover:bg-slate-100 border border-line rounded-[6px] text-xs font-medium text-text transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white rounded-[6px] text-xs font-medium transition-colors shadow-xs cursor-pointer"
                    >
                        Kirim Diskusi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
