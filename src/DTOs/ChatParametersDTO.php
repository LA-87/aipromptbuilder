<?php

namespace LA87\AIPromptBuilder\DTOs;

use LA87\AIPromptBuilder\Enums\AIModelEnum;

class ChatParametersDTO
{
    public AIModelEnum $model = AIModelEnum::GPT4_O;
    public array $messages = [];
    public float $temperature;
    public int|null $max_completion_tokens = null;
    public array|string|null $tool_choice = null;
    public array|null $tools = null;

    public function toArray()
    {
        return [
            'model' => $this->model,
            'messages' => $this->messages,
            'temperature' => $this->temperature,
            'max_completion_tokens' => $this->max_completion_tokens,
            'tool_choice' => $this->tool_choice,
            'tools' => $this->tools
        ];
    }
}
