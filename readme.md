
# AIPromptBuilder

AIPromptBuilder is a Laravel package that simplifies building and managing prompts for AI models, using OpenAI’s API. It provides a structured way to configure prompts, roles, tools, and parameters for creating and sending chat requests, as well as transcribing audio using Whisper.

## Features

- Easily configure prompts, models, temperature, and tokens.
- Supports AI function tools to enhance responses.
- Caches responses to reduce API requests.
- Transcribe audio with Whisper.

## Installation

1. **Install the package via Composer**:
   ```bash
   composer require la87/ai-prompt-builder
   ```

2. **Publish the Configuration**:
   ```bash
   php artisan vendor:publish --provider="LA87\AIPromptBuilder\AIPromptBuilderServiceProvider"
   ```

3. **Configure `.env`**:
   Set your OpenAI API key and other parameters in `.env`:
   ```env
   AI_PROMPT_BUILDER_API_KEY=your_openai_api_key
   AI_PROMPT_BUILDER_DEFAULT_MODEL=gpt-3.5-turbo
   AI_PROMPT_BUILDER_DEFAULT_TEMPERATURE=0.7
   AI_PROMPT_BUILDER_CACHE_TTL=3600
   ```

## Usage

### Basic Prompt Creation

```php
use LA87\AIPromptBuilder\Services\AIPromptBuilderService;
use LA87\AIPromptBuilder\Enums\AIModelEnum;

$aiPromptBuilder = app(AIPromptBuilderService::class);

$response = $aiPromptBuilder
    ->model(AIModelEnum::GPT_3_5_TURBO)
    ->prompt('Translate "Hello" to French.')
    ->temperature(0.5)
    ->send();

echo $response->content;
```

### Configuring the Prompt

- **Setting Role**: Define a role for the AI to follow (e.g., "assistant").
  ```php
  $aiPromptBuilder->role('translator');
  ```

- **Adjusting Temperature**: Control the creativity of the AI's response.
  ```php
  $aiPromptBuilder->temperature(0.8);
  ```

- **Token Limit**: Limit the number of tokens in the response.
  ```php
  $aiPromptBuilder->limitTokens(100);
  ```

- **Adding History**: Provide conversation history for context.
  ```php
  $aiPromptBuilder->history($conversationHistoryArray);
  ```

### Using AI Function Tools

AIPromptBuilder supports custom tools that extend response capabilities:

1. **Define a Tool**: Tools must implement `AIFunctionInterface`.
2. **Add Tools**:
   ```php
   $aiPromptBuilder->tools([$toolInstance1, $toolInstance2]);
   ```

### Transcribing Audio

You can transcribe audio files with the `transcribe` method:
```php
$text = $aiPromptBuilder->transcribe('/path/to/audio/file.wav');
echo $text;
```

## Configuration

The package's configuration file (`config/ai-prompt-builder.php`) contains settings for API key, model, temperature, and caching. Modify these as needed.

## Exception Handling

The package will retry failed API calls up to 3 times for resilience. In case of multiple failures, it throws a `TransporterException`.

## Testing

To run the package tests, add the package’s `tests` directory in your Laravel application’s `phpunit.xml`:

```xml
<testsuites>
    <testsuite name="Feature">
        <directory suffix="Test.php">./tests/Feature</directory>
    </testsuite>
</testsuites>
```

Run tests with:
```bash
php artisan test
```

## License

This package is open-source software licensed under the [MIT license](LICENSE).
```

## Contributing

We welcome contributions! Feel free to submit issues or pull requests for new features, bug fixes, or improvements.
