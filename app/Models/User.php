<?php

namespace App\Models;

use App\Support\Vault;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mobile',
        'pin',
        'locale',
    ];

    /**
     * Defaults matching the database, so fresh models have them in memory.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'plan' => 'free',
        'locale' => 'bn',
        'pin_failed_attempts' => 0,
        'form_credits' => 0,
        'is_admin' => false,
        'premium_until' => null,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'pin',
        'data_key',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->data_key ??= Vault::newDataKey();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
            'pin_locked_until' => 'datetime',
            'premium_until' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    public function isPremium(): bool
    {
        return $this->premium_until?->isFuture() ?? false;
    }

    public function getAuthPasswordName(): string
    {
        return 'pin';
    }

    public function isPinLocked(): bool
    {
        return $this->pin_locked_until?->isFuture() ?? false;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function documentFields(): HasMany
    {
        return $this->hasMany(DocumentField::class);
    }

    public function profileFields(): HasMany
    {
        return $this->hasMany(ProfileField::class);
    }

    public function knownDevices(): HasMany
    {
        return $this->hasMany(KnownDevice::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function careerItems(): HasMany
    {
        return $this->hasMany(CareerItem::class)->orderBy('position')->orderBy('id');
    }

    public function jobKits(): HasMany
    {
        return $this->hasMany(JobKit::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }
}
