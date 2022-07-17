<?php

namespace App\Console\Commands;

use App\Models\WeatherSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchCityWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:fetch {city}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches latest weather summary for a city';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $city = $this->argument('city');

        $ws = Cache::remember($city, 3600, function () use ($city) {
            return WeatherSummary::where('city', $city)->latest()->first();
        });

        if ($ws) {
            $summary = json_decode($ws->data, true);
            $temp = $summary['main']['temp'];
            $description = $summary['weather'][0]['description'];

            return $this->line("The current temp for ${city} is ${temp} and the weather is ${description}");
        }

        return $this->line("No weather data is available for ${city} :(");
    }
}
