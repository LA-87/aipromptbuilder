<?php

namespace LA87\AIPromptBuilder\Responses;

use LA87\AIPromptBuilder\Enums\AIModelEnum;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class ChatResponse
{
    /**
     * @var CreateResponseToolCall[]|null
     */
    public string|null $content;
    protected array|null $toolsCalls;
    protected array|null $toolsCallResults = null;
    protected array|null $toolsByFunction = null;
    protected array|null $functionAliases = null;
    protected string|null $finishReason = null;
    public int|null $prompt_tokens;
    public int|null $completion_tokens;
    public AIModelEnum $model;

    public function __construct(
        array|null $toolsCalls,
        array|null $tools,
        string|null $text,
        int|null $prompt_tokens,
        int|null $completion_tokens,
        AIModelEnum $model,
    ) {
        $this->toolsCalls = $toolsCalls;
        $this->prompt_tokens = $prompt_tokens;
        $this->completion_tokens = $completion_tokens;
        $this->model = $model;

        $this->toolsByFunction = array_reduce($tools, function ($result, $tool) {
            $functionName = $tool->getName();
            $result[$functionName] = $tool;
            return $result;
        }, []);

        $this->functionAliases = array_reduce(array_keys($tools), function ($result, $ref) use ($tools) {
            $result[$ref] = $tools[$ref]->getName();
            return $result;
        });

        $this->content = $text;
    }

    public static function new(CreateResponse $response, array $tools): self
    {
        if (
            $response->choices[0]->finishReason === 'tool_calls' ||
            ($response->choices[0]->finishReason === 'stop' && count($response->choices[0]->message->toolCalls) > 0)
        ) {
            return new self(
                $response->choices[0]->message->toolCalls,
                $tools,
                null,
                $response->usage->promptTokens,
                $response->usage->completionTokens,
                AIModelEnum::from($response->model)
            );
        }

        if($response->choices[0]->finishReason === 'stop') {
            return new self(
                null,
                $tools,
                $response->choices[0]->message->content,
                $response->usage->promptTokens,
                $response->usage->completionTokens,
                AIModelEnum::from($response->model)
            );
        }
    }

    public function hasToolCalls(): bool
    {
        return filled($this->toolsCalls);
    }

    public function executeToolCalls(): self
    {
        foreach ($this->toolsCalls as $toolsCall) {
            if($toolsCall->type === 'function') {
                $methodName = $toolsCall->function->name;
                $arguments = json_decode($toolsCall->function->arguments, true);

                if (isset($this->toolsByFunction[$methodName])) {
                    $tool = $this->toolsByFunction[$methodName];

                    $reflectionMethod = new \ReflectionMethod($tool, $methodName);
                    $parameters = [];

                    foreach ($reflectionMethod->getParameters() as $param) {
                        $paramName = $param->getName();
                        $parameters[] = $arguments[$paramName] ?? null;
                    }

                    $result = $reflectionMethod->invokeArgs($tool, $parameters);

                    $this->toolsCallResults[$methodName] = $result;
                }
            }
        }

        return $this;
    }

    public function getToolCallResults(): array
    {
        return $this->toolsCallResults;
    }

    public function getToolCallResult(string $methodName): array|null
    {
        $availableMethods = array_keys($this->toolsByFunction);

        if(in_array($methodName, $this->functionAliases)) {
            $methodName = $this->functionAliases[$methodName];
        }

        if(in_array($methodName, $availableMethods)) {
            return $this->toolsCallResults[$methodName];
        }

        return null;
    }

    public function dd()
    {
        dd([
            'content' => $this->content,
            'toolsCalls' => $this->toolsCalls,
            'toolsCallResults' => $this->toolsCallResults,
            'toolsByFunction' => $this->toolsByFunction,
            'functionAliases' => $this->functionAliases
        ]);
    }
}
