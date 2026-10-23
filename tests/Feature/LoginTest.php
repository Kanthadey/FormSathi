<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Sms\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function lastSmsCode(): string
    {
        $sent = app(SmsGateway::class)->sent;
        preg_match('/\d{6}/', end($sent)['message'], $match);

        return $match[0];
    }

    public function test_a_new_user_signs_up_with_mobile_code_and_pin(): void
    {
        // Bangla digits and the +880 prefix are both accepted.
        $this->post('/login', ['mobile' => '+৮৮০ ১৭১১-২২৩৩৪৪'])->assertRedirect('/login/verify');
        $this->post('/login/verify', ['code' => $this->lastSmsCode()])->assertRedirect('/login/pin');
        $this->get('/login/pin')->assertInertia(fn ($page) => $page->component('Auth/Pin')->where('mode', 'set'));

        $this->post('/login/pin', ['pin' => '246810', 'pin_confirmation' => '246810'])->assertRedirect('/dashboard');

        $user = User::firstWhere('mobile', '01711223344');
        $this->assertAuthenticatedAs($user);
        $this->assertNotEmpty($user->data_key);
        $this->assertNotSame('246810', $user->pin);
    }

    public function test_pin_must_be_confirmed(): void
    {
        $this->post('/login', ['mobile' => '01711223344']);
        $this->post('/login/verify', ['code' => $this->lastSmsCode()]);

        $this->post('/login/pin', ['pin' => '246810', 'pin_confirmation' => '111111'])->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_a_wrong_code_is_rejected(): void
    {
        $this->post('/login', ['mobile' => '01711223344']);
        $code = $this->lastSmsCode();

        $this->post('/login/verify', ['code' => $code === '000000' ? '111111' : '000000'])->assertSessionHasErrors('code');
        $this->get('/login/pin')->assertRedirect('/login');
    }

    public function test_an_invalid_mobile_is_rejected(): void
    {
        $this->post('/login', ['mobile' => '0123'])->assertSessionHasErrors('mobile');
    }

    public function test_a_returning_user_enters_their_pin_and_a_new_device_triggers_an_alert(): void
    {
        $user = User::factory()->create(['mobile' => '01811223344']);
        $user->knownDevices()->create(['token_hash' => hash('sha256', 'old-device')]);

        $this->post('/login', ['mobile' => '01811223344']);
        $this->post('/login/verify', ['code' => $this->lastSmsCode()]);
        $this->get('/login/pin')->assertInertia(fn ($page) => $page->where('mode', 'enter'));
        $this->post('/login/pin', ['pin' => '123456'])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertStringContainsString('নতুন ডিভাইস', end(app(SmsGateway::class)->sent)['message']);
    }

    public function test_too_many_wrong_pins_lock_the_account(): void
    {
        User::factory()->create(['mobile' => '01811223344']);

        $this->post('/login', ['mobile' => '01811223344']);
        $this->post('/login/verify', ['code' => $this->lastSmsCode()]);

        foreach (range(1, 5) as $_) {
            $this->post('/login/pin', ['pin' => '999999'])->assertSessionHasErrors('pin');
        }

        $this->post('/login/pin', ['pin' => '123456'])->assertSessionHasErrors('pin');
        $this->assertGuest();
    }

    public function test_the_app_locks_after_idle_minutes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['last_active_at' => now()->subMinutes(6)->timestamp])
            ->get('/dashboard')
            ->assertRedirect('/lock');

        $this->post('/unlock', ['pin' => '123456'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
    }
}
