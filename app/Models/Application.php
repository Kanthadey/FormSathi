<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'form_template_id',
        'status',
        'copied',
        'summary',
        'completed_at',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'copied' => 'array',
            'summary' => VaultEncrypted::class,
            'completed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** @return array<string, string> template field key => value as submitted */
    public function snapshot(): array
    {
        return $this->summary ? json_decode($this->summary, true) : [];
    }
}
