<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\UserPreference;

class NotificationHelper
{
    /**
     * Determine whether to send a notification of a given type to the user.
     * Connected to User settings and UserPreference in Batch 2 (Fase 12).
     */
    public static function shouldNotify(?User $user, string $jenis): bool
    {
        if (! $user) {
            return false;
        }

        // 1. Check user settings array (used by Lecturer & System)
        $settings = $user->settings ?? [];
        if (isset($settings['notifikasi']) && is_array($settings['notifikasi'])) {
            $notifSettings = $settings['notifikasi'];

            // Map jenis to lecturer settings key
            $lecturerKeyMap = [
                'tugas' => 'tugas',
                'submission_received' => 'tugas',
                'submission_graded' => 'tugas',
                'assessment_published' => 'tugas',
                'ujian' => 'ujian',
                'kuis' => 'ujian',
                'presensi' => 'presensi',
                'attendance' => 'presensi',
                'diskusi' => 'diskusi',
                'discussion' => 'diskusi',
                'ringkasan_harian' => 'ringkasan_harian',
            ];

            if (isset($lecturerKeyMap[$jenis]) && array_key_exists($lecturerKeyMap[$jenis], $notifSettings)) {
                return (bool) $notifSettings[$lecturerKeyMap[$jenis]];
            }
        }

        // 2. Check UserPreference model (used by Student portal)
        $preference = $user->relationLoaded('preference')
            ? $user->preference
            : UserPreference::where('user_id', $user->id)->first();

        if ($preference && is_array($preference->notifications)) {
            $prefNotifs = $preference->notifications;

            $prefKeyMap = [
                'tugas' => 'assignments_deadlines',
                'submission_received' => 'assignments_deadlines',
                'submission_graded' => 'assignments_deadlines',
                'assessment_published' => 'assignments_deadlines',
                'pengumuman' => 'class_announcements',
                'class_announcements' => 'class_announcements',
                'pesan' => 'private_messages',
                'private_messages' => 'private_messages',
                'diskusi' => 'discussion_activities',
                'discussion' => 'discussion_activities',
                'sistem' => 'system_notifications',
                'system_notifications' => 'system_notifications',
            ];

            if (isset($prefKeyMap[$jenis]) && array_key_exists($prefKeyMap[$jenis], $prefNotifs)) {
                return (bool) $prefNotifs[$prefKeyMap[$jenis]];
            }
        }

        // Default to true if not explicitly disabled
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
