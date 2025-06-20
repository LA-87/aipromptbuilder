<?php

namespace LA87\AIPromptBuilder\Services;

use Closure;
use LA87\AIPromptBuilder\DTOs\BatchRequestDTO;
use LA87\AIPromptBuilder\Services\Pipes\SetChatHistoryPipe;
use LA87\AIPromptBuilder\Traits\EvaluatesClosures;
use OpenAI;
use OpenAI\Client;
use Illuminate\Pipeline\Pipeline;
use OpenAI\Responses\Chat\CreateResponse;
use LA87\AIPromptBuilder\Enums\AIModelEnum;
use OpenAI\Exceptions\TransporterException;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;
use LA87\AIPromptBuilder\DTOs\PromptPayloadDTO;
use LA87\AIPromptBuilder\DTOs\ChatParametersDTO;
use LA87\AIPromptBuilder\Responses\ChatResponse;
use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;
use LA87\AIPromptBuilder\Services\Pipes\ResolveRolePipe;
use LA87\AIPromptBuilder\Services\Pipes\ResolveToolsPipe;
use LA87\AIPromptBuilder\Services\Pipes\ResolvePromptPipe;
use LA87\AIPromptBuilder\Services\Pipes\SetInitialParamsPipe;
use OpenAI\Responses\Embeddings\CreateResponse as OpenAICreateResponse;

class AIPromptBuilderService
{
    use EvaluatesClosures;

    protected static ?Closure $costCallback = null;

    protected Client $client;
    protected PromptConfigDTO $config;
    protected CreateResponse $response;
    private array $functionResults = [];
    protected string $endpoint = '/v1/chat/completions';

    public function chat(): self
    {
        $this->endpoint = '/v1/chat/completions'; return $this;
    }
    public function completion(): self
    {
        $this->endpoint = '/v1/completions'; return $this;
    }
    public function embeddings(): self
    {
        $this->endpoint = '/v1/embeddings'; return $this;
    }
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }


    public function __construct(
        string|null $apiKey,
        string|AIModelEnum $defaultModel,
        float $defaultTemperature,
        int $cacheTTL
    ) {
        $this->client = OpenAI::client($apiKey);

        if(is_string($defaultModel))
        {
            if(!in_array($defaultModel, AIModelEnum::toArray())) {
                throw new \Exception('Default model not found');
            }

            $defaultModel = AIModelEnum::from($defaultModel);
        }

        $this->config = new PromptConfigDTO($defaultModel, $defaultTemperature, $cacheTTL);
    }

    public function model(AIModelEnum|string $model): self
    {
        if(is_string($model))
        {
            if(!in_array($model, AIModelEnum::toArray())) {
                throw new \Exception('Model not found');
            }

            $model = AIModelEnum::from($model);
        }

        $this->config->model = $model;

        return $this;
    }

    public function getModel(): AIModelEnum
    {
        return $this->config->model;
    }

    public function prompt($prompt): self
    {
        $this->config->prompt = $prompt;
        return $this;
    }

    public function input(string $input): self
    {
        $this->config->prompt = $input; // or a separate property like $this->config->input
        return $this;
    }


    public function normalizePrompt(bool $normalize = true): self
    {
        $this->config->normalizePrompt = $normalize;
    }

    public function normalizeRole(bool $normalize = true): self
    {
        $this->config->normalizeRole = $normalize;
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

    public function parallelToolCalls(bool $parallelToolCalls = true): self
    {
        $this->config->parallelToolCalls = $parallelToolCalls;
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

    public function toolChoice(string $toolChoice): self
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
                SetChatHistoryPipe::class,
                ResolveRolePipe::class,
                ResolvePromptPipe::class,
                ResolveToolsPipe::class,
            ])
            ->then(function (PromptPayloadDTO $payload) {
                return $payload->parameters;
            });
    }

    public function send(): ChatResponse|null
    {
        $data = $this->getParameters()->toArray();

        $retryCount = 3;
        $success = false;
        $response = null;

        while (!$success && $retryCount > 0) {
            try {
//                dd(sha1(json_encode($data)));
                $response = cache()->remember(
                    sha1(json_encode($data)),
                    $this->config->cacheTTL,
                    fn() => $this->client->chat()->create($data)
                );

                $success = true;
            } catch (TransporterException $e) {
                $retryCount--;

                sleep(2);

                if ($retryCount <= 0) {
                    throw $e;
                }
            }
        }

        if(!$response) {
            return null;
        }

        return ChatResponse::new($response, $this->config->tools, $this->config->model);
    }

    public function transcribe(string $path): string
    {
        $this->model(AIModelEnum::WHISPER_1);

        $response = $this->client->audio()->transcribe([
            'model' => $this->config->model->value,
            'file' => fopen($path, 'r'),
            'response_format' => 'text',
//            'response_format' => 'verbose_json',
//            'timestamp_granularities' => ['segment', 'word']
        ]);

        return $response->text;
    }

    public function getAvailableModels(): array
    {
        return $this->client->models()->list()->data;
    }

    /**
     * @throws \Exception
     */
    public function getEstimateTokens(): int
    {
        $data = $this->getParameters()->toArray();

        return estimateOpenAITokens(json_encode($data));
    }

    public function costInUsd(): float
    {
        $tokens = $this->getEstimateTokens();

        return ($tokens / 1000) * $this->config->model->costPer1KTokens();
    }

    public function hash(): string
    {
        return sha1(json_encode($this->getParameters()));
    }

    public static function registerCostCallback(Closure $callback): void
    {
        self::$costCallback = $callback;
    }

    public function getCost(...$args): mixed
    {
        return $this->evaluate(self::$costCallback, [
            'self' => $this,
            'args' => $args,
        ], [
            self::class => $this,
        ]);
    }

    public function embed(string $text): OpenAICreateResponse
    {
        if(!$this->config->model->isEmbeddingModel()) {
            throw new \Exception('Model must be embedding model');
        }

        return $this->client->embeddings()->create([
            'model' => $this->config->model->value,
            'input' => $text,
        ]);
    }

    public function toBatchRequestDTO(string $customId): BatchRequestDTO
    {
        switch ($this->endpoint) {
            case '/v1/embeddings':
                $body = [
                    'model' => $this->config->model->value,
                    'input' => $this->config->prompt, // or input property if you add input()
                ];
                break;
            case '/v1/completions':
                $body = $this->getParameters()->toArray();
                break;
            case '/v1/chat/completions':
            default:
                $body = $this->getParameters()->toArray();
                break;
        }
        return new BatchRequestDTO(
            customId: $customId,
            method: 'POST',
            url: $this->endpoint,
            body: $body
        );
    }
}
