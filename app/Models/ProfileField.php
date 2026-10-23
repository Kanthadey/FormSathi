<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileField extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
        'source_document_id',
        'confirmed',
    ];

    protected function casts(): array
    {
        return [
            'value' => VaultEncrypted::class,
            'confirmed' => 'boolean',
        ];
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'source_document_id');
    }
}
