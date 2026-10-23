<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Local stand-in for bKash: a page on this site lets you choose
 * "paid" or "cancelled". Never used in production.
 */
class FakeGateway implements PaymentGateway
{
    public function create(Payment $payment, string $callbackUrl): string
    {
        $payment->update(['payment_id' => 'FAKE'.Str::upper(Str::random(12))]);

        return route('payments.fake', $payment->payment_id);
    }

    public function execute(Payment $payment, array $callback): ?string
    {
        return ($callback['status'] ?? null) === 'success' ? 'FAKETRX'.Str::upper(Str::random(8)) : null;
    }
}
