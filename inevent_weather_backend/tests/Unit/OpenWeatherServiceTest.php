<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\OpenWeatherService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

final class OpenWeatherServiceTest extends TestCase
{
    public function testGetWeatherByCityReturnsFormattedData(): void
    {
        $json = json_encode([
            'name' => 'São Paulo',
            'sys' => ['country' => 'BR', 'sunrise' => 1, 'sunset' => 2],
            'coord' => ['lat' => -23.5, 'lon' => -46.6],
            'main' => [
                'temp' => 25.5,
                'feels_like' => 26,
                'temp_min' => 22,
                'temp_max' => 28,
                'humidity' => 65,
                'pressure' => 1015,
            ],
            'visibility' => 10000,
            'wind' => ['speed' => 3.5, 'deg' => 90],
            'clouds' => ['all' => 50],
            'weather' => [['main' => 'Clouds', 'description' => 'nublado', 'icon' => '04d']],
            'timezone' => -10800,
            'dt' => 1234567890,
        ]);
        $response = new Response(200, [], Utils::streamFor($json));
        $client = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $service = new OpenWeatherService($client);
        $result = $service->getWeatherByCity('São Paulo');

        $this->assertArrayNotHasKey('error', $result);
        $this->assertSame('São Paulo', $result['city']);
        $this->assertSame('BR', $result['country']);
        // round() in PHP returns float; use assertEquals for numeric comparison
        $this->assertEquals(26, $result['temperature']);
        $this->assertEquals(65, $result['humidity']);
        $this->assertSame('Clouds', $result['weather']['main']);
        $this->assertArrayHasKey('direction', $result['wind']);
    }

    public function testGetWeatherByCityReturnsErrorOnRequestException(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(
            new RequestException('Network error', new \GuzzleHttp\Psr7\Request('GET', 'test'))
        );

        $service = new OpenWeatherService($client);
        $result = $service->getWeatherByCity('Invalid');

        $this->assertTrue($result['error']);
        $this->assertArrayHasKey('message', $result);
    }

    public function testGetForecastReturnsFormattedData(): void
    {
        $json = json_encode([
            'city' => ['name' => 'SP', 'country' => 'BR', 'coord' => [], 'timezone' => -10800],
            'list' => [
                [
                    'dt' => strtotime('2025-02-01 12:00:00'),
                    'main' => ['temp' => 25, 'feels_like' => 26, 'temp_min' => 22, 'temp_max' => 28, 'humidity' => 60],
                    'weather' => [['main' => 'Clear', 'description' => 'céu limpo', 'icon' => '01d']],
                    'wind' => ['speed' => 5, 'deg' => 180],
                    'pop' => 0.1,
                    'rain' => [],
                    'clouds' => ['all' => 10],
                ],
            ],
        ]);
        $response = new Response(200, [], Utils::streamFor($json));
        $client = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $service = new OpenWeatherService($client);
        $result = $service->getForecast('São Paulo');

        $this->assertArrayNotHasKey('error', $result);
        $this->assertSame('SP', $result['city']);
        $this->assertArrayHasKey('hourly', $result);
        $this->assertArrayHasKey('daily', $result);
    }

    public function testGetAirQualityReturnsFormattedData(): void
    {
        $json = json_encode([
            'list' => [
                [
                    'main' => ['aqi' => 1],
                    'components' => [
                        'co' => 100, 'no' => 0, 'no2' => 10, 'o3' => 30,
                        'so2' => 5, 'pm2_5' => 5, 'pm10' => 10, 'nh3' => 1,
                    ],
                    'dt' => 1234567890,
                ],
            ],
        ]);
        $response = new Response(200, [], Utils::streamFor($json));
        $client = $this->createMock(Client::class);
        $client->method('get')->willReturn($response);

        $service = new OpenWeatherService($client);
        $result = $service->getAirQuality(-23.5, -46.6);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertSame(1, $result['aqi']);
        $this->assertSame('Bom', $result['label']);
        $this->assertSame('good', $result['color']);
        $this->assertArrayHasKey('components', $result);
    }

    public function testGetAirQualityReturnsErrorOnRequestException(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('get')->willThrowException(
            new RequestException('API error', new \GuzzleHttp\Psr7\Request('GET', 'test'))
        );

        $service = new OpenWeatherService($client);
        $result = $service->getAirQuality(0.0, 0.0);

        $this->assertTrue($result['error']);
        $this->assertArrayHasKey('message', $result);
    }
}
