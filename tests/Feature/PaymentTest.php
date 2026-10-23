<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\BkashGateway;
use App\Services\Sms\SmsGateway;
use Database\Seeders\FormTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.bkash.driver' => 'fake']);
        $this->user = User::factory()->create();
        $this->actingAs($this->user)->withSession(['last_active_at' => now()->timestamp]);
    }

    private function pay(string $product, string $status = 'success'): Payment
    {
        $response = $this->post('/payments', ['product' => $product]);
        $payment = Payment::latest('id')->first();
        $response->assertRedirect(route('payments.fake', $payment->payment_id));
        $this->get("/payments/callback?paymentID={$payment->payment_id}&status={$status}")->assertRedirect('/plans');

        return $payment->fresh();
    }

    public function test_a_form_pack_adds_one_credit_and_sends_a_receipt(): void
    {
        $payment = $this->pay('form_pack');

        $this->assertSame('completed', $payment->status);
        $this->assertSame(99, $payment->amount);
        $this->assertNotNull($payment->trx_id);
        $this->assertSame(1, $this->user->fresh()->form_credits);
        $this->assertStringContainsString($payment->trx_id, end(app(SmsGateway::class)->sent)['message']);
    }

    public function test_premium_extends_from_the_current_end_date(): void
    {
        $this->pay('premium_month');
        $first = $this->user->fresh()->premium_until;
        $this->assertTrue($this->user->fresh()->isPremium());

        $this->pay('premium_year');
        $this->assertTrue($this->user->fresh()->premium_until->equalTo($first->copy()->addMonths(12)));
    }

    public function test_a_callback_twice_grants_once(): void
    {
        $payment = $this->pay('form_pack');
        $this->get("/payments/callback?paymentID={$payment->payment_id}&status=success");

        $this->assertSame(1, $this->user->fresh()->form_credits);
    }

    public function test_a_cancelled_payment_grants_nothing(): void
    {
        $payment = $this->pay('premium_month', 'cancel');

        $this->assertSame('cancelled', $payment->status);
        $this->assertFalse($this->user->fresh()->isPremium());
    }

    public function test_another_users_payment_cannot_be_completed(): void
    {
        $other = User::factory()->create();
        $payment = $other->payments()->create(['product' => 'form_pack', 'amount' => 99, 'gateway' => 'fake', 'invoice' => 'X1', 'payment_id' => 'FAKEX']);

        $this->get('/payments/callback?paymentID=FAKEX&status=success')->assertNotFound();
        $this->assertSame('initiated', $payment->fresh()->status);
    }

    public function test_unknown_products_are_rejected(): void
    {
        $this->post('/payments', ['product' => 'free_money'])->assertSessionHasErrors('product');
    }

    public function test_bkash_executes_and_checks_the_amount(): void
    {
        config(['services.bkash.base_url' => 'https://bkash.test']);

        Http::fake([
            'bkash.test/tokenized/checkout/token/grant' => Http::response(['id_token' => 'tok']),
            'bkash.test/tokenized/checkout/create' => Http::response(['paymentID' => 'PAY1', 'bkashURL' => 'https://pay.bka.sh/PAY1']),
            'bkash.test/tokenized/checkout/execute' => Http::response([
                'paymentID' => 'PAY1', 'trxID' => 'TRX9', 'transactionStatus' => 'Completed', 'amount' => '99',
            ]),
        ]);

        $gateway = app(BkashGateway::class);
        $payment = $this->user->payments()->create(['product' => 'form_pack', 'amount' => 99, 'gateway' => 'bkash', 'invoice' => 'INV1']);

        $this->assertSame('https://pay.bka.sh/PAY1', $gateway->create($payment, 'https://app.test/cb'));
        $this->assertSame('TRX9', $gateway->execute($payment->fresh(), ['status' => 'success']));

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'tok') && ! str_contains($request->body(), 'tok'));
    }

    public function test_bkash_rejects_a_payment_with_the_wrong_amount(): void
    {
        config(['services.bkash.base_url' => 'https://bkash.test']);
        Http::fake([
            'bkash.test/tokenized/checkout/token/grant' => Http::response(['id_token' => 'tok']),
            'bkash.test/tokenized/checkout/execute' => Http::response([
                'paymentID' => 'PAY1', 'trxID' => 'TRX9', 'transactionStatus' => 'Completed', 'amount' => '1',
            ]),
        ]);

        $payment = $this->user->payments()->create(['product' => 'form_pack', 'amount' => 99, 'gateway' => 'bkash', 'invoice' => 'INV1', 'payment_id' => 'PAY1']);

        $this->assertNull(app(BkashGateway::class)->execute($payment, ['status' => 'success']));
    }

    public function test_a_credit_pays_for_an_assist_after_the_free_ones(): void
    {
        $this->seed(FormTemplateSeeder::class);
        $this->user->forceFill(['form_credits' => 1])->save();

        foreach (range(1, 3) as $_) {
            $this->post('/forms/e-passport');
            $this->user->applications()->latest('id')->first()->update(['status' => 'completed']);
        }

        $this->post('/forms/e-passport')->assertRedirect();
        $this->assertSame('credit', $this->user->applications()->latest('id')->first()->funded_by);
        $this->assertSame(0, $this->user->fresh()->form_credits);

        $this->user->applications()->latest('id')->first()->update(['status' => 'completed']);
        $this->post('/forms/e-passport')->assertSessionHasErrors('form');
    }

    public function test_premium_users_have_no_assist_limit(): void
    {
        $this->seed(FormTemplateSeeder::class);
        $this->user->forceFill(['premium_until' => now()->addMonth()])->save();

        foreach (range(1, 5) as $_) {
            $this->post('/forms/e-passport')->assertSessionHasNoErrors();
            $this->user->applications()->latest('id')->first()->update(['status' => 'completed']);
        }

        $this->assertSame(5, $this->user->applications()->where('funded_by', 'premium')->count());
    }

    public function test_the_plans_page_renders(): void
    {
        $this->get('/plans')->assertOk()->assertInertia(fn ($page) => $page->component('Plans')->where('plan.freeAssistsLeft', 3));
    }
}
