<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_landing_page_and_legal_pages(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn ($page) => $page->component('Public/Home'));
        $this->get('/pricing')->assertOk()->assertInertia(fn ($page) => $page->component('Public/Pricing'));
        $this->get('/privacy')->assertOk()->assertInertia(fn ($page) => $page->component('Public/Privacy'));
        $this->get('/terms')->assertOk()->assertInertia(fn ($page) => $page->component('Public/Terms'));
    }

    public function test_signed_in_users_go_straight_to_their_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['last_active_at' => now()->timestamp]);

        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/privacy')->assertOk();
    }

    public function test_app_pages_need_sign_in(): void
    {
        foreach (['/dashboard', '/jobs', '/plans', '/assistant', '/help', '/career', '/admin'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }
}
