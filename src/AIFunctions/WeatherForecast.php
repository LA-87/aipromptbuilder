<?php

namespace LA87\AIPromptBuilder\AIFunctions;

use LA87\AIPromptBuilder\AIFunctions\traits\AIFunctionTrait;
use LA87\AIPromptBuilder\Contracts\AIFunctionInterface;

class WeatherForecast implements AIFunctionInterface
{
    use AIFunctionTrait;

    /**
     * @aiFunction
     * @description Fetches weather data for a specified location and date.
     *
     * @param string $location The city or location to get the weather forecast for.
     * @param string $date The date for the forecast in YYYY-MM-DD format.
     * @return string The weather forecast.
     */
    public function getWeatherForecast(string $location, string|null $date = null): string
    {
        return "Forecast for $location on $date";
    }

    /**
     * Some other function.
     *
     * @param string $location The city or location to get the weather forecast for.
     * @param string $date The date for the forecast in YYYY-MM-DD format.
     * @return string The weather forecast.
     */
    public function someOtherFunction(string $location, string $date): string
    {
        return "Forecast for $location on $date";
    }
}
