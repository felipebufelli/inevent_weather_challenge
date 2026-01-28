<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OpenWeatherService
{
    private Client $client;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = $_ENV['OPENWEATHER_API_KEY'];

        $this->client = new Client([
            'base_uri' => 'https://api.openweathermap.org/',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Get current weather with full details
     */
    public function getWeatherByCity(string $city): array
    {
        try {
            $response = $this->client->get('data/2.5/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'pt_br'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'city' => $data['name'],
                'country' => $data['sys']['country'],
                'coord' => [
                    'lat' => $data['coord']['lat'],
                    'lon' => $data['coord']['lon']
                ],
                'temperature' => round($data['main']['temp']),
                'feels_like' => round($data['main']['feels_like']),
                'temp_min' => round($data['main']['temp_min']),
                'temp_max' => round($data['main']['temp_max']),
                'humidity' => $data['main']['humidity'],
                'pressure' => $data['main']['pressure'],
                'visibility' => isset($data['visibility']) ? $data['visibility'] / 1000 : null, // km
                'wind' => [
                    'speed' => round($data['wind']['speed'] * 3.6), // m/s to km/h
                    'deg' => $data['wind']['deg'] ?? 0,
                    'direction' => $this->getWindDirection($data['wind']['deg'] ?? 0)
                ],
                'clouds' => $data['clouds']['all'],
                'weather' => [
                    'main' => $data['weather'][0]['main'],
                    'description' => $data['weather'][0]['description'],
                    'icon' => $data['weather'][0]['icon']
                ],
                'sunrise' => $data['sys']['sunrise'],
                'sunset' => $data['sys']['sunset'],
                'timezone' => $data['timezone'],
                'dt' => $data['dt']
            ];
        } catch (RequestException $e) {
            return [
                'error' => true,
                'message' => $this->parseError($e)
            ];
        }
    }

    /**
     * Get 5 day / 3 hour forecast
     */
    public function getForecast(string $city): array
    {
        try {
            $response = $this->client->get('data/2.5/forecast', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'pt_br'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $hourly = [];
            $daily = [];
            $currentDate = '';

            foreach ($data['list'] as $item) {
                $date = date('Y-m-d', $item['dt']);
                
                // Hourly data (next 24 hours - 8 items)
                if (count($hourly) < 8) {
                    $hourly[] = [
                        'dt' => $item['dt'],
                        'time' => date('H:i', $item['dt']),
                        'temperature' => round($item['main']['temp']),
                        'feels_like' => round($item['main']['feels_like']),
                        'humidity' => $item['main']['humidity'],
                        'weather' => [
                            'main' => $item['weather'][0]['main'],
                            'description' => $item['weather'][0]['description'],
                            'icon' => $item['weather'][0]['icon']
                        ],
                        'wind' => [
                            'speed' => round($item['wind']['speed'] * 3.6),
                            'direction' => $this->getWindDirection($item['wind']['deg'] ?? 0)
                        ],
                        'pop' => round(($item['pop'] ?? 0) * 100), // Probability of precipitation
                        'rain' => $item['rain']['3h'] ?? 0,
                        'clouds' => $item['clouds']['all']
                    ];
                }

                // Daily aggregation
                if ($date !== $currentDate) {
                    $currentDate = $date;
                    $daily[] = [
                        'dt' => $item['dt'],
                        'date' => $date,
                        'day_name' => $this->getDayName($item['dt']),
                        'temp_min' => round($item['main']['temp_min']),
                        'temp_max' => round($item['main']['temp_max']),
                        'humidity' => $item['main']['humidity'],
                        'weather' => [
                            'main' => $item['weather'][0]['main'],
                            'description' => $item['weather'][0]['description'],
                            'icon' => $item['weather'][0]['icon']
                        ],
                        'pop' => round(($item['pop'] ?? 0) * 100),
                        'wind_speed' => round($item['wind']['speed'] * 3.6)
                    ];
                }
            }

            return [
                'city' => $data['city']['name'],
                'country' => $data['city']['country'],
                'coord' => $data['city']['coord'],
                'timezone' => $data['city']['timezone'],
                'hourly' => $hourly,
                'daily' => array_slice($daily, 0, 5)
            ];
        } catch (RequestException $e) {
            return [
                'error' => true,
                'message' => $this->parseError($e)
            ];
        }
    }

    /**
     * Get air quality data
     */
    public function getAirQuality(float $lat, float $lon): array
    {
        try {
            $response = $this->client->get('data/2.5/air_pollution', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $item = $data['list'][0];

            $aqiLabels = [
                1 => ['label' => 'Bom', 'color' => 'good'],
                2 => ['label' => 'Razoável', 'color' => 'fair'],
                3 => ['label' => 'Moderado', 'color' => 'moderate'],
                4 => ['label' => 'Ruim', 'color' => 'poor'],
                5 => ['label' => 'Muito Ruim', 'color' => 'very_poor']
            ];

            $aqi = $item['main']['aqi'];

            return [
                'aqi' => $aqi,
                'label' => $aqiLabels[$aqi]['label'],
                'color' => $aqiLabels[$aqi]['color'],
                'components' => [
                    'co' => round($item['components']['co'], 1),
                    'no' => round($item['components']['no'], 1),
                    'no2' => round($item['components']['no2'], 1),
                    'o3' => round($item['components']['o3'], 1),
                    'so2' => round($item['components']['so2'], 1),
                    'pm2_5' => round($item['components']['pm2_5'], 1),
                    'pm10' => round($item['components']['pm10'], 1),
                    'nh3' => round($item['components']['nh3'], 1)
                ],
                'dt' => $item['dt']
            ];
        } catch (RequestException $e) {
            return [
                'error' => true,
                'message' => $this->parseError($e)
            ];
        }
    }

    /**
     * Get wind direction from degrees
     */
    private function getWindDirection(int $degrees): string
    {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSO', 'SO', 'OSO', 'O', 'ONO', 'NO', 'NNO'];
        $index = round($degrees / 22.5) % 16;
        return $directions[$index];
    }

    /**
     * Get day name in Portuguese
     */
    private function getDayName(int $timestamp): string
    {
        $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        return $days[date('w', $timestamp)];
    }

    /**
     * Parse error from API response
     */
    private function parseError(RequestException $e): string
    {
        if ($e->getResponse()) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $body['message'] ?? 'Erro ao consultar API';
        }
        return $e->getMessage();
    }
}
