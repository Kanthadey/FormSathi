<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Development gateway: writes messages to the log instead of sending them.
 * Swap for a real provider (e.g. SSL Wireless, BulkSMSBD) in production.
 */
class LogSmsGateway implements SmsGateway
{
    /** @var list<array{mobile: string, message: string}> */
    public array $sent = [];

    public function send(string $mobile, string $message): void
    {
        $this->sent[] = compact('mobile', 'message');

        Log::info("SMS to {$mobile}: {$message}");
    }
}
