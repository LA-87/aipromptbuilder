<?php

use App\Custom\AIPromptBuilderService;

return [
    'api_key' => env('OPENAI_API_KEY'),
    'cache_ttl' => 60*60,
    'default_model' => \LA87\AIPromptBuilder\Enums\AIModelEnum::GPT4_O_mini,
    'default_temperature' => 0.8,
    'o1-preview' => [
        'cost-per-1k-tokens' => 0.015,
    ],
    'o1-mini' => [
        'cost-per-1k-tokens' => 0.003,
    ],
    'gpt-4' => [
        'cost-per-1k-tokens' => 0.03,
    ],
    'gpt-4-turbo' => [
        'cost-per-1k-tokens' => 0.01,
    ],
    'gpt-4o' => [
        'cost-per-1k-tokens' => 0.0025,
    ],
    'gpt-4o-mini' => [
        'cost-per-1k-tokens' => 0.00015,
    ],
    'gpt-4-0314' => [
        'cost-per-1k-tokens' => 0.03,
    ],
    'gpt-4-0613' => [
        'cost-per-1k-tokens' => 0.03,
    ],
    'gpt-4-32k' => [
        'cost-per-1k-tokens' => 0.06,
    ],
    'gpt-4-32k-0314' => [
        'cost-per-1k-tokens' => 0.06,
    ],
    'gpt-3.5-turbo' => [
        'cost-per-1k-tokens' => 0.0015,
    ],
    'gpt-3.5-turbo-0301' => [
        'cost-per-1k-tokens' => 0.0015,
    ],
    'gpt-3.5-turbo-0613' => [
        'cost-per-1k-tokens' => 0.0015,
    ],
    'text-embedding-ada-002' => [
        'cost-per-1k-tokens' => 0.0001,
    ],
    'text-curie-001' => [
        'cost-per-1k-tokens' => 0.002,
    ],
    'text-babbage-001' => [
        'cost-per-1k-tokens' => 0.0004,
    ],
    'text-ada-001' => [
        'cost-per-1k-tokens' => 0.0004,
    ],
    'whisper-1' => [
        'cost-per-1k-tokens' => 1,
    ],
];
