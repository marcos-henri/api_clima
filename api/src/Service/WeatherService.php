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

    /** Recebe os dados do WeatherController para a consulta na API Open Meteo
     * 
     * @param float $latitude - Contém a latitude do local
     * @param float $longitude - Contém a latitude do local
     * @param string $hourly - Contém a lista de parâmetros para a consulta na API Open Meteo
     * @param string $timezone - Contém o timezone do local
     * 
     * @return Array - Retorna um JSON com os dados da consulta
     */
    public function getWeather(float $latitude, float $longitude, string $hourly, string $timezone): array
    {
        try {
            $hourlyParams = explode(',', $hourly);
            $params = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'hourly' => $hourlyParams,
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