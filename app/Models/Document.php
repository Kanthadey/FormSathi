<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use App\Support\Vault;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    // user_id comes first so encrypted attributes can find the user's key.
    protected $fillable = [
        'user_id',
        'type',
        'category',
        'title',
        'original_name',
        'file_path',
        'mime',
        'size',
        'expires_at',
        'expiry_reminded_at',
        'ocr_status',
        'ocr_suggestions',
    ];

    protected function casts(): array
    {
        return [
            'title' => VaultEncrypted::class,
            'original_name' => VaultEncrypted::class,
            'ocr_suggestions' => VaultEncrypted::class,
            'expires_at' => 'date',
            'expiry_reminded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Document $document) {
            Storage::disk('local')->delete($document->file_path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(DocumentField::class);
    }

    public function typeConfig(): array
    {
        return config("formsathi.document_types.{$this->type}");
    }

    /** Decrypted file contents. */
    public function contents(): string
    {
        return Vault::forUser($this->user_id)->open(Storage::disk('local')->get($this->file_path));
    }

    public function displayName(string $locale): string
    {
        return $this->title ?: $this->typeConfig()[$locale];
    }
}
