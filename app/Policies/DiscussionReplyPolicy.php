<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DiscussionReply;
use App\Models\User;

class DiscussionReplyPolicy
{
    /**
     * Determine whether the user can upvote the discussion reply.
     */
    public function upvote(User $user, DiscussionReply $reply): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        $thread = $reply->thread;
        if (! $thread || $thread->class_id === null) {
            return true;
        }

        $class = $thread->academicClass;

        if ($class && ($class->isFinished() || $class->isLocked())) {
            return false;
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
     * Determine whether the user can mark the discussion reply as solution.
     */
    public function markSolution(User $user, DiscussionReply $reply): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true)) {
            return true;
        }

        $thread = $reply->thread;
        if (! $thread) {
            return false;
        }

        $class = $thread->academicClass;

        if ($class && ($class->isFinished() || $class->isLocked())) {
            return false;
        }

        // Lecturer of the class can always mark solution
        if ($user->role === UserRole::Lecturer && $class?->lecturer_id === $user->id) {
            return true;
        }

        // Author of the thread can mark solution (if student, must have active enrollment in class)
        if ($thread->user_id === $user->id) {
            if ($thread->class_id !== null) {
                if ($user->role === UserRole::Student) {
                    return $class?->enrollments()->where('user_id', $user->id)->aktif()->exists() ?? false;
                }

                return true;
            }

            return true;
        }

        return false;
    }
}
