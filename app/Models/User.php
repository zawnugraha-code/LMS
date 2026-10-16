<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Models\Concerns\BelongsToCampus;
use App\Models\Concerns\HasUlid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToCampus, HasFactory, HasUlid, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'campus_id',
        'role',
        'identifier',
        'avatar_path',
        'phone',
        'locale',
        'timezone',
        'settings',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Role checking helpers
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isCampusAdmin(): bool
    {
        return $this->role === UserRole::CampusAdmin;
    }

    public function isLecturer(): bool
    {
        return $this->role === UserRole::Lecturer;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    public function isAuditor(): bool
    {
        return $this->role === UserRole::Auditor;
    }

    public function isIndustryMentor(): bool
    {
        return $this->role === UserRole::IndustryMentor;
    }

    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $this->role?->value === $roleValue;
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_user')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function xpLedgers(): HasMany
    {
        return $this->hasMany(XpLedger::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function discussionThreads(): HasMany
    {
        return $this->hasMany(DiscussionThread::class);
    }

    public function discussionReplies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function materialProgresses(): HasMany
    {
        return $this->hasMany(MaterialProgress::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function totalXp(): int
    {
        return (int) $this->xpLedgers()->sum('amount');
    }

    public function currentLevel(): int
    {
        $xp = $this->totalXp();

        return (int) floor(sqrt($xp / 50)) + 1;
    }
}
