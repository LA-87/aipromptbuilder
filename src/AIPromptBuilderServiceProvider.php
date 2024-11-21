<?php

namespace LA87\AIPromptBuilder;

use Illuminate\Support\ServiceProvider;
use LA87\AIPromptBuilder\Services\AIPromptBuilderService;
use LA87\AIPromptBuilder\Services\StringUtilsService;

class AIPromptBuilderServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton('stringutils', function ($app) {
            return new StringUtilsService();
        });

        $class = config('ai-prompt-builder.class', AIPromptBuilderService::class);

        $this->app->bind($class, function () use ($class): AIPromptBuilderService {
            return new $class(
                config('ai-prompt-builder.api_key'),
                config('ai-prompt-builder.default_model'),
                config('ai-prompt-builder.default_temperature'),
                config('ai-prompt-builder.cache_ttl')
            );
        });
    }

    public function boot() {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('ai-prompt-builder.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'ai-prompt-builder'
        );
    }
}
