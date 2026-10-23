<?php

namespace App\Support;

use App\Models\User;
use RuntimeException;

/**
 * Caches unwrapped data keys for the length of one request. Registered as a
 * scoped binding so keys never outlive the request that needed them.
 */
class VaultKeyring
{
    /** @var array<int, Vault> */
    private array $vaults = [];

    public function vaultFor(int $userId): Vault
    {
        return $this->vaults[$userId] ??= new Vault($userId, $this->loadKey($userId));
    }

    private function loadKey(int $userId): string
    {
        $wrapped = User::query()->whereKey($userId)->value('data_key');

        if ($wrapped === null) {
            throw new RuntimeException("No data key for user {$userId}.");
        }

        return Vault::unwrap($wrapped);
    }
}
