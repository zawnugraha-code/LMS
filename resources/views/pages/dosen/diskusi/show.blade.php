@extends('layouts.dosen')

@section('title', $thread->title . ' — Forum Diskusi')
@section('page_breadcrumb', 'Diskusi / Detail')

@section('content')
<div class="space-y-6">
    
    <!-- 1. Top Navigation & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a 
                href="{{ route('dosen.diskusi.index', ['class_id' => $class->id]) }}" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface hover:bg-slate-100 text-text text-xs font-medium rounded-[6px] border border-line transition-colors shadow-2xs"
            >
                <x-icon name="arrow-left" :size="14" />
                <span>Kembali ke Forum</span>
            </a>
            <div class="hidden sm:block h-4 w-px bg-line"></div>
            <span class="text-xs text-text-muted font-medium truncate max-w-xs md:max-w-md">
                {{ $class->course?->code }} - {{ $class->course?->name }} ({{ $class->section }})
            </span>
        </div>

        <!-- Action Buttons (Pin, Lock, Favorite) -->
        <div class="flex items-center gap-2">
            <!-- Toggle Favorite -->
            <form action="{{ route('dosen.diskusi.favorite', $thread->id) }}" method="POST">
                @csrf
                <button 
                    type="submit" 
                    title="{{ $isFavorited ? 'Hapus dari favorit' : 'Tandai sebagai favorit' }}"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-[6px] border text-xs font-medium transition-colors cursor-pointer {{ $isFavorited ? 'bg-amber-50 border-amber-300 text-amber-700' : 'bg-surface border-line text-text hover:bg-slate-50' }}"
                >
                    <x-icon name="star" :size="14" class="{{ $isFavorited ? 'fill-amber-400 text-amber-500' : 'text-text-muted' }}" />
                    <span>{{ $isFavorited ? 'Favorit' : 'Beri Bintang' }}</span>
                </button>
            </form>

            <!-- Toggle Pin -->
            <form action="{{ route('dosen.diskusi.toggle_pin', $thread->id) }}" method="POST">
                @csrf
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-[6px] border text-xs font-medium transition-colors cursor-pointer {{ $thread->is_pinned ? 'bg-blue-50 border-blue-300 text-[#2563EB]' : 'bg-surface border-line text-text hover:bg-slate-50' }}"
                >
                    <x-icon name="pin" :size="14" class="{{ $thread->is_pinned ? 'text-[#2563EB]' : 'text-text-muted' }}" />
                    <span>{{ $thread->is_pinned ? 'Lepas Sematan' : 'Sematkan' }}</span>
                </button>
            </form>

            <!-- Toggle Lock -->
            <form action="{{ route('dosen.diskusi.toggle_lock', $thread->id) }}" method="POST">
                @csrf
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-[6px] border text-xs font-medium transition-colors cursor-pointer {{ $thread->is_locked ? 'bg-red-50 border-red-300 text-red-700' : 'bg-surface border-line text-text hover:bg-slate-50' }}"
                >
                    <x-icon name="{{ $thread->is_locked ? 'unlock' : 'lock' }}" :size="14" class="{{ $thread->is_locked ? 'text-red-600' : 'text-text-muted' }}" />
                    <span>{{ $thread->is_locked ? 'Buka Kunci' : 'Kunci Diskusi' }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Feedback Alerts -->
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

    <!-- Main Content Layout (Col 8 / Col 4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Thread Detail & Conversation Flow (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Thread Card Container -->
            <div class="bg-surface border border-line rounded-[8px] p-6 shadow-xs">
                
                <!-- Category, Pin & Lock status badges -->
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    @php
                        $katSlug = $thread->kategori ?? $thread->category?->slug ?? 'diskusi';
                        if ($katSlug === 'tanya_jawab' || $katSlug === 'tanya-jawab') {
                            $badgeLabel = 'Tanya Jawab';
                            $badgeColor = 'bg-blue-50 text-[#2563EB] border-blue-200';
                        } elseif ($katSlug === 'pengumuman') {
                            $badgeLabel = 'Pengumuman';
                            $badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } else {
                            $badgeLabel = 'Diskusi Umum';
                            $badgeColor = 'bg-purple-50 text-purple-700 border-purple-200';
                        }
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-[4px] text-[11px] font-semibold border {{ $badgeColor }}">
                        {{ $badgeLabel }}
                    </span>

                    @if ($thread->is_pinned)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <x-icon name="pin" :size="11" />
                            <span>Disematkan</span>
                        </span>
                    @endif

                    @if ($thread->is_locked)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <x-icon name="lock" :size="11" />
                            <span>Terkunci</span>
                        </span>
                    @endif
                </div>

                <!-- Thread Title -->
                <h1 class="text-xl font-bold text-text tracking-tight mb-4 leading-snug">
                    {{ $thread->title }}
                </h1>

                <!-- Author Header -->
                <div class="flex items-center justify-between pb-4 border-b border-line mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 overflow-hidden border border-line">
                            @if ($thread->user?->avatar_path)
                                <img src="{{ $thread->user->avatar_path }}" alt="{{ $thread->user->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($thread->user?->name ?? 'P', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-text">{{ $thread->user?->name ?? 'Pengguna' }}</span>
                                @if ($thread->user_id === $class->lecturer_id)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-100 text-[#2563EB]">Dosen Pengampu</span>
                                @else
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Mahasiswa</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-text-muted mt-0.5">
                                Dipublikasikan pada {{ $thread->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB ({{ $thread->created_at->diffForHumans() }})
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Thread Body -->
                <div class="text-xs sm:text-[13px] text-text leading-relaxed whitespace-pre-line space-y-3 font-normal">
                    {{ $thread->content }}
                </div>

                <!-- Thread Meta Footer -->
                <div class="mt-6 pt-4 border-t border-line flex items-center justify-between text-xs text-text-muted">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="message-circle" :size="14" class="text-text-muted" />
                            <span>{{ $thread->replies->count() }} Balasan</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="eye" :size="14" class="text-text-muted" />
                            <span>{{ $thread->views_count ?? 1 }} Dilihat</span>
                        </span>
                    </div>
                    <div>
                        <span class="text-[11px] text-text-subtle">
                            Status: {{ $thread->is_locked ? 'Terkunci dari balasan' : 'Diskusi aktif' }}
                        </span>
                    </div>
                </div>

            </div>

            <!-- 2. Replies / Balasan Section -->
            <div class="space-y-4">
                
                <div class="flex items-center justify-between px-1">
                    <h2 class="text-sm font-bold text-text tracking-tight flex items-center gap-2">
                        <span>Semua Balasan</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                            {{ $thread->replies->count() }}
                        </span>
                    </h2>
                </div>

                <!-- Lock Notice Banner if Thread is Locked -->
                @if ($thread->is_locked)
                    <div class="p-4 rounded-[6px] bg-amber-50/70 border border-amber-200 flex items-start gap-3 text-xs text-amber-800">
                        <x-icon name="lock" :size="16" class="text-amber-600 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="font-semibold">Diskusi Ini Telah Dikunci</p>
                            <p class="text-[11px] text-amber-700 mt-0.5">
                                Topik diskusi ini telah ditutup oleh Dosen Pengampu sehingga mahasiswa dan peserta kelas tidak dapat menambahkan balasan baru. Klik "Buka Kunci" di bilah atas jika ingin mengaktifkannya kembali.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Reply Input Form (Active if not locked) -->
                @if (! $thread->is_locked)
                    <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                            </div>
                            <span class="text-xs font-semibold text-text">Tulis Balasan Sebagai Dosen</span>
                        </div>

                        <form action="{{ route('dosen.diskusi.reply', $thread->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea 
                                name="content" 
                                rows="3" 
                                required
                                placeholder="Berikan arahan, konfirmasi, atau tanggapan Anda untuk mahasiswa di kelas ini..." 
                                class="w-full p-3 bg-canvas/40 border border-line rounded-[6px] text-xs text-text placeholder:text-text-muted focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                            ></textarea>

                            <div class="flex items-center justify-end">
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-medium rounded-[6px] transition-colors shadow-xs cursor-pointer"
                                >
                                    <x-icon name="send" :size="13" />
                                    <span>Kirim Balasan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Conversation Thread List -->
                <div class="space-y-3">
                    @forelse ($thread->replies as $reply)
                        <div class="bg-surface border border-line rounded-[8px] p-4 shadow-2xs space-y-3" x-data="{ replyOpen: false }">
                            <!-- Reply Header -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden border border-line">
                                        @if ($reply->user?->avatar_path)
                                            <img src="{{ $reply->user->avatar_path }}" alt="{{ $reply->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($reply->user?->name ?? 'P', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-text">{{ $reply->user?->name ?? 'Pengguna' }}</span>
                                            @if ($reply->user_id === $class->lecturer_id)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-100 text-[#2563EB]">Dosen</span>
                                            @else
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Mahasiswa</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-text-subtle mt-0.5">
                                            {{ $reply->created_at->diffForHumans() }} • {{ $reply->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                                        </p>
                                    </div>
                                </div>

                                @if (! $thread->is_locked)
                                    <button 
                                        type="button" 
                                        @click="replyOpen = !replyOpen"
                                        class="text-[11px] font-medium text-[#2563EB] hover:underline cursor-pointer inline-flex items-center gap-1"
                                    >
                                        <x-icon name="corner-down-right" :size="12" />
                                        <span>Balas</span>
                                    </button>
                                @endif
                            </div>

                            <!-- Reply Content -->
                            <div class="text-xs text-text leading-relaxed whitespace-pre-line pl-10">
                                {{ $reply->content }}
                            </div>

                            <!-- Nested Replies (Children) -->
                            @if ($reply->children->isNotEmpty())
                                <div class="pl-10 pt-2 space-y-2 border-l-2 border-line/70 ml-4">
                                    @foreach ($reply->children as $child)
                                        <div class="p-3 rounded-[6px] bg-slate-50/70 border border-line/60 space-y-1.5">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0 overflow-hidden">
                                                    {{ strtoupper(substr($child->user?->name ?? 'P', 0, 1)) }}
                                                </div>
                                                <span class="text-xs font-semibold text-text">{{ $child->user?->name ?? 'Pengguna' }}</span>
                                                @if ($child->user_id === $class->lecturer_id)
                                                    <span class="px-1 py-0.2 rounded text-[9px] font-semibold bg-blue-100 text-[#2563EB]">Dosen</span>
                                                @endif
                                                <span class="text-[10px] text-text-subtle ml-auto">{{ $child->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs text-text pl-8 leading-relaxed whitespace-pre-line">
                                                {{ $child->content }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Inline Reply Form for this reply -->
                            @if (! $thread->is_locked)
                                <div x-show="replyOpen" x-cloak class="pl-10 pt-2">
                                    <form action="{{ route('dosen.diskusi.reply', $thread->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $reply->id }}">
                                        <textarea 
                                            name="content" 
                                            rows="2" 
                                            required
                                            placeholder="Tulis balasan langsung untuk {{ $reply->user?->name }}..." 
                                            class="w-full p-2.5 bg-canvas/50 border border-line rounded-[6px] text-xs text-text focus:outline-hidden focus:border-[#2563EB]"
                                        ></textarea>
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                type="button" 
                                                @click="replyOpen = false" 
                                                class="px-2.5 py-1 text-xs text-text-muted hover:text-text cursor-pointer"
                                            >
                                                Batal
                                            </button>
                                            <button 
                                                type="submit" 
                                                class="px-3 py-1 bg-[#1A2E5E] text-white text-xs font-medium rounded-[4px] hover:bg-[#152449] cursor-pointer"
                                            >
                                                Kirim
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="bg-surface border border-line rounded-[8px] p-8 text-center text-text-muted">
                            <x-icon name="message-square-dashed" :size="32" class="mx-auto mb-2 text-text-muted/60" />
                            <p class="text-xs font-medium">Belum ada balasan pada diskusi ini.</p>
                            <p class="text-[11px] text-text-subtle mt-0.5">Gunakan formulir di atas untuk memulai tanggapan pertama.</p>
                        </div>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- Right: Information & Class Context Sidebar (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- Class Context Card -->
            <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-line mb-3">
                    <x-icon name="layers" :size="16" class="text-[#2563EB]" />
                    <h3 class="text-xs font-bold text-text tracking-tight">Konteks Kelas</h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[11px] text-text-muted">Mata Kuliah:</span>
                        <p class="font-semibold text-text mt-0.5">{{ $class->course?->code }} - {{ $class->course?->name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div class="bg-slate-50 border border-line/60 rounded-[6px] p-2">
                            <span class="text-[10px] text-text-muted block">Kelas</span>
                            <span class="font-bold text-text">{{ $class->section }}</span>
                        </div>
                        <div class="bg-slate-50 border border-line/60 rounded-[6px] p-2">
                            <span class="text-[10px] text-text-muted block">Semester</span>
                            <span class="font-bold text-text">Ganjil 2026/2027</span>
                        </div>
                    </div>

                    <div class="pt-1">
                        <span class="text-[11px] text-text-muted">Dosen Pengampu:</span>
                        <p class="font-medium text-text mt-0.5">{{ $class->lecturer?->name ?? 'Dr. Budi Rahardjo' }}</p>
                    </div>

                    <div class="pt-2 border-t border-line">
                        <a 
                            href="{{ route('dosen.kelas.show', $class->id) }}" 
                            class="inline-flex items-center gap-1.5 text-xs text-[#2563EB] hover:underline font-medium"
                        >
                            <span>Buka Halaman Detail Kelas</span>
                            <x-icon name="arrow-up-right" :size="12" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Thread Etiquette / Guidelines Card -->
            <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-line mb-3">
                    <x-icon name="shield-alert" :size="16" class="text-amber-600" />
                    <h3 class="text-xs font-bold text-text tracking-tight">Panduan Forum Diskusi</h3>
                </div>

                <ul class="text-[11px] text-text-muted space-y-2 list-disc list-inside leading-relaxed">
                    <li>Gunakan bahasa yang santun, profesional, dan konstruktif.</li>
                    <li>Sematkan (Pin) topik pengumuman atau panduan penting agar tetap berada di urutan teratas.</li>
                    <li>Kunci topik diskusi jika sesi tanya jawab telah ditutup atau masalah sudah terselesaikan.</li>
                    <li>Notifikasi balasan akan otomatis dikirimkan ke mahasiswa pembuat diskusi jika preferensi aktif.</li>
                </ul>
            </div>

        </div>

    </div>

</div>
@endsection
