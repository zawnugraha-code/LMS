<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, Submission $submission): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Auditor], true)) {
            return true;
        }

        // Submitting student
        if ($submission->user_id === $user->id) {
            return true;
        }

        // Class lecturer
        $lecturerId = $submission->assignment?->academicClass?->lecturer_id ?? $submission->assignment?->dosen_id;

        return $user->role === UserRole::Lecturer && $lecturerId === $user->id;
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, Submission $submission): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        // Dilarang menilai jika kelas sudah selesai (read-only)
        if ($submission->assignment?->academicClass?->isFinished()) {
            return false;
        }

        $lecturerId = $submission->assignment?->academicClass?->lecturer_id ?? $submission->assignment?->dosen_id;

        return $user->role === UserRole::Lecturer && $lecturerId === $user->id;
    }

    /**
     * Determine whether the user can download the submission file.
     */
    public function download(User $user, Submission $submission): bool
    {
        // Admin (role from /admin middleware)
        if ($user->role === UserRole::Admin) {
            return true;
        }

        // Submitting student
        if ($submission->user_id === $user->id) {
            $enrollment = $submission->assignment?->academicClass?->enrollments()
                ->where('user_id', $user->id)
                ->first();

            // Expelled student cannot download
            if ($enrollment && $enrollment->isDikeluarkan()) {
                return false;
            }

            return true;
        }

        // Class lecturer
        $lecturerId = $submission->assignment?->academicClass?->lecturer_id ?? $submission->assignment?->dosen_id;
        if ($user->role === UserRole::Lecturer && $lecturerId === $user->id) {
            return true;
        }

        return false;
    }
}
