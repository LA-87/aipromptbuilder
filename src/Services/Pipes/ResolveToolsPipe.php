<?php

namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;

class ResolveToolsPipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        if($payload->config->tools && count($payload->config->tools) > 0) {
            $payload->parameters->tools = array_values(array_map(fn($tool) => $tool->getSchema(), $payload->config->tools));
        }

        if(is_string($payload->config->tool_choice)) {
            if(array_key_exists($payload->config->tool_choice, $payload->config->tools)) {
                $payload->parameters->tool_choice = $payload->config->tools[$payload->config->tool_choice]->getSchemaForToolChoice();
            }
        }

        return $next($payload);
    }
}
