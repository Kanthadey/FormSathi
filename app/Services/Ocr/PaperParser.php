<?php

namespace App\Services\Ocr;

use App\Support\Bangla;
use Carbon\Carbon;

/**
 * Turns OCR text into suggested field values for one paper type. Results
 * are only suggestions: the user checks each one before it is saved.
 * A field the parser is unsure of is left out rather than guessed.
 */
class PaperParser
{
    /** @return array<string, string> field key => suggested value */
    public function parse(string $type, string $text): array
    {
        $text = Bangla::toAsciiDigits(str_replace("\r", '', $text));

        $values = match ($type) {
            'nid' => $this->nid($text),
            'passport' => $this->passport($text),
            'birth_registration' => $this->birthRegistration($text),
            'ssc', 'hsc' => $this->certificate($text),
            default => [],
        };

        // Only fields this paper type actually holds.
        $allowed = config("formsathi.document_types.{$type}.fields", []);

        return array_filter(
            array_intersect_key($values, array_flip($allowed)),
            fn ($v) => $v !== null && trim($v) !== '',
        );
    }

    private function nid(string $text): array
    {
        return [
            'name_bn' => $this->after($text, '(?:নাম|নামঃ)'),
            'name_en' => $this->upperName($this->after($text, 'Name')),
            'father_name_bn' => $this->after($text, 'পিতা'),
            'mother_name_bn' => $this->after($text, 'মাতা'),
            'date_of_birth' => $this->date($this->after($text, 'Date of Birth')),
            // Smart cards use 10 digits; older cards 13 or 17.
            'nid_number' => $this->match($text, '/(?:ID\s*NO|NID\s*No|জাতীয় পরিচয়পত্র নম্বর)[^\d]{0,6}(\d{10}|\d{13}|\d{17})\b/iu')
                ?? $this->match($text, '/\b(\d{3}\s?\d{3}\s?\d{4})\b/'),
        ];
    }

    /** The machine-readable zone is far more reliable than the printed text. */
    private function passport(string $text): array
    {
        $lines = array_values(array_filter(array_map(
            fn ($l) => strtoupper(preg_replace('/\s+/', '', $l)),
            explode("\n", $text),
        ), fn ($l) => strlen($l) >= 40 && substr_count($l, '<') >= 2));

        $line1 = collect($lines)->first(fn ($l) => str_starts_with($l, 'P<') || str_starts_with($l, 'P'));
        $line2 = collect($lines)->first(fn ($l) => $l !== $line1 && preg_match('/^[A-Z0-9<]{9}\d[A-Z<]{3}\d{6}/', $l));

        if (! $line1 || ! $line2) {
            return [];
        }

        [$surname, $given] = array_pad(explode('<<', substr($line1, 5), 2), 2, '');
        $name = trim(str_replace('<', ' ', $given).' '.str_replace('<', ' ', $surname));

        $number = rtrim(substr($line2, 0, 9), '<');
        $values = [
            'name_en' => preg_replace('/\s+/', ' ', $name),
            'passport_number' => $this->checkDigit(substr($line2, 0, 9), $line2[9] ?? '') ? $number : null,
            'date_of_birth' => $this->checkDigit(substr($line2, 13, 6), $line2[19] ?? '') ? $this->mrzDate(substr($line2, 13, 6), past: true) : null,
            'passport_expiry_date' => $this->checkDigit(substr($line2, 21, 6), $line2[27] ?? '') ? $this->mrzDate(substr($line2, 21, 6), past: false) : null,
        ];

        return $values;
    }

    private function birthRegistration(string $text): array
    {
        return [
            'birth_reg_number' => $this->match($text, '/\b(\d{17})\b/'),
            'name_en' => $this->upperName($this->after($text, "(?:Registered Person'?s?\s*)?Name")),
            'name_bn' => $this->after($text, 'নাম'),
            'date_of_birth' => $this->date($this->after($text, 'Date of Birth')),
            'father_name_en' => $this->upperName($this->after($text, "Father'?s?\s*Name")),
            'mother_name_en' => $this->upperName($this->after($text, "Mother'?s?\s*Name")),
            'father_name_bn' => $this->after($text, 'পিতার নাম'),
            'mother_name_bn' => $this->after($text, 'মাতার নাম'),
        ];
    }

    private function certificate(string $text): array
    {
        return [
            'name_en' => $this->upperName($this->match($text, '/certify that\s+(.+?)\s+(?:son|daughter|of)\b/is')),
            'father_name_en' => $this->upperName($this->after($text, "Father'?s?\s*Name")),
            'mother_name_en' => $this->upperName($this->after($text, "Mother'?s?\s*Name")),
            'date_of_birth' => $this->date($this->after($text, 'Date of Birth')),
        ];
    }

    /** The rest of the line after a label like "Name:" */
    private function after(string $text, string $label): ?string
    {
        $value = $this->match($text, '/(?:^|\n)\s*'.$label.'\s*[:ঃ.\-]?\s*([^\n]{2,80})/iu');

        // Not trim(): its character list is byte-based and would cut Bangla letters.
        return $value ? preg_replace('/^[\s:ঃ.\-]+|[\s:ঃ.\-]+$/u', '', $value) : null;
    }

    private function match(string $text, string $pattern): ?string
    {
        return preg_match($pattern, $text, $m) ? trim(preg_replace('/\s+/', ' ', $m[1])) : null;
    }

    /** English names on these papers are capitals and letters only. */
    private function upperName(?string $value): ?string
    {
        if (! $value || ! preg_match('/^[A-Za-z .\-]{3,}$/', $value)) {
            return null;
        }

        return mb_strtoupper(preg_replace('/\s+/', ' ', $value));
    }

    /** "12 Apr 1999", "12/04/1999", "12-04-1999" -> 1999-04-12 */
    private function date(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        foreach (['d M Y', 'j M Y', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat("!{$format}", trim(preg_replace('/\s+/', ' ', $value)));

                if ($date && $date->year > 1900 && $date->isPast()) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                // Try the next format.
            }
        }

        return null;
    }

    private function mrzDate(string $yymmdd, bool $past): ?string
    {
        if (! ctype_digit($yymmdd)) {
            return null;
        }

        $yy = (int) substr($yymmdd, 0, 2);
        $century = $past ? ($yy > (int) now()->format('y') ? 1900 : 2000) : 2000;

        return checkdate((int) substr($yymmdd, 2, 2), (int) substr($yymmdd, 4, 2), $century + $yy)
            ? sprintf('%04d-%s-%s', $century + $yy, substr($yymmdd, 2, 2), substr($yymmdd, 4, 2))
            : null;
    }

    /** ICAO 9303 check digit: weights 7,3,1; letters A=10..Z=35; '<' = 0. */
    private function checkDigit(string $field, string $digit): bool
    {
        $sum = 0;

        foreach (str_split($field) as $i => $char) {
            $value = match (true) {
                ctype_digit($char) => (int) $char,
                ctype_alpha($char) => ord($char) - 55,
                default => 0,
            };
            $sum += $value * [7, 3, 1][$i % 3];
        }

        return (string) ($sum % 10) === $digit;
    }
}
