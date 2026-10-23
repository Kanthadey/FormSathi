<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * Envelope encryption: every user has their own random 256-bit data key,
 * stored wrapped with the app key. Field values and files are sealed with
 * AES-256-GCM under that data key, bound to the user's id.
 *
 * Moving the wrapping key to a KMS later only changes wrap()/unwrap().
 */
class Vault
{
    private const CIPHER = 'aes-256-gcm';

    private const PREFIX = 'v1.';

    public function __construct(private readonly int $userId, private readonly string $key) {}

    public static function newDataKey(): string
    {
        return Crypt::encryptString(base64_encode(random_bytes(32)));
    }

    public static function forUser(User|int $user): self
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return app(VaultKeyring::class)->vaultFor($userId);
    }

    public static function unwrap(string $wrapped): string
    {
        return base64_decode(Crypt::decryptString($wrapped), true);
    }

    public function encrypt(string $plain): string
    {
        return self::PREFIX.base64_encode($this->seal($plain));
    }

    public function decrypt(string $payload): string
    {
        if (! str_starts_with($payload, self::PREFIX)) {
            throw new RuntimeException('Unknown vault payload version.');
        }

        return $this->open(base64_decode(substr($payload, strlen(self::PREFIX)), true));
    }

    /** Raw binary sealing, used for files. */
    public function seal(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag, $this->aad());

        if ($cipher === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return $iv.$tag.$cipher;
    }

    public function open(string $sealed): string
    {
        $plain = openssl_decrypt(
            substr($sealed, 28), self::CIPHER, $this->key, OPENSSL_RAW_DATA,
            substr($sealed, 0, 12), substr($sealed, 12, 16), $this->aad()
        );

        if ($plain === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plain;
    }

    private function aad(): string
    {
        return 'formsathi:user:'.$this->userId;
    }
}
