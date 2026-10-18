<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Catat aktivitas admin/sistem ke tabel activity_logs secara append-only.
     */
    public static function log(
        string $aksi,
        string $kategori,
        ?Model $subjek = null,
        ?string $deskripsi = null,
        array $properti = [],
        ?int $userId = null,
        ?string $ipAddress = null
    ): ActivityLog {
        // Bersihkan data properti dari kunci-kunci sensitif
        $sanitizedProperties = self::sanitizeProperties($properti);

        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'aksi' => $aksi,
            'kategori' => $kategori,
            'subjek_type' => $subjek ? get_class($subjek) : null,
            'subjek_id' => $subjek ? (string) $subjek->getKey() : null,
            'deskripsi' => $deskripsi,
            'properti' => empty($sanitizedProperties) ? null : $sanitizedProperties,
            'ip_address' => $ipAddress ?? (app()->runningInConsole() ? 'CLI' : request()->ip()),
            'created_at' => now(),
        ]);
    }

    /**
     * Hapus kunci-kunci rahasia dari log properti.
     */
    protected static function sanitizeProperties(array $properti): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'remember_token', 'secret', 'api_key'];

        foreach ($properti as $key => $value) {
            if (is_array($value)) {
                $properti[$key] = self::sanitizeProperties($value);
            } elseif (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $properti[$key] = '[REDACTED]';
            }
        }

        return $properti;
    }
}
