<?php

namespace App\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class WeatherService
{
    public function __construct(private string              $baseUrlApi,
                                private HttpClientInterface $client,
                                private LoggerInterface     $logger)
    {}

    public function getWeather(float $latitude, float $longitude, string $hourly, string $timezone): array
    {
        try {
            $params = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'hourly' => $hourly,
                'timezone' => $timezone
            ];
            $urlApi = $this->baseUrlApi . '/forecast?' . http_build_query($params);
    
            $response = $this->client->request(
                'GET',
                $urlApi
            );

            $statusCode = $response->getStatusCode();
            if($statusCode >= 400) {
                try {
                    $errorContent = $response->toArray(false);
                    $errorMessage = $errorContent['reason'] ?? 'Erro de API desconhecido.';
                    $this->logger->error(print_r($errorMessage, true));
                } catch(\Exception $e) {
                    $errorMessage = $response->getContent(false);
                }

                throw new \Exception('Erro na requisição: '.$errorMessage. ' Status Code: '.$statusCode);
            }

            $responseContent = $response->toArray();

            $hourlyResponse = $responseContent['hourly'];
            $time = $hourlyResponse['time'];
            $temperature = $hourlyResponse[$hourly];

            return $response->toArray();
        } catch(\Throwable $e) {
            $this->logger->error('Error in WeatherService::getWeather(): '.$e->getMessage(), [
                'exception' => $e
            ]);

            $statusCode = ($e instanceof HttpExceptionInterface) ? $e->getStatusCode() : 500;

            throw new HttpException($statusCode, 'Error in WeatherService::getWeather(): '.$e->getMessage(), $e);
        }
    }
}