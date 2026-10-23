<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use Illuminate\Database\Eloquent\Model;

class JobKit extends Model
{
    protected $fillable = [
        'user_id',
        'job_title',
        'company',
        'advert',
        'result',
        'status',
        'funded_by',
    ];

    protected function casts(): array
    {
        return [
            'advert' => VaultEncrypted::class,
            'result' => VaultEncrypted::class,
        ];
    }

    public function data(): array
    {
        return $this->result ? json_decode($this->result, true) : [];
    }
}
