<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Ocr\PaperParser;
use App\Services\Ocr\TesseractOcr;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Reads a freshly uploaded paper (F4). The decrypted image exists only in
 * a temp file for the length of the OCR run. Results are stored encrypted
 * as suggestions; nothing enters the profile until the user confirms.
 */
class ReadDocument implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $documentId) {}

    public static function canRead(Document $document): bool
    {
        return str_starts_with($document->mime, 'image/') && $document->typeConfig()['fields'] !== [];
    }

    public function handle(TesseractOcr $ocr, PaperParser $parser): void
    {
        $document = Document::find($this->documentId);

        if (! $document) {
            return;
        }

        if (! $ocr->available()) {
            $document->update(['ocr_status' => 'unavailable']);

            return;
        }

        $path = tempnam(sys_get_temp_dir(), 'fs-ocr-');

        try {
            file_put_contents($path, $document->contents());
            $suggestions = $parser->parse($document->type, $ocr->read($path));

            $document->update([
                'ocr_suggestions' => json_encode($suggestions, JSON_UNESCAPED_UNICODE),
                'ocr_status' => $suggestions === [] ? 'nothing_found' : 'read',
            ]);
        } catch (\Throwable $e) {
            Log::warning('OCR failed', ['document' => $document->id, 'error' => $e->getMessage()]);
            $document->update(['ocr_status' => 'failed']);
        } finally {
            @unlink($path);
        }
    }
}
