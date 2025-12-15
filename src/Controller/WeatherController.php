<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class WeatherController extends AbstractController
{
    public function __construct(private WeatherService $weather_service)
    {}

    #[Route('/get-weather', name: 'get-weather', methods: ['GET'])]
    public function getWeather(Request $request): Response
    {
        $latitude = (float)$request->query->get('latitude');
        $longitude = (float)$request->query->get('longitude');
        $hourly = $request->query->get('hourly');
        $timezone = $request->query->get('timezone');

        $weather = $this->weather_service->getWeather($latitude, $longitude, $hourly, $timezone);
        return $this->json($weather, Response::HTTP_OK);
    }
}