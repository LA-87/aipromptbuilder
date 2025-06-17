<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\ChatMessageDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;

class SetChatHistoryPipe
{
    public function handle(PromptPayloadDTO $payload, Closure $next)
    {
        if (empty($payload->config->history)) {
            return $next($payload);
        }

        $history = array_map(function ($message) {
            if ($message instanceof ChatMessageDTO) {
                return $message->toArray();
            }
            return $message;
        }, $payload->config->history);

        $payload->parameters->messages = array_merge($history, $payload->parameters->messages);

        return $next($payload);
    }
}
