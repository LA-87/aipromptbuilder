<?php

namespace LA87\AIPromptBuilder;

use Illuminate\Support\ServiceProvider;
use LA87\AIPromptBuilder\Services\AiBatchService;
use LA87\AIPromptBuilder\Services\AIPromptBuilderService;
use LA87\AIPromptBuilder\Services\StringUtilsService;

class AIPromptBuilderServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton('stringutils', function ($app) {
            return new StringUtilsService();
        });

        $this->app->bind(AIPromptBuilderService::class, function (): AIPromptBuilderService {
            return new AIPromptBuilderService(
                config('ai-prompt-builder.api_key'),
                config('ai-prompt-builder.default_model'),
                config('ai-prompt-builder.default_temperature'),
                config('ai-prompt-builder.cache_ttl')
            );
        });

        $this->app->bind(AiBatchService::class, function (): AiBatchService {
            return new AiBatchService(
                config('ai-prompt-builder.api_key'),
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
