<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Services\Sms\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function signedIn(?User $user = null): User
    {
        $user ??= User::factory()->create();
        $this->actingAs($user)->withSession(['last_active_at' => now()->timestamp]);

        return $user;
    }

    private function upload(string $type = 'nid', string $contents = 'NID-IMAGE-BYTES'): Document
    {
        $this->post('/documents', [
            'type' => $type,
            'file' => UploadedFile::fake()->createWithContent('nid.png', $contents),
        ])->assertRedirect();

        return Document::latest('id')->first();
    }

    public function test_an_upload_is_stored_encrypted(): void
    {
        $this->signedIn();
        $document = $this->upload(contents: 'NID-IMAGE-BYTES');

        $this->assertSame('identity', $document->category);
        $stored = Storage::disk('local')->get($document->file_path);
        $this->assertStringNotContainsString('NID-IMAGE-BYTES', $stored);
        $this->assertSame('NID-IMAGE-BYTES', $document->contents());

        // The original filename is encrypted in the database too.
        $this->assertStringStartsWith('v1.', DB::table('documents')->value('original_name'));
    }

    public function test_viewing_a_paper_needs_a_recent_pin(): void
    {
        $this->signedIn();
        $document = $this->upload();

        $this->get("/documents/{$document->id}/file")->assertForbidden();

        $this->post('/pin/confirm', ['pin' => '123456']);
        $this->get("/documents/{$document->id}/file")
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->assertDatabaseHas('access_logs', ['action' => 'document.view', 'subject_id' => $document->id]);
    }

    public function test_users_cannot_see_each_others_papers(): void
    {
        $owner = $this->signedIn();
        $document = $this->upload();

        $this->signedIn(User::factory()->create());
        $this->withSession(['pin_confirmed_at' => now()->timestamp]);

        $this->get("/documents/{$document->id}")->assertNotFound();
        $this->get("/documents/{$document->id}/file")->assertNotFound();
        $this->delete("/documents/{$document->id}")->assertNotFound();
        $this->assertModelExists($document);
    }

    public function test_the_free_plan_holds_ten_papers(): void
    {
        $this->signedIn();

        foreach (range(1, 10) as $_) {
            $this->upload('photo');
        }

        $this->post('/documents', [
            'type' => 'photo',
            'file' => UploadedFile::fake()->createWithContent('one-more.png', 'PHOTO'),
        ])->assertSessionHasErrors('file');
    }

    public function test_only_images_and_pdfs_are_accepted(): void
    {
        $this->signedIn();

        $this->post('/documents', [
            'type' => 'nid',
            'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');
    }

    public function test_deleting_a_paper_removes_its_file(): void
    {
        $this->signedIn();
        $document = $this->upload();
        $this->withSession(['pin_confirmed_at' => now()->timestamp]);

        $this->delete("/documents/{$document->id}")->assertRedirect('/documents');

        $this->assertModelMissing($document);
        Storage::disk('local')->assertMissing($document->file_path);
    }

    public function test_passport_expiry_is_set_from_its_details_and_reminded_by_sms(): void
    {
        $user = $this->signedIn();
        $document = $this->upload('passport');

        $this->put("/documents/{$document->id}/fields", ['fields' => [
            'passport_number' => 'A01234567',
            'name_en' => 'RAFIQ ISLAM',
            'passport_expiry_date' => now()->addMonths(3)->toDateString(),
        ]])->assertRedirect('/profile');

        $this->assertNotNull($document->fresh()->expires_at);

        $this->artisan('documents:remind-expiry')->assertSuccessful();
        $this->artisan('documents:remind-expiry')->assertSuccessful();

        $reminders = collect(app(SmsGateway::class)->sent)->where('mobile', $user->mobile);
        $this->assertCount(1, $reminders);
        $this->assertStringContainsString('পাসপোর্ট', $reminders->first()['message']);
    }

    public function test_deleting_the_account_removes_everything(): void
    {
        $user = $this->signedIn();
        $document = $this->upload();

        $this->delete('/account', ['pin' => '000000'])->assertSessionHasErrors('pin');
        $this->delete('/account', ['pin' => '123456'])->assertRedirect('/login');

        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertDatabaseCount('documents', 0);
        Storage::disk('local')->assertMissing($document->file_path);
    }
}
