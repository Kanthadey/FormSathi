<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    // user_id is the ticket owner, so the body is sealed with their key.
    protected $fillable = [
        'ticket_id',
        'user_id',
        'author_id',
        'from_staff',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'from_staff' => 'boolean',
            'body' => VaultEncrypted::class,
        ];
    }
}
