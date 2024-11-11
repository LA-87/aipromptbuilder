<?php

namespace LA87\AIPromptBuilder\DTOs;

use LA87\AIPromptBuilder\Enums\AIModelEnum;

class PromptConfigDTO
{
    public AIModelEnum $model = AIModelEnum::GPT4_O;
    public string $prompt = '';
    public string $role = '';
    public bool $normalizePrompt = true;
    public bool $normalizeRole = true;
    public array $tools = [];
    public array|string|null $tool_choice = null;
    public array $meta = [];
    public array $history = [];
    public float $temperature;
    public int|null $maxTokens = null;
    public int|null $cacheTTL = null;

    public function __construct(AIModelEnum $model, float $temperature, int|null $cacheTTL)
    {
        $this->model = $model;
        $this->temperature = $temperature;
        $this->cacheTTL = $cacheTTL;
    }

    public function toArray()
    {
        return [
            'model' => $this->model->value,
            'prompt' => $this->prompt,
            'role' => $this->role,
            'tools' => $this->tools,
            'tool_choice' => $this->tool_choice,
            'meta' => $this->meta,
            'history' => $this->history,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'cache_ttl' => $this->cacheTTL,
        ];
    }
}
