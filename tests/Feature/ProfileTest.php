<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->actingAs($this->user)->withSession(['last_active_at' => now()->timestamp]);
    }

    private function paper(string $type, array $fields): Document
    {
        $document = Document::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'category' => config("formsathi.document_types.{$type}.category"),
            'original_name' => "{$type}.jpg",
            'file_path' => "documents/{$this->user->id}/{$type}.bin",
            'mime' => 'image/jpeg',
            'size' => 1,
        ]);

        $this->put("/documents/{$document->id}/fields", ['fields' => $fields])->assertSessionHasNoErrors();

        return $document;
    }

    private function field(string $key): array
    {
        return collect(app(ProfileService::class)->overview($this->user))->firstWhere('key', $key);
    }

    public function test_a_papers_details_fill_an_empty_profile_unconfirmed(): void
    {
        $nid = $this->paper('nid', ['name_en' => 'Mohammad Rafiq', 'date_of_birth' => '1999-04-12']);

        $name = $this->field('name_en');
        $this->assertSame('Mohammad Rafiq', $name['value']);
        $this->assertSame($nid->id, $name['sourceDocumentId']);
        $this->assertFalse($name['confirmed']);
        $this->assertFalse($name['conflict']);

        // Values are encrypted at rest.
        $this->assertStringNotContainsString('Rafiq', DB::table('profile_fields')->pluck('value')->implode(' '));
    }

    public function test_md_and_mohammad_are_flagged_as_a_mismatch(): void
    {
        $this->paper('nid', ['name_en' => 'Mohammad Rafiq']);
        $this->paper('ssc', ['name_en' => 'Md. Rafiq']);

        $name = $this->field('name_en');
        $this->assertTrue($name['conflict']);
        $this->assertTrue($name['needsReview']);
        $this->assertCount(2, $name['candidates']);
    }

    public function test_case_and_spacing_differences_are_not_a_mismatch(): void
    {
        $this->paper('nid', ['name_en' => 'MOHAMMAD  RAFIQ']);
        $this->paper('ssc', ['name_en' => 'Mohammad Rafiq']);

        $this->assertFalse($this->field('name_en')['conflict']);
    }

    public function test_picking_the_right_value_resolves_the_review(): void
    {
        $this->paper('nid', ['name_en' => 'Mohammad Rafiq']);
        $ssc = $this->paper('ssc', ['name_en' => 'Md. Rafiq']);

        $this->put('/profile/name_en', ['value' => 'Md. Rafiq', 'source_document_id' => $ssc->id])->assertSessionHasNoErrors();

        $name = $this->field('name_en');
        $this->assertSame('Md. Rafiq', $name['value']);
        $this->assertTrue($name['confirmed']);
        $this->assertTrue($name['conflict']);
        $this->assertFalse($name['needsReview']);
    }

    public function test_a_new_disagreeing_paper_reopens_a_confirmed_field(): void
    {
        $this->paper('nid', ['date_of_birth' => '1999-04-12']);
        $this->put('/profile/date_of_birth', ['value' => '1999-04-12']);
        $this->paper('ssc', ['date_of_birth' => '1999-04-21']);

        $this->assertTrue($this->field('date_of_birth')['needsReview']);
    }

    public function test_a_source_document_must_belong_to_the_user(): void
    {
        $other = User::factory()->create();
        $theirs = Document::create([
            'user_id' => $other->id, 'type' => 'nid', 'category' => 'identity', 'original_name' => 'x',
            'file_path' => 'x', 'mime' => 'image/jpeg', 'size' => 1,
        ]);

        $this->put('/profile/name_en', ['value' => 'X', 'source_document_id' => $theirs->id])
            ->assertSessionHasErrors('source_document_id');
    }

    public function test_only_profile_fields_can_be_set(): void
    {
        $this->put('/profile/passport_expiry_date', ['value' => '2030-01-01'])->assertNotFound();
        $this->put('/profile/not_a_field', ['value' => 'x'])->assertNotFound();
    }

    public function test_completeness_counts_core_fields(): void
    {
        $this->assertSame(0, app(ProfileService::class)->completeness($this->user));

        $this->paper('nid', ['name_bn' => 'মোহাম্মদ রফিক', 'name_en' => 'Mohammad Rafiq']);

        $this->assertGreaterThan(0, app(ProfileService::class)->completeness($this->user));
        $this->get('/profile')->assertOk();
        $this->get('/dashboard')->assertOk();
    }
}
