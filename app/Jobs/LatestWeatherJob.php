<?php

namespace App\Jobs;

use App\Models\WeatherSummary;
use App\Services\OpenWeather\OpenWeather;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class LatestWeatherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $city)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($timestamp = Cache::tags('weather')->get($this->city)) {
            $this->release((int) $timestamp - time());
        }

        try {

            $openWeather = new OpenWeather();

            $response = $openWeather->makeRequest([
                'q' => $this->city,
                'units' => 'metric'
            ]);

            // This would serve as a way to release the job in the queue once again if we hit the limit but unfortunately 
            // the API does not return any headers or response that would help us determine when we could release it back
            if ($response->failed() && $response->status() === 429) {
                $secondsRemaining = $response->header('X-RateLimit-Remaining');

                if (is_null($secondsRemaining)) {
                    $secondsRemaining = 600;
                }

                Cache::put(
                    key: 'openweather-limit',
                    value: now()->addSeconds($secondsRemaining)->timestamp,
                    ttl: $secondsRemaining
                );

                // $this->release($secondsRemaining);
            }

            $created_at = now();

            $ws = WeatherSummary::create([
                'city' => $this->city,
                'data' => $response->body(),
                'created_at' => $created_at
            ]);

            Cache::tags('weather')->put(
                key: $ws->city,
                value: $created_at->addHour(1)->timestamp,
                ttl: 3600
            );
        } catch (ConnectException $ex) {
            // Log the timeout, release it back into the queue and incr how many times it has been re-released, we dont want to do this forever
            $this->release(10);
        }
    }

    public function middleware()
    {
        return [(new WithoutOverlapping($this->city))->releaseAfter(60 * 60)];
    }
}
