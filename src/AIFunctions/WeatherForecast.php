<?php

namespace LA87\AIPromptBuilder\AIFunctions;

use LA87\AIPromptBuilder\AIFunctions\Traits\AIFunctionTrait;
use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;

class WeatherForecast implements AIFunctionInterface
{
    use AIFunctionTrait;

    public function getDescription(): string
    {
        return 'Fetches weather data for a specified location and date.';
    }

    public function getParams(): array
    {
        return [
            'location' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The city or location to get the weather forecast for.',
            ],
            'date' => [
                'type' => ['string', 'null'],
                'required' => false,
                'description' => 'The date for the forecast in YYYY-MM-DD format.',
            ],
        ];
    }

    /**
     * @aiFunction
     */
    public function getWeatherForecast(string $location, string|null $date = null): string
    {
        return "Forecast for $location on $date";
    }

    public function someOtherFunction(string $location, string $date): string
    {
        return "Forecast for $location on $date";
    }
}
