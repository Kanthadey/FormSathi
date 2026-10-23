<?php

namespace App\Support;

class Bangla
{
    private const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    /** Turns Bangla digits into ASCII so "০১৭১১" and "01711" are the same input. */
    public static function toAsciiDigits(string $value): string
    {
        return str_replace(self::DIGITS, range(0, 9), $value);
    }

    /** Numbers inside messages: Bangla digits when the interface is Bangla. */
    public static function localDigits(int|string $value): string
    {
        return app()->getLocale() === 'bn' ? str_replace(range(0, 9), self::DIGITS, (string) $value) : (string) $value;
    }

    /**
     * Normalises a Bangladeshi mobile number to 01XXXXXXXXX, accepting
     * +880 / 880 prefixes, spaces, dashes and Bangla digits.
     * Returns null when it is not a valid mobile number.
     */
    public static function mobile(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', self::toAsciiDigits((string) $value));

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) ? $digits : null;
    }

    /** 01711223344 -> 017•••••344, for alerts and screens. */
    public static function maskMobile(string $mobile): string
    {
        return substr($mobile, 0, 3).str_repeat('•', 5).substr($mobile, -3);
    }

    /**
     * The form used to decide whether two papers say the same thing:
     * case, dots, commas and repeated spaces are ignored. "Md." and
     * "Mohammad" stay different on purpose, since offices treat them so.
     */
    public static function comparable(string $value): string
    {
        $value = mb_strtolower(self::toAsciiDigits($value));
        $value = preg_replace('/[.,\-]+/u', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
