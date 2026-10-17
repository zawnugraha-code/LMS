<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    /**
     * Determine whether the user can view the material.
     */
    public function view(User $user, Material $material): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Auditor])) {
            return true;
        }

        $class = $material->academicClass ?? $material->meeting?->academicClass;

        if ($user->role === UserRole::Lecturer) {
            return $class?->lecturer_id === $user->id || $material->uploaded_by === $user->id;
        }

        if ($user->role === UserRole::Student) {
            // Draft materials cannot be viewed by students
            if ($material->status === 'draft') {
                return false;
            }

            return $class?->enrollments()->where('user_id', $user->id)->exists() ?? false;
        }

        return false;
    }

    /**
     * Determine whether the user can manage (create, edit, delete) this material.
     */
    public function manage(User $user, Material $material): bool
    {
        if (in_array($user->role, [UserRole::SuperAdmin, UserRole::CampusAdmin])) {
            return true;
        }

        $class = $material->academicClass ?? $material->meeting?->academicClass;

        return $user->role === UserRole::Lecturer &&
            ($class?->lecturer_id === $user->id || $material->uploaded_by === $user->id);
    }
}
