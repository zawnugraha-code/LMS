<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Meeting;
use App\Models\Submission;
use App\Models\XpLedger;
use App\Notifications\SubmissionReceivedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicActionController extends Controller
{
    /**
     * Submit an assignment for the authenticated student.
     */
    public function submitAssignment(Request $request, Assignment $assignment): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Akses ditolak.');
        }

        // Guard: Kelas selesai tidak menerima mutasi pengumpulan
        if ($assignment->academicClass?->isFinished()) {
            abort(403, 'Kelas ini telah selesai. Pengumpulan tugas ditutup.');
        }

        // Guard: Hanya mahasiswa aktif di kelas yang boleh mengumpulkan tugas
        $isEnrolled = $assignment->academicClass?->enrollments()
            ->where('user_id', $user->id)
            ->aktif()
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar aktif dalam kelas untuk tugas ini.');
        }

        $request->validate([
            'content' => 'nullable|string',
            'file_url' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:25600',
            'action' => 'nullable|string',
        ]);

        $isDraft = $request->input('action') === 'draft';
        $status = $isDraft ? 'draft' : 'submitted';

        $filePath = $request->input('file_url');
        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $fileName = $user->identifier.'-'.time().'-'.$uploaded->getClientOriginalName();
            $filePath = $uploaded->storeAs('submissions', $fileName, 'public');
        } elseif (! $filePath) {
            $filePath = '/submissions/'.$user->identifier.'-submission.zip';
        }

        $now = now();
        $statusWaktu = ($assignment->due_at && $now->greaterThan($assignment->due_at)) ? 'terlambat' : 'tepat_waktu';

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ],
            [
                'content' => $request->input('content') ?? '',
                'file_path' => $filePath,
                'status' => $status,
                'status_waktu' => $isDraft ? null : $statusWaktu,
                'submitted_at' => $isDraft ? null : $now,
            ]
        );

        if (! $isDraft) {
            // Notify lecturer about new submission
            $lecturer = $assignment->academicClass?->lecturer;
            if ($lecturer && NotificationHelper::shouldNotify($lecturer, 'submission_received')) {
                $lecturer->notify(new SubmissionReceivedNotification($submission));
            }

            // Award XP for submitting assignment if not already awarded
            $hasXp = XpLedger::where('user_id', $user->id)
                ->where('source_type', 'assignment')
                ->where('source_id', $assignment->id)
                ->exists();

            if (! $hasXp) {
                XpLedger::create([
                    'user_id' => $user->id,
                    'class_id' => $assignment->class_id,
                    'amount' => config('arunika.gamification.xp_per_assignment_submit', 10),
                    'source_type' => 'assignment',
                    'source_id' => $assignment->id,
                    'description' => "Pengumpulan tugas: {$assignment->title}",
                ]);
            }

            return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan! Anda mendapatkan +10 XP.');
        }

        return redirect()->back()->with('success', 'Draf jawaban tugas berhasil disimpan.');
    }

    /**
     * Grade a student submission (lecturer action).
     */
    public function gradeSubmission(Request $request, Submission $submission): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || (! $user->isLecturer() && ! $user->isCampusAdmin() && ! $user->isSuperAdmin())) {
            abort(403, 'Hanya dosen pengampu atau administrator yang berwenang memberikan nilai tugas.');
        }

        if ($user->isLecturer()) {
            $classLecturerId = $submission->assignment?->academicClass?->lecturer_id;
            if ($classLecturerId !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk memberikan nilai pada tugas di kelas ini.');
            }
        }

        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $submission->update([
            'score' => $request->input('score'),
            'feedback' => $request->input('feedback'),
            'status' => 'graded',
            'graded_at' => now(),
            'graded_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Nilai dan umpan balik tugas berhasil disimpan.');
    }

    /**
     * Check in attendance for a student via meeting QR code.
     */
    public function checkInAttendance(Request $request, Meeting $meeting): RedirectResponse
    {
        $user = Auth::user();

        // Guard: Hanya mahasiswa aktif di kelas yang boleh presensi
        $isEnrolled = $meeting->academicClass?->enrollments()
            ->where('user_id', $user->id)
            ->aktif()
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar aktif dalam kelas untuk pertemuan ini.');
        }

        $session = $meeting->attendanceSession ?? AttendanceSession::firstOrCreate(
            ['meeting_id' => $meeting->id],
            [
                'qr_secret' => 'ARUNIKA-PRESENSI-W'.$meeting->week,
                'is_active' => true,
                'opened_at' => now(),
            ]
        );

        AttendanceLog::updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'present',
                'method' => 'qr',
                'ip_address' => $request->ip(),
                'checked_at' => now(),
                'notes' => 'Presensi mandiri berhasil diverifikasi',
            ]
        );

        // Award XP for attendance
        XpLedger::create([
            'user_id' => $user->id,
            'class_id' => $meeting->class_id,
            'amount' => config('arunika.gamification.xp_per_attendance', 5),
            'source_type' => 'attendance',
            'source_id' => $meeting->id,
            'description' => "Hadir pada pertemuan ke-{$meeting->week}: {$meeting->title}",
        ]);

        return redirect()->back()->with('success', "Presensi Pertemuan ke-{$meeting->week} berhasil dicatat! (+5 XP)");
    }
}
