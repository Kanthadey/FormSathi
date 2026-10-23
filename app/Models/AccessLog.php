<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'ip_address',
    ];

    /** Records that someone touched a user's papers or profile. */
    public static function record(User $user, string $action, ?Model $subject = null): self
    {
        return static::create([
            'user_id' => $user->id,
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
        ]);
    }
}
