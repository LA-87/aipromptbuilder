<?php

namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;

class ResolveToolsPipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        $payload->parameters->tools = array_values(array_map(fn($tool) => $tool->getSchema(), $payload->config->tools));

        if(is_array($payload->config->tool_choice)) {
            $payload->parameters->tool_choice = array_values(array_map(fn($tool) => $tool->getSchema(), $payload->config->tool_choice))[0];
        }

        if(is_string($payload->config->tool_choice)) {
            $payload->parameters->tool_choice = $payload->config->tool_choice;
        }

        return $next($payload);
    }
}
