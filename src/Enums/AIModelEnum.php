<?php

namespace LA87\AIPromptBuilder\Enums;

enum AIModelEnum: string
{
    case O1_preview = 'o1-preview';
    case O1_mini = 'o1-mini';
    case GPT4 = 'gpt-4';
    case GPT4_TURBO = 'gpt-4-turbo';
    case GPT4_O = 'gpt-4o';
    case GPT4_O_mini = 'gpt-4o-mini';
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
    case WHISPER_1 = 'whisper-1';

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

    public function getLabel(): string
    {
        return match($this) {
            self::O1_preview => 'O1 Preview',
            self::O1_mini => 'O1 Mini',
            self::GPT4_O => 'GPT-4o',
            self::GPT4_O_mini => 'GPT-4o mini',
        };
    }

    public function maxTokens(): int
    {
        return match($this) {
            self::O1_preview => $this->maxTokensReal(),
            self::O1_mini => $this->maxTokensReal(),
            self::GPT4_0613 => 8192,
            self::GPT4 => $this->maxTokensReal(), //round($this->maxTokensReal() * 0.5),
            self::GPT4_O => $this->maxTokensReal(),
            self::GPT4_O_mini => $this->maxTokensReal(),
            self::GPT4_TURBO => $this->maxTokensReal(),
            self::GPT4_0314 => $this->maxTokensReal(), round($this->maxTokensReal() * 0.5),
            self::GPT4_32k => 32768,
            self::GPT4_0314_32k => 32768,
            self::GPT35_TURBO => $this->maxTokensReal(),
            self::GPT35_TURBO_0301 => $this->maxTokensReal(),
            self::GPT35_TURBO_0613 => $this->maxTokensReal(),
            self::TEXT_EMBEDDING_ADA_002 => 2048,
            self::TEXT_CURIE_001 => 2048,
            self::TEXT_BABBAGE_001 => 2048,
            self::TEXT_ADA_001 => 2048,
        };
    }

    public function maxTokensReal(): int
    {
        return match($this) {
            self::O1_preview => 128000,
            self::O1_mini => 128000,
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

    public function costPer1kTokens(): float
    {
        return match ($this) {
            self::O1_preview => config("ai-prompt-builder." . self::O1_preview->value . ".cost-per-1k-tokens", 1),
            self::O1_mini => config("ai-prompt-builder." . self::O1_mini->value . ".cost-per-1k-tokens", 1),
            self::GPT4 => config("ai-prompt-builder." . self::GPT4->value . ".cost-per-1k-tokens", 1),
            self::GPT4_TURBO => config("ai-prompt-builder." . self::GPT4_TURBO->value . ".cost-per-1k-tokens", 1),
            self::GPT4_O => config("ai-prompt-builder." . self::GPT4_O->value . ".cost-per-1k-tokens", 1),
            self::GPT4_O_mini => config("ai-prompt-builder." . self::GPT4_O_mini->value . ".cost-per-1k-tokens", 1),
            self::GPT4_0314 => config("ai-prompt-builder." . self::GPT4_0314->value . ".cost-per-1k-tokens", 1),
            self::GPT4_0613 => config("ai-prompt-builder." . self::GPT4_0613->value . ".cost-per-1k-tokens", 1),
            self::GPT4_32k => config("ai-prompt-builder." . self::GPT4_32k->value . ".cost-per-1k-tokens", 1),
            self::GPT4_0314_32k => config("ai-prompt-builder." . self::GPT4_0314_32k->value . ".cost-per-1k-tokens", 1),
            self::GPT35_TURBO => config("ai-prompt-builder." . self::GPT35_TURBO->value . ".cost-per-1k-tokens", 1),
            self::GPT35_TURBO_0301 => config("ai-prompt-builder." . self::GPT35_TURBO_0301->value . ".cost-per-1k-tokens", 1),
            self::GPT35_TURBO_0613 => config("ai-prompt-builder." . self::GPT35_TURBO_0613->value . ".cost-per-1k-tokens", 1),
            self::TEXT_EMBEDDING_ADA_002 => config("ai-prompt-builder." . self::TEXT_EMBEDDING_ADA_002->value . ".cost-per-1k-tokens", 1),
            self::TEXT_CURIE_001 => config("ai-prompt-builder." . self::TEXT_CURIE_001->value . ".cost-per-1k-tokens", 1),
            self::TEXT_BABBAGE_001 => config("ai-prompt-builder." . self::TEXT_BABBAGE_001->value . ".cost-per-1k-tokens", 1),
            self::TEXT_ADA_001 => config("ai-prompt-builder." . self::TEXT_ADA_001->value . ".cost-per-1k-tokens", 1),
            self::WHISPER_1 => config("ai-prompt-builder." . self::WHISPER_1->value . ".cost-per-1k-tokens", 1),
            default => 1,
        };
    }

}
