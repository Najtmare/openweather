<?php

namespace Tests\Unit;

use App\Services\OpenWeather\OpenWeather;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use TypeError;

class OpenWeatherTest extends TestCase
{

    public function test_throws_an_exception_when_params_are_empty()
    {
        OpenWeather::latest([]);

        $this->expectException(Exception::class);
    }

    public function test_can_retrieve_latest_weather_with_a_city_name()
    {
        Http::fake([
            config('services.openweather.api_url') => Http::response([], 200)
        ]);

        $openWeather = new OpenWeather;

        $openWeather->makeRequest(['q' => 'Skopje']);

        Http::assertSent(function (Request $request) {
            return $request['q'] == 'Skopje';
        });
    }

    public function test_can_retrieve_latest_weather_with_geo_coords()
    {
        Http::fake([
            config('services.openweather.api_url') => Http::response([], 200)
        ]);

        $openWeather = new OpenWeather;

        $openWeather->makeRequest(['lat' => '35', 'lon' => '139']);

        Http::assertSent(function (Request $request) {
            return $request['lat'] === '35' && $request['lon'] === '139';
        });
    }
}
