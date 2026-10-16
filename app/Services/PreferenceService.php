<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;

class PreferenceService
{
    /**
     * Get or create preference for a user.
     */
    public function getForUser(User $user): UserPreference
    {
        return UserPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'light',
                'font_size' => 'normal',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'date_format' => 'DD MMM YYYY',
                'time_format' => 'HH:mm',
                'notifications' => [
                    'class_announcements' => true,
                    'assignments_deadlines' => true,
                    'private_messages' => true,
                    'discussion_activities' => false,
                    'system_notifications' => true,
                ],
                'two_factor_enabled' => false,
            ]
        );
    }

    /**
     * Update preferences for a user.
     */
    public function updatePreferences(User $user, array $data): UserPreference
    {
        $preference = $this->getForUser($user);
        $preference->update($data);

        return $preference;
    }
}
