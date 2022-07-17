<?php

namespace Tests\Unit;

use App\Jobs\LatestWeatherJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LatestWeatherJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_latest_weather_conditions()
    {

        Http::fake([
            config('services.openweather.api_url') => Http::response([
                'weather' => ['id' => 888, 'description' => 'cloudy']
            ])
        ]);

        $job = new LatestWeatherJob('Skopje');

        $job->handle();

        $this->assertDatabaseHas('weather_summaries', [
            'city' => 'Skopje',
        ]);
    }

    public function test_it_stores_city_into_tagged_weather_cache()
    {
        Http::fake([
            config('services.openweather.api_url') => Http::response([
                'weather' => ['id' => 888, 'description' => 'cloudy']
            ])
        ]);

        $job = new LatestWeatherJob('Skopje');

        $job->handle();

        $this->assertNotNull(Cache::tags('weather')->get('Skopje'));
    }

    public function test_it_stores_release_lock_on_attempts_exceded()
    {
    }
}
