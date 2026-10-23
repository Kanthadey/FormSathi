<?php

namespace App\Services\Ai;

use App\Support\Bangla;

/**
 * The masking gateway (PRD security measures): strips anything that looks
 * like an ID number, phone number or email before text leaves for the AI.
 */
class Masker
{
    public static function mask(string $text): string
    {
        $text = Bangla::toAsciiDigits($text);

        // Emails first, then any run of 6+ digits (NID, birth reg, phone, account).
        $text = preg_replace('/[\w.+-]+@[\w-]+\.[\w.-]+/u', '[EMAIL]', $text);
        $text = preg_replace('/\+?\d[\d\s-]{4,}\d/', '[NUMBER]', $text);

        return $text;
    }
}
