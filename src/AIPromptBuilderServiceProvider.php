<?php

namespace LA87\AIPromptBuilder;

use Illuminate\Support\ServiceProvider;
use LA87\AIPromptBuilder\Services\StringUtilsService;

class AIPromptBuilderServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton('stringutils', function ($app) {
            return new StringUtilsService();
        });

//        $this->app->singleton(OpenAI\Client::class, function (): OpenAI\Client {
//            return OpenAI::factory()
//                ->withApiKey(env('OPENAI_API_KEY') ?? '')
////                ->withOrganization('your-organization') // default: null
////                ->withBaseUri('openai.example.com/v1') // default: api.openai.com/v1
////                ->withHttpClient($client = new \GuzzleHttp\Client([])) // default: HTTP client found using PSR-18 HTTP Client Discovery
////                ->withHttpHeader('X-My-Header', 'foo')
////                ->withQueryParam('my-param', 'bar')
//                ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, [
//                    'stream' => true // Allows to provide a custom stream handler for the http client.
//                ]))
//                ->make();
////            return OpenAI::client(env('OPENAI_API_KEY') ?? '');
//        });
    }

    public function boot() {
        // Load routes, views, etc.
    }
}
