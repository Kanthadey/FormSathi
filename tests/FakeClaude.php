<?php

namespace Tests;

use App\Services\Ai\AiUnavailable;
use App\Services\Ai\Claude;

/** Stands in for the AI in tests: returns a canned reply and records what was sent. */
class FakeClaude extends Claude
{
    /** @var list<array{system: string, messages: array}> */
    public array $calls = [];

    public function __construct(private readonly ?array $reply = null, private readonly bool $up = true) {}

    public function available(): bool
    {
        return $this->up;
    }

    public function json(string $system, array $messages, array $schema, string $effort = 'low', int $maxTokens = 4000): array
    {
        $this->calls[] = compact('system', 'messages');

        if (! $this->up || $this->reply === null) {
            throw new AiUnavailable('Fake outage.');
        }

        return $this->reply;
    }

    /** Everything that would have left the server, as one string. */
    public function sentText(): string
    {
        return collect($this->calls)->map(fn ($c) => $c['system'].json_encode($c['messages'], JSON_UNESCAPED_UNICODE))->implode("\n");
    }
}
