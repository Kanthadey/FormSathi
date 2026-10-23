<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Services\Ai\AiUnavailable;
use App\Services\Ai\Claude;
use App\Services\Ai\Masker;

/**
 * The AI assistant (F7): understands what the user wants in Bangla,
 * English or Banglish and sends them to the right place. It only routes;
 * it never fills a form value (PRD key design decision).
 *
 * Common requests are matched by keyword first, so most never reach the
 * AI. When unsure, it asks one short question back.
 */
class Assistant
{
    /** Destinations the assistant can send a user to. Templates are added at runtime. */
    private const ROUTES = [
        'job_kit' => ['bn' => 'চাকরির আবেদন কিট', 'en' => 'Job application kit'],
        'upload_paper' => ['bn' => 'কাগজ যোগ করা', 'en' => 'Add a paper'],
        'profile' => ['bn' => 'প্রোফাইল দেখা/ঠিক করা', 'en' => 'View or fix the profile'],
        'plans' => ['bn' => 'প্ল্যান ও পেমেন্ট', 'en' => 'Plans and payment'],
        'help' => ['bn' => 'সাহায্য / অভিযোগ', 'en' => 'Help and support'],
    ];

    /** Lowercased fragments; Bangla, English and common Banglish spellings. */
    private const KEYWORDS = [
        'e-passport' => ['পাসপোর্ট', 'পাসপোট', 'passport', 'pasport', 'paspot', 'e-passport', 'ই-পাসপোর্ট'],
        'job_kit' => ['চাকরি', 'চাকুরি', 'সিভি', 'বায়োডাটা', 'কভার লেটার', 'job', 'chakri', 'chakuri', 'cv', 'resume', 'biodata', 'cover letter'],
        'upload_paper' => ['আপলোড', 'ছবি তুল', 'স্ক্যান', 'upload', 'scan'],
        'plans' => ['প্রিমিয়াম', 'পেমেন্ট', 'বিকাশ', 'premium', 'payment', 'bkash', 'price', 'দাম'],
        'help' => ['অভিযোগ', 'সমস্যা', 'হেল্প', 'complain', 'problem', 'help'],
    ];

    private const SYSTEM = <<<'PROMPT'
        You are the assistant inside FormSathi, a Bangla-first website where people in Bangladesh keep their papers and get the details for government and job forms laid out. You only decide where to send the user; you never fill in form values, give legal advice, or say you will submit anything for them.

        Read the conversation and pick the destination that matches what the user wants. Users write in Bangla, English or Banglish (Bangla in Latin letters), often briefly. Numbers and emails have been replaced with [NUMBER] and [EMAIL].

        If the request clearly matches one destination, set "destination" to it, "confidence" to "high", and write a one-sentence "reply" in Bangla telling them what will open. If it could be more than one, or none fits, set "destination" to "unclear" and put one short, friendly question in Bangla in "reply" that would settle it (for example which form they need). If they ask for a form that is not available, say so briefly in Bangla and set "destination" to "unsupported".
        PROMPT;

    public function __construct(private readonly Claude $claude) {}

    /**
     * @param  list<array{role: string, text: string}>  $history  earlier turns this session
     * @return array{destination: string, reply: ?string, source: string}
     */
    public function understand(string $message, array $history = []): array
    {
        $destinations = $this->destinations();

        if ($match = $this->keywordMatch($message, $destinations)) {
            return ['destination' => $match, 'reply' => null, 'source' => 'keyword'];
        }

        try {
            $reply = $this->claude->json(
                self::SYSTEM."\n\nDestinations:\n".collect($destinations)->map(fn ($d, $key) => "- {$key}: {$d['en']}")->implode("\n"),
                $this->conversation($history, $message),
                [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['destination', 'confidence', 'reply'],
                    'properties' => [
                        'destination' => ['type' => 'string', 'enum' => [...array_keys($destinations), 'unclear', 'unsupported']],
                        'confidence' => ['type' => 'string', 'enum' => ['high', 'low']],
                        'reply' => ['type' => 'string'],
                    ],
                ],
                effort: 'low',
                maxTokens: 1000,
            );
        } catch (AiUnavailable) {
            return ['destination' => 'unclear', 'reply' => null, 'source' => 'fallback'];
        }

        $destination = $reply['confidence'] === 'high' ? $reply['destination'] : 'unclear';

        return ['destination' => $destination, 'reply' => $reply['reply'], 'source' => 'ai'];
    }

    /** @return array<string, array{bn: string, en: string}> */
    public function destinations(): array
    {
        $templates = FormTemplate::current()->get(['slug', 'name_bn', 'name_en'])
            ->mapWithKeys(fn ($t) => [$t->slug => ['bn' => $t->name_bn, 'en' => $t->name_en]])
            ->all();

        return [...$templates, ...self::ROUTES];
    }

    private function keywordMatch(string $message, array $destinations): ?string
    {
        $text = mb_strtolower($message);
        $hits = [];

        foreach (self::KEYWORDS as $destination => $words) {
            if (! isset($destinations[$destination])) {
                continue;
            }

            foreach ($words as $word) {
                // Latin keywords must be whole words ("cv" inside "civil" is not a match).
                $found = preg_match('/^[a-z\s-]+$/', $word)
                    ? preg_match('/\b'.preg_quote($word, '/').'\b/u', $text)
                    : str_contains($text, $word);

                if ($found) {
                    $hits[$destination] = true;
                    break;
                }
            }
        }

        // Two different matches ("passport for a job abroad") go to the AI to decide.
        return count($hits) === 1 ? array_key_first($hits) : null;
    }

    private function conversation(array $history, string $message): array
    {
        $messages = [];

        foreach (array_slice($history, -6) as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => Masker::mask($turn['text'])];
        }

        $messages[] = ['role' => 'user', 'content' => Masker::mask($message)];

        // The API needs the first turn to be the user's.
        while ($messages[0]['role'] !== 'user') {
            array_shift($messages);
        }

        return $messages;
    }
}
