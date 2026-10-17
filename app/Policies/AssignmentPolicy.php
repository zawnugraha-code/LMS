<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    /**
     * Determine whether the user can view the assignment.
     */
    public function view(User $user, Assignment $assignment): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Auditor])) {
            return true;
        }

        // Lecturer can view if they own the assignment or teach the class
        if ($user->role === UserRole::Lecturer) {
            return $assignment->dosen_id === $user->id || $assignment->academicClass?->lecturer_id === $user->id;
        }

        // Student can view if enrolled in the class and assignment is published
        if ($user->role === UserRole::Student) {
            $isEnrolled = $assignment->academicClass?->enrollments()->where('user_id', $user->id)->exists();
            if (! $isEnrolled) {
                return false;
            }

            // If draft, student cannot view
            if ($assignment->status === 'draft') {
                return false;
            }

            // If scheduled in the future, student cannot view yet
            if ($assignment->status === 'terjadwal' || ($assignment->publish_at && $assignment->publish_at->isFuture())) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create assignments.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Lecturer]);
    }

    /**
     * Determine whether the user can update the assignment.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin])) {
            return true;
        }

        return $user->role === UserRole::Lecturer &&
            ($assignment->dosen_id === $user->id || $assignment->academicClass?->lecturer_id === $user->id);
    }

    /**
     * Determine whether the user can delete the assignment.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin])) {
            return true;
        }

        return $user->role === UserRole::Lecturer &&
            ($assignment->dosen_id === $user->id || $assignment->academicClass?->lecturer_id === $user->id);
    }
}
