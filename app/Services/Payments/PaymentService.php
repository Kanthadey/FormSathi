<?php

namespace App\Services\Payments;

use App\Models\AccessLog;
use App\Models\Payment;
use App\Models\User;
use App\Services\Sms\SmsGateway;
use App\Support\Bangla;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly SmsGateway $sms,
    ) {}

    /** @return array{payment: Payment, url: string} */
    public function start(User $user, string $product): array
    {
        $config = config("formsathi.products.{$product}");

        $payment = $user->payments()->create([
            'product' => $product,
            'amount' => $config['amount'],
            'gateway' => config('services.bkash.driver'),
            'invoice' => 'FS'.now()->format('ymd').Str::upper(Str::random(8)),
        ]);

        return ['payment' => $payment, 'url' => $this->gateway->create($payment, route('payments.callback'))];
    }

    /**
     * Handles the user's return from the gateway. Safe to call twice: a
     * payment is confirmed and its product granted exactly once.
     */
    public function complete(Payment $payment, array $callback): bool
    {
        if ($payment->status === 'completed') {
            return true;
        }

        if ($payment->status !== 'initiated') {
            return false;
        }

        $trxId = $this->gateway->execute($payment, $callback);

        return DB::transaction(function () use ($payment, $trxId, $callback) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if ($payment->status !== 'initiated') {
                return $payment->status === 'completed';
            }

            if (! $trxId) {
                $payment->update(['status' => ($callback['status'] ?? null) === 'cancel' ? 'cancelled' : 'failed']);

                return false;
            }

            $payment->update(['status' => 'completed', 'trx_id' => $trxId, 'completed_at' => now()]);
            $this->grant($payment);

            return true;
        });
    }

    private function grant(Payment $payment): void
    {
        $user = $payment->user;
        $config = $payment->productConfig();

        if (isset($config['credits'])) {
            $user->increment('form_credits', $config['credits']);
        }

        if (isset($config['months'])) {
            $from = $user->isPremium() ? $user->premium_until : now();
            $user->forceFill(['premium_until' => $from->copy()->addMonths($config['months'])])->save();
        }

        AccessLog::record($user, 'payment.complete', $payment);

        $this->sms->send($user->mobile, sprintf(
            'FormSathi: %s এর জন্য ৳%s পরিশোধ হয়েছে। TrxID %s। ধন্যবাদ!',
            $config['bn'], Bangla::localDigits($payment->amount), $payment->trx_id,
        ));
    }
}
