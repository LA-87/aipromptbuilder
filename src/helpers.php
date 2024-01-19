<?php

use LA87\AIPromptBuilder\Services\StringUtilsService;

if (!function_exists('strUtils')) {
    function strUtils(): StringUtilsService {
        return app(StringUtilsService::class);
    }
}
