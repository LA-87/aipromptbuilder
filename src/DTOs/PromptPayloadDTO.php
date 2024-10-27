<?php

namespace LA87\AIPromptBuilder\DTOs;

use LA87\AIPromptBuilder\Enums\AIModelEnum;

class PromptPayloadDTO
{
    public PromptConfigDTO $config;
    public ChatParametersDTO $parameters;

    public function __construct(PromptConfigDTO $config, ChatParametersDTO $parameters) {
        $this->config = $config;
        $this->parameters = $parameters;
    }
}
