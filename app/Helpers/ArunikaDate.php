<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ArunikaDate
{
    /**
     * Format a date/timestamp according to user timezone and preference format.
     */
    public static function format($date, ?string $customFormat = null): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $user = Auth::user();
        $pref = $user?->preference;

        $timezone = $pref?->timezone ?? 'Asia/Jakarta';
        $carbon->setTimezone($timezone);

        if ($customFormat) {
            return $carbon->translatedFormat($customFormat);
        }

        $format = $pref?->date_format ?? 'd M Y';
        // Convert common format tokens to PHP/Carbon format
        $phpFormat = match ($format) {
            'DD MMM YYYY' => 'd M Y',
            'DD/MM/YYYY' => 'd/m/Y',
            'YYYY-MM-DD' => 'Y-m-d',
            default => 'd M Y',
        };

        return $carbon->translatedFormat($phpFormat);
    }

    /**
     * Format time according to user preference (e.g. 14:30).
     */
    public static function time($date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $user = Auth::user();
        $pref = $user?->preference;

        $timezone = $pref?->timezone ?? 'Asia/Jakarta';
        $carbon->setTimezone($timezone);

        $format = $pref?->time_format ?? 'HH:mm';
        $phpFormat = $format === 'hh:mm A' ? 'h:i A' : 'H:i';

        return $carbon->format($phpFormat);
    }

    /**
     * Relative difference (e.g. "2 hari lagi", "3 hari yang lalu", "Hari ini").
     */
    public static function diffForHumans($date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->locale('id')->diffForHumans();
    }
}
