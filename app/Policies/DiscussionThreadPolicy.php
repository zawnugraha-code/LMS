<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\DiscussionThread;
use App\Models\User;

class DiscussionThreadPolicy
{
    /**
     * Determine whether the user can view the discussion thread.
     */
    public function view(User $user, DiscussionThread $thread): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin, UserRole::Auditor], true)) {
            return true;
        }

        if ($thread->class_id === null) {
            return true;
        }

        $class = $thread->academicClass;

        if ($user->role === UserRole::Lecturer) {
            return $class?->lecturer_id === $user->id;
        }

        if ($user->role === UserRole::Student) {
            return $class?->enrollments()->where('user_id', $user->id)->accessible()->exists() ?? false;
        }

        return false;
    }

    /**
     * Determine whether the user can create a discussion thread in a specific class.
     */
    public function createInClass(User $user, $classOrModel, ?AcademicClass $class = null): bool
    {
        $targetClass = ($classOrModel instanceof AcademicClass) ? $classOrModel : $class;

        if (! $targetClass) {
            return false;
        }

        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        if ($targetClass->isFinished()) {
            return false;
        }

        if ($user->role === UserRole::Lecturer) {
            return $targetClass->lecturer_id === $user->id;
        }

        if ($user->role === UserRole::Student) {
            return $targetClass->enrollments()->where('user_id', $user->id)->aktif()->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can reply to the discussion thread.
     */
    public function reply(User $user, DiscussionThread $thread): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        $class = $thread->academicClass;

        if ($class?->isFinished()) {
            return false;
        }

        if ($thread->class_id === null) {
            return true;
        }

        if ($user->role === UserRole::Lecturer) {
            return $class?->lecturer_id === $user->id;
        }

        if ($user->role === UserRole::Student) {
            return $class?->enrollments()->where('user_id', $user->id)->aktif()->exists() ?? false;
        }

        return false;
    }

    /**
     * Determine whether the user can upvote the discussion thread.
     */
    public function upvote(User $user, DiscussionThread $thread): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        if ($thread->class_id === null) {
            return true;
        }

        $class = $thread->academicClass;

        if ($user->role === UserRole::Lecturer) {
            return $class?->lecturer_id === $user->id;
        }

        if ($user->role === UserRole::Student) {
            return $class?->enrollments()->where('user_id', $user->id)->accessible()->exists() ?? false;
        }

        return false;
    }
}
