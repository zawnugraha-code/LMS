<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\User;

class AcademicClassPolicy
{
    /**
     * Determine whether the user can view the course detail / RPS.
     */
    public function view(User $user, AcademicClass $class): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Auditor])) {
            return true;
        }

        if ($user->role === UserRole::Lecturer) {
            return $class->lecturer_id === $user->id;
        }

        // Student can view details of classes enrolled or classes in their active term
        return $class->enrollments()->where('user_id', $user->id)->exists()
            || $class->term_id === $user->preference?->active_term_id;
    }

    /**
     * Determine whether the user can enter the virtual classroom.
     */
    public function enterClassroom(User $user, AcademicClass $class): bool
    {
        // Guard against entering locked / upcoming classes
        if ($class->isLocked()) {
            return false;
        }

        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin])) {
            return true;
        }

        if ($user->role === UserRole::Lecturer) {
            return $class->lecturer_id === $user->id;
        }

        // Student must have an active enrollment and class must not be locked
        return $class->enrollments()
            ->where('user_id', $user->id)
            ->whereIn('status', ['enrolled', 'completed'])
            ->exists();
    }

    /**
     * Determine whether the user can manage this class (materials, assessments, grades).
     */
    public function manage(User $user, AcademicClass $class): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin])) {
            return true;
        }

        return $user->role === UserRole::Lecturer && $class->lecturer_id === $user->id;
    }
}
