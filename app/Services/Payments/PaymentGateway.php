<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /** Starts a checkout and returns the URL to send the user to. */
    public function create(Payment $payment, string $callbackUrl): string;

    /**
     * Confirms a payment the user returned from. Returns the gateway's
     * transaction id on success, or null when it was not paid.
     *
     * @param  array<string, mixed>  $callback  query string from the callback
     */
    public function execute(Payment $payment, array $callback): ?string;
}
