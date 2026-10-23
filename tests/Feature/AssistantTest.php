<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\Claude;
use App\Services\Assistant;
use Database\Seeders\FormTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FakeClaude;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FormTemplateSeeder::class);
    }

    private function understand(string $message, ?FakeClaude $fake = null): array
    {
        $this->app->instance(Claude::class, $fake ?? new FakeClaude(up: false));

        return app(Assistant::class)->understand($message);
    }

    public function test_common_requests_are_routed_without_the_ai(): void
    {
        $fake = new FakeClaude(up: true, reply: ['destination' => 'help', 'confidence' => 'high', 'reply' => 'x']);

        $this->assertSame('e-passport', $this->understand('আমি পাসপোর্ট করতে চাই', $fake)['destination']);
        $this->assertSame('e-passport', $this->understand('ami pasport korte chai', $fake)['destination']);
        $this->assertSame('job_kit', $this->understand('chakrir jonno cv lagbe', $fake)['destination']);
        $this->assertSame('job_kit', $this->understand('আমার একটা সিভি দরকার', $fake)['destination']);
        $this->assertSame([], $fake->calls);
    }

    public function test_latin_keywords_match_whole_words_only(): void
    {
        // "cv" inside "civil" must not route to the job kit.
        $this->assertSame('unclear', $this->understand('civil service question')['destination']);
    }

    public function test_unclear_requests_go_to_the_ai_masked(): void
    {
        $fake = new FakeClaude(['destination' => 'profile', 'confidence' => 'high', 'reply' => 'প্রোফাইল খুলছি।']);

        $result = $this->understand('amar NID 1234567890 te nam vul ache', $fake);

        $this->assertSame('profile', $result['destination']);
        $this->assertSame('ai', $result['source']);
        $this->assertStringNotContainsString('1234567890', $fake->sentText());
    }

    public function test_low_confidence_asks_back(): void
    {
        $fake = new FakeClaude(['destination' => 'e-passport', 'confidence' => 'low', 'reply' => 'কোন ফর্ম দরকার?']);

        $result = $this->understand('form fill korbo', $fake);

        $this->assertSame('unclear', $result['destination']);
        $this->assertSame('কোন ফর্ম দরকার?', $result['reply']);
    }

    public function test_the_page_keeps_the_conversation_in_the_session(): void
    {
        $this->app->instance(Claude::class, new FakeClaude(up: false));
        $this->actingAs(User::factory()->create())->withSession(['last_active_at' => now()->timestamp]);

        $this->post('/assistant', ['message' => 'passport'])->assertRedirect('/assistant');

        $this->get('/assistant')->assertInertia(fn ($page) => $page
            ->component('Assistant')
            ->has('turns', 2)
            ->where('turns.1.destination.key', 'e-passport')
            ->where('turns.1.destination.method', 'post')
        );

        $this->delete('/assistant');
        $this->get('/assistant')->assertInertia(fn ($page) => $page->has('turns', 0));
    }
}
