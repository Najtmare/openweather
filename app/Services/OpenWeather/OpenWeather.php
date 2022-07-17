<?php

namespace App\Services\OpenWeather;

use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class OpenWeather
{
    protected string $key;
    protected string $apiUrl;

    public function __construct()
    {
        $this->key = config('services.openweather.api_key');
        $this->apiUrl = config('services.openweather.api_url');
    }

    public static function latest(array $params)
    {
        $openWeather = new self;

        return $openWeather->makeRequest($params);
    }

    public function makeRequest(array $params): Response
    {
        $sanitizedQuery = $this->sanitizeQuery($params);

        if (empty($sanitizedQuery)) {
            throw new Exception('OpenWeather API requires at least one parameter');
        }

        $query = Arr::add($sanitizedQuery, 'appid', $this->key);

        return Http::retry(1, 1000)
            ->timeout(3)
            ->acceptJson()
            ->get($this->apiUrl, $query);
    }

    protected function sanitizeQuery(array $query): array
    {
        return Arr::only($query, ['lat', 'lon', 'q', 'id', 'zip', 'units']);
    }
}
