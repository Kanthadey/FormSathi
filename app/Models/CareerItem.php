<?php

namespace App\Models;

use App\Casts\VaultEncrypted;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of a user's career record: an education entry, a job, a skill,
 * a language or a training. The job kit may only use what is here.
 */
class CareerItem extends Model
{
    public const KINDS = ['education', 'work', 'skill', 'language', 'training'];

    protected $fillable = [
        'user_id',
        'kind',
        'data',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'data' => VaultEncrypted::class,
        ];
    }

    /** @return array<string, string> */
    public function fields(): array
    {
        return json_decode($this->data, true) ?? [];
    }
}
