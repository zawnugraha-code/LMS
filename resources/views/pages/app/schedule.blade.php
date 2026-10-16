@extends('layouts.app')

@section('title', 'Jadwal & Kalender Akademik — Arunika')
@section('page_breadcrumb', 'Jadwal Kuliah')

@section('content')
<div 
    x-data="{
        activeView: 'weekly', // 'weekly', 'calendar', 'agenda'
        filterCourse: '',
        filterDay: '',
        selectedDateEvents: null,
        selectedDateLabel: '',
        modalOpen: false,
        openDayModal(dateStr, events, label) {
            this.selectedDateEvents = events;
            this.selectedDateLabel = label;
            this.modalOpen = true;
        }
    }"
    class="space-y-6"
>
    <!-- ==========================================
         1. PAGE HEADER: JUDUL, SUBTITLE, TOMBOL AKSI & DROPDOWN SEMESTER
         ========================================== -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-[10px] bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <x-icon name="calendar" :size="26" class="text-primary" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Jadwal &amp; Kalender Akademik
                </h1>
                <p class="text-[13px] text-text-muted mt-0.5">
                    Matriks jadwal perkuliahan mingguan, agenda harian, dan sinkronisasi kalender semester aktif.
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Kanan: Dropdown Semester & Tombol Ekspor -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Dropdown Semester -->
            <div class="relative" x-data="{ termDropdownOpen: false }">
                <button 
                    type="button"
                    @click="termDropdownOpen = !termDropdownOpen"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs hover:bg-canvas transition-colors cursor-pointer"
                >
                    <x-icon name="calendar" :size="14" class="text-text-muted" />
                    <span>{{ $activeTerm->name ?? 'Semester Ganjil 2026/2027' }}</span>
                    <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                </button>

                <div 
                    x-show="termDropdownOpen" 
                    @click.outside="termDropdownOpen = false"
                    x-transition
                    class="absolute right-0 mt-1 w-64 rounded-[8px] bg-surface border border-line shadow-lg p-1.5 z-30"
                    style="display: none;"
                >
                    <div class="px-2 py-1 text-[11px] font-semibold text-text-subtle uppercase">Pilih Semester</div>
                    @if(isset($allTerms))
                        @foreach($allTerms as $term)
                            <form action="{{ route('app.term.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="term_id" value="{{ $term->id }}">
                                <button 
                                    type="submit" 
                                    class="w-full text-left px-2.5 py-1.5 text-xs rounded-[6px] transition-colors flex items-center justify-between {{ $term->id === ($selectedTermId ?? null) ? 'bg-primary/10 text-primary font-semibold' : 'text-text hover:bg-canvas' }}"
                                >
                                    <span>{{ $term->name }}</span>
                                    @if($term->id === ($selectedTermId ?? null))
                                        <x-icon name="check" :size="13" class="text-primary" />
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Tombol Cetak Jadwal -->
            <button 
                type="button" 
                onclick="window.print()" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs hover:bg-canvas transition-colors cursor-pointer"
                title="Cetak Jadwal Kuliah Resmi"
            >
                <x-icon name="printer" :size="14" class="text-text-muted" />
                <span class="hidden sm:inline">Cetak</span>
            </button>

            <!-- Tombol Ekspor iCalendar (.ics) -->
            <a 
                href="{{ route('app.schedule.export.ics') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold shadow-xs transition-colors"
                title="Sinkronisasi ke Google Calendar, Apple Calendar, atau Outlook"
            >
                <x-icon name="download" :size="14" class="text-white" />
                <span>Ekspor iCal (.ics)</span>
            </a>
        </div>
    </div>

    <!-- ==========================================
         2. RINGKASAN STATISTIK JADWAL (4 SEGMEN)
         ========================================== -->
    <x-ui.card class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-0 divide-y sm:divide-y-0 lg:divide-x divide-line">
            <!-- Segmen 1: Total SKS Diambil -->
            <div class="flex items-center gap-3.5 lg:pr-6 pb-3 sm:pb-0">
                <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <x-icon name="graduation-cap" :size="22" class="text-[#2563EB]" />
                </div>
                <div>
                    <span class="text-[11px] font-medium text-text-muted block">Total Beban SKS</span>
                    <span class="text-lg font-bold text-text mt-0.5 block leading-tight">{{ $summary['total_credits'] ?? 18 }} SKS</span>
                    <span class="text-[10px] text-text-subtle">{{ $summary['total_courses'] ?? 6 }} Mata Kuliah Terdaftar</span>
                </div>
            </div>

            <!-- Segmen 2: Jam Kuliah per Minggu -->
            <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                <div class="p-2.5 rounded-[6px] bg-emerald-50 border border-emerald-200 shrink-0">
                    <x-icon name="clock" :size="18" class="text-emerald-700" />
                </div>
                <div>
                    <span class="text-base font-bold text-text tabular-nums block leading-tight">{{ $summary['total_hours_per_week'] ?? 12 }} Jam</span>
                    <span class="text-[11px] font-medium text-text-muted block mt-0.5">Waktu Belajar / Minggu</span>
                </div>
            </div>

            <!-- Segmen 3: Hari Terpadat -->
            <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                <div class="p-2.5 rounded-[6px] bg-amber-50 border border-amber-200 shrink-0">
                    <x-icon name="trending-up" :size="18" class="text-amber-700" />
                </div>
                <div>
                    <span class="text-base font-bold text-text block leading-tight">{{ $summary['busiest_day'] ?? 'Jumat' }}</span>
                    <span class="text-[11px] font-medium text-text-muted block mt-0.5">Hari Perkuliahan Terpadat</span>
                </div>
            </div>

            <!-- Segmen 4: Sesi Kuliah Hari Ini -->
            <div class="flex items-center gap-3.5 lg:pl-6 pt-3 sm:pt-0">
                <div class="p-2.5 rounded-[6px] bg-indigo-50 border border-indigo-200 shrink-0">
                    <x-icon name="calendar-check" :size="18" class="text-indigo-700" />
                </div>
                <div>
                    <span class="text-base font-bold text-text tabular-nums block leading-tight">{{ $summary['today_count'] ?? 0 }} Sesi</span>
                    <span class="text-[11px] font-medium text-text-muted block mt-0.5">Jadwal Kuliah Hari Ini</span>
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- ==========================================
         3. KONTROL TAMPILAN (TAB VIEW) & FILTER
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface p-2.5 rounded-[10px] border border-line shadow-2xs">
        <!-- Switcher Mode Tampilan: Mingguan / Kalender / Agenda -->
        <div class="inline-flex p-1 rounded-[8px] bg-canvas border border-line/60">
            <button 
                type="button" 
                @click="activeView = 'weekly'" 
                :class="activeView === 'weekly' ? 'bg-surface text-primary font-semibold shadow-2xs' : 'text-text-muted hover:text-text'" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs transition-all cursor-pointer"
            >
                <x-icon name="layout-grid" :size="14" />
                <span>Matriks Mingguan</span>
            </button>
            <button 
                type="button" 
                @click="activeView = 'calendar'" 
                :class="activeView === 'calendar' ? 'bg-surface text-primary font-semibold shadow-2xs' : 'text-text-muted hover:text-text'" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs transition-all cursor-pointer"
            >
                <x-icon name="calendar" :size="14" />
                <span>Kalender Bulanan</span>
            </button>
            <button 
                type="button" 
                @click="activeView = 'agenda'" 
                :class="activeView === 'agenda' ? 'bg-surface text-primary font-semibold shadow-2xs' : 'text-text-muted hover:text-text'" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs transition-all cursor-pointer"
            >
                <x-icon name="list" :size="14" />
                <span>Agenda &amp; Rincian</span>
            </button>
        </div>

        <!-- Filter Dropdown & Tombol Lompat ke Hari Ini -->
        <div class="flex items-center gap-2">
            <!-- Filter Mata Kuliah -->
            <select 
                x-model="filterCourse" 
                class="text-xs bg-surface border border-line rounded-[6px] px-2.5 py-1.5 text-text focus:outline-none focus:border-accent"
            >
                <option value="">Semua Mata Kuliah</option>
                @if(isset($enrolledClasses))
                    @foreach($enrolledClasses as $cl)
                        <option value="{{ $cl->course?->code }}">{{ $cl->course?->code }} — {{ $cl->course?->name }}</option>
                    @endforeach
                @endif
            </select>

            <!-- Hari Ini Pill -->
            <span class="text-xs px-2.5 py-1 rounded-[6px] bg-blue-50 text-[#2563EB] font-medium border border-blue-200 shrink-0">
                {{ now()->locale('id')->isoFormat('dddd, D MMM') }}
            </span>
        </div>
    </div>

    <!-- ==========================================
         4. VIEW 1: MATRIKS MINGGUAN (TIMETABLE GRID)
         ========================================== -->
    <div x-show="activeView === 'weekly'" x-transition class="space-y-4">
        <x-ui.card class="overflow-hidden border border-line">
            <div class="p-4 border-b border-line bg-gradient-to-r from-canvas via-surface to-canvas flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-icon name="clock" :size="16" class="text-primary" />
                    <h3 class="text-sm font-bold text-text">Jadwal Kuliah Mingguan (Senin &ndash; Sabtu)</h3>
                </div>
                <span class="text-xs text-text-muted">Zona Waktu: WIB (Asia/Jakarta)</span>
            </div>

            <!-- Grid Header Hari -->
            <div class="overflow-x-auto">
                <div class="min-w-[840px]">
                    <!-- Table Header -->
                    <div class="grid grid-cols-7 border-b border-line bg-canvas text-xs font-semibold text-text divide-x divide-line text-center">
                        <div class="py-2.5 text-text-muted font-medium w-20">Waktu</div>
                        @php
                            $daysHeader = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            $todayName = now()->locale('id')->isoFormat('dddd');
                        @endphp
                        @foreach($daysHeader as $day)
                            <div class="py-2.5 flex items-center justify-center gap-1.5 {{ strcasecmp($todayName, $day) === 0 ? 'bg-primary/10 text-primary font-bold' : '' }}">
                                <span>{{ $day }}</span>
                                @if(strcasecmp($todayName, $day) === 0)
                                    <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Timetable Rows (07:00 to 17:00) -->
                    <div class="divide-y divide-line text-xs">
                        @php
                            $hours = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
                        @endphp

                        @foreach($hours as $hour)
                            @php
                                $hourInt = (int) substr($hour, 0, 2);
                            @endphp
                            <div class="grid grid-cols-7 divide-x divide-line min-h-[64px]">
                                <!-- Time Column -->
                                <div class="py-2 px-2 text-[11px] font-medium text-text-subtle text-right bg-canvas/40 w-20 shrink-0">
                                    {{ $hour }}
                                </div>

                                <!-- 6 Day Cells -->
                                @foreach($daysHeader as $day)
                                    @php
                                        $dayClasses = $weeklySchedule[$day] ?? collect();
                                        // Match classes starting at this hour
                                        $matchingClasses = $dayClasses->filter(function($c) use ($hourInt) {
                                            $cStart = (int) \Carbon\Carbon::parse($c->start_time)->format('H');
                                            return $cStart === $hourInt;
                                        });
                                        $isToday = strcasecmp($todayName, $day) === 0;
                                    @endphp

                                    <div class="p-1 relative {{ $isToday ? 'bg-blue-50/20' : '' }}">
                                        @foreach($matchingClasses as $class)
                                            <div 
                                                x-show="!filterCourse || filterCourse === '{{ $class->course?->code }}'"
                                                class="rounded-[6px] p-2 border shadow-2xs transition-all hover:shadow-xs {{ $class->color_theme['bg'] }} {{ $class->color_theme['border'] }} relative group"
                                            >
                                                <div class="flex items-center justify-between gap-1 mb-1">
                                                    <span class="font-bold text-[11px] {{ $class->color_theme['text'] }}">{{ $class->course?->code }}</span>
                                                    <span class="text-[10px] text-text-muted font-medium tabular-nums">{{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($class->end_time)->format('H:i') }}</span>
                                                </div>
                                                <p class="text-xs font-semibold text-text line-clamp-2 leading-tight">
                                                    {{ $class->course?->name }}
                                                </p>
                                                <div class="flex items-center justify-between mt-1.5 pt-1 border-t border-line/60 text-[10px] text-text-muted">
                                                    <span class="truncate max-w-[90px]">{{ $class->room ?? 'Lab' }}</span>
                                                    <a 
                                                        href="{{ route('app.courses.classroom', $class) }}" 
                                                        class="font-semibold text-accent hover:underline flex items-center gap-0.5"
                                                    >
                                                        <span>Kelas</span>
                                                        <x-icon name="arrow-up-right" :size="10" />
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- ==========================================
         5. VIEW 2: KALENDER BULANAN INTERAKTIF (MONTHLY GRID)
         ========================================== -->
    <div x-show="activeView === 'calendar'" x-transition class="space-y-4">
        <x-ui.card class="overflow-hidden border border-line">
            <!-- Calendar Navigation Header -->
            <div class="p-4 border-b border-line bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1">
                        @php
                            $prevMonth = $currentMonthCarbon->copy()->subMonth();
                            $nextMonth = $currentMonthCarbon->copy()->addMonth();
                        @endphp
                        <a 
                            href="{{ route('app.schedule', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" 
                            class="p-1.5 rounded-[6px] border border-line hover:bg-canvas text-text-muted hover:text-text transition-colors"
                            title="Bulan Sebelumnya"
                        >
                            <x-icon name="chevron-left" :size="16" />
                        </a>
                        <a 
                            href="{{ route('app.schedule', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
                            class="p-1.5 rounded-[6px] border border-line hover:bg-canvas text-text-muted hover:text-text transition-colors"
                            title="Bulan Berikutnya"
                        >
                            <x-icon name="chevron-right" :size="16" />
                        </a>
                    </div>
                    <h3 class="text-base font-bold text-text">
                        {{ $currentMonthCarbon->locale('id')->isoFormat('MMMM Y') }}
                    </h3>
                </div>

                <!-- Event Category Legend -->
                <div class="flex flex-wrap items-center gap-3 text-xs text-text-muted">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB]"></span>
                        <span>Perkuliahan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>Tenggat Tugas</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                        <span>Ujian &amp; Kuis</span>
                    </div>
                </div>
            </div>

            <!-- Month Grid Table -->
            @php
                $startOfMonth = $currentMonthCarbon->copy()->startOfMonth();
                $endOfMonth = $currentMonthCarbon->copy()->endOfMonth();
                $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
                $daysInMonth = $currentMonthCarbon->daysInMonth;
                $todayDateStr = now()->format('Y-m-d');
            @endphp

            <div class="overflow-x-auto">
                <div class="min-w-[700px]">
                    <!-- Weekday Headers -->
                    <div class="grid grid-cols-7 border-b border-line bg-canvas text-xs font-semibold text-text-muted text-center py-2">
                        <div>Minggu</div>
                        <div>Senin</div>
                        <div>Selasa</div>
                        <div>Rabu</div>
                        <div>Kamis</div>
                        <div>Jumat</div>
                        <div>Sabtu</div>
                    </div>

                    <!-- Day Cells Grid -->
                    <div class="grid grid-cols-7 divide-x divide-y divide-line text-xs">
                        {{-- Empty cells before day 1 --}}
                        @for($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="min-h-[96px] p-2 bg-canvas/30"></div>
                        @endfor

                        {{-- Days of current month --}}
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $cellDate = Carbon\Carbon::createFromDate($currentMonthCarbon->year, $currentMonthCarbon->month, $day);
                                $dateKey = $cellDate->format('Y-m-d');
                                $isToday = $dateKey === $todayDateStr;
                                $dayEvents = $monthlyEvents[$dateKey] ?? [];
                            @endphp

                            <div 
                                class="min-h-[96px] p-2 transition-colors relative hover:bg-canvas/40 cursor-pointer {{ $isToday ? 'bg-blue-50/30' : '' }}"
                                @if(count($dayEvents) > 0)
                                    @click="openDayModal('{{ $dateKey }}', {{ json_encode($dayEvents) }}, '{{ $cellDate->locale('id')->isoFormat('dddd, D MMMM Y') }}')"
                                @endif
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold tabular-nums {{ $isToday ? 'w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center -ml-1' : 'text-text' }}">
                                        {{ $day }}
                                    </span>
                                    @if(count($dayEvents) > 0)
                                        <span class="text-[10px] text-text-subtle font-medium">{{ count($dayEvents) }} kegiatan</span>
                                    @endif
                                </div>

                                <!-- Event chips in cell -->
                                <div class="space-y-1">
                                    @foreach(array_slice($dayEvents, 0, 2) as $ev)
                                        <div class="truncate text-[10px] px-1.5 py-0.5 rounded-[4px] border {{ $ev['color'] }} font-medium" title="{{ $ev['title'] }}">
                                            {{ $ev['short_title'] }}
                                        </div>
                                    @endforeach
                                    @if(count($dayEvents) > 2)
                                        <span class="text-[9px] text-accent font-semibold block text-right">+{{ count($dayEvents) - 2 }} lainnya</span>
                                    @endif
                                </div>
                            </div>
                        @endfor

                        {{-- Fill end of grid --}}
                        @php
                            $totalCells = $startDayOfWeek + $daysInMonth;
                            $remaining = (7 - ($totalCells % 7)) % 7;
                        @endphp
                        @for($i = 0; $i < $remaining; $i++)
                            <div class="min-h-[96px] p-2 bg-canvas/30"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- ==========================================
         6. VIEW 3: AGENDA HARIAN & RINCIAN PERKULIAHAN
         ========================================== -->
    <div x-show="activeView === 'agenda'" x-transition class="space-y-6">
        @foreach($weeklySchedule as $dayName => $dayClasses)
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-line pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ strcasecmp(now()->locale('id')->isoFormat('dddd'), $dayName) === 0 ? 'bg-accent' : 'bg-line-strong' }}"></span>
                        <h3 class="font-bold text-base text-text">{{ $dayName }}</h3>
                        <span class="text-xs text-text-muted">({{ $dayClasses->count() }} Mata Kuliah)</span>
                    </div>
                    @if(strcasecmp(now()->locale('id')->isoFormat('dddd'), $dayName) === 0)
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-[#2563EB] text-[11px] font-bold">Hari Ini</span>
                    @endif
                </div>

                @if($dayClasses->isEmpty())
                    <p class="text-xs text-text-muted italic py-2 pl-4">Tidak ada jadwal perkuliahan di hari {{ $dayName }}.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($dayClasses as $item)
                            <x-ui.card class="p-4 border border-line hover:border-line-strong transition-all">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-[4px] text-[11px] font-bold {{ $item->color_theme['badge'] }}">
                                                {{ $item->course?->code }}
                                            </span>
                                            <span class="text-xs font-semibold text-text tabular-nums">
                                                {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }} WIB
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-sm text-text leading-snug">
                                            {{ $item->course?->name }}
                                        </h4>
                                    </div>

                                    @if(isset($item->status_label))
                                        <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-medium border shrink-0 {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 pt-3 border-t border-line flex flex-wrap items-center justify-between gap-2 text-xs text-text-muted">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1.5" title="Ruang Perkuliahan">
                                            <x-icon name="map-pin" :size="13" class="text-text-subtle" />
                                            <span>{{ $item->room ?? 'Lab Komputer' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5" title="Beban SKS">
                                            <x-icon name="book" :size="13" class="text-text-subtle" />
                                            <span>{{ $item->course?->credits ?? 3 }} SKS</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a 
                                            href="{{ route('app.courses.classroom', $item) }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-[6px] bg-primary hover:bg-primary-hover text-white text-xs font-semibold transition-colors"
                                        >
                                            <span>Ruang Kelas</span>
                                            <x-icon name="chevron-right" :size="12" />
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-2.5 pt-2 border-t border-line/50 flex items-center gap-2 text-[11px] text-text-subtle">
                                    <div class="w-5 h-5 rounded-full overflow-hidden bg-canvas border border-line shrink-0">
                                        <img 
                                            src="{{ $item->lecturer?->avatar_path ?? '/images/avatar-budi.jpg' }}" 
                                            alt="{{ $item->lecturer?->name }}" 
                                            class="w-full h-full object-cover"
                                            onerror="this.src='/images/avatar-budi.jpg'"
                                        />
                                    </div>
                                    <span class="truncate">Dosen: {{ $item->lecturer?->name ?? 'Dosen Pengampu' }}</span>
                                </div>
                            </x-ui.card>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- ==========================================
         7. MODAL POPUP DETAIL AGENDA TANGGAL
         ========================================== -->
    <div 
        x-show="modalOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="modalOpen = false"
    >
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <!-- Backdrop -->
            <div 
                x-show="modalOpen" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                class="fixed inset-0 bg-navy-950/40 backdrop-blur-xs transition-opacity" 
                @click="modalOpen = false"
            ></div>

            <!-- Modal Content -->
            <div 
                x-show="modalOpen" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100 scale-100" 
                x-transition:leave-end="opacity-0 scale-95" 
                class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-surface rounded-[12px] border border-line shadow-xl relative z-10 space-y-4"
            >
                <div class="flex items-center justify-between border-b border-line pb-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" :size="18" class="text-primary" />
                        <h3 class="font-bold text-base text-text" x-text="selectedDateLabel"></h3>
                    </div>
                    <button 
                        type="button" 
                        @click="modalOpen = false" 
                        class="p-1 rounded-[6px] text-text-muted hover:text-text hover:bg-canvas transition-colors cursor-pointer"
                    >
                        <x-icon name="x" :size="16" />
                    </button>
                </div>

                <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                    <template x-for="ev in selectedDateEvents" :key="ev.id">
                        <div class="p-3 rounded-[8px] border text-xs space-y-1.5 transition-colors" :class="ev.border_color + ' ' + ev.bg_color">
                            <div class="flex items-center justify-between">
                                <span class="font-bold" :class="ev.text_color" x-text="ev.short_title"></span>
                                <span class="text-[11px] text-text-muted tabular-nums" x-text="ev.time"></span>
                            </div>
                            <h4 class="font-semibold text-text text-sm" x-text="ev.title"></h4>
                            <div class="flex items-center justify-between text-[11px] text-text-muted pt-1 border-t border-line/40">
                                <span x-text="'Ruang: ' + ev.room"></span>
                                <a :href="ev.url" class="font-semibold text-accent hover:underline flex items-center gap-1">
                                    <span>Buka Detail</span>
                                    <x-icon name="arrow-up-right" :size="11" />
                                </a>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-2 border-t border-line flex justify-end">
                    <button 
                        type="button" 
                        @click="modalOpen = false" 
                        class="px-4 py-2 rounded-[6px] bg-canvas border border-line text-xs font-semibold text-text hover:bg-canvas/80 transition-colors cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Stylesheet: Professional Clean Timetable Format -->
<style>
@media print {
    body {
        background-color: white !important;
        color: black !important;
    }
    header, aside, .no-print, [role="dialog"], button, .shadow-2xs, .shadow-xs, .shadow-xl {
        display: none !important;
    }
    .grid {
        display: grid !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endsection
