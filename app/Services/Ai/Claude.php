<?php

namespace App\Services\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\BadRequestException;
use Anthropic\Core\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Log;

/**
 * The one place the app talks to the LLM. Callers pass text that has
 * already been masked (see Masker); this class never sees raw NID numbers,
 * phone numbers or addresses.
 *
 * Every call asks for JSON matching a schema, so callers get an array back
 * or an AiUnavailable exception, never free text to parse.
 */
class Claude
{
    private ?Client $client = null;

    public function available(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    /**
     * @param  array<string, mixed>  $schema  JSON schema for the reply
     * @param  list<array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     *
     * @throws AiUnavailable
     */
    public function json(string $system, array $messages, array $schema, string $effort = 'low', int $maxTokens = 4000): array
    {
        if (! $this->available()) {
            throw new AiUnavailable('No API key configured.');
        }

        try {
            $message = $this->client()->beta->messages->create(
                model: config('services.anthropic.model'),
                maxTokens: $maxTokens,
                system: $system,
                messages: $messages,
                outputConfig: [
                    'effort' => $effort,
                    'format' => ['type' => 'json_schema', 'schema' => $schema],
                ],
                // If the model declines, the API retries on a fallback model in the same call.
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (RateLimitException|APIConnectionException $e) {
            throw new AiUnavailable('The AI service is busy or unreachable.', previous: $e);
        } catch (AuthenticationException|BadRequestException $e) {
            Log::error('Claude request rejected', ['type' => $e->type?->value, 'message' => $e->getMessage()]);

            throw new AiUnavailable('The AI request was rejected.', previous: $e);
        } catch (APIStatusException $e) {
            throw new AiUnavailable('The AI service returned an error.', previous: $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new AiUnavailable('The AI declined this request.');
        }

        if ($message->stopReason === 'max_tokens') {
            throw new AiUnavailable('The AI reply was cut off.');
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $data = json_decode($block->text, true);

                if (is_array($data)) {
                    return $data;
                }
            }
        }

        throw new AiUnavailable('The AI reply was not valid JSON.');
    }

    private function client(): Client
    {
        return $this->client ??= new Client(apiKey: config('services.anthropic.key'));
    }
}
