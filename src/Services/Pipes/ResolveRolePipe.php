<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;

class ResolveRolePipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        $role = fillPlaceholders($payload->config->role, $payload->config->meta);

        $payload->parameters->messages[] = ['role' => 'system', 'content' => $role];

        return $next($payload);
    }
}
