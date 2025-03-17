<?php

namespace LA87\AIPromptBuilder\Enums;

enum AIModelEnum: string
{
    case O1 = 'o1';
    case O1_mini = 'o1-mini';
    case GPT4_O = 'gpt-4o';
    case GPT4_O_audio_preview = 'gpt-4o-audio-preview';
    case GPT4_O_mini = 'gpt-4o-mini';
    case O3_mini = 'o3-mini';
    case WHISPER_1 = 'whisper-1';
    case TSS_1 = 'tss-1';
    case TSS_1_hd = 'tss-1-hd';
    case TEXT_EMBEDDING_3_large = 'text-embedding-3-large';
    case TEXT_EMBEDDING_3_small = 'text-embedding-3-small';
    case TEXT_EMBEDDING_ADA_002 = 'text-embedding-ada-002';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getChatModelsForSelect(): array
    {
        return array_map(fn($model) => [
            'name' => $model->value,
            'description' => $model->description(),
            'tokens' => $model->maxTokens(),
        ], [
            self::O1,
            self::O1_mini,
            self::GPT4_O,
            self::GPT4_O_mini,
            self::O3_mini,
        ]);
    }

    public static function modelsForChat(): array
    {
        $array = [];

        $cases = [
            self::O1,
            self::O1_mini,
            self::GPT4_O,
            self::GPT4_O_mini,
            self::O3_mini,
        ];

        foreach ($cases as $case) {
            $array[$case->value] = $case->getLabel();
        }

        return $array;
    }

    public function description(): string
    {
        return match ($this) {
            self::O1 => 'O1 is our first model optimized for chat. It is more capable than GPT-3.5, but still only 1/10th the cost.',
            self::O1_mini => 'O1 Mini is a smaller version of our first model, optimized for chat. It is more capable than GPT-3.5, but still only 1/10th the cost.',
            self::GPT4_O => 'More capable than any GPT-3.5 model, able to do more complex tasks, and optimized for chat. Will be updated with our latest model iteration.',
            self::GPT4_O_mini => 'More capable than any GPT-3.5 model, able to do more complex tasks, and optimized for chat. Will be updated with our latest model iteration.',
            self::O3_mini => 'O3 Mini',
            self::WHISPER_1 => 'A small model that transcribes audio to text.',
            self::GPT4_O_audio_preview => 'provides audio input and output',
            self::TSS_1 => 'A small model that transcribes audio to text.',
            self::TSS_1_hd => 'A small model that transcribes audio to text.',
            self::TEXT_EMBEDDING_3_large => 'A large model that embeds text.',
            self::TEXT_EMBEDDING_3_small => 'A small model that embeds text.',
            self::TEXT_EMBEDDING_ADA_002 => 'A large model that embeds text.',
            default => 'No description available.',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::O1 => 'O1',
            self::O1_mini => 'O1 Mini',
            self::GPT4_O => 'GPT-4o',
            self::O3_mini => 'O3 Mini',
            self::GPT4_O_mini => 'GPT-4o Mini',
            self::WHISPER_1 => 'Whisper 1',
            self::GPT4_O_audio_preview => 'GPT-4 Audio Preview',
            self::TSS_1 => 'TSS 1',
            self::TSS_1_hd => 'TSS 1 HD',
            self::TEXT_EMBEDDING_3_large => 'Text Embedding 3 Large',
            self::TEXT_EMBEDDING_3_small => 'Text Embedding 3 Small',
            self::TEXT_EMBEDDING_ADA_002 => 'Text Embedding Ada 002',
            default => 'Unknown Model',
        };
    }

    public function maxTokens(): int
    {
        return match ($this) {
            self::O1 => 200000,
            self::O3_mini => 200000,
            self::O1_mini, self::GPT4_O, self::GPT4_O_mini => 128000,
            self::TEXT_EMBEDDING_ADA_002 => 2048,
            self::TEXT_EMBEDDING_3_large, self::TEXT_EMBEDDING_3_small => 8192,
            self::WHISPER_1 => 4000,
            self::TSS_1, self::TSS_1_hd => 4096,
            default => 2048,
        };
    }

    public function costPer1kTokens(): float
    {
        return config("ai-prompt-builder." . $this->value . ".cost-per-1k-tokens", 1);
    }

    public function isReasoningModel(): bool
    {
        return match($this) {
            self::O3_mini, self::O1, self::O1_mini => true,
            default => false
        };
    }

    public function isEmbeddingModel(): bool
    {
        return match($this) {
            self::TEXT_EMBEDDING_ADA_002, self::TEXT_EMBEDDING_3_large, self::TEXT_EMBEDDING_3_small => true,
            default => false
        };
    }
}
