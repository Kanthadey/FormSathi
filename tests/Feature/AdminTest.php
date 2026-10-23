<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\FormTemplate;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\FormTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FormTemplateSeeder::class);
        $this->user = User::factory()->create(['mobile' => '01711223344']);
        $this->admin = User::factory()->create(['mobile' => '01811223344']);
        $this->artisan('formsathi:make-admin', ['mobile' => '01811223344'])->assertSuccessful();
        $this->admin->refresh();
    }

    private function as(User $user): static
    {
        return $this->actingAs($user)->withSession(['last_active_at' => now()->timestamp]);
    }

    public function test_only_staff_can_open_the_panel(): void
    {
        $this->as($this->user)->get('/admin')->assertNotFound();
        $this->as($this->user)->get('/admin/templates')->assertNotFound();
        $this->as($this->admin)->get('/admin')->assertOk()->assertInertia(fn ($page) => $page->where('stats.users', 2));
    }

    public function test_staff_cannot_open_a_users_papers(): void
    {
        $document = $this->user->documents()->create([
            'user_id' => $this->user->id, 'type' => 'nid', 'category' => 'identity', 'original_name' => 'x',
            'file_path' => 'x', 'mime' => 'image/png', 'size' => 1,
        ]);

        $this->as($this->admin)->withSession(['pin_confirmed_at' => now()->timestamp, 'last_active_at' => now()->timestamp]);
        $this->get("/documents/{$document->id}")->assertNotFound();
        $this->get("/documents/{$document->id}/file")->assertNotFound();
    }

    public function test_edits_save_as_a_new_inactive_version_then_publish(): void
    {
        $v1 = FormTemplate::where('slug', 'e-passport')->first();
        $sections = $v1->sections;
        $sections[0]['fields'][0]['site'] = 'Full Name (as in NID)';

        $this->as($this->admin)->post("/admin/templates/{$v1->id}", [
            'nameBn' => $v1->name_bn,
            'nameEn' => $v1->name_en,
            'officialUrl' => $v1->official_url,
            'sections' => json_encode($sections),
        ])->assertSessionHasNoErrors();

        $v2 = FormTemplate::where('slug', 'e-passport')->where('version', 2)->first();
        $this->assertFalse($v2->is_active);
        $this->assertSame('Full Name (as per NID/BRC)', $v1->fresh()->sections[0]['fields'][0]['site']);
        $this->assertSame($v1->id, FormTemplate::current()->where('slug', 'e-passport')->value('id'));

        $this->post("/admin/templates/{$v2->id}/publish");
        $this->assertSame($v2->id, FormTemplate::current()->where('slug', 'e-passport')->value('id'));
        $this->assertFalse($v1->fresh()->is_active);

        $this->post("/admin/templates/{$v2->id}/verify");
        $this->assertNotNull($v2->fresh()->verified_at);
    }

    public function test_redeploying_keeps_staff_checks_and_edits(): void
    {
        $template = FormTemplate::first();
        $this->as($this->admin)->post("/admin/templates/{$template->id}/verify");

        // deploy.sh re-runs the seeder on every release.
        $this->seed(FormTemplateSeeder::class);

        $this->assertNotNull($template->fresh()->verified_at);
        $this->assertSame(1, FormTemplate::count());
    }

    public function test_broken_templates_are_refused(): void
    {
        $v1 = FormTemplate::first();
        $sections = $v1->sections;
        $sections[0]['fields'][0]['profile'] = 'favourite_colour';
        $sections[0]['fields'][1]['key'] = $sections[0]['fields'][0]['key'];
        $sections[0]['fields'][2]['format'] = 'shout';

        $this->as($this->admin)->post("/admin/templates/{$v1->id}", [
            'nameBn' => 'x', 'nameEn' => 'x', 'sections' => json_encode($sections),
        ])->assertSessionHasErrors('sections');

        $this->post("/admin/templates/{$v1->id}", ['nameBn' => 'x', 'nameEn' => 'x', 'sections' => '{not json'])
            ->assertSessionHasErrors('sections');

        $this->assertSame(1, FormTemplate::count());
    }

    public function test_tickets_go_back_and_forth_encrypted(): void
    {
        $this->as($this->user)->post('/help', ['subject' => 'Help', 'body' => 'My NID 1234567890 shows wrong'])->assertRedirect();
        $ticket = Ticket::first();
        $this->assertStringNotContainsString('1234567890', DB::table('ticket_messages')->value('body'));

        $this->as($this->admin)->get("/admin/tickets/{$ticket->id}")->assertInertia(fn ($page) => $page
            ->where('ticket.user', '017•••••344')
            ->where('ticket.messages.0.body', 'My NID 1234567890 shows wrong')
        );
        $this->post("/admin/tickets/{$ticket->id}", ['body' => 'Fixed, please check.'])->assertSessionHasNoErrors();
        $this->assertSame('answered', $ticket->fresh()->status);

        $this->as($this->user)->get("/help/{$ticket->id}")->assertInertia(fn ($page) => $page
            ->where('ticket.messages.1.fromStaff', true)
            ->where('ticket.messages.1.body', 'Fixed, please check.')
        );

        $other = User::factory()->create();
        $this->as($other)->get("/help/{$ticket->id}")->assertNotFound();
    }

    public function test_a_wrong_field_can_be_reported_from_copy_assist(): void
    {
        $this->as($this->user)->post('/forms/e-passport');
        $application = Application::first();

        $this->post("/applications/{$application->id}/report", ['key' => 'surname', 'note' => 'Site says Family Name'])
            ->assertSessionHasNoErrors();
        $this->post("/applications/{$application->id}/report", ['key' => 'nope'])->assertSessionHasErrors('key');

        $ticket = Ticket::first();
        $this->assertSame(['template' => 'e-passport', 'version' => 1, 'field' => 'surname'], $ticket->context);
    }

    public function test_admin_pages_render(): void
    {
        $this->as($this->admin);

        foreach (['/admin/templates', '/admin/templates/'.FormTemplate::first()->id, '/admin/tickets', '/admin/tickets?status=all', '/admin/payments'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
