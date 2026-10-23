<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentField extends Model
{
    protected $fillable = [
        'user_id',
        'document_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => VaultEncrypted::class,
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
