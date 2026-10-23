<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Self-hosted OCR (PRD: data stays in-house). Needs the tesseract binary
 * with the "ben" and "eng" language packs on the server.
 */
class TesseractOcr
{
    public function available(): bool
    {
        return Cache::remember('ocr.tesseract.available', now()->addMinutes(10), function () {
            try {
                return Process::timeout(10)->run([config('formsathi.ocr.tesseract'), '--version'])->successful();
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public function read(string $imagePath): string
    {
        $result = Process::timeout(90)->run([
            config('formsathi.ocr.tesseract'), $imagePath, 'stdout',
            '-l', config('formsathi.ocr.languages'),
            '--psm', '3',
        ]);

        if (! $result->successful()) {
            throw new RuntimeException('Tesseract failed: '.$result->errorOutput());
        }

        return $result->output();
    }
}
