<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'product',
        'amount',
        'currency',
        'gateway',
        'status',
        'invoice',
        'payment_id',
        'trx_id',
        'gateway_response',
        'completed_at',
    ];

    protected $attributes = [
        'status' => 'initiated',
        'currency' => 'BDT',
    ];

    protected function casts(): array
    {
        return [
            'gateway_response' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productConfig(): array
    {
        return config("formsathi.products.{$this->product}");
    }
}
