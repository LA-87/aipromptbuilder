<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;
use LA87\AIPromptBuilder\Enums\ChatRoleEnum;

class ResolveRolePipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        $roleContent = fillPlaceholders($payload->config->role, $payload->config->meta);
        $roleContent = $payload->config->normalizeRoleWhitespace ? normalizeWhitespace($roleContent) : $roleContent;
        $roleContent = $payload->config->normalizeRoleNewLines ? normalizeNewLines($roleContent) : $roleContent;

        $roleContent = fillPlaceholders($roleContent, [
            'br' => PHP_EOL
        ]);

        if (!empty(trim($roleContent))) {
            array_unshift($payload->parameters->messages, [
                'role' => ChatRoleEnum::System->value,
                'content' => $roleContent
            ]);
        }

        return $next($payload);
    }
}
