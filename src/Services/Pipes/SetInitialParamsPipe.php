<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;

class SetInitialParamsPipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        $payload->parameters->model = $payload->config->model;
        $payload->parameters->temperature = $payload->config->temperature;

        return $next($payload);
    }
}
