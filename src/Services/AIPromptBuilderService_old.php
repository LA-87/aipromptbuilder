<?php

namespace LA87\AIPromptBuilder\Services;

use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;
use LA87\AIPromptBuilder\DTOs\ChatResponseDTO;
use LA87\AIPromptBuilder\Enums\AIModelEnum;
use LA87\AIPromptBuilder\Exceptions\MissingFunctionCallException;
use LA87\AIPromptBuilder\Exceptions\MissingFunctionResultException;
use OpenAI;
use OpenAI\Client;
use OpenAI\Exceptions\TransporterException;

class AIPromptBuilderService_old
{
    protected AIModelEnum $model = AIModelEnum::GPT4_O;
    protected Client $client;
    protected string $prompt = '';
    protected string $role = '';
    protected array $functions = [];
    protected array $meta = [];
    protected array $history = [];
    protected float $temperature = 0.8;
    protected int|null $maxTokens = null;
    protected int|null $cacheTTL = null;

    private array $functionResults = [];

    public function __construct(string|null $apiKey)
    {
        $this->client = OpenAI::client($apiKey);
        $this->cacheTTL = config('ai-prompt-builder.cache_ttl');
    }

    public function model(AIModelEnum $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function prompt($prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    public function role(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function temperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function limitTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function meta(array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    public function history(array $history): self
    {
        $this->history = $history;
        return $this;
    }

    /**
     * @param AIFunctionInterface[] $functions
     * @return $this
     */
    public function functions(array $functions): self
    {
        // check if implements interface
        foreach ($functions as $function) {
            if (! $function instanceof AIFunctionInterface) {
                throw new \Exception('Function must implement AIFunctionInterface');
            }
        }

        $this->functions = array_merge($this->functions, $functions);
        return $this;
    }

    public function function(AIFunctionInterface $function): self
    {
        $this->functions[] = new $function;
        return $this;
    }

    public function buildPrompt(): string
    {
        $prompt = $this->resolveMeta($this->prompt, $this->meta);

        $prompt = $this->resolveFunctions($prompt, $this->functions);

        return normalizeWhitespace($prompt);
    }

    public function buildRole(): array|string|null
    {
        $role = $this->role;

        $role = $this->resolveMeta($role, $this->meta);

        foreach ($this->functions as $key => $function) {
            $role = str_replace(':function'.$key+1, $function->getName(), $role);
        }

        return normalizeWhitespace($role);
    }

    public function getMessages(): array
    {
        return [
            ['role' => 'system', 'content' => $this->buildRole()],
            ...$this->history,
            ['role' => 'user', 'content' => $this->buildPrompt()],
        ];
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

    public function getData(): array
    {
        $data = [
            'model' => $this->model,
            'messages' => $this->getMessages(),
            'functions' => $this->getFunctions(),
            'function_call' => $this->getFunctionCall(),
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ];

        if(count($this->functions)) {
            $data['tools'] = $this->getFunctions();
        }

        return $data;
    }

    private function resolveMeta(string $str, array $meta): string
    {
//        foreach ($meta as $key => $value) {
//            $str = str_replace(':' . $key, $value, $str);
//        }
//
//        return $str;
        $placeholders = array_map(fn($key) => "{{{$key}}}", array_keys($meta));
        $values = array_values($meta);

        return str_replace($placeholders, $values, $str);
    }

    private function resolveFunctions(string $str, array $functions): string
    {
//        foreach ($functions as $key => $function) {
//            $str = str_replace(':function'.$key+1, $function->getName(), $str);
//        }
//
//        return $str;
        $placeholders = array_map(fn($key) => "{{{$key}}}", array_keys($functions));
        $values = array_values($functions);

        return str_replace($placeholders, $values, $str);
    }
}
