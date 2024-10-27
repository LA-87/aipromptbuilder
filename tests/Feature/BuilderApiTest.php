<?php

use LA87\AIPromptBuilder\AIFunctions\WeatherForecast;
use LA87\AIPromptBuilder\Enums\AIModelEnum;
use LA87\AIPromptBuilder\Services\AIPromptBuilderService;

test('can initialize builder', function () {
    $data = ai()
        ->prompt('Hello world!')
        ->role('You are an AI assistant.')
        ->model(AIModelEnum::GPT4)
        ->getParameters()
        ->toArray();

    $this->assertArrayHasKey('messages', $data);
    $this->assertArrayHasKey('temperature', $data);
    $this->assertArrayHasKey('max_completion_tokens', $data);
    $this->assertArrayHasKey('tool_choices', $data);

    $this->assertEquals('You are an AI assistant.', $data['messages'][0]['content']);
    $this->assertEquals('Hello world!', $data['messages'][1]['content']);
    $this->assertEquals(AIModelEnum::GPT4, $data['model']);
})->group('a');

test('can use meta in role', function () {
    $data = ai()
        ->role('meta:{{test}}')
        ->meta([
            'test' => 'test123'
        ])
        ->getParameters()
        ->toArray();

    $this->assertEquals('meta:test123', $data['messages'][0]['content']);
})->group('a');

test('can use meta in prompt', function () {
    $data = ai()
        ->prompt('meta:{{test}}')
        ->meta([
            'test' => 'test123'
        ])
        ->getParameters()
        ->toArray();

    $this->assertEquals('meta:test123', $data['messages'][1]['content']);
})->group('a');

test('builder with functions in prompt', function () {

    $data = ai()
        ->prompt('function {{weather}}')
        ->tools([
            'weather' => new WeatherForecast()
        ])
        ->getParameters()
        ->toArray();

    $this->assertCount(1, $data['tools']);
    $this->assertEquals('getWeatherForecast', $data['tools'][0]['name']);
    $this->assertEquals('Fetches weather data for a specified location and date.', $data['tools'][0]['description']);
    $this->assertEquals('string', $data['tools'][0]['parameters']['properties']['location']['type']);
    $this->assertEquals('The city or location to get the weather forecast for.', $data['tools'][0]['parameters']['properties']['location']['description']);
    $this->assertContains('string', $data['tools'][0]['parameters']['properties']['date']['type']);
    $this->assertContains('null', $data['tools'][0]['parameters']['properties']['date']['type']);
    $this->assertEquals('The date for the forecast in YYYY-MM-DD format.', $data['tools'][0]['parameters']['properties']['date']['description']);



})->group('b');

test('builder with toolchoise', function () {

    $data = ai()
        ->prompt('function {{weather}}')
        ->toolChoice([
            'weather' => new WeatherForecast()
        ])
        ->getParameters()
        ->toArray();

    $this->assertEquals('getWeatherForecast', $data['tool_choice']['name']);
    $this->assertEquals('Fetches weather data for a specified location and date.', $data['tool_choice']['description']);
    $this->assertEquals('string', $data['tool_choice']['parameters']['properties']['location']['type']);
    $this->assertEquals('The city or location to get the weather forecast for.', $data['tool_choice']['parameters']['properties']['location']['description']);
    $this->assertContains('string', $data['tool_choice']['parameters']['properties']['date']['type']);
    $this->assertContains('null', $data['tool_choice']['parameters']['properties']['date']['type']);
    $this->assertEquals('The date for the forecast in YYYY-MM-DD format.', $data['tool_choice']['parameters']['properties']['date']['description']);

})->group('b1');
