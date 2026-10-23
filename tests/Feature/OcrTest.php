<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Services\Ocr\PaperParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OcrTest extends TestCase
{
    use RefreshDatabase;

    private const NID_TEXT = <<<'TXT'
        গণপ্রজাতন্ত্রী বাংলাদেশ সরকার
        Government of the People's Republic of Bangladesh
        নাম: মোহাম্মদ রফিক
        Name: MOHAMMAD RAFIQ
        পিতা: আব্দুল করিম
        মাতা: রহিমা বেগম
        Date of Birth: 12 Apr 1999
        ID NO: 1234567890
        TXT;

    /** ICAO 9303 check digit, written independently of the parser. */
    private static function cd(string $s): string
    {
        $w = [7, 3, 1];
        $sum = 0;
        foreach (str_split($s) as $i => $c) {
            $v = ctype_digit($c) ? (int) $c : (ctype_alpha($c) ? ord($c) - 55 : 0);
            $sum += $v * $w[$i % 3];
        }

        return (string) ($sum % 10);
    }

    private static function mrz(string $number = 'A01234567', string $dob = '990412', string $expiry = '310101'): string
    {
        $line1 = str_pad('P<BGDRAFIQ<<MOHAMMAD', 44, '<');
        $line2 = $number.self::cd($number).'BGD'.$dob.self::cd($dob).'M'.$expiry.self::cd($expiry);

        return "PASSPORT\nRandom printed text\n{$line1}\n".str_pad($line2, 44, '<')."\n";
    }

    public function test_nid_text_becomes_suggestions(): void
    {
        $values = app(PaperParser::class)->parse('nid', self::NID_TEXT);

        $this->assertSame('মোহাম্মদ রফিক', $values['name_bn']);
        $this->assertSame('MOHAMMAD RAFIQ', $values['name_en']);
        $this->assertSame('আব্দুল করিম', $values['father_name_bn']);
        $this->assertSame('রহিমা বেগম', $values['mother_name_bn']);
        $this->assertSame('1999-04-12', $values['date_of_birth']);
        $this->assertSame('1234567890', $values['nid_number']);
    }

    public function test_passport_mrz_is_read_and_check_digits_enforced(): void
    {
        $values = app(PaperParser::class)->parse('passport', self::mrz());

        $this->assertSame('MOHAMMAD RAFIQ', $values['name_en']);
        $this->assertSame('A01234567', $values['passport_number']);
        $this->assertSame('1999-04-12', $values['date_of_birth']);
        $this->assertSame('2031-01-01', $values['passport_expiry_date']);

        // One misread digit fails its check digit, so the field is left out rather than guessed.
        // (The original check digit stays, so it no longer matches.)
        $corrupt = str_replace('A01234567', 'A01234667', self::mrz());
        $this->assertArrayNotHasKey('passport_number', app(PaperParser::class)->parse('passport', $corrupt));
    }

    public function test_unreadable_text_suggests_nothing(): void
    {
        $this->assertSame([], app(PaperParser::class)->parse('nid', "blurry\n???"));
        $this->assertSame([], app(PaperParser::class)->parse('photo', self::NID_TEXT));
    }

    public function test_an_upload_is_read_in_the_background_and_suggestions_wait_for_the_user(): void
    {
        Storage::fake('local');
        Cache::forget('ocr.tesseract.available');
        Process::fake([
            '*--version*' => Process::result('tesseract 5.3.0'),
            '*' => Process::result(self::NID_TEXT),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['last_active_at' => now()->timestamp]);

        $this->post('/documents', ['type' => 'nid', 'file' => UploadedFile::fake()->createWithContent('nid.png', 'IMG')]);
        $document = Document::first();

        $this->assertSame('read', $document->ocr_status);
        $this->assertStringNotContainsString('RAFIQ', DB::table('documents')->value('ocr_suggestions'));
        // Nothing reaches the profile until the user saves.
        $this->assertSame(0, $user->profileFields()->count());

        $this->get("/documents/{$document->id}")->assertInertia(fn ($page) => $page
            ->where('ocrStatus', 'read')
            ->where('fields.1.key', 'name_en')
            ->where('fields.1.suggestion', 'MOHAMMAD RAFIQ')
        );

        $this->put("/documents/{$document->id}/fields", ['fields' => ['name_en' => 'MOHAMMAD RAFIQ']]);
        $this->assertSame('confirmed', $document->fresh()->ocr_status);
        $this->assertNull($document->fresh()->ocr_suggestions);
        $this->assertSame(1, $user->profileFields()->count());
    }

    public function test_without_tesseract_the_paper_is_marked_unavailable(): void
    {
        Storage::fake('local');
        Cache::forget('ocr.tesseract.available');
        Process::fake(['*' => Process::result('', 'not found', 1)]);

        $this->actingAs(User::factory()->create())->withSession(['last_active_at' => now()->timestamp]);
        $this->post('/documents', ['type' => 'nid', 'file' => UploadedFile::fake()->createWithContent('nid.png', 'IMG')]);

        $this->assertSame('unavailable', Document::first()->ocr_status);
    }

    public function test_pdfs_and_photos_are_not_queued(): void
    {
        Storage::fake('local');
        Queue::fake();

        $this->actingAs(User::factory()->create())->withSession(['last_active_at' => now()->timestamp]);
        $this->post('/documents', ['type' => 'nid', 'file' => UploadedFile::fake()->create('nid.pdf', 10, 'application/pdf')]);
        $this->post('/documents', ['type' => 'photo', 'file' => UploadedFile::fake()->createWithContent('me.png', 'IMG')]);

        Queue::assertNothingPushed();
    }
}
