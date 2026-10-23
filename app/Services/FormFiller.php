<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use App\Support\Bangla;
use Carbon\Carbon;

/**
 * Lays out a form template from the master profile (F8, F9).
 *
 * Every value comes straight from one profile field and is shaped only by
 * the template's format rule. Nothing here guesses, translates or calls the
 * AI: if the profile lacks a value, the field shows as missing.
 */
class FormFiller
{
    public function __construct(private readonly ProfileService $profile) {}

    /**
     * The template's sections with each field's value and state. A completed
     * application shows the snapshot taken when it was submitted.
     *
     * @return array{sections: list<array<string, mixed>>, totals: array<string, int>}
     */
    public function fill(Application $application, User $user): array
    {
        $completed = $application->isCompleted();
        $snapshot = $application->snapshot();
        $profile = $completed ? collect() : collect($this->profile->overview($user))->keyBy('key');
        $copied = $application->copied ?? [];

        $sections = collect($application->template->sections)->map(fn ($section) => [
            'key' => $section['key'],
            'label' => ['bn' => $section['bn'], 'en' => $section['en']],
            'fields' => collect($section['fields'])->map(function ($field) use ($completed, $snapshot, $profile, $copied, $user) {
                $config = config("formsathi.fields.{$field['profile']}");
                $row = $profile->get($field['profile']);

                $value = $completed
                    ? ($snapshot[$field['key']] ?? null)
                    : self::format($field['profile'] === 'account_mobile' ? $user->mobile : $row['value'] ?? null, $field['format'], $config);

                return [
                    'key' => $field['key'],
                    'site' => $field['site'],
                    'label' => [
                        'bn' => $field['bn'] ?? $config['bn'] ?? $field['site'],
                        'en' => $field['en'] ?? $config['en'] ?? $field['site'],
                    ],
                    'profileKey' => $field['profile'],
                    'value' => $value,
                    'required' => $field['required'],
                    'missing' => $value === null,
                    'needsReview' => $row['needsReview'] ?? false,
                    'sensitive' => $config['sensitive'] ?? false,
                    'help' => $field['help'] ?? null,
                    'copied' => in_array($field['key'], $copied, true),
                ];
            })->values()->all(),
        ])->values()->all();

        $fields = collect($sections)->flatMap(fn ($s) => $s['fields']);

        return [
            'sections' => $sections,
            'totals' => [
                'fields' => $fields->count(),
                'filled' => $fields->where('missing', false)->count(),
                'missingRequired' => $fields->where('missing', true)->where('required', true)->count(),
                'needsReview' => $fields->where('needsReview', true)->count(),
                'copied' => $fields->where('copied', true)->count(),
            ],
        ];
    }

    /** @return array<string, string> template field key => value, filled fields only */
    public function values(Application $application, User $user): array
    {
        return collect($this->fill($application, $user)['sections'])
            ->flatMap(fn ($s) => $s['fields'])
            ->whereNotNull('value')
            ->pluck('value', 'key')
            ->all();
    }

    /** Shapes a profile value the way the form expects it. */
    public static function format(?string $value, string $format, ?array $config = null): ?string
    {
        $value = $value === null ? null : trim($value);

        if ($value === null || $value === '') {
            return null;
        }

        return match ($format) {
            'upper' => mb_strtoupper($value),
            'digits' => Bangla::toAsciiDigits($value),
            'date_dmy' => Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y'),
            'option_en' => $config['options'][$value]['en'] ?? $value,
            default => $value,
        };
    }
}
