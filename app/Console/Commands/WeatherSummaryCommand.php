<?php

namespace App\Console\Commands;

use App\Jobs\LatestWeatherJob;
use Illuminate\Console\Command;

class WeatherSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches a weather summary for every city';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $list = file_get_contents(base_path('current.city.list.json'));

        $cities = json_decode($list, true);

        $bar = $this->output->createProgressBar(count($cities));

        $bar->start();

        foreach ($cities as $city) {
            LatestWeatherJob::dispatch($city['name']);

            $bar->advance();
        }

        $bar->finish();
    }
}
