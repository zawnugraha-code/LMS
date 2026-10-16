<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Meeting;
use App\Models\Submission;
use App\Models\XpLedger;
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
        $request->validate([
            'content' => 'nullable|string',
            'file_url' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:25600',
            'action' => 'nullable|string',
        ]);

        $user = Auth::user();
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

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ],
            [
                'content' => $request->input('content') ?? '',
                'file_path' => $filePath,
                'status' => $status,
                'submitted_at' => $isDraft ? null : now(),
            ]
        );

        if (! $isDraft) {
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
