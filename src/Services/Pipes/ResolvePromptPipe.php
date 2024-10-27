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

        $payload->parameters->messages[] = ['role' => 'user', 'content' => $prompt];

        return $next($payload);
    }
}
