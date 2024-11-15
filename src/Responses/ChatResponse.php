<?php

namespace LA87\AIPromptBuilder\Responses;

use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class ChatResponse
{
    /**
     * @var CreateResponseToolCall[]|null
     */
    public string|null $content;
    protected array|null $toolsCalls;
    protected array|null $toolsCallResults;
    protected array|null $toolsByFunction;
    protected array|null $functionAliases;
    protected string|null $finishReason;

    public function __construct(array|null $toolsCalls, array|null $tools, string|null $text)
    {
        $this->toolsCalls = $toolsCalls;

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
        if ($response->choices[0]->finishReason === 'tool_calls') {
            return new self(
                $response->choices[0]->message->toolCalls,
                $tools,
                null
            );
        }

        if($response->choices[0]->finishReason === 'stop') {
            return new self(
                null,
                $tools,
                $response->choices[0]->message->content
            );
        }
    }

    public function hasToolCalls(): bool
    {
        return filled($this->toolsCalls);
    }

    public function executToolCalls(): self
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
}
