<?php

namespace Tests\Feature;

use App\Models\JobKit;
use App\Models\User;
use App\Services\Ai\Claude;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\FakeClaude;
use Tests\TestCase;

class JobKitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private const ADVERT = "Sales Executive at Pran Foods Ltd, Dhaka.\nRequirements: HSC, 2 years of sales experience, MS Excel, driving licence.\nApplicants must answer: Why do you want this job?\nContact hr@pran.test or 01999888777.";

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['mobile' => '01711223344']);
        $this->actingAs($this->user)->withSession(['last_active_at' => now()->timestamp]);

        app(ProfileService::class)->setField($this->user, 'name_en', 'MOHAMMAD RAFIQ');
        app(ProfileService::class)->setField($this->user, 'nid_number', '1234567890');

        foreach ([
            ['kind' => 'education', 'data' => ['degree' => 'HSC', 'institution' => 'Cumilla Victoria College', 'year' => 2017]],
            ['kind' => 'work', 'data' => ['title' => 'Sales Representative', 'employer' => 'Akij Group', 'start' => '2019-01', 'description' => 'Sold to 40 shops']],
            ['kind' => 'skill', 'data' => ['name' => 'MS Excel']],
            ['kind' => 'skill', 'data' => ['name' => 'Negotiation']],
        ] as $item) {
            $this->post('/career', $item)->assertSessionHasNoErrors();
        }
    }

    private function fake(?array $reply = null, bool $up = true): FakeClaude
    {
        $fake = new FakeClaude($reply ?? $this->reply(), $up);
        $this->app->instance(Claude::class, $fake);

        return $fake;
    }

    private function reply(): array
    {
        return [
            'job_title' => 'Sales Executive',
            'company' => 'Pran Foods Ltd',
            'summary' => 'Sales professional with field experience.',
            'skills' => ['MS Excel', 'Driving', 'negotiation'],
            'experience' => [
                ['id' => 'W1', 'bullets' => ['Served 40 retail shops']],
                ['id' => 'W9', 'bullets' => ['Invented job at Google']],
            ],
            'education_order' => ['E1', 'E7'],
            'cover_letter' => "Dear Hiring Manager,\nI am applying...\n{{NAME}}\n{{PHONE}}",
            'answers' => [['question' => 'Why do you want this job?', 'answer' => 'I enjoy sales.']],
            'gaps' => ['ড্রাইভিং লাইসেন্স'],
        ];
    }

    public function test_a_kit_is_built_only_from_the_users_own_record(): void
    {
        $this->fake();

        $this->post('/jobs', ['advert' => self::ADVERT])->assertRedirect();
        $kit = JobKit::first();
        $data = $kit->data();

        $this->assertSame('Sales Executive', $kit->job_title);
        // "Driving" is not in the record; "negotiation" maps to the user's own spelling.
        $this->assertSame(['MS Excel', 'Negotiation'], $data['skills']);
        // W9 does not exist, so its invented bullet is dropped.
        $this->assertCount(1, $data['experience']);
        $this->assertSame(['Served 40 retail shops'], $data['experience'][0]['bullets']);
        $this->assertCount(1, $data['educationOrder']);
        $this->assertSame(['ড্রাইভিং লাইসেন্স'], $data['gaps']);
        $this->assertSame('free', $kit->funded_by);
    }

    public function test_personal_details_never_reach_the_ai(): void
    {
        $fake = $this->fake();

        $this->post('/jobs', ['advert' => self::ADVERT]);
        $sent = $fake->sentText();

        $this->assertStringNotContainsString('RAFIQ', mb_strtoupper($sent));
        $this->assertStringNotContainsString('01711223344', $sent);
        $this->assertStringNotContainsString('1234567890', $sent);
        // Numbers and emails in the advert are masked too.
        $this->assertStringNotContainsString('01999888777', $sent);
        $this->assertStringNotContainsString('hr@pran.test', $sent);
        $this->assertStringContainsString('Akij Group', $sent);
    }

    public function test_contact_details_are_filled_in_locally(): void
    {
        $this->fake();
        $this->post('/jobs', ['advert' => self::ADVERT]);

        $this->get('/jobs/'.JobKit::first()->id)->assertInertia(fn ($page) => $page
            ->component('Jobs/Show')
            ->where('cv.contact.name', 'Mohammad Rafiq')
            ->where('coverLetter', fn ($letter) => str_contains($letter, 'Mohammad Rafiq') && str_contains($letter, '+88 01711223344'))
        );
    }

    public function test_a_failed_ai_call_costs_nothing(): void
    {
        // Available, but the call fails (outage, refusal, bad reply).
        $this->app->instance(Claude::class, new FakeClaude(reply: null, up: true));

        $this->post('/jobs', ['advert' => self::ADVERT])->assertSessionHasErrors('advert');
        $this->assertSame(0, JobKit::count());
    }

    public function test_the_kit_counts_towards_the_free_limit(): void
    {
        $this->fake();

        foreach (range(1, 3) as $_) {
            $this->post('/jobs', ['advert' => self::ADVERT])->assertSessionHasNoErrors();
        }

        $this->post('/jobs', ['advert' => self::ADVERT])->assertSessionHasErrors('advert');
        $this->assertSame(3, JobKit::count());
    }

    public function test_edits_are_saved_and_stored_encrypted(): void
    {
        $this->fake();
        $this->post('/jobs', ['advert' => self::ADVERT]);
        $kit = JobKit::first();

        $this->put("/jobs/{$kit->id}", [
            'summary' => 'Edited summary.',
            'coverLetter' => 'Edited letter.',
            'answers' => [['question' => 'Why?', 'answer' => 'Edited answer.']],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Edited letter.', $kit->fresh()->data()['coverLetter']);
        $this->assertStringNotContainsString('Edited', DB::table('job_kits')->value('result'));
        $this->assertStringNotContainsString('Pran', DB::table('job_kits')->value('advert'));
    }

    public function test_pdfs_download(): void
    {
        $this->fake();
        $this->post('/jobs', ['advert' => self::ADVERT]);
        $id = JobKit::first()->id;

        $this->get("/jobs/{$id}/cv.pdf")->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get("/jobs/{$id}/cover-letter.pdf")->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get("/jobs/{$id}/other.pdf")->assertNotFound();
    }

    public function test_users_cannot_open_each_others_kits(): void
    {
        $this->fake();
        $this->post('/jobs', ['advert' => self::ADVERT]);
        $id = JobKit::first()->id;

        $this->actingAs(User::factory()->create())->withSession(['last_active_at' => now()->timestamp]);

        $this->get("/jobs/{$id}")->assertNotFound();
        $this->get("/jobs/{$id}/cv.pdf")->assertNotFound();
    }

    public function test_career_items_are_validated_and_encrypted(): void
    {
        $this->post('/career', ['kind' => 'work', 'data' => ['title' => 'X']])->assertSessionHasErrors(['data.employer', 'data.start']);
        $this->post('/career', ['kind' => 'nonsense', 'data' => []])->assertSessionHasErrors('kind');

        $this->assertStringNotContainsString('Akij', DB::table('career_items')->pluck('data')->implode(' '));
    }

    public function test_pages_render_without_an_api_key(): void
    {
        $this->fake(up: false);

        $this->get('/jobs')->assertOk();
        $this->get('/jobs/new')->assertInertia(fn ($page) => $page->where('aiAvailable', false)->where('career.skill', 2));
        $this->get('/career')->assertOk();
    }
}
