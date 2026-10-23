<?php

namespace App\Casts;

use App\Support\Vault;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Encrypts a column with the owning user's data key. The model must have
 * user_id set before the encrypted attribute is assigned.
 */
class VaultEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : Vault::forUser($this->userId($attributes))->decrypt($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : Vault::forUser($this->userId($attributes))->encrypt((string) $value);
    }

    private function userId(array $attributes): int
    {
        return $attributes['user_id'] ?? throw new LogicException('Set user_id before encrypted attributes.');
    }
}
