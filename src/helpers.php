<?php

use LA87\AIPromptBuilder\Services\AiBatchService;
use LA87\AIPromptBuilder\Services\AIPromptBuilderService;
use LA87\AIPromptBuilder\Services\StringUtilsService;

if (!function_exists('strUtils')) {
    function strUtils(): StringUtilsService {
        return app(StringUtilsService::class);
    }
}


if (!function_exists('ai')) {
    function ai(): AIPromptBuilderService {
        return app(AIPromptBuilderService::class);
    }
}

if (!function_exists('aiBatch')) {
    function aiBatch(): AiBatchService {
        return app(AiBatchService::class);
    }
}

if (!function_exists('fillPlaceholders')) {
    function fillPlaceholders(string $str, array $placeholders): string {
        $wrappedPlaceholders = array_map(fn($key) => "{{{$key}}}", array_keys($placeholders));

        $values = array_values($placeholders);

        return str_replace($wrappedPlaceholders, $values, $str);
    }
}

if (!function_exists('estimateOpenAITokens')) {
    function estimateOpenAITokens($text)
    {
        $python_script = __DIR__.'/tokenizer.py';

        $command = "python3 " . escapeshellarg($python_script) . " " . escapeshellarg($text);

        exec($command, $output, $return_code);

        if ($return_code == 0) {
            return intval($output[0]);
        } else {
            throw new Exception('Cannot estimate token count');
        }
    }
}

if (! function_exists('normalizeWhitespace')) {
    function normalizeWhitespace($string) {
        // Replace multiple spaces/tabs with a single space
        $string = preg_replace('/[ \t]+/', ' ', $string);

        // Replace multiple newlines with a single newline
        return preg_replace('/[\r\n]{3,}/', "\n\n", $string);
    }
}

if (! function_exists('normalizeNewLines')) {
    function normalizeNewLines($string) {
        return str_replace(PHP_EOL, ' ' , $string);
    }
}
