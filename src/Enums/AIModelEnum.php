<?php

namespace LA87\AIPromptBuilder\Enums;

enum AIModelEnum: string
{
    case GPT4 = 'gpt-4';
    case GPT4_TURBO = 'gpt-4-turbo';
    case GPT4_O = 'gpt-4o';
    case GPT4_0314 = 'gpt-4-0314';
    case GPT4_0613 = 'gpt-4-0613';
    case GPT4_32k = 'gpt-4-32k';
    case GPT4_0314_32k = 'gpt-4-32k-0314';
    case GPT35_TURBO = 'gpt-3.5-turbo';
    case GPT35_TURBO_0613 = 'gpt-3.5-turbo-0613';
    case GPT35_TURBO_0301 = 'gpt-3.5-turbo-0301';
    case TEXT_EMBEDDING_ADA_002 = 'text-embedding-ada-002';
    case TEXT_CURIE_001 = 'text-curie-001';
    case TEXT_BABBAGE_001 = 'text-babbage-001';
    case TEXT_ADA_001 = 'text-ada-001';

    public static function toArray(): array
    {
        return array_map(
            fn(self $enum) => $enum->value,
            self::cases()
        );
    }

    public static function getChatModelsForSelect(): array
    {
        return [
            ['name' => self::GPT4,'description' => self::GPT4->description(),'tokens' => self::GPT4->maxTokens()],
            ['name' => self::GPT4_0314,'description' => self::GPT4_0314->description(),'tokens' => self::GPT4_0314->maxTokens()],
            ['name' => self::GPT35_TURBO,'description' => self::GPT35_TURBO->description(),'tokens' => self::GPT35_TURBO->maxTokens()],
            ['name' => self::GPT35_TURBO_0301,'description' => self::GPT35_TURBO_0301->description(),'tokens' => self::GPT35_TURBO_0301->maxTokens()],
        ];
    }

    public function description(): string
    {
        return match($this) {
            self::GPT4 => 'More capable than any GPT-3.5 model, able to do more complex tasks, and optimized for chat. Will be updated with our latest model iteration.',
            self::GPT4_0314 => 'Snapshot of gpt-4 from March 14th 2023. Unlike gpt-4, this model will not receive updates, and will only be supported for a three month period ending on June 14th 2023.',
            self::GPT4_32k => 32768,
            self::GPT4_0314_32k => 32768,
            self::GPT35_TURBO => 'Most capable GPT-3.5 model and optimized for chat at 1/10th the cost of text-davinci-003. Will be updated with our latest model iteration.',
            self::GPT35_TURBO_0301 => 'Snapshot of gpt-3.5-turbo from March 1st 2023. Unlike gpt-3.5-turbo, this model will not receive updates, and will only be supported for a three month period ending on June 1st 2023.',
            self::TEXT_EMBEDDING_ADA_002 => 2048,
            self::TEXT_CURIE_001 => 2048,
            self::TEXT_BABBAGE_001 => 2048,
            self::TEXT_ADA_001 => 2048,
        };
    }

    public function maxTokens(): int
    {
        return match($this) {
            self::GPT4 => $this->maxTokensReal(), //round($this->maxTokensReal() * 0.5),
            self::GPT4_O => 128000,
            self::GPT4_TURBO => 4097,
            self::GPT4_0314 => $this->maxTokensReal(), round($this->maxTokensReal() * 0.5),
            self::GPT4_32k => 32768,
            self::GPT4_0314_32k => 32768,
            self::GPT35_TURBO => 4097,
            self::GPT35_TURBO_0301 => 4097,
            self::GPT35_TURBO_0613 => 4097,
            self::TEXT_EMBEDDING_ADA_002 => 2048,
            self::TEXT_CURIE_001 => 2048,
            self::TEXT_BABBAGE_001 => 2048,
            self::TEXT_ADA_001 => 2048,
        };
    }

    public function maxTokensReal(): int
    {
        return match($this) {
            self::GPT4 => 8192,
            self::GPT4_O => 128000,
            self::GPT4_TURBO => 4097,
            self::GPT4_0314 => 8192,
            self::GPT4_32k => 32768,
            self::GPT4_0314_32k => 32768,
            self::GPT35_TURBO => 4097,
            self::GPT35_TURBO_0301 => 4097,
            self::GPT35_TURBO_0613 => 4097,
            self::TEXT_EMBEDDING_ADA_002 => 2048,
            self::TEXT_CURIE_001 => 2048,
            self::TEXT_BABBAGE_001 => 2048,
            self::TEXT_ADA_001 => 2048,
        };
    }
}
