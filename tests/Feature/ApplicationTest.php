<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\FormTemplate;
use App\Models\User;
use App\Services\FormFiller;
use App\Services\ProfileService;
use Database\Seeders\FormTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FormTemplateSeeder::class);
        $this->user = User::factory()->create(['mobile' => '01711223344']);
        $this->actingAs($this->user)->withSession(['last_active_at' => now()->timestamp]);
    }

    private function profile(array $values): void
    {
        foreach ($values as $key => $value) {
            app(ProfileService::class)->setField($this->user, $key, $value);
        }
    }

    private function start(): Application
    {
        $this->post('/forms/e-passport')->assertRedirect();

        return Application::latest('id')->first();
    }

    /** @return array<string, array<string, mixed>> template field key => field */
    private function fields(Application $application): array
    {
        return collect(app(FormFiller::class)->fill($application->fresh(), $this->user)['sections'])
            ->flatMap(fn ($s) => $s['fields'])
            ->keyBy('key')
            ->all();
    }

    public function test_every_template_field_points_at_a_real_profile_field(): void
    {
        foreach (FormTemplate::all() as $template) {
            foreach ($template->fields() as $field) {
                $this->assertTrue(
                    $field['profile'] === 'account_mobile' || config("formsathi.fields.{$field['profile']}.profile") === true,
                    "{$template->slug}.{$field['key']} points at unknown profile field {$field['profile']}",
                );
            }

            $this->assertSame(count($template->fieldKeys()), count(array_unique($template->fieldKeys())), 'Field keys must be unique');
        }
    }

    public function test_values_come_from_the_profile_in_the_forms_format(): void
    {
        $this->profile([
            'name_en' => 'Mohammad Rafiq',
            'date_of_birth' => '1999-04-12',
            'gender' => 'male',
            'nid_number' => '১২৩৪৫৬৭৮৯০',
            'profession' => 'Private Service',
        ]);

        $fields = $this->fields($this->start());

        $this->assertSame('MOHAMMAD RAFIQ', $fields['full_name']['value']);
        $this->assertSame('12/04/1999', $fields['date_of_birth']['value']);
        $this->assertSame('Male', $fields['gender']['value']);
        $this->assertSame('1234567890', $fields['nid']['value']);
        $this->assertSame('Private Service', $fields['profession']['value']);
        $this->assertSame('01711223344', $fields['mobile']['value']);
    }

    public function test_missing_fields_are_shown_not_guessed(): void
    {
        // Full name is known, but the given name / surname split is not.
        $this->profile(['name_en' => 'Mohammad Rafiq']);

        $fields = $this->fields($this->start());

        $this->assertNull($fields['given_name']['value']);
        $this->assertTrue($fields['given_name']['missing']);
        $this->assertNull($fields['surname']['value']);
    }

    public function test_the_pre_check_counts_missing_and_disputed_fields(): void
    {
        $application = $this->start();
        $before = app(FormFiller::class)->fill($application, $this->user)['totals'];
        $this->assertGreaterThan(0, $before['missingRequired']);
        $this->assertSame(0, $before['needsReview']);

        // Two papers that disagree on the name.
        foreach (['nid' => 'MOHAMMAD RAFIQ', 'ssc' => 'MD. RAFIQ'] as $type => $name) {
            $document = $this->user->documents()->create([
                'user_id' => $this->user->id, 'type' => $type, 'category' => 'identity',
                'original_name' => 'x', 'file_path' => 'x', 'mime' => 'image/png', 'size' => 1,
            ]);
            app(ProfileService::class)->saveDocumentFields($document, ['name_en' => $name]);
        }

        $fields = $this->fields($application);
        $this->assertTrue($fields['full_name']['needsReview']);
    }

    public function test_starting_twice_resumes_the_draft(): void
    {
        $first = $this->start();
        $this->post('/forms/e-passport')->assertRedirect("/applications/{$first->id}");

        $this->assertSame(1, Application::count());
    }

    public function test_the_free_plan_allows_three_form_assists_a_month(): void
    {
        foreach (range(1, 3) as $_) {
            $this->start()->update(['status' => 'completed']);
        }

        $this->post('/forms/e-passport')->assertSessionHasErrors('form');
        $this->assertSame(3, Application::count());
    }

    public function test_copied_fields_are_ticked_and_can_be_reset(): void
    {
        $this->profile(['name_en' => 'Mohammad Rafiq']);
        $application = $this->start();

        $this->post("/applications/{$application->id}/copied", ['key' => 'full_name'])->assertRedirect();
        $this->post("/applications/{$application->id}/copied", ['key' => 'full_name']);
        $this->post("/applications/{$application->id}/copied", ['key' => 'not_a_field'])->assertSessionHasErrors('key');

        $this->assertSame(['full_name'], $application->fresh()->copied);
        $this->assertTrue($this->fields($application)['full_name']['copied']);

        $this->delete("/applications/{$application->id}/copied");
        $this->assertSame([], $application->fresh()->copied);
    }

    public function test_completing_keeps_an_encrypted_snapshot(): void
    {
        $this->profile(['name_en' => 'Mohammad Rafiq']);
        $application = $this->start();

        $this->post("/applications/{$application->id}/complete")->assertRedirect("/applications/{$application->id}");

        // A later profile change does not rewrite what was submitted.
        $this->profile(['name_en' => 'Someone Else']);

        $this->assertSame('MOHAMMAD RAFIQ', $this->fields($application)['full_name']['value']);
        $this->assertStringNotContainsString('RAFIQ', DB::table('applications')->value('summary'));
        $this->post("/applications/{$application->id}/complete")->assertStatus(409);
    }

    public function test_users_cannot_open_each_others_applications(): void
    {
        $application = $this->start();

        $this->actingAs(User::factory()->create())->withSession(['last_active_at' => now()->timestamp]);

        $this->get("/applications/{$application->id}")->assertNotFound();
        $this->get("/applications/{$application->id}/print")->assertNotFound();
        $this->post("/applications/{$application->id}/copied", ['key' => 'full_name'])->assertNotFound();
        $this->delete("/applications/{$application->id}")->assertNotFound();
    }

    public function test_pages_render(): void
    {
        $application = $this->start();

        $this->get('/forms')->assertOk()->assertInertia(fn ($page) => $page->component('Forms/Index')->has('templates', 1));
        $this->get("/applications/{$application->id}")->assertOk()->assertInertia(fn ($page) => $page->component('Forms/Show'));
        $this->get("/applications/{$application->id}/print")->assertOk()->assertInertia(fn ($page) => $page->component('Forms/Print'));
        $this->get('/dashboard')->assertInertia(fn ($page) => $page->where('draft.id', $application->id));
    }

    public function test_select_profile_fields_only_accept_their_options(): void
    {
        $this->put('/profile/gender', ['value' => 'female'])->assertSessionHasNoErrors();
        $this->put('/profile/gender', ['value' => 'robot'])->assertSessionHasErrors('value');
    }
}
