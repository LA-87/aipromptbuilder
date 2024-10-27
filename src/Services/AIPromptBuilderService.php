<?php

namespace LA87\AIPromptBuilder\Services;

use Illuminate\Pipeline\Pipeline;
use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;
use LA87\AIPromptBuilder\DTOs\ChatParametersDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;
use LA87\AIPromptBuilder\Enums\AIModelEnum;
use LA87\AIPromptBuilder\Services\Pipes\BuildPromptPipe;
use LA87\AIPromptBuilder\Services\Pipes\ResolveMetaPipe;
use LA87\AIPromptBuilder\Services\Pipes\ResolvePromptPipe;
use LA87\AIPromptBuilder\Services\Pipes\ResolveRolePipe;
use LA87\AIPromptBuilder\Services\Pipes\ResolveToolsPipe;
use LA87\AIPromptBuilder\Services\Pipes\SetInitialParamsPipe;
use OpenAI;
use OpenAI\Client;

class AIPromptBuilderService
{
    protected Client $client;
    protected PromptConfigDTO $config;
    private array $functionResults = [];

    public function __construct(
        string|null $apiKey,
        string $defaultModel,
        float $defaultTemperature,
        int $cacheTTL
    ) {
        $this->client = OpenAI::client($apiKey);

        if(!in_array($defaultModel, AIModelEnum::toArray())) {
            throw new \Exception('Default model not found');
        }

        $defaultModel = AIModelEnum::from($defaultModel);

        $this->config = new PromptConfigDTO($defaultModel, $defaultTemperature, $cacheTTL);
    }

    public function model(AIModelEnum $model): self
    {
        $this->config->model = $model;
        return $this;
    }

    public function prompt($prompt): self
    {
        $this->config->prompt = $prompt;
        return $this;
    }

    public function role(string $role): self
    {
        $this->config->role = $role;
        return $this;
    }

    public function temperature(float $temperature): self
    {
        $this->config->temperature = $temperature;
        return $this;
    }

    public function limitTokens(int $maxTokens): self
    {
        $this->config->maxTokens = $maxTokens;
        return $this;
    }

    public function meta(array $meta): self
    {
        $this->config->meta = $meta;
        return $this;
    }

    public function history(array $history): self
    {
        $this->config->history = $history;
        return $this;
    }

    /**
     * @param AIFunctionInterface[] $tools
     * @return $this
     */
    public function tools(array $tools): self
    {
        // check if implements interface
        foreach ($tools as $tool) {
            if (! $tool instanceof AIFunctionInterface) {
                throw new \Exception('Tool must implement AIFunctionInterface');
            }
        }

        $this->config->tools = array_merge($this->config->tools, $tools);
        return $this;
    }

    public function tool(array $tool): self
    {
        $this->config->tools[] = $tool;
        return $this;
    }

    public function toolChoice(array|string $toolChoice): self
    {
        $this->config->tool_choice = $toolChoice;
        return $this;
    }

    public function getParameters(): ChatParametersDTO
    {
        return app(Pipeline::class)
            ->send(
                new PromptPayloadDTO(
                    $this->config,
                    new ChatParametersDTO()
                )
            )
            ->through([
                SetInitialParamsPipe::class,
                ResolveRolePipe::class,
                ResolvePromptPipe::class,
                ResolveToolsPipe::class,
            ])
            ->then(function (PromptPayloadDTO $payload) {
                return $payload->parameters;
            });
    }
}
