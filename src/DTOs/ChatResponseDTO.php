<?php

namespace LA87\AIPromptBuilder\DTOs;


use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\Chat\CreateResponseFunctionCall;

class ChatResponseDTO
{
    public string|null $id;
    public string|null $object;
    public int|null $created;
    public array|null $choices;
    public string|null $completion;
    public string|null $finish_reason;
    public int|null $prompt_tokens;
    private int|null $completion_tokens;
    public CreateResponseFunctionCall|null $functionCall;

    public function __construct($id, $object, $created, $choices, $completion, $functionCall, $finishReason, $promptTokens, $completionTokens)
    {
        $this->id = $id;
        $this->object = $object;
        $this->created = $created;
        $this->choices = $choices;
        $this->completion = $completion;
        $this->functionCall = $functionCall;
        $this->finish_reason = $finishReason;
        $this->prompt_tokens = $promptTokens;
        $this->completion_tokens = $completionTokens;
    }

    public function __get(string $property)
    {
        if($property === 'completion_tokens') {
            return $this->completion_tokens === null ? estimateOpenAITokens($this->completion) : $this->completion_tokens;
        }
    }

    public static function parse(CreateResponse $response): static
    {
        return new static(
            id: $response->id,
            object: $response->object,
            created: $response->created,
            choices: $response->choices,
            completion: $response->choices[0]->message->content ?? '',
            functionCall: $response->choices[0]->message->functionCall,
            finishReason: $response->choices[0]->finishReason,
            promptTokens: $response->usage->promptTokens,
            completionTokens: $response->usage->completionTokens,
        );
    }

    public static function fromSream(CreateStreamedResponse $response):static
    {
        return new static(
            id: $response->id,
            object: $response->object,
            created: $response->created,
            choices: $response->choices,
            completion: $response->choices[0]->delta?->content ?? '',
            functionCall: $response->choices[0]->message->functionCall,
            finishReason: $response->choices[0]->finishReason,
            promptTokens: null,
            completionTokens: null,
        );
    }

    public function appendToCompletion($content)
    {
        $this->completion .= $content;
    }
}
