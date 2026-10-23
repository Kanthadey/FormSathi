<?php

namespace App\Services;

use App\Models\Document;
use App\Models\ProfileField;
use App\Models\User;
use App\Support\Bangla;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the master profile (F5) from the details on each paper and finds
 * where papers disagree (F6).
 *
 * A profile value is "confirmed" only when the user picked or typed it on
 * the profile screen. Values copied in from a paper start unconfirmed, and
 * a new paper that disagrees un-confirms the value so the user looks again.
 */
class ProfileService
{
    /** Saves what the user read off one paper, then updates the profile. */
    public function saveDocumentFields(Document $document, array $values): void
    {
        $allowed = $document->typeConfig()['fields'];

        DB::transaction(function () use ($document, $values, $allowed) {
            foreach ($allowed as $key) {
                $value = trim((string) ($values[$key] ?? ''));

                if ($value === '') {
                    $document->fields()->where('key', $key)->delete();

                    continue;
                }

                $document->fields()->updateOrCreate(
                    ['key' => $key],
                    ['user_id' => $document->user_id, 'value' => $value],
                );

                if (config("formsathi.fields.{$key}.profile")) {
                    $this->absorb($document, $key, $value);
                }
            }

            $expiryField = $document->typeConfig()['expiry_field'] ?? null;

            if ($expiryField && array_key_exists($expiryField, $values)) {
                $document->update([
                    'expires_at' => $values[$expiryField] ?: null,
                    'expiry_reminded_at' => null,
                ]);
            }

            // Saved by the user, so the OCR suggestions are spent.
            $document->update(['ocr_status' => 'confirmed', 'ocr_suggestions' => null]);
        });
    }

    /** Sets a profile value the user picked or typed themselves. */
    public function setField(User $user, string $key, ?string $value, ?int $sourceDocumentId = null): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            $user->profileFields()->where('key', $key)->delete();

            return;
        }

        $field = $user->profileFields()->firstOrNew(['key' => $key]);
        $field->fill([
            'user_id' => $user->id,
            'value' => $value,
            'source_document_id' => $sourceDocumentId,
            'confirmed' => true,
        ])->save();
    }

    /**
     * Every profile field with its value, where it came from, what each
     * paper says, and whether those papers disagree.
     *
     * @return list<array<string, mixed>>
     */
    public function overview(User $user): array
    {
        $profile = $user->profileFields()->get()->keyBy('key');
        $fromPapers = $user->documentFields()->with('document:id,user_id,type,title')->get()->groupBy('key');

        $rows = [];

        foreach (config('formsathi.fields') as $key => $config) {
            if (! ($config['profile'] ?? false)) {
                continue;
            }

            $field = $profile->get($key);
            $candidates = $this->candidates($fromPapers->get($key, collect()));
            $conflict = ($config['compare'] ?? false) && $this->conflicts($candidates, $field?->value);

            $rows[] = [
                'key' => $key,
                'label' => ['bn' => $config['bn'], 'en' => $config['en']],
                'type' => $config['type'],
                'options' => $config['options'] ?? null,
                'sensitive' => $config['sensitive'] ?? false,
                'core' => $config['core'] ?? false,
                'value' => $field?->value,
                'sourceDocumentId' => $field?->source_document_id,
                'confirmed' => $field?->confirmed ?? false,
                'candidates' => $candidates,
                'conflict' => $conflict,
                'needsReview' => $conflict && ! ($field?->confirmed ?? false),
            ];
        }

        return $rows;
    }

    /** Share of core fields filled, 0-100. */
    public function completeness(User $user): int
    {
        $core = collect(config('formsathi.fields'))->filter(fn ($c) => $c['core'] ?? false)->keys();
        $filled = $user->profileFields()->whereIn('key', $core)->count();

        return $core->isEmpty() ? 0 : (int) floor($filled / $core->count() * 100);
    }

    /** Copies a paper's value into the profile, or flags it for review. */
    private function absorb(Document $document, string $key, string $value): void
    {
        $field = ProfileField::where('user_id', $document->user_id)->where('key', $key)->first();

        if (! $field) {
            ProfileField::create([
                'user_id' => $document->user_id,
                'key' => $key,
                'value' => $value,
                'source_document_id' => $document->id,
                'confirmed' => false,
            ]);

            return;
        }

        if (Bangla::comparable($field->value) !== Bangla::comparable($value)) {
            $field->update(['confirmed' => false]);
        }
    }

    /**
     * Groups what the papers say by comparable value.
     *
     * @return list<array{value: string, documents: list<array{id: int, type: string, title: ?string}>}>
     */
    private function candidates(Collection $fields): array
    {
        return $fields
            ->groupBy(fn ($field) => Bangla::comparable($field->value))
            ->map(fn (Collection $group) => [
                'value' => $group->first()->value,
                'documents' => $group->map(fn ($field) => [
                    'id' => $field->document->id,
                    'type' => $field->document->type,
                    'title' => $field->document->title,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function conflicts(array $candidates, ?string $value): bool
    {
        if (count($candidates) > 1) {
            return true;
        }

        if ($value === null || $candidates === []) {
            return false;
        }

        return Bangla::comparable($candidates[0]['value']) !== Bangla::comparable($value);
    }
}
