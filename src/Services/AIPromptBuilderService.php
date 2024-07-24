<?php

namespace LA87\AIPromptBuilder\Services;

use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;
use LA87\AIPromptBuilder\DTOs\ChatResponseDTO;
use LA87\AIPromptBuilder\Enums\AIModelEnum;
use LA87\AIPromptBuilder\Exceptions\MissingFunctionCallException;
use LA87\AIPromptBuilder\Exceptions\MissingFunctionResultException;
use OpenAI\Client;
use OpenAI\Exceptions\TransporterException;

class AIPromptBuilderService
{
    protected AIModelEnum $model = AIModelEnum::GPT4_O;
    protected string $prompt = '';
    protected string $role = '';
    protected array $functions = [];
    protected array $meta = [];
    protected array $history = [];
    protected float $temperature = 0.8;
    protected int|null $maxTokens = null;
    protected int|null $cacheTTL = null;

    private array $functionResults = [];

    public function __construct(
        protected Client $client,
    )
    {
        $this->cacheTTL = config('ai-prompt-builder.cache_ttl');
    }

    public function buildPrompt(): string
    {
        $prompt = $this->prompt;

        foreach ($this->meta as $key => $value) {
            $prompt = str_replace(':' . $key, $value, $prompt);
        }

        foreach ($this->functions as $key => $function) {
            $prompt = str_replace(':function'.$key+1, $function->getName(), $prompt);
        }

        return normalizeWhitespace($prompt);
    }

    public function buildRole(): array|string|null
    {
        $role = $this->role;

        foreach ($this->meta as $key => $value) {
            $role = str_replace(':' . $key, $value, $role);
        }

        foreach ($this->functions as $key => $function) {
            $role = str_replace(':function'.$key+1, $function->getName(), $role);
        }

        return normalizeWhitespace($role);
    }

    public function setModel(AIModelEnum $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setPrompt($prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    public function setFunctionCalls(array $functions): self
    {
        $this->functions = array_merge($this->functions, $functions);
        return $this;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    public function getMessages(): array
    {
        return [
            ['role' => 'system', 'content' => $this->buildRole()],
            ...$this->history,
            ['role' => 'user', 'content' => $this->buildPrompt()],
        ];
    }

    public function setHistory(array $history): self
    {
        $this->history = $history;
        return $this;
    }

    public function setAvailableFunction(AIFunctionInterface $function): self
    {
        $this->functions[] = new $function;
        return $this;
    }

    public function getFunctions()
    {
        $functions = [];

        foreach ($this->functions as $function) {
            $functions[] = [
                'type' => 'function',
                'function' => [
                    'name' => $function->getName(),
                    'description' => $function->getDescription(),
                    'parameters' => $function->getParametersSchema(),
                ]
            ];
        }

        dd($functions);

        return $functions;
    }

    public function getFunctionsParam(): array
    {
        $functions = $this->getFunctions();

        return count($functions) > 0 ? ['functions' => $functions] : [];
    }

    public function getFunctionCallParam(): array
    {
        $functions = $this->getFunctionCall();

        return count($functions) > 0 ? ['function_call' => $functions] : [];
    }

    public function getFunctionCall()
    {
        $functions = [];

        foreach ($this->functions as $function) {
            $functions[] = [
                'name' => $function->getName(),
            ];
        }

        return $functions[0];
    }

    public function getParamsForChat(): array
    {
        return [
            'model' => $this->model,
            'messages' => $this->getMessages(),
            ...$this->getFunctionsParam(),
            ...$this->getFunctionCallParam(),
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ];
    }

    public function ask(): ChatResponseDTO
    {
        $data = $this->getData();

        $this->validateState();

        $retryCount = 3;
        $success = false;

        while (!$success && $retryCount > 0) {
            try {
                $response = $this->client->chat()->create($data);
                $success = true;
            } catch (TransporterException $e) {
                $retryCount--;

                 sleep(2);

                if ($retryCount <= 0) {
                    throw $e;
                }
            }
        }

        return ChatResponseDTO::parse($response);

    }

    /**
     * @throws MissingFunctionCallException
     */
    public function askAndExecute(): self
    {
        $response = $this->ask();

        if(!$response->functionCall) {
            throw new MissingFunctionCallException;
        }

        $this->functionResults = collect($this->functions)
            ->filter(fn(AIFunctionInterface $function) => $function->getName() === $response->functionCall->name)
            ->mapWithKeys(fn(AIFunctionInterface $function) => [
                $function->getName() => $function->execute(json_decode($response->functionCall->arguments))
            ])
            ->toArray();

        return $this;
    }

    /**
     * @throws MissingFunctionResultException
     */
    public function getFunctionResult(string $functionName)
    {
        $functionResults = collect($this->functionResults);

        if(!$functionResults->has($functionName)) {
            throw new MissingFunctionResultException;
        }

        return $functionResults->get($functionName);
    }

    public function listModels(): array
    {
        $response = $this->client->models()->list();

        return collect($response->data)->pluck('id')->toArray();
    }

    /**
     * @throws \Exception
     */
    private function validateState(): void
    {
        if(!$this->cacheTTL) {
            throw new \Exception('Cache TTL missing');
        }
    }

    public function dd(): self
    {
        dd($this->getData());
    }

    private function getData(): array
    {
        $data = [
            'model' => $this->model,
            'messages' => $this->getMessages(),
//            'functions' => $this->getFunctions(),
//            'function_call' => $this->getFunctionCall(),
//            'temperature' => $this->temperature,
//            'max_tokens' => $this->maxTokens,
        ];

        if(count($this->functions)) {
            $data['tools'] = $this->getFunctions();
        }
    }

}
