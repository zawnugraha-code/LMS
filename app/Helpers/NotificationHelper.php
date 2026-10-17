<?php

namespace App\Helpers;

use App\Models\User;

class NotificationHelper
{
    /**
     * Determine whether to send a notification of a given type to the user.
     * Currently returns true for MVP; will connect to UserPreference in Batch 2.
     */
    public static function shouldNotify(?User $user, string $jenis): bool
    {
        if (! $user) {
            return false;
        }

        return true;
    }
}

if (! function_exists('shouldNotify')) {
    /**
     * Global helper to check notification preference.
     */
    function shouldNotify(?User $user, string $jenis): bool
    {
        return NotificationHelper::shouldNotify($user, $jenis);
    }
}
