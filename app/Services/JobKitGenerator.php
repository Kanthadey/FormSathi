<?php

namespace App\Services;

use App\Models\User;
use App\Services\Ai\AiUnavailable;
use App\Services\Ai\Claude;
use App\Services\Ai\Masker;
use Illuminate\Support\Collection;

/**
 * Job application kit (F10): a CV, cover letter and answers tailored to one
 * advert, built only from the user's own career record.
 *
 * The AI sees career facts by id (E1, W1...) and never the user's name,
 * phone, email, NID or address. It may reword, order and select, but every
 * job, degree and skill in the output must map back to an item the user
 * entered: anything else is dropped here before the user sees it. Contact
 * details are filled in locally from placeholders.
 */
class JobKitGenerator
{
    private const SYSTEM = <<<'PROMPT'
        You help job seekers in Bangladesh apply for one specific job. You receive a job advert and the applicant's career record, where each item has an id (E = education, W = work, T = training). Write in clear, plain English suited to Bangladeshi employers.

        Use only facts in the career record. Never add an employer, job title, date, degree, result, skill or achievement that is not there, and never inflate numbers. If the advert asks for something the record does not show, do not cover it up: list it in "gaps" in simple Bangla so the applicant can decide what to do.

        - summary: 2-3 sentences for the top of the CV, aimed at this job.
        - skills: pick the applicant's skills most relevant to the advert, most relevant first, copied exactly as written in the record.
        - experience: for each relevant W item, 2-4 short achievement-style bullets rewritten from its description. Use the item's id. Leave out items with nothing relevant.
        - education_order: the E ids, most relevant first.
        - cover_letter: a letter of 180-260 words. Write {{NAME}} where the applicant's name goes, and {{PHONE}} and {{EMAIL}} in the sign-off. Do not invent a hiring manager's name.
        - answers: any questions the advert asks applicants to answer (expected salary aside), each answered truthfully from the record. Empty if there are none.
        - job_title and company: as written in the advert; empty string if not stated.
        PROMPT;

    private const SCHEMA = [
        'type' => 'object',
        'additionalProperties' => false,
        'required' => ['job_title', 'company', 'summary', 'skills', 'experience', 'education_order', 'cover_letter', 'answers', 'gaps'],
        'properties' => [
            'job_title' => ['type' => 'string'],
            'company' => ['type' => 'string'],
            'summary' => ['type' => 'string'],
            'skills' => ['type' => 'array', 'items' => ['type' => 'string']],
            'experience' => ['type' => 'array', 'items' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['id', 'bullets'],
                'properties' => [
                    'id' => ['type' => 'string'],
                    'bullets' => ['type' => 'array', 'items' => ['type' => 'string']],
                ],
            ]],
            'education_order' => ['type' => 'array', 'items' => ['type' => 'string']],
            'cover_letter' => ['type' => 'string'],
            'answers' => ['type' => 'array', 'items' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['question', 'answer'],
                'properties' => [
                    'question' => ['type' => 'string'],
                    'answer' => ['type' => 'string'],
                ],
            ]],
            'gaps' => ['type' => 'array', 'items' => ['type' => 'string']],
        ],
    ];

    public function __construct(private readonly Claude $claude) {}

    public function available(): bool
    {
        return $this->claude->available();
    }

    /**
     * @return array<string, mixed> the kit, ready to store and render
     *
     * @throws AiUnavailable
     */
    public function generate(User $user, string $advert): array
    {
        $items = $user->careerItems()->get();
        $ids = $this->ids($items);

        $reply = $this->claude->json(
            self::SYSTEM,
            [[
                'role' => 'user',
                'content' => "<job_advert>\n".Masker::mask($advert)."\n</job_advert>\n\n<career_record>\n"
                    .Masker::mask($this->record($user, $items, $ids))."\n</career_record>",
            ]],
            self::SCHEMA,
            effort: 'medium',
            maxTokens: 8000,
        );

        return $this->ground($reply, $items, $ids);
    }

    /** Fills placeholders with the user's real details, which never left the server. */
    public static function personalise(string $text, array $contact): string
    {
        return strtr($text, [
            '{{NAME}}' => $contact['name'] ?? '',
            '{{PHONE}}' => $contact['phone'] ?? '',
            '{{EMAIL}}' => $contact['email'] ?? '',
        ]);
    }

    /** @return array<int, string> career item id => prompt id (E1, W1, T1) */
    private function ids(Collection $items): array
    {
        $prefixes = ['education' => 'E', 'work' => 'W', 'training' => 'T'];
        $counters = [];
        $ids = [];

        foreach ($items as $item) {
            if (isset($prefixes[$item->kind])) {
                $counters[$item->kind] = ($counters[$item->kind] ?? 0) + 1;
                $ids[$item->id] = $prefixes[$item->kind].$counters[$item->kind];
            }
        }

        return $ids;
    }

    private function record(User $user, Collection $items, array $ids): string
    {
        $lines = [];
        $profession = $user->profileFields()->where('key', 'profession')->first()?->value;

        if ($profession) {
            $lines[] = "Current profession: {$profession}";
        }

        foreach ($items as $item) {
            $data = $item->fields();
            $line = match ($item->kind) {
                'education' => sprintf('%s: %s, %s%s%s', $ids[$item->id], $data['degree'], $data['institution'],
                    isset($data['subject']) ? ", {$data['subject']}" : '', isset($data['year']) ? " ({$data['year']}".(isset($data['result']) ? ", {$data['result']}" : '').')' : ''),
                'work' => sprintf('%s: %s at %s%s, %s to %s. %s', $ids[$item->id], $data['title'], $data['employer'],
                    isset($data['location']) ? " ({$data['location']})" : '', $data['start'], $data['end'] ?? 'present', $data['description'] ?? ''),
                'training' => sprintf('%s: %s%s%s', $ids[$item->id], $data['name'],
                    isset($data['provider']) ? ", {$data['provider']}" : '', isset($data['year']) ? " ({$data['year']})" : ''),
                'skill' => "Skill: {$data['name']}",
                'language' => "Language: {$data['name']}".(isset($data['level']) ? " ({$data['level']})" : ''),
            };
            $lines[] = trim($line);
        }

        return $lines === [] ? 'No career record entered.' : implode("\n", $lines);
    }

    /** Drops anything in the reply that does not map back to the user's record. */
    private function ground(array $reply, Collection $items, array $ids): array
    {
        $byPromptId = array_flip($ids);
        $skills = $items->where('kind', 'skill')->map(fn ($i) => $i->fields()['name'])->values();
        $known = $skills->mapWithKeys(fn ($s) => [mb_strtolower($s) => $s]);

        $experience = collect($reply['experience'])
            ->filter(fn ($e) => str_starts_with($e['id'], 'W') && isset($byPromptId[$e['id']]))
            ->map(fn ($e) => ['itemId' => $byPromptId[$e['id']], 'bullets' => array_values(array_filter(array_map('trim', $e['bullets'])))])
            ->values()->all();

        $educationOrder = collect($reply['education_order'])
            ->filter(fn ($id) => str_starts_with($id, 'E') && isset($byPromptId[$id]))
            ->map(fn ($id) => $byPromptId[$id])
            ->unique()->values()->all();

        return [
            'jobTitle' => trim($reply['job_title']),
            'company' => trim($reply['company']),
            'summary' => trim($reply['summary']),
            'skills' => collect($reply['skills'])->map(fn ($s) => $known[mb_strtolower(trim($s))] ?? null)->filter()->unique()->values()->all(),
            'experience' => $experience,
            'educationOrder' => $educationOrder,
            'coverLetter' => trim($reply['cover_letter']),
            'answers' => array_values(array_filter($reply['answers'], fn ($a) => trim($a['question']) !== '')),
            'gaps' => array_values(array_filter(array_map('trim', $reply['gaps']))),
        ];
    }
}
