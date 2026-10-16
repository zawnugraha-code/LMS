@extends('layouts.app')

@section('title', 'Mata Kuliah — Arunika')
@section('page_breadcrumb', 'Mata Kuliah')

@section('content')

    <div x-data="{ 
        activeTab: 'semua', 
        searchQuery: '',
        filterOpen: false,
        filterLecturer: '',
        filterDay: '',
        filterStatus: '',
        resetFilters() {
            this.filterLecturer = '';
            this.filterDay = '';
            this.filterStatus = '';
        }
    }">
        
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI, DROPDOWN SEMESTER
             ========================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3.5">
                <div class="shrink-0 text-primary">
                    <x-icon name="book-open" :size="32" :strokeWidth="2" class="text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Mata Kuliah
                    </h1>
                    <p class="text-[13px] text-text-muted mt-0.5">
                        Kelola dan akses semua mata kuliah yang kamu ambil di semester ini.
                    </p>
                </div>
            </div>

            <!-- Dropdown Semester (Kanan) -->
            <div class="relative" x-data="{ termDropdownOpen: false }">
                <button 
                    type="button"
                    @click="termDropdownOpen = !termDropdownOpen"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs cursor-pointer hover:bg-canvas transition-colors self-start md:self-auto"
                >
                    <x-icon name="calendar" :size="15" class="text-text-muted" />
                    <span>{{ $activeTerm->name ?? 'Semester 3 (Ganjil 2026/2027)' }}</span>
                    <x-icon name="chevron-down" :size="13" class="text-text-muted" />
                </button>

                <!-- Dropdown Menu -->
                <div 
                    x-show="termDropdownOpen" 
                    @click.outside="termDropdownOpen = false"
                    x-transition
                    class="absolute right-0 mt-1 w-64 rounded-[8px] bg-surface border border-line shadow-lg p-1.5 z-20"
                    style="display: none;"
                >
                    <div class="px-2 py-1 text-[11px] font-semibold text-text-subtle uppercase">Pilih Semester</div>
                    @if(isset($availableTerms))
                        @foreach($availableTerms as $term)
                            <form method="POST" action="{{ route('app.term.switch') }}">
                                @csrf
                                <input type="hidden" name="term_id" value="{{ $term->id }}">
                                <button 
                                    type="submit" 
                                    class="w-full text-left px-2.5 py-1.5 text-xs rounded-[6px] transition-colors flex items-center justify-between {{ $term->id === ($activeTerm->id ?? null) ? 'bg-primary/10 text-primary font-semibold' : 'text-text hover:bg-canvas' }}"
                                >
                                    <span>{{ $term->name }}</span>
                                    @if($term->id === ($activeTerm->id ?? null))
                                        <x-icon name="check" :size="13" class="text-primary" />
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- ==========================================
             2. RINGKASAN STATISTIK (5 SEGMEN)
             ========================================== -->
        <x-ui.card class="p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-0 divide-y sm:divide-y-0 lg:divide-x divide-line">
                <!-- Segmen 1: Total Mata Kuliah (Dengan Avatar Ikon Topi Wisuda) -->
                <div class="flex items-center gap-4 lg:pr-6 pb-3 sm:pb-0">
                    <div class="w-12 h-12 rounded-full bg-accent-soft flex items-center justify-center shrink-0">
                        <x-icon name="graduation-cap" :size="24" class="text-accent" />
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-text-muted block">Total Mata Kuliah</span>
                        <span class="text-lg font-bold text-text mt-0.5 block leading-tight">{{ $summary['total_courses'] ?? 6 }} Mata Kuliah</span>
                    </div>
                </div>

                <!-- Segmen 2: SKS Total -->
                <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="landmark" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">{{ $summary['total_credits'] ?? 18 }}</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">SKS Total</span>
                    </div>
                </div>

                <!-- Segmen 3: SKS Diambil -->
                <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="folder-open" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">{{ $summary['credits_taken'] ?? 12 }}</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">SKS Diambil</span>
                    </div>
                </div>

                <!-- Segmen 4: Mata Kuliah Aktif -->
                <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="users" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">{{ $summary['active_courses'] ?? 6 }}</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">Mata Kuliah Aktif</span>
                    </div>
                </div>

                <!-- Segmen 5: Mata Kuliah Selesai -->
                <div class="flex items-center gap-3.5 lg:pl-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="check-circle" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">{{ $summary['completed_courses'] ?? 0 }}</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">Mata Kuliah Selesai</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- ==========================================
             3. TOOLBAR: TAB FILTER, PENCARIAN & FILTER BUTTON
             ========================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <!-- Tab Filter Pills -->
            <div class="inline-flex items-center gap-1 bg-surface border border-line rounded-[8px] p-1 shadow-2xs self-start">
                <button 
                    type="button"
                    @click="activeTab = 'semua'"
                    :class="activeTab === 'semua' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Semua
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'aktif'"
                    :class="activeTab === 'aktif' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Aktif
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'selesai'"
                    :class="activeTab === 'selesai' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Selesai
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'arsip'"
                    :class="activeTab === 'arsip' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Arsip
                </button>
            </div>

            <!-- Input Pencarian & Tombol Filter (Kanan) -->
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input 
                        x-model="searchQuery" 
                        type="text" 
                        placeholder="Cari nama mata kuliah..." 
                        class="w-64 h-9 pl-8 pr-3 rounded-[6px] bg-surface border border-line text-text text-xs placeholder:text-text-subtle focus:outline-none focus:border-accent shadow-2xs transition-colors"
                    >
                    <x-icon name="search" :size="14" class="text-text-subtle absolute left-2.5 top-2.5" />
                </div>

                <div class="relative">
                    <button 
                        type="button"
                        @click="filterOpen = !filterOpen"
                        :class="{'border-primary text-primary font-semibold': filterLecturer || filterDay || filterStatus}"
                        class="h-9 px-3.5 rounded-[6px] bg-surface hover:bg-canvas border border-line text-text text-xs font-medium inline-flex items-center gap-2 shadow-2xs transition-colors cursor-pointer"
                    >
                        <x-icon name="filter" :size="14" class="text-text-muted" />
                        <span>Filter</span>
                        <span x-show="filterLecturer || filterDay || filterStatus" class="w-1.5 h-1.5 rounded-full bg-primary" style="display: none;"></span>
                    </button>

                    <!-- Filter Popover -->
                    <div 
                        x-show="filterOpen" 
                        @click.outside="filterOpen = false"
                        x-transition
                        class="absolute right-0 mt-1.5 w-72 p-3.5 rounded-[8px] bg-surface border border-line shadow-lg z-20 space-y-3"
                        style="display: none;"
                    >
                        <div class="flex items-center justify-between pb-2 border-b border-line">
                            <span class="text-xs font-bold text-text">Filter Mata Kuliah</span>
                            <button type="button" @click="resetFilters()" class="text-[11px] text-primary hover:underline font-medium">Reset</button>
                        </div>

                        <!-- Filter Dosen -->
                        @if(isset($filterOptions['lecturers']) && count($filterOptions['lecturers']) > 0)
                            <div>
                                <label class="block text-[11px] font-medium text-text-muted mb-1">Dosen Pengampu</label>
                                <select x-model="filterLecturer" class="w-full h-8 px-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-accent">
                                    <option value="">Semua Dosen</option>
                                    @foreach($filterOptions['lecturers'] as $lecturer)
                                        <option value="{{ $lecturer->name }}">{{ $lecturer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Hari -->
                        @if(isset($filterOptions['days']) && count($filterOptions['days']) > 0)
                            <div>
                                <label class="block text-[11px] font-medium text-text-muted mb-1">Hari Kuliah</label>
                                <select x-model="filterDay" class="w-full h-8 px-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-accent">
                                    <option value="">Semua Hari</option>
                                    @foreach($filterOptions['days'] as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Status -->
                        <div>
                            <label class="block text-[11px] font-medium text-text-muted mb-1">Status</label>
                            <select x-model="filterStatus" class="w-full h-8 px-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-accent">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="completed">Selesai</option>
                                <option value="upcoming">Akan Datang</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             4. GRID KARTU MATA KULIAH (DATABASE-DRIVEN, IDENTIK DENGAN DESAIN)
             ========================================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 items-stretch">
            
            @forelse($courses as $course)
                <x-ui.card 
                    x-show="(activeTab === 'semua' || (activeTab === 'aktif' && '{{ $course->status }}' !== 'completed' && '{{ $course->status }}' !== 'archived') || (activeTab === 'selesai' && '{{ $course->status }}' === 'completed') || (activeTab === 'arsip' && '{{ $course->status }}' === 'archived')) && (!searchQuery || '{{ strtolower($course->code . ' ' . $course->name) }}'.includes(searchQuery.toLowerCase())) && (!filterLecturer || filterLecturer === '{{ $course->lecturer_name }}') && (!filterDay || filterDay === '{{ $course->schedule_day }}') && (!filterStatus || filterStatus === '{{ $course->status }}')"
                    class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group"
                >
                    <div class="space-y-3.5">
                        <!-- Kode MK & Badge Status -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-text-muted">{{ $course->code }}</span>
                            @if($course->is_locked)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-[4px] bg-accent-soft text-accent border border-accent/20">
                                    {{ $course->badge_text }}
                                </span>
                            @elseif($course->code === 'IF2105')
                                <x-ui.badge variant="success" class="font-semibold">{{ $course->credits }} SKS</x-ui.badge>
                            @else
                                <x-ui.badge variant="success" class="font-semibold">{{ $course->badge_text }}</x-ui.badge>
                            @endif
                        </div>

                        <!-- Judul Mata Kuliah & Chevron -->
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                                {{ $course->name }}
                            </h3>
                            <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                        </div>

                        <!-- Info Dosen & Jadwal -->
                        <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                            <div class="flex items-center gap-2">
                                <x-icon name="user" :size="14" class="text-text-subtle shrink-0" />
                                <span class="truncate">{{ $course->lecturer_name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                                <span class="truncate">{{ $course->schedule_text }}</span>
                            </div>
                        </div>

                        <!-- Progres Materi -->
                        <div class="pt-3 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-text">Progres Materi</span>
                                <span class="text-text-muted tabular-nums">{{ $course->progress_completed }} / {{ $course->progress_total }} Pertemuan</span>
                            </div>
                            <x-ui.progress :value="$course->progress_percentage" height="h-1.5" color="bg-accent" />
                        </div>
                    </div>

                    <!-- Footer Aksi -->
                    <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                        <a href="{{ $course->detail_url }}" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                            <x-icon name="file-text" :size="14" class="text-text-muted" />
                            <span>Lihat Detail</span>
                        </a>
                        @if($course->is_locked)
                            <button type="button" disabled title="Tersedia mulai {{ $course->start_date ? \App\Helpers\ArunikaDate::formatDate($course->start_date) : 'Semester Depan' }}" class="px-4 py-1.5 rounded-[6px] bg-canvas border border-line text-text-muted text-xs font-medium inline-flex items-center gap-1.5 cursor-not-allowed opacity-80">
                                <x-icon name="lock" :size="13" class="text-text-muted" />
                                <span>Masuk Kelas</span>
                            </button>
                        @else
                            <a href="{{ $course->classroom_url }}" class="px-4 py-1.5 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors">
                                <span>Masuk Kelas</span>
                                <x-icon name="chevron-right" :size="13" class="text-white" />
                            </a>
                        @endif
                    </div>
                </x-ui.card>
            @empty
                <div class="col-span-full py-12 text-center text-text-muted">
                    <x-icon name="book-open" :size="32" class="mx-auto text-text-subtle mb-2" />
                    <p class="font-medium text-sm">Tidak ada mata kuliah yang ditemukan.</p>
                    <p class="text-xs text-text-subtle mt-0.5">Coba ubah filter atau semester yang dipilih.</p>
                </div>
            @endforelse

        </div>

    </div>

@endsection
