<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Entitlements;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

/** Plans and bKash checkout (F11). */
class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function plans(Request $request, Entitlements $entitlements): Response
    {
        $user = $request->user();

        return Inertia::render('Plans', [
            'plan' => $entitlements->summary($user),
            'products' => config('formsathi.products'),
            'payments' => $user->payments()->latest()->limit(20)->get()->map(fn (Payment $p) => [
                'id' => $p->id,
                'product' => $p->product,
                'amount' => $p->amount,
                'status' => $p->status,
                'trxId' => $p->trx_id,
                'date' => $p->created_at->toDateString(),
            ]),
        ]);
    }

    public function store(Request $request): HttpResponse
    {
        $data = $request->validate(['product' => ['required', Rule::in(array_keys(config('formsathi.products')))]]);

        try {
            $checkout = $this->payments->start($request->user(), $data['product']);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['payment' => __('app.payment_unavailable')]);
        }

        // bKash is another site, so leave the SPA with a full-page redirect.
        return Inertia::location($checkout['url']);
    }

    /** bKash sends the user back here with ?paymentID=...&status=success|failure|cancel. */
    public function callback(Request $request): RedirectResponse
    {
        $payment = $request->user()->payments()
            ->where('payment_id', (string) $request->query('paymentID'))
            ->firstOrFail();

        try {
            $paid = $this->payments->complete($payment, $request->query());
        } catch (Throwable $e) {
            report($e);
            $paid = false;
        }

        return redirect()->route('plans')->with(
            $paid ? 'status' : 'error',
            $paid ? __('app.payment_success') : __('app.payment_failed'),
        );
    }

    /** Local stand-in for the bKash checkout page. */
    public function fake(Request $request, string $paymentId): Response
    {
        abort_unless(config('services.bkash.driver') === 'fake' && ! app()->isProduction(), 404);

        $payment = $request->user()->payments()->where('payment_id', $paymentId)->firstOrFail();

        return Inertia::render('FakeCheckout', [
            'payment' => [
                'paymentId' => $payment->payment_id,
                'amount' => $payment->amount,
                'product' => config("formsathi.products.{$payment->product}"),
            ],
        ]);
    }
}
