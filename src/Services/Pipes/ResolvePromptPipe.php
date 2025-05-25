<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;

class ResolvePromptPipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        $prompt = fillPlaceholders($payload->config->prompt, $payload->config->meta);
        $prompt = fillPlaceholders($prompt, $payload->config->getToolsPlaceholderReplacements());

        $prompt = $payload->config->normalizePromptWhitespace ? normalizeWhitespace($prompt) : $prompt;
        $prompt = $payload->config->normalizePromptNewLines ? normalizeNewLines($prompt) : $prompt;

        $prompt = fillPlaceholders($prompt, [
            'br' => PHP_EOL
        ]);

        $payload->parameters->messages[] = ['role' => 'user', 'content' => $prompt];

        return $next($payload);
    }
}
