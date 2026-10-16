@extends('layouts.app')

@section('title', 'Ruang Kelas: ' . $course->name . ' (' . $course->code . ')')
@section('page_breadcrumb', 'Mata Kuliah / ' . $course->code . ' / Ruang Kelas')

@section('content')

    <div class="space-y-6">
        
        <!-- Header & Navigasi -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('app.courses') }}" class="w-9 h-9 rounded-[6px] bg-surface hover:bg-canvas border border-line flex items-center justify-center text-text-muted hover:text-text transition-colors shadow-2xs">
                    <x-icon name="arrow-left" :size="16" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-text-muted">{{ $course->code }}</span>
                        <x-ui.badge variant="success">Ruang Kelas Aktif</x-ui.badge>
                        <span class="text-xs text-text-subtle">• Kelas {{ $class->section ?? 'IF-3A' }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight mt-1">
                        {{ $course->name }}
                    </h1>
                </div>
            </div>

            <!-- Header Quick Info & RPS link -->
            <div class="flex items-center gap-3">
                <a href="{{ route('app.courses.show', $class) }}" class="px-3.5 py-1.5 rounded-[6px] bg-surface hover:bg-canvas border border-line text-xs font-medium text-text inline-flex items-center gap-1.5 shadow-2xs transition-colors">
                    <x-icon name="file-text" :size="14" class="text-text-muted" />
                    <span>Lihat Silabus / RPS</span>
                </a>
            </div>
        </div>

        <!-- Banner Info Kelas & Progres -->
        <x-ui.card class="p-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <x-ui.avatar :name="$lecturer->name ?? 'Dosen'" :src="$lecturer->avatar_path ?? null" size="lg" />
                    <div class="space-y-0.5">
                        <span class="text-[11px] font-medium text-text-muted">Dosen Pengampu</span>
                        <h3 class="text-xs font-bold text-text">{{ $lecturer->name ?? 'Dr. Rudi Rahardjo, S.T., M.Kom.' }}</h3>
                        <p class="text-[11px] text-text-muted">
                            {{ $class->schedule_day ?? 'Senin' }}, {{ substr($class->start_time ?? '08:00', 0, 5) }} - {{ substr($class->end_time ?? '10:00', 0, 5) }} WIB &bull; {{ $class->room ?? 'Lab Pemrograman' }}
                        </p>
                    </div>
                </div>

                <!-- Progres Bar -->
                @php
                    $totalMeetings = $meetings->count() > 0 ? $meetings->count() : 14;
                    $completedMeetingsCount = 0;
                    foreach ($meetings as $m) {
                        if ($m->materials->contains(fn($mat) => $mat->progress->first()?->status === 'completed')) {
                            $completedMeetingsCount++;
                        }
                    }
                    if ($completedMeetingsCount === 0 && isset($class->settings['progress_meetings'])) {
                        $completedMeetingsCount = (int) $class->settings['progress_meetings'];
                    }
                    $progPercent = $totalMeetings > 0 ? (int) round(($completedMeetingsCount / $totalMeetings) * 100) : 0;
                @endphp
                <div class="w-full md:w-72 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-text">Progres Perkuliahan</span>
                        <span class="text-text-muted tabular-nums">{{ $completedMeetingsCount }} / {{ $totalMeetings }} Pertemuan ({{ $progPercent }}%)</span>
                    </div>
                    <x-ui.progress :value="$progPercent" height="h-2" color="bg-accent" />
                </div>
            </div>
        </x-ui.card>

        <!-- Timeline 14 Pertemuan Perkuliahan -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-text">Timeline & Materi Pertemuan (1 - 14)</h2>
                <span class="text-xs text-text-muted">Diurutkan berdasarkan minggu perkuliahan</span>
            </div>

            <div class="space-y-3.5">
                @forelse($meetings as $meeting)
                    @php
                        $isMeetingCompleted = $meeting->materials->contains(fn($mat) => $mat->progress->first()?->status === 'completed');
                        $hasAttended = $meeting->attendanceSession?->logs->contains('status', 'present');
                    @endphp
                    <x-ui.card class="p-4 sm:p-5 hover:border-line-strong transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <!-- Left: Week Badge, Title, Materials -->
                            <div class="flex items-start gap-3.5 min-w-0">
                                <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center font-bold text-xs {{ $isMeetingCompleted ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-surface text-text-muted border border-line' }}">
                                    @if($isMeetingCompleted)
                                        <x-icon name="check" :size="16" />
                                    @else
                                        {{ $meeting->week }}
                                    @endif
                                </div>
                                <div class="space-y-2 min-w-0">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-text">Minggu ke-{{ $meeting->week }}</span>
                                            @if($isMeetingCompleted)
                                                <x-ui.badge variant="success">Selesai</x-ui.badge>
                                            @endif
                                        </div>
                                        <h3 class="text-xs font-semibold text-text-muted mt-0.5 truncate">
                                            {{ $meeting->title }}
                                        </h3>
                                    </div>

                                    <!-- Materi di Pertemuan Ini -->
                                    <div class="space-y-1.5 pt-1">
                                        @foreach($meeting->materials as $mat)
                                            @php
                                                $p = $mat->progress->first();
                                                $isMatCompleted = $p && $p->status === 'completed';
                                            @endphp
                                            <div class="flex items-center gap-2 text-xs">
                                                <div class="w-5 h-5 rounded flex items-center justify-center {{ $mat->type === 'video' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-primary' }} shrink-0">
                                                    <x-icon :name="$mat->type === 'video' ? 'video' : 'file-text'" :size="12" />
                                                </div>
                                                <span class="text-text font-medium truncate">{{ $mat->title }}</span>
                                                <span class="text-[10px] text-text-subtle uppercase">({{ $mat->type }})</span>
                                                @if($isMatCompleted)
                                                    <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-0.5">
                                                        <x-icon name="check" :size="10" /> Selesai
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Presensi & Aksi -->
                            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-line shrink-0">
                                @if($hasAttended)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        <x-icon name="check-circle" :size="12" class="text-emerald-600" />
                                        <span>Presensi Tercatat</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-text-muted bg-canvas px-2 py-0.5 rounded border border-line">
                                        <x-icon name="clock" :size="12" class="text-text-subtle" />
                                        <span>{{ $meeting->week <= 8 ? 'Tidak Hadir' : 'Belum Dibuka' }}</span>
                                    </span>
                                @endif

                                <a href="{{ route('app.materials') }}" class="px-3 py-1 text-xs font-medium text-primary hover:text-primary-hover hover:underline inline-flex items-center gap-1">
                                    <span>Buka Materi</span>
                                    <x-icon name="chevron-right" :size="12" />
                                </a>
                            </div>
                        </div>
                    </x-ui.card>
                @empty
                    <p class="text-xs text-text-muted text-center py-8">Belum ada pertemuan perkuliahan yang terjadwal.</p>
                @endforelse
            </div>
        </div>

    </div>

@endsection
