<?php

namespace LA87\AIPromptBuilder\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use LA87\AIPromptBuilder\AIPromptBuilderServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $loadEnvironmentVariables = true;
    protected function getPackageProviders($app)
    {
        return [
            AIPromptBuilderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);

        $app['config']->set('ai-prompt-builder.api_key', env('OPENAI_API_KEY', 'sk-123'));
        $app['config']->set('ai-prompt-builder.cache_ttl', 60 * 60);
        $app['config']->set('ai-prompt-builder.default_temperature', 0.8);
        $app['config']->set('ai-prompt-builder.default_model', 'gpt-3.5-turbo');
    }
}
