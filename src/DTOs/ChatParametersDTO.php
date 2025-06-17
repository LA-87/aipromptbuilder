<?php

namespace LA87\AIPromptBuilder\DTOs;

use LA87\AIPromptBuilder\Enums\AIModelEnum;
use LA87\AIPromptBuilder\Enums\ReasoningEffortEnum;

class ChatParametersDTO
{
    public AIModelEnum $model = AIModelEnum::GPT4_O_mini;
    public array $messages = [];
    public float $temperature;
    public ReasoningEffortEnum $reasoning_effort = ReasoningEffortEnum::Medium;
    public int|null $max_completion_tokens = null;
    public array|null $tool_choice = null;
    public array|null $tools = null;

    public function toArray()
    {
        $params = [
            'model' => $this->model,
            'messages' => $this->messages,
            'max_completion_tokens' => $this->max_completion_tokens,
        ];

        if($this->model->isReasoningModel()) {
            $params['reasoning_effort'] = $this->reasoning_effort;
        } else {
            $params['temperature'] = $this->temperature;
        }

        if($this->tool_choice) {
            $params['tool_choice'] = $this->tool_choice;
        }

        if($this->tools) {
            $params['tools'] = $this->tools;
        }

        return $params;
    }

    public function dd(): void
    {
        dd($this->toArray());
    }
}
