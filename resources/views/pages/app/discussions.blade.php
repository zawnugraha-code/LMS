@extends('layouts.app')

@section('title', 'Diskusi — Arunika')
@section('page_breadcrumb', 'Diskusi')

@section('content')

    <div 
        x-data="{
            activeTab: 'all',
            searchQuery: '',
            selectedCategory: 'all',
            newThreadModalOpen: false,
            detailModalOpen: false,
            selectedThread: null,
            openThread(thread) {
                this.selectedThread = thread;
                this.detailModalOpen = true;
            }
        }"
    >
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI & TOMBOL BUAT DISKUSI
             ========================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <!-- Left Header -->
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-full bg-[#17306B] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <x-icon name="message-circle" :size="20" class="text-white fill-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Diskusi
                    </h1>
                    <p class="text-xs sm:text-[13px] text-text-muted mt-1 max-w-xl leading-relaxed">
                        Diskusi, tanya jawab, dan berbagi pengetahuan bersama dosen dan teman kelas.
                    </p>
                </div>
            </div>

            <!-- Right Header Button: + Buat Diskusi Baru -->
            <button 
                type="button" 
                @click="newThreadModalOpen = true"
                class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer self-start sm:self-auto shrink-0"
            >
                <x-icon name="plus" :size="14" />
                <span>Buat Diskusi Baru</span>
            </button>
        </div>

        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-[8px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle" :size="16" class="text-emerald-600 shrink-0" />
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 cursor-pointer">
                    <x-icon name="x" :size="14" />
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-[8px] bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <x-icon name="alert-triangle" :size="16" class="text-rose-600 shrink-0" />
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 cursor-pointer">
                    <x-icon name="x" :size="14" />
                </button>
            </div>
        @endif

        <!-- ==========================================
             2. FILTER TABS & SEARCH BAR (PERSIS GAMBAR ACUAN)
             ========================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            
            <!-- Left: Tab Pills (Semua, Forum Kelas, Tanya Jawab, Pengumuman, Topik Populer) -->
            <div class="overflow-x-auto py-1 no-scrollbar">
                <div class="inline-flex items-center gap-2 whitespace-nowrap">
                    
                    <!-- Tab 1: Semua (Active) -->
                    <button 
                        type="button" 
                        @click="activeTab = 'all'; selectedCategory = 'all'"
                        :class="activeTab === 'all' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-4 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="message-square" :size="14" />
                        <span>Semua</span>
                    </button>

                    <!-- Tab 2: Forum Kelas -->
                    <button 
                        type="button" 
                        @click="activeTab = 'forum'; selectedCategory = 'forum'"
                        :class="activeTab === 'forum' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="notebook-text" :size="14" />
                        <span>Forum Kelas</span>
                    </button>

                    <!-- Tab 3: Tanya Jawab -->
                    <button 
                        type="button" 
                        @click="activeTab = 'tanya'; selectedCategory = 'tanya'"
                        :class="activeTab === 'tanya' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="help-circle" :size="14" />
                        <span>Tanya Jawab</span>
                    </button>

                    <!-- Tab 4: Pengumuman -->
                    <button 
                        type="button" 
                        @click="activeTab = 'pengumuman'; selectedCategory = 'pengumuman'"
                        :class="activeTab === 'pengumuman' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="megaphone" :size="14" />
                        <span>Pengumuman</span>
                    </button>

                    <!-- Tab 5: Topik Populer -->
                    <button 
                        type="button" 
                        @click="activeTab = 'populer'; selectedCategory = 'all'"
                        :class="activeTab === 'populer' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="trophy" :size="14" />
                        <span>Topik Populer</span>
                    </button>

                </div>
            </div>

            <!-- Right: Search Bar & Filter Button -->
            <div class="flex items-center gap-2 shrink-0">
                <div class="relative w-full sm:w-60">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                        <x-icon name="search" :size="13" />
                    </span>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Cari topik diskusi..." 
                        class="w-full pl-8 pr-3 py-1.5 rounded-[6px] border border-line bg-surface text-xs text-text placeholder:text-text-subtle focus:outline-none focus:border-[#2563EB] transition-colors shadow-2xs"
                    >
                </div>
                <button 
                    type="button" 
                    class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer shrink-0 shadow-2xs"
                >
                    <x-icon name="filter" :size="13" class="text-text-muted" />
                    <span>Filter</span>
                </button>
            </div>

        </div>

        <!-- ==========================================
             3. MAIN LAYOUT: 2 KOLOM (KIRI ~68%, KANAN ~32%)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ==========================================
                 KOLOM KIRI (lg:col-span-8): KOTAK TULIS DISKUSI & LIST THREAD
                 ========================================== -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Kotak Tulis Diskusi Cepat (Persis Gambar Acuan) -->
                <div class="bg-surface border border-line rounded-[10px] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="file-text" :size="18" />
                        </div>
                        <div class="min-w-0 flex-1 cursor-pointer" @click="newThreadModalOpen = true">
                            <h3 class="font-bold text-xs sm:text-sm text-text truncate">
                                Ada pertanyaan atau ingin berbagi sesuatu?
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5 truncate hover:text-text transition-colors">
                                Tulis di sini untuk memulai diskusi baru...
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <button 
                            type="button" 
                            @click="newThreadModalOpen = true"
                            class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs"
                        >
                            <x-icon name="paperclip" :size="13" class="text-text-muted" />
                            <span>Lampirkan File</span>
                        </button>
                        <button 
                            type="button" 
                            @click="newThreadModalOpen = true"
                            class="px-4 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                        >
                            <span>Kirim</span>
                            <x-icon name="send" :size="12" />
                        </button>
                    </div>
                </div>

                <!-- ==========================================
                     DAFTAR THREAD DISKUSI (1 KARTU UTUH DENGAN GARIS PEMISAH PERSIS ACUAN)
                     ========================================== -->
                <div class="bg-surface border border-line rounded-[10px] divide-y divide-line/70 shadow-2xs overflow-hidden">

                    @forelse($threads as $thread)
                        @php
                            $userName = $thread->user->name;
                            $userAvatar = null;
                            $initials = 'U';
                            $avatarBg = 'bg-blue-600';
                            if (str_contains(strtolower($userName), 'budi')) {
                                $userAvatar = '/images/avatar-budi.jpg';
                            } elseif (str_contains(strtolower($userName), 'siti')) {
                                $userAvatar = '/images/avatar-siti.jpg';
                            } elseif (str_contains(strtolower($userName), 'dewi')) {
                                $userAvatar = '/images/avatar-dewi.jpg';
                            } elseif (str_contains(strtolower($userName), 'fajar')) {
                                $userAvatar = '/images/avatar-fajar.jpg';
                            } elseif (str_contains(strtolower($userName), 'arya')) {
                                $userAvatar = '/images/avatar-student.jpg';
                            } else {
                                $words = explode(' ', $userName);
                                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                                if (str_contains(strtolower($userName), 'andi')) {
                                    $avatarBg = 'bg-purple-500';
                                } elseif (str_contains(strtolower($userName), 'raka')) {
                                    $avatarBg = 'bg-teal-500';
                                } elseif (str_contains(strtolower($userName), 'bayu')) {
                                    $avatarBg = 'bg-amber-500';
                                }
                            }

                            $catSlug = $thread->category->slug ?? 'forum-kelas';
                            $categoryBadgeMap = [
                                'pengumuman' => 'bg-blue-50 text-blue-600',
                                'tanya-jawab' => 'bg-emerald-50 text-emerald-600',
                                'diskusi' => 'bg-purple-50 text-purple-600',
                                'forum-kelas' => 'bg-blue-50 text-blue-600',
                                'berbagi' => 'bg-orange-50 text-orange-600',
                            ];
                            $categoryColor = $categoryBadgeMap[$catSlug] ?? 'bg-blue-50 text-blue-600';
                            $formattedViews = $thread->views_count >= 1000 ? number_format($thread->views_count / 1000, 1) . 'k' : (string) $thread->views_count;
                        @endphp

                        <div 
                            x-show="(selectedCategory === 'all' || selectedCategory === '{{ $catSlug }}' || (selectedCategory === 'forum' && '{{ $catSlug }}' === 'forum-kelas') || (selectedCategory === 'tanya' && '{{ $catSlug }}' === 'tanya-jawab') || (selectedCategory === 'pengumuman' && '{{ $catSlug }}' === 'pengumuman') || (selectedCategory === 'diskusi' && '{{ $catSlug }}' === 'diskusi') || (selectedCategory === 'berbagi' && '{{ $catSlug }}' === 'berbagi')) && (activeTab === 'all' || (activeTab === 'forum' && '{{ $catSlug }}' === 'forum-kelas') || (activeTab === 'tanya' && '{{ $catSlug }}' === 'tanya-jawab') || (activeTab === 'pengumuman' && '{{ $catSlug }}' === 'pengumuman') || (activeTab === 'populer')) && (searchQuery === '' || '{{ strtolower(addslashes($thread->title)) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($thread->content)) }}'.includes(searchQuery.toLowerCase()))"
                            @click="openThread({
                                id: {{ $thread->id }},
                                title: {{ json_encode($thread->title) }},
                                author: {{ json_encode($thread->user->name) }},
                                role: {{ json_encode($thread->user->isLecturer() ? 'Dosen Pengampu' : 'Mahasiswa') }},
                                course: {{ json_encode($thread->academicClass?->course?->name ?? 'Mata Kuliah Umum') }},
                                category: {{ json_encode($thread->category->name) }},
                                categoryColor: {{ json_encode($categoryColor) }},
                                snippet: {{ json_encode($thread->content) }},
                                commentsCount: {{ $thread->replies_count }},
                                viewsCount: '{{ $formattedViews }}',
                                time: '{{ $thread->created_at->diffForHumans() }}',
                                avatar: '{{ $userAvatar }}',
                                initials: '{{ $initials }}',
                                avatarBg: '{{ $avatarBg }}',
                                isPinned: {{ $thread->is_pinned ? 'true' : 'false' }},
                                detailUrl: '{{ route('app.discussions.show', $thread) }}',
                                upvotesCount: {{ $thread->upvotes_count }}
                            })"
                            class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                    <!-- Author Avatar / Initials -->
                                    @if($userAvatar)
                                        <div class="w-9 h-9 rounded-full bg-navy-800 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs overflow-hidden mt-0.5">
                                            <img src="{{ $userAvatar }}" alt="{{ $thread->user->name }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-9 h-9 rounded-full {{ $avatarBg }} text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs mt-0.5">
                                            {{ $initials }}
                                        </div>
                                    @endif

                                    <!-- Content -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($thread->is_pinned)
                                                <x-icon name="pin" :size="13" class="text-[#2563EB] shrink-0" />
                                            @elseif($thread->upvotes_count > 20)
                                                <x-icon name="arrow-up" :size="12" class="text-blue-500 shrink-0" />
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0"></span>
                                            @endif

                                            <a 
                                                href="{{ route('app.discussions.show', $thread) }}" 
                                                @click.stop
                                                class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug"
                                            >
                                                {{ $thread->title }}
                                            </a>
                                        </div>

                                        <p class="text-[11px] text-text-muted mt-0.5">
                                            Dibuat oleh <span class="font-medium text-text">{{ $thread->user->name }}</span> &bull; Mata Kuliah: {{ $thread->academicClass?->course?->name ?? 'Mata Kuliah Umum' }}
                                        </p>

                                        <div class="mt-1.5">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold {{ $categoryColor }}">
                                                {{ $thread->category->name }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                            {{ Str::limit($thread->content, 140) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Right Stats & Menu -->
                                <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                    <a 
                                        href="{{ route('app.discussions.show', $thread) }}" 
                                        @click.stop
                                        class="text-text-muted hover:text-text p-1 cursor-pointer"
                                        title="Buka detail diskusi"
                                    >
                                        <x-icon name="more-horizontal" :size="14" />
                                    </a>
                                    
                                    <div class="mt-3">
                                        <div class="flex items-center gap-3 text-xs text-text-muted">
                                            <span class="inline-flex items-center gap-1 tabular-nums">
                                                <x-icon name="message-circle" :size="13" />
                                                <span>{{ $thread->replies_count }}</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 tabular-nums">
                                                <x-icon name="eye" :size="13" />
                                                <span>{{ $formattedViews }}</span>
                                            </span>
                                        </div>
                                        <span class="text-[11px] text-text-subtle mt-1 block">
                                            {{ $thread->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-text-muted">
                            <x-icon name="message-square" :size="28" class="mx-auto text-text-subtle mb-2" />
                            <p class="font-semibold text-text">Belum ada topik diskusi yang cocok.</p>
                            <p class="text-text-muted mt-1">Mulai diskusi pertama Anda menggunakan tombol di atas!</p>
                        </div>
                    @endforelse

                </div>

            </div>

            <!-- ==========================================
                 KOLOM KANAN (lg:col-span-4): STATISTIK, KATEGORI & DISKUSI TERPOPULER
                 ========================================== -->
            <div class="lg:col-span-4 space-y-4">
                
                <!-- 1. KARTU STATISTIK DISKUSI (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <h3 class="font-bold text-sm text-text mb-3">
                        Statistik Diskusi
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        
                        <!-- Stat 1: Total Diskusi -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="message-circle" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">{{ $stats['total_threads'] }}</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Total Diskusi</span>
                            </div>
                        </div>

                        <!-- Stat 2: Total Komentar -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="message-square" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">{{ $stats['total_replies'] }}</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Total Komentar</span>
                            </div>
                        </div>

                        <!-- Stat 3: Total Dilihat -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="eye" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">{{ $stats['total_views'] }}</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Total Dilihat</span>
                            </div>
                        </div>

                        <!-- Stat 4: Pengguna Aktif -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="users" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">{{ $stats['active_users'] }}</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Pengguna Aktif</span>
                            </div>
                        </div>

                    </div>
                </x-ui.card>

                <!-- 2. KARTU KATEGORI (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h3 class="font-bold text-sm text-text">
                            Kategori
                        </h3>
                        <button type="button" @click="selectedCategory = 'all'; activeTab = 'all'" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline cursor-pointer">
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </button>
                    </div>

                    <div class="space-y-1">
                        
                        <!-- Kategori: Semua -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'all'; activeTab = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-blue-50/70 text-[#2563EB] font-semibold' : 'text-text hover:bg-canvas/60 font-medium'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] text-xs transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="message-square" :size="14" class="text-[#2563EB]" />
                                <span>Semua</span>
                            </div>
                            <span class="tabular-nums text-xs">{{ $stats['total_threads'] }}</span>
                        </button>

                        @foreach($categories as $cat)
                            @php
                                $iconMap = [
                                    'forum-kelas' => ['icon' => 'notebook-text', 'color' => 'text-blue-500'],
                                    'tanya-jawab' => ['icon' => 'help-circle', 'color' => 'text-emerald-500'],
                                    'pengumuman' => ['icon' => 'megaphone', 'color' => 'text-amber-500'],
                                    'diskusi' => ['icon' => 'message-circle', 'color' => 'text-purple-500'],
                                    'berbagi' => ['icon' => 'share-2', 'color' => 'text-orange-500'],
                                ];
                                $catMeta = $iconMap[$cat->slug] ?? ['icon' => 'message-circle', 'color' => 'text-blue-500'];
                            @endphp
                            <button 
                                type="button" 
                                @click="selectedCategory = '{{ $cat->slug }}'; activeTab = 'all'"
                                :class="selectedCategory === '{{ $cat->slug }}' ? 'bg-blue-50/70 text-[#2563EB] font-semibold' : 'text-text hover:bg-canvas/60 font-medium'"
                                class="w-full flex items-center justify-between p-2 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                <div class="flex items-center gap-2.5">
                                    <x-icon :name="$catMeta['icon']" :size="14" class="{{ $catMeta['color'] }}" />
                                    <span>{{ $cat->name }}</span>
                                </div>
                                <span class="tabular-nums text-text-muted text-xs">{{ $cat->threads_count }}</span>
                            </button>
                        @endforeach

                    </div>
                </x-ui.card>

                <!-- 3. KARTU DISKUSI TERPOPULER (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <h3 class="font-bold text-sm text-text">
                            Diskusi Terpopuler
                        </h3>
                        <button type="button" @click="activeTab = 'populer'; selectedCategory = 'all'" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline cursor-pointer">
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </button>
                    </div>

                    <div class="space-y-3.5">
                        @foreach($popularThreads as $index => $pop)
                            @php
                                $popUser = $pop->user->name;
                                $popAvatar = null;
                                $popInitials = 'U';
                                if (str_contains(strtolower($popUser), 'budi')) {
                                    $popAvatar = '/images/avatar-budi.jpg';
                                } elseif (str_contains(strtolower($popUser), 'siti')) {
                                    $popAvatar = '/images/avatar-siti.jpg';
                                } elseif (str_contains(strtolower($popUser), 'dewi')) {
                                    $popAvatar = '/images/avatar-dewi.jpg';
                                } elseif (str_contains(strtolower($popUser), 'fajar')) {
                                    $popAvatar = '/images/avatar-fajar.jpg';
                                } else {
                                    $pWords = explode(' ', $popUser);
                                    $popInitials = strtoupper(substr($pWords[0], 0, 1) . (isset($pWords[1]) ? substr($pWords[1], 0, 1) : ''));
                                }
                                $popViews = $pop->views_count >= 1000 ? number_format($pop->views_count / 1000, 1) . 'k' : (string) $pop->views_count;
                                $rankBadge = $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600';
                            @endphp

                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full {{ $rankBadge }} flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                    {{ $index + 1 }}
                                </span>
                                
                                @if($popAvatar)
                                    <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 mt-0.5 shadow-2xs">
                                        <img src="{{ $popAvatar }}" alt="{{ $popUser }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-6 h-6 rounded-full bg-[#8B5CF6] text-white flex items-center justify-center text-[9px] font-bold shrink-0 mt-0.5 shadow-2xs">
                                        {{ $popInitials }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <a 
                                        href="{{ route('app.discussions.show', $pop) }}" 
                                        class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer block leading-snug"
                                    >
                                        {{ $pop->title }}
                                    </a>
                                    <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="message-circle" :size="10" />
                                            <span>{{ $pop->replies_count }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="eye" :size="10" />
                                            <span>{{ $popViews }}</span>
                                        </span>
                                        <span class="text-slate-300">|</span>
                                        <span>{{ $pop->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

            </div>

        </div>

        <!-- ==========================================
             4. MODAL INTERAKTIF: BUAT DISKUSI BARU
             ========================================== -->
        <div 
            x-show="newThreadModalOpen" 
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 bg-text/40 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="newThreadModalOpen = false"
        >
            <div 
                @click.outside="newThreadModalOpen = false"
                class="bg-surface border border-line rounded-[12px] max-w-xl w-full p-6 shadow-card space-y-4"
            >
                <form method="POST" action="{{ route('app.discussions.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center">
                                <x-icon name="plus" :size="16" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-text">Buat Diskusi Baru</h3>
                                <p class="text-[11px] text-text-muted">Dapatkan +15 XP saat mempublikasikan topik diskusi.</p>
                            </div>
                        </div>
                        <button type="button" @click="newThreadModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                            <x-icon name="x" :size="18" />
                        </button>
                    </div>

                    <div class="space-y-3 text-xs pt-3">
                        <div>
                            <label class="font-semibold text-text block mb-1">Judul Topik Diskusi</label>
                            <input 
                                type="text" 
                                name="title"
                                required
                                placeholder="Tuliskan pertanyaan atau topik yang jelas..." 
                                class="w-full px-3 py-2 rounded-[6px] border border-line bg-canvas/30 text-xs text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors"
                            >
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-semibold text-text block mb-1">Mata Kuliah</label>
                                <select name="class_id" class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-[#2563EB]">
                                    <option value="">Umum (Semua Mata Kuliah)</option>
                                    @foreach($enrolledClasses as $cl)
                                        <option value="{{ $cl->id }}">{{ $cl->course->code }} - {{ $cl->course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-semibold text-text block mb-1">Kategori</label>
                                <select name="category_id" required class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-[#2563EB]">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="font-semibold text-text block mb-1">Isi Pesan / Pertanyaan</label>
                            <textarea 
                                name="content"
                                rows="4" 
                                required
                                placeholder="Jelaskan secara rinci permasalahan, potongan kode, atau pertanyaan yang ingin Anda diskusikan..." 
                                class="w-full px-3 py-2 rounded-[6px] border border-line bg-canvas/30 text-xs text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors resize-none"
                            ></textarea>
                        </div>

                        <div class="p-3 rounded-[6px] border border-dashed border-line bg-canvas/40 flex items-center justify-between text-xs text-text-muted">
                            <div class="flex items-center gap-2">
                                <x-icon name="paperclip" :size="14" class="text-text-muted" />
                                <span>Lampirkan tangkapan layar atau berkas kode (opsional)</span>
                            </div>
                            <label for="thread_attachment_input" class="text-[#2563EB] font-semibold hover:underline cursor-pointer">
                                <span>Pilih Berkas</span>
                                <input type="file" name="attachment" id="thread_attachment_input" class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-line mt-4">
                        <button 
                            type="button" 
                            @click="newThreadModalOpen = false" 
                            class="px-4 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text cursor-pointer transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                        >
                            <span>Publikasikan Diskusi</span>
                            <x-icon name="send" :size="12" />
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==========================================
             5. MODAL INTERAKTIF: DETAIL THREAD & KOMENTAR
             ========================================== -->
        <div 
            x-show="detailModalOpen" 
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 bg-text/40 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="detailModalOpen = false"
        >
            <div 
                @click.outside="detailModalOpen = false"
                class="bg-surface border border-line rounded-[12px] max-w-2xl w-full p-6 shadow-card space-y-4 max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-line">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span 
                                class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                :class="selectedThread?.categoryColor"
                                x-text="selectedThread?.category"
                            ></span>
                            <span class="text-xs text-text-muted" x-text="selectedThread?.course"></span>
                        </div>
                        <a 
                            :href="selectedThread?.detailUrl" 
                            class="text-base font-bold text-text leading-snug hover:text-[#2563EB] transition-colors block" 
                            x-text="selectedThread?.title"
                        ></a>
                        <p class="text-xs text-text-muted mt-0.5">
                            Dibuat oleh <span class="font-medium text-text" x-text="selectedThread?.author"></span> &bull; <span x-text="selectedThread?.time"></span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a 
                            :href="selectedThread?.detailUrl" 
                            class="px-2.5 py-1 rounded-[6px] bg-blue-50 text-[#2563EB] hover:bg-blue-100 text-[11px] font-semibold inline-flex items-center gap-1 transition-colors"
                        >
                            <span>Buka Lengkap</span>
                            <x-icon name="arrow-right" :size="11" />
                        </a>
                        <button @click="detailModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                            <x-icon name="x" :size="18" />
                        </button>
                    </div>
                </div>

                <!-- Isi Diskusi -->
                <div class="p-3.5 rounded-[8px] bg-canvas border border-line text-xs text-text leading-relaxed whitespace-pre-line" x-text="selectedThread?.snippet">
                </div>

                <!-- Interaksi & Komentar -->
                <div class="pt-2 border-t border-line space-y-3">
                    <div class="flex items-center justify-between text-xs text-text-muted">
                        <span class="font-semibold text-text" x-text="'Tanggapan & Komentar (' + (selectedThread?.commentsCount ?? 0) + ')'"></span>
                        <div class="flex items-center gap-3">
                            <form method="POST" :action="'/app/discussions/' + selectedThread?.id + '/upvote'">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 hover:text-[#2563EB] cursor-pointer">
                                    <x-icon name="thumbs-up" :size="13" />
                                    <span x-text="'Suka (' + (selectedThread?.upvotesCount ?? 0) + ')'"></span>
                                </button>
                            </form>
                            <a :href="selectedThread?.detailUrl" class="inline-flex items-center gap-1 hover:text-[#2563EB] cursor-pointer">
                                <x-icon name="message-square" :size="13" />
                                <span>Lihat Semua Balasan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Input Tambah Komentar Cepat -->
                    <div class="pt-2">
                        <form method="POST" :action="'/app/discussions/' + selectedThread?.id + '/replies'" class="flex items-center gap-2">
                            @csrf
                            <input 
                                type="text" 
                                name="content"
                                required
                                placeholder="Tulis balasan Anda untuk diskusi ini..." 
                                class="flex-1 px-3 py-2 rounded-[6px] border border-line bg-canvas/30 text-xs text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors"
                            >
                            <button 
                                type="submit" 
                                class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1 shadow-2xs transition-colors cursor-pointer shrink-0"
                            >
                                <span>Balas</span>
                                <x-icon name="send" :size="12" />
                            </button>
                        </form>
                        <p class="text-[10px] text-text-subtle mt-1.5">Tanggapan Anda akan memberikan +10 XP pada akun Anda.</p>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-line">
                    <a :href="selectedThread?.detailUrl" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1">
                        <span>Lihat thread selengkapnya</span>
                        <x-icon name="arrow-right" :size="12" />
                    </a>
                    <button 
                        type="button" 
                        @click="detailModalOpen = false" 
                        class="px-4 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
