<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Enrollment;
use App\Models\User;

class AcademicClassPolicy
{
    /**
     * Determine whether the user can view the course detail / RPS.
     */
    public function view(User $user, AcademicClass $class): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Auditor], true)) {
            return true;
        }

        if ($user->role === UserRole::Lecturer) {
            return $class->lecturer_id === $user->id;
        }

        if ($user->role === UserRole::Student) {
            return $class->enrollments()
                ->where('user_id', $user->id)
                ->accessible()
                ->exists();
        }

        return false;
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

        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        if ($user->role === UserRole::Lecturer) {
            return $class->lecturer_id === $user->id;
        }

        // Student must have an active enrollment and class must not be locked
        return $class->enrollments()
            ->where('user_id', $user->id)
            ->accessible()
            ->exists();
    }

    /**
     * Determine whether the user can manage this class (materials, assessments, grades).
     */
    public function manage(User $user, AcademicClass $class): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        if ($class->isFinished()) {
            return false;
        }

        return $user->role === UserRole::Lecturer && $class->lecturer_id === $user->id;
    }
}
