<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\Sms\SmsGateway;
use Illuminate\Console\Command;

/** SMS reminder before a paper expires (F3: passports 6 months ahead). */
class SendExpiryReminders extends Command
{
    protected $signature = 'documents:remind-expiry';

    protected $description = 'Send SMS reminders for papers that will expire soon';

    public function handle(SmsGateway $sms): int
    {
        $sent = 0;

        foreach (config('formsathi.document_types') as $type => $config) {
            if (! isset($config['remind_months_before'])) {
                continue;
            }

            Document::with('user:id,mobile,locale')
                ->where('type', $type)
                ->whereNull('expiry_reminded_at')
                ->whereDate('expires_at', '>=', today())
                ->whereDate('expires_at', '<=', today()->addMonths($config['remind_months_before']))
                ->chunkById(200, function ($documents) use ($sms, $config, &$sent) {
                    foreach ($documents as $document) {
                        $locale = $document->user->locale;

                        $sms->send($document->user->mobile, __('app.expiry_sms', [
                            'document' => $config[$locale],
                            'date' => $document->expires_at->format('d/m/Y'),
                        ], $locale));

                        $document->update(['expiry_reminded_at' => now()]);
                        $sent++;
                    }
                });
        }

        $this->info("Sent {$sent} reminders.");

        return self::SUCCESS;
    }
}
