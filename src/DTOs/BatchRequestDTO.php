<?php

namespace LA87\AIPromptBuilder\DTOs;

class BatchRequestDTO
{
    public function __construct(
        public string $customId,
        public string $method,
        public string $url,
        public array $body
    ) {}

    public function toArray(): array
    {
        return [
            'custom_id' => $this->customId,
            'method' => $this->method,
            'url' => $this->url,
            'body' => $this->body,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

