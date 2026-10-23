<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * bKash tokenized checkout: grant token -> create payment -> the user pays
 * on bKash -> callback -> execute (or query, if execute did not answer).
 *
 * Check the endpoints and field names against the merchant documentation
 * bKash sends after onboarding, and run the sandbox test cases they require.
 */
class BkashGateway implements PaymentGateway
{
    public function create(Payment $payment, string $callbackUrl): string
    {
        $response = $this->api()->post('/tokenized/checkout/create', [
            'mode' => '0011',
            'payerReference' => (string) $payment->user_id,
            'callbackURL' => $callbackUrl,
            'amount' => (string) $payment->amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $payment->invoice,
        ])->json();

        if (empty($response['paymentID']) || empty($response['bkashURL'])) {
            $payment->update(['status' => 'failed', 'gateway_response' => $this->safe($response)]);

            throw new RuntimeException('bKash did not create the payment.');
        }

        $payment->update(['payment_id' => $response['paymentID'], 'gateway_response' => $this->safe($response)]);

        return $response['bkashURL'];
    }

    public function execute(Payment $payment, array $callback): ?string
    {
        if (($callback['status'] ?? null) !== 'success') {
            return null;
        }

        $result = $this->api()->post('/tokenized/checkout/execute', ['paymentID' => $payment->payment_id])->json();

        // Execute can time out after the money moved; the status query is the source of truth.
        if (! isset($result['transactionStatus'])) {
            $result = $this->api()->post('/tokenized/checkout/payment/status', ['paymentID' => $payment->payment_id])->json();
        }

        $payment->update(['gateway_response' => $this->safe($result)]);

        $paid = ($result['transactionStatus'] ?? null) === 'Completed'
            && (float) ($result['amount'] ?? 0) === (float) $payment->amount
            && ($result['paymentID'] ?? null) === $payment->payment_id;

        return $paid ? ($result['trxID'] ?? null) : null;
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl(config('services.bkash.base_url'))
            ->acceptJson()
            ->timeout(30)
            ->withHeaders([
                'Authorization' => $this->token(),
                'X-APP-Key' => config('services.bkash.app_key'),
            ]);
    }

    /** id_token lasts an hour; cache it a little less. */
    private function token(): string
    {
        return Cache::remember('bkash.id_token', now()->addMinutes(50), function () {
            $response = Http::baseUrl(config('services.bkash.base_url'))
                ->acceptJson()
                ->timeout(30)
                ->withHeaders([
                    'username' => config('services.bkash.username'),
                    'password' => config('services.bkash.password'),
                ])
                ->post('/tokenized/checkout/token/grant', [
                    'app_key' => config('services.bkash.app_key'),
                    'app_secret' => config('services.bkash.app_secret'),
                ])->json();

            return $response['id_token'] ?? throw new RuntimeException('bKash token grant failed.');
        });
    }

    /** Keeps the useful parts of a response; never tokens. */
    private function safe(?array $response): array
    {
        return collect($response ?? [])->only([
            'paymentID', 'trxID', 'transactionStatus', 'amount', 'currency',
            'merchantInvoiceNumber', 'statusCode', 'statusMessage', 'paymentExecuteTime',
        ])->all();
    }
}
