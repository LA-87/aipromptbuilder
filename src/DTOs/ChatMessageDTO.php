<?php

namespace LA87\AIPromptBuilder\DTOs;

use LA87\AIPromptBuilder\Enums\ChatRoleEnum;

class ChatMessageDTO
{
    public function __construct(
        public ChatRoleEnum $role,
        public string $content,
        public ?string $name = null,
        public ?string $tool_call_id = null,
    ) {}

    public function toArray(): array
    {
        $message = [
            'role' => $this->role->value,
            'content' => $this->content,
        ];

        if ($this->name) {
            $message['name'] = $this->name;
        }

        if ($this->tool_call_id) {
            $message['tool_call_id'] = $this->tool_call_id;
        }

        return $message;
    }
}
