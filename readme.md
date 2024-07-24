# AI Prompt Builder for Laravel

## Introduction

The AI Prompt Builder package provides a streamlined way to interact with OpenAI's models within a Laravel application. It facilitates the construction of prompts, management of function calls, and handling of responses in a structured and configurable manner.

## Installation

First, install the package via Composer:

```bash
composer require la87/ai-prompt-builder
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --provider="LA87\AIPromptBuilder\AIPromptBuilderServiceProvider"
```

Finally, configure the `ai-prompt-builder.php` file located in your `config` directory with your OpenAI API key and other settings as needed.

## Configuration

The `ai-prompt-builder.php` configuration file includes settings such as:

```php
return [
    'api_key' => env('OPENAI_API_KEY', ''),
    'cache_ttl' => env('AI_PROMPT_CACHE_TTL', 600),
];
```

Ensure you set your OpenAI API key in your `.env` file:

```
OPENAI_API_KEY=your-api-key
AI_PROMPT_CACHE_TTL=600
```

## Usage

### Basic Usage

To use the AI Prompt Builder service, you need to create an instance of the `AIPromptBuilderService` and configure it with your desired settings:

```php
use LA87\AIPromptBuilder\Services\AIPromptBuilderService;
use LA87\AIPromptBuilder\Enums\AIModelEnum;
use OpenAI\Client;

$client = app(Client::class);

$service = new AIPromptBuilderService($client);

$response = $service->setModel(AIModelEnum::GPT4)
                    ->setPrompt('What is the capital of France?')
                    ->setRole('You are a knowledgeable assistant.')
                    ->ask();

echo $response->completion;
```

### Using Function Calls

You can define and use functions that the AI can call during the interaction. Implement the `AIFunctionInterface` for any custom functions:

```php
use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;
use stdClass;

class GetWeatherFunction implements AIFunctionInterface
{
    public function getName(): string
    {
        return 'getWeather';
    }

    public function getDescription(): string
    {
        return 'Fetches the current weather for a given location.';
    }

    public function getParametersSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The location to get the weather for.'
                ]
            ],
            'required' => ['location']
        ];
    }

    public function getMustBeCalled(): bool
    {
        return true;
    }

    public function execute(stdClass|null $arguments = null)
    {
        // Custom logic to fetch the weather
        return 'Sunny, 25°C';
    }
}
```

Then, register and use the function in your prompt:

```php
$service->setFunctionCalls([new GetWeatherFunction()])
        ->askAndExecute();

$result = $service->getFunctionResult('getWeather');
echo $result;
```

### Listing Available Models

You can list all available OpenAI models using the `listModels` method:

```php
$models = $service->listModels();
print_r($models);
```

### Handling Function Results

To handle the results of function calls, use the `askAndExecute` method followed by `getFunctionResult`:

```php
try {
    $service->askAndExecute();
    $result = $service->getFunctionResult('getWeather');
    echo $result;
} catch (MissingFunctionCallException $e) {
    echo "Function call missing!";
} catch (MissingFunctionResultException $e) {
    echo "Function result missing!";
}
```

## Exception Handling

The package includes custom exceptions for handling various error states:

- `MissingFunctionCallException`: Thrown when a function call is expected but not returned.
- `MissingFunctionResultException`: Thrown when a function result is expected but not found.

## Contributing

Contributions are welcome! Please submit a pull request or create an issue to report bugs or suggest new features.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
