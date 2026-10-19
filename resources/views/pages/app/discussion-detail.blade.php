@extends('layouts.app')

@section('title', $thread->title . ' — Diskusi Arunika')
@section('page_breadcrumb', 'Diskusi / Detail')

@section('content')

    <div class="space-y-6">

        <!-- ==========================================
             1. BACK LINK & PAGE HEADER
             ========================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a 
                    href="{{ route('app.discussions') }}" 
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563EB] hover:text-blue-800 transition-colors mb-2"
                >
                    <x-icon name="arrow-left" :size="14" />
                    <span>Kembali ke Semua Forum Diskusi</span>
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    @php
                        $catSlug = $thread->category->slug ?? 'forum-kelas';
                        $categoryBadgeMap = [
                            'pengumuman' => 'bg-blue-50 text-blue-600',
                            'tanya-jawab' => 'bg-emerald-50 text-emerald-600',
                            'diskusi' => 'bg-purple-50 text-purple-600',
                            'forum-kelas' => 'bg-blue-50 text-blue-600',
                            'berbagi' => 'bg-orange-50 text-orange-600',
                        ];
                        $catBadge = $categoryBadgeMap[$catSlug] ?? 'bg-blue-50 text-blue-600';
                    @endphp

                    <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold {{ $catBadge }}">
                        {{ $thread->category->name }}
                    </span>

                    @if($thread->is_pinned)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <x-icon name="pin" :size="11" />
                            <span>Disematkan (Pinned)</span>
                        </span>
                    @endif

                    @if($thread->academicClass)
                        <span class="text-xs text-text-muted font-medium">
                            &bull; {{ $thread->academicClass->course?->code }} - {{ $thread->academicClass->course?->name }}
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight mt-1.5 leading-snug">
                    {{ $thread->title }}
                </h1>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="{{ route('app.discussions.upvote', $thread) }}">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-4 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                        title="Berikan apresiasi pada thread ini (+5 XP)"
                    >
                        <x-icon name="thumbs-up" :size="14" class="text-[#2563EB]" />
                        <span>Suka / Upvote</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full bg-blue-50 text-[#2563EB] text-[10px] tabular-nums font-bold">
                            {{ $thread->upvotes_count }}
                        </span>
                    </button>
                </form>

                <a 
                    href="#form-balasan" 
                    class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                >
                    <x-icon name="message-square" :size="14" />
                    <span>Tulis Balasan</span>
                </a>
            </div>
        </div>

        <!-- Session flash message alerts -->
        @if(session('success'))
            <div class="p-3.5 rounded-[8px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-2xs">
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
            <div class="p-3.5 rounded-[8px] bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-2xs">
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
             2. MAIN LAYOUT (2 KOLOM: UTAMA 8, SIDEBAR 4)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- KOLOM KIRI (lg:col-span-8): POSTINGAN UTAMA & BALASAN KOMENTAR -->
            <div class="lg:col-span-8 space-y-6">

                <!-- KARTU THREAD UTAMA -->
                <x-ui.card class="p-5 sm:p-6 space-y-4">
                    <!-- Author Bar -->
                    <div class="flex items-center justify-between gap-4 pb-3.5 border-b border-line">
                        <div class="flex items-center gap-3">
                            @php
                                $uName = $thread->user->name;
                                $uAvatar = null;
                                if (str_contains(strtolower($uName), 'budi')) {
                                    $uAvatar = '/images/avatar-budi.jpg';
                                } elseif (str_contains(strtolower($uName), 'siti')) {
                                    $uAvatar = '/images/avatar-siti.jpg';
                                } elseif (str_contains(strtolower($uName), 'dewi')) {
                                    $uAvatar = '/images/avatar-dewi.jpg';
                                } elseif (str_contains(strtolower($uName), 'fajar')) {
                                    $uAvatar = '/images/avatar-fajar.jpg';
                                }
                                $uWords = explode(' ', $uName);
                                $uInitials = strtoupper(substr($uWords[0], 0, 1) . (isset($uWords[1]) ? substr($uWords[1], 0, 1) : ''));
                            @endphp

                            @if($uAvatar)
                                <div class="w-10 h-10 rounded-full bg-navy-800 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs overflow-hidden">
                                    <img src="{{ $uAvatar }}" alt="{{ $uName }}" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs">
                                    {{ $uInitials }}
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-xs sm:text-sm text-text leading-tight">
                                        {{ $thread->user->name }}
                                    </h3>
                                    @if($thread->user->isLecturer())
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-600">
                                            Dosen Pengampu
                                        </span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                            Mahasiswa
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    Dipublikasikan {{ $thread->created_at->translatedFormat('l, d F Y - H:i') }} &bull; ({{ $thread->created_at->diffForHumans() }})
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-text-muted">
                            <span class="inline-flex items-center gap-1 tabular-nums">
                                <x-icon name="eye" :size="13" />
                                <span>{{ $thread->views_count >= 1000 ? number_format($thread->views_count / 1000, 1) . 'k' : $thread->views_count }} dilihat</span>
                            </span>
                        </div>
                    </div>

                    <!-- Thread Body Content -->
                    <div class="text-xs sm:text-[13px] text-text leading-relaxed whitespace-pre-line space-y-3 pt-1">
                        {{ $thread->content }}
                    </div>

                    <!-- Thread Attachment (if available) -->
                    @if($thread->attachment_path)
                        <div class="p-3 rounded-[8px] bg-canvas border border-line flex items-center justify-between text-xs mt-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <x-icon name="paperclip" :size="15" class="text-[#2563EB] shrink-0" />
                                <span class="text-text font-medium truncate">{{ basename($thread->attachment_path) }}</span>
                            </div>
                            <a 
                                href="{{ route('berkas.diskusi', $thread) }}" 
                                target="_blank"
                                class="px-3 py-1 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1 transition-colors"
                            >
                                <x-icon name="download" :size="12" />
                                <span>Unduh Berkas</span>
                            </a>
                        </div>
                    @endif

                    <!-- Thread Bottom Meta & Actions -->
                    <div class="pt-3 border-t border-line flex items-center justify-between text-xs text-text-muted flex-wrap gap-3">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="message-circle" :size="14" class="text-text-muted" />
                                <span class="font-semibold text-text tabular-nums">{{ $thread->replies->count() }}</span>
                                <span>Tanggapan</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="thumbs-up" :size="14" class="text-text-muted" />
                                <span class="font-semibold text-text tabular-nums">{{ $thread->upvotes_count }}</span>
                                <span>Upvotes</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('app.discussions.upvote', $thread) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                                >
                                    <x-icon name="thumbs-up" :size="12" class="text-[#2563EB]" />
                                    <span>Beri Apresiasi</span>
                                </button>
                            </form>
                            <button 
                                type="button" 
                                @click="navigator.clipboard.writeText(window.location.href); alert('Tautan diskusi telah disalin ke clipboard!')" 
                                class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                            >
                                <x-icon name="share-2" :size="12" class="text-text-muted" />
                                <span>Bagikan</span>
                            </button>
                        </div>
                    </div>
                </x-ui.card>

                <!-- ==========================================
                     DAFTAR BALASAN & KOMENTAR (TIERED / BERJENJANG)
                     ========================================== -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-text flex items-center gap-2">
                            <x-icon name="message-square" :size="16" class="text-[#2563EB]" />
                            <span>Tanggapan &amp; Komentar ({{ $thread->replies->count() }})</span>
                        </h2>
                        <span class="text-xs text-text-muted">
                            Diurutkan dari solusi terbaik &amp; terlama
                        </span>
                    </div>

                    @forelse($thread->replies as $reply)
                        @php
                            $rName = $reply->user->name;
                            $rAvatar = null;
                            if (str_contains(strtolower($rName), 'budi')) {
                                $rAvatar = '/images/avatar-budi.jpg';
                            } elseif (str_contains(strtolower($rName), 'siti')) {
                                $rAvatar = '/images/avatar-siti.jpg';
                            } elseif (str_contains(strtolower($rName), 'dewi')) {
                                $rAvatar = '/images/avatar-dewi.jpg';
                            } elseif (str_contains(strtolower($rName), 'fajar')) {
                                $rAvatar = '/images/avatar-fajar.jpg';
                            }
                            $rWords = explode(' ', $rName);
                            $rInitials = strtoupper(substr($rWords[0], 0, 1) . (isset($rWords[1]) ? substr($rWords[1], 0, 1) : ''));
                            $canMarkSolution = ($user->id === $thread->user_id || $user->isLecturer()) && ! $reply->is_solution;
                        @endphp

                        <div class="p-4 sm:p-5 rounded-[10px] border {{ $reply->is_solution ? 'bg-emerald-50/30 border-emerald-300' : 'bg-surface border-line' }} shadow-2xs space-y-3 transition-colors">
                            
                            <!-- Header Reply -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    @if($rAvatar)
                                        <div class="w-8 h-8 rounded-full bg-navy-800 text-white flex items-center justify-center shrink-0 text-xs font-bold overflow-hidden shadow-2xs">
                                            <img src="{{ $rAvatar }}" alt="{{ $rName }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs">
                                            {{ $rInitials }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs text-text">{{ $reply->user->name }}</span>
                                            @if($reply->user->isLecturer())
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-600">
                                                    Dosen
                                                </span>
                                            @elseif($reply->user_id === $thread->user_id)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">
                                                    Penulis Thread
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-text-muted">
                                            {{ $reply->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Solution Badge -->
                                @if($reply->is_solution)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] bg-emerald-100 text-emerald-800 border border-emerald-300 text-[11px] font-bold shadow-2xs">
                                        <x-icon name="check-circle" :size="13" class="text-emerald-700" />
                                        <span>Solusi / Jawaban Terbaik</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Reply Content -->
                            <div class="text-xs sm:text-[13px] text-text leading-relaxed whitespace-pre-line pl-11">
                                {{ $reply->content }}
                            </div>

                            <!-- Actions Row -->
                            <div class="pl-11 pt-2 border-t border-line/60 flex items-center justify-between text-xs flex-wrap gap-2">
                                <div class="flex items-center gap-3">
                                    <form method="POST" action="{{ route('app.discussions.reply.upvote', $reply) }}">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] hover:bg-canvas text-text-muted hover:text-[#2563EB] transition-colors cursor-pointer text-xs font-medium"
                                        >
                                            <x-icon name="thumbs-up" :size="12" />
                                            <span>Suka</span>
                                            <span class="tabular-nums font-bold text-text">({{ $reply->upvotes_count }})</span>
                                        </button>
                                    </form>

                                    @if($canMarkSolution)
                                        <form method="POST" action="{{ route('app.discussions.reply.solution', $reply) }}">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-800 text-xs font-semibold hover:underline cursor-pointer"
                                                title="Tandai jawaban ini sebagai solusi resmi (+20 XP untuk pembuat jawaban)"
                                            >
                                                <x-icon name="check" :size="13" />
                                                <span>Tandai sebagai Solusi Terbaik</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <span class="text-[11px] text-text-subtle">
                                    +10 XP telah dikreditkan
                                </span>
                            </div>

                        </div>
                    @empty
                        <div class="p-8 rounded-[10px] border border-line bg-surface text-center text-xs text-text-muted">
                            <x-icon name="message-square" :size="32" class="mx-auto text-text-subtle mb-2" />
                            <p class="font-bold text-text">Belum ada tanggapan pada diskusi ini.</p>
                            <p class="text-text-muted mt-1">Jadilah yang pertama memberikan solusi atau sudut pandang Anda!</p>
                        </div>
                    @endforelse
                </div>

                <!-- ==========================================
                     FORM BALASAN CEPAT (STICKY AT BOTTOM)
                     ========================================== -->
                <div id="form-balasan" class="p-5 rounded-[10px] border border-line bg-surface shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center">
                                <x-icon name="message-circle" :size="14" />
                            </div>
                            <h3 class="font-bold text-xs sm:text-sm text-text">
                                Berikan Tanggapan Anda
                            </h3>
                        </div>
                        <span class="text-[11px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-semibold inline-flex items-center gap-1">
                            <x-icon name="zap" :size="12" class="text-emerald-600" />
                            <span>Dapatkan +10 XP</span>
                        </span>
                    </div>

                    <form method="POST" action="{{ route('app.discussions.reply', $thread) }}" class="space-y-3">
                        @csrf
                        <textarea 
                            name="content" 
                            rows="4" 
                            required
                            placeholder="Tuliskan pemikiran, solusi langkah demi langkah, rujukan materi, atau potongan kode..." 
                            class="w-full px-3.5 py-2.5 rounded-[8px] border border-line bg-canvas/30 text-xs sm:text-[13px] text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors resize-none leading-relaxed"
                        ></textarea>

                        <div class="flex items-center justify-between gap-3 pt-1">
                            <p class="text-[11px] text-text-muted">
                                Berikan jawaban yang santun, objektif, dan mendukung kemajuan akademik bersama.
                            </p>
                            <button 
                                type="submit" 
                                class="px-5 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer shrink-0"
                            >
                                <span>Kirim Tanggapan</span>
                                <x-icon name="send" :size="13" />
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- KOLOM KANAN (lg:col-span-4): INFORMASI THREAD & TOPIK LAINNYA -->
            <div class="lg:col-span-4 space-y-4">

                <!-- 1. KARTU INFORMASI THREAD -->
                <x-ui.card class="p-4 sm:p-4.5 space-y-3.5">
                    <h3 class="font-bold text-sm text-text border-b border-line pb-2.5">
                        Informasi Diskusi
                    </h3>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-text-muted">
                            <span>Status Solusi</span>
                            @if($thread->replies->where('is_solution', true)->isNotEmpty())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                    Terjawab (Solved)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">
                                    Belum Terjawab
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-text-muted">
                            <span>Mata Kuliah</span>
                            <span class="font-semibold text-text truncate max-w-[170px] text-right">
                                {{ $thread->academicClass?->course?->name ?? 'Umum' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-text-muted">
                            <span>Dosen Pengampu</span>
                            <span class="font-semibold text-text truncate max-w-[170px] text-right">
                                {{ $thread->academicClass?->lecturer?->name ?? 'Tim Dosen Prodi' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-text-muted">
                            <span>Dibuat Pada</span>
                            <span class="font-semibold text-text">
                                {{ $thread->created_at->format('d M Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-text-muted">
                            <span>Total Pembaca</span>
                            <span class="font-semibold text-text tabular-nums">
                                {{ $thread->views_count }} Kali
                            </span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 2. KARTU DISKUSI TERPOPULER LAINNYA -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <h3 class="font-bold text-sm text-text">
                            Topik Terpopuler Lainnya
                        </h3>
                        <a href="{{ route('app.discussions') }}?tab=populer" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline">
                            <span>Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>

                    <div class="space-y-3">
                        @foreach($popularThreads->where('id', '!=', $thread->id)->take(4) as $pop)
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                    #
                                </span>
                                <div class="min-w-0 flex-1">
                                    <a 
                                        href="{{ route('app.discussions.show', $pop) }}" 
                                        class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer block leading-snug"
                                    >
                                        {{ $pop->title }}
                                    </a>
                                    <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                        <span>{{ $pop->replies_count }} komentar</span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span>{{ $pop->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <!-- 3. ATURAN & ETIKA KOMUNITAS -->
                <x-ui.card class="p-4 sm:p-4.5 bg-canvas/30 border-line">
                    <div class="flex items-center gap-2 mb-2">
                        <x-icon name="shield-check" :size="15" class="text-[#2563EB]" />
                        <h3 class="font-bold text-xs sm:text-sm text-text">
                            Aturan Komunitas Arunika
                        </h3>
                    </div>
                    <ul class="text-[11px] text-text-muted space-y-1.5 list-disc pl-4 leading-relaxed">
                        <li>Jaga kesantunan dan integritas akademik saat berdiskusi.</li>
                        <li>Sertakan potongan kode ringkas bila melaporkan galat/bug.</li>
                        <li>Tandai solusi terbaik agar membantu teman sekelas Anda.</li>
                    </ul>
                </x-ui.card>

            </div>

        </div>

    </div>

@endsection
