<?php

namespace LA87\AIPromptBuilder\Enums;

enum AIModelEnum: string
{
    case O1 = 'o1';
    case O1_pro = 'o1-pro';
    case O1_mini = 'o1-mini';
    case O3 = 'o3';
    case O3_mini = 'o3-mini';
    case O4_mini = 'o4-mini';
    case GPT4_O = 'gpt-4o';
    case GPT4_O_mini = 'gpt-4o-mini';
    case GPT4_O_transcribe = 'gpt-4o-transcribe';
    case GPT4_O_audio = 'gpt-4o-audio';
    case GPT4_O_mini_transcribe = 'gpt-4o-mini-transcribe';
    case GPT41 = 'gpt-4.1';
    case GPT41_mini = 'gpt-4.1-mini';
    case GPT41_nano = 'gpt-4.1-nano';
    case GPT_image_1 = 'gpt-image-1';
    case WHISPER_1 = 'whisper-1';
    case TTS_1 = 'tss-1';
    case TTS_1_hd = 'tss-1-hd';
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
            self::O1_pro,
            self::O3,
            self::O3_mini,
            self::O4_mini,
            self::GPT41,
            self::GPT41_mini,
            self::GPT41_nano,
            self::GPT4_O,
            self::GPT4_O_mini,
        ]);
    }

    public function description(): string
    {
        return match ($this) {
            self::O1 => 'O1: Full-sized reasoning model from the o-series, optimized for chat and more affordable than GPT-3.5.',
            self::O1_pro => 'O1 Pro: Enhanced version of O1 with more compute for better responses.',
            self::O1_mini => 'O1 Mini: Smaller, cost-optimized version of O1. Deprecated.',
            self::O3 => 'O3: High-performance reasoning model for complex, multi-step tasks.',
            self::O3_mini => 'O3 Mini: Smaller alternative to O3 with strong reasoning abilities.',
            self::O4_mini => 'O4 Mini: Fast, affordable o-series reasoning model for complex tasks.',

            self::GPT4_O => 'GPT-4o: Flagship GPT model optimized for speed, intelligence, and flexibility across text, image, and audio.',
            self::GPT4_O_mini => 'GPT-4o Mini: Cost-effective small version of GPT-4o for fast, focused tasks.',
            self::GPT4_O_audio => 'GPT-4o Audio Preview: Preview model supporting audio input and output.',
            self::GPT4_O_transcribe => 'GPT-4o Transcribe: Speech-to-text model powered by GPT-4o.',
            self::GPT4_O_mini_transcribe => 'GPT-4o Mini Transcribe: Lightweight audio transcription model using GPT-4o mini.',

            self::GPT41 => 'GPT-4.1: High-intelligence flagship GPT model for complex reasoning and creative tasks.',
            self::GPT41_mini => 'GPT-4.1 Mini: Balanced GPT model offering a trade-off between cost and capability.',
            self::GPT41_nano => 'GPT-4.1 Nano: Fastest and most cost-effective GPT-4.1 model.',

            self::GPT_image_1 => 'GPT Image 1: State-of-the-art model for generating and editing images from natural language.',

            self::WHISPER_1 => 'Whisper: General-purpose model for transcribing and translating audio to text.',

            self::TTS_1 => 'TTS-1: Fast text-to-speech model optimized for speed.',
            self::TTS_1_hd => 'TTS-1 HD: High-quality text-to-speech model with natural-sounding audio output.',

            self::TEXT_EMBEDDING_3_large => 'Text Embedding 3 Large: Most capable embedding model for generating vector representations from text.',
            self::TEXT_EMBEDDING_3_small => 'Text Embedding 3 Small: Lightweight embedding model for efficient text vectorization.',
            self::TEXT_EMBEDDING_ADA_002 => 'Text Embedding Ada 002: Previous generation embedding model optimized for versatility.',

            default => 'No description available.',
        };
    }


    public function getLabel(): string
    {
        return match ($this) {
            self::O1 => 'O1',
            self::O1_pro => 'O1 Pro',
            self::O1_mini => 'O1 Mini',
            self::O3 => 'O3',
            self::O3_mini => 'O3 Mini',
            self::O4_mini => 'O4 Mini',

            self::GPT4_O => 'GPT-4o',
            self::GPT4_O_mini => 'GPT-4o Mini',
            self::GPT4_O_transcribe => 'GPT-4o Transcribe',
            self::GPT4_O_mini_transcribe => 'GPT-4o Mini Transcribe',
            self::GPT4_O_audio => 'GPT-4o Audio Preview',

            self::GPT41 => 'GPT-4.1',
            self::GPT41_mini => 'GPT-4.1 Mini',
            self::GPT41_nano => 'GPT-4.1 Nano',

            self::GPT_image_1 => 'GPT Image 1',

            self::WHISPER_1 => 'Whisper 1',
            self::TTS_1 => 'TTS 1',
            self::TTS_1_hd => 'TTS 1 HD',

            self::TEXT_EMBEDDING_3_large => 'Text Embedding 3 Large',
            self::TEXT_EMBEDDING_3_small => 'Text Embedding 3 Small',
            self::TEXT_EMBEDDING_ADA_002 => 'Text Embedding Ada 002',

            default => 'Unknown Model',
        };
    }


    public function maxTokens(): int
    {
        return match ($this) {
            self::O1, self::O1_pro, self::O3, self::O3_mini, self::O4_mini,  => 200_000,
            self::O1_mini, self::GPT4_O, self::GPT4_O_mini, self::GPT4_O_audio => 128_000,

            self::GPT4_O_transcribe, self::GPT4_O_mini_transcribe => 16_000,

            self::GPT41, self::GPT41_mini, self::GPT41_nano => 1_047_576,

            self::GPT_image_1 => null,

            self::WHISPER_1 => 4_000,
            self::TTS_1, self::TTS_1_hd => null,

            self::TEXT_EMBEDDING_3_large, self::TEXT_EMBEDDING_3_small, self::TEXT_EMBEDDING_ADA_002 => null,

            default => null,
        };
    }


    public function costPer1kTokens(): float
    {
        return config("ai-prompt-builder." . $this->value . ".cost-per-1k-tokens", 1);
    }

    public function isReasoningModel(): bool
    {
        return match($this) {
            self::O1, self::O1_pro, self::O1_mini,
            self::O3, self::O3_mini,
            self::O4_mini => true,

            default => false,
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
