<?php

namespace LA87\AIPromptBuilder\Enums;

enum ReasoningEffortEnum: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }



    public function getLabel(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            default => 'Unknown Model',
        };
    }
}
