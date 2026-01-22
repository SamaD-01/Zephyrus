<?php

namespace App\Controller;

use App\Repository\SensorReadingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(SensorReadingRepository $repository): Response
    {
        $latestReading = $repository->findOneBy([], ['timestamp' => 'DESC']);
        $totalReadings = $repository->count([]);
        
        return $this->render('dashboard/index.html.twig', [
            'latestReading' => $latestReading,
            'totalReadings' => $totalReadings,
        ]);
    }

    #[Route('/api/readings/latest', name: 'api_readings_latest', methods: ['GET'])]
    public function latestReadings(SensorReadingRepository $repository): JsonResponse
    {
        $readings = $repository->findBy([], ['timestamp' => 'DESC'], 20);
        
        $data = array_map(function($reading) {
            return [
                'id' => $reading->getId(),
                'deviceId' => $reading->getDeviceId(),
                'temperature' => $reading->getTemperature(),
                'humidity' => $reading->getHumidity(),
                'co2' => $reading->getCo2(),
                'noise' => $reading->getNoise(),
                'timestamp' => $reading->getTimestamp()->format('Y-m-d H:i:s'),
            ];
        }, $readings);
        
        return $this->json($data);
    }

    #[Route('/api/readings/chart', name: 'api_readings_chart', methods: ['GET'])]
    public function chartData(SensorReadingRepository $repository): JsonResponse
    {
        $readings = $repository->findBy([], ['timestamp' => 'ASC'], 50);
        
        $labels = [];
        $temperatures = [];
        $humidity = [];
        $co2 = [];
        $noise = [];
        
        foreach ($readings as $reading) {
            $labels[] = $reading->getTimestamp()->format('H:i:s');
            $temperatures[] = round($reading->getTemperature(), 1);
            $humidity[] = round($reading->getHumidity(), 1);
            $co2[] = $reading->getCo2();
            $noise[] = round($reading->getNoise(), 1);
        }
        
        return $this->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Temperature (°C)',
                    'data' => $temperatures,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Humidity (%)',
                    'data' => $humidity,
                    'borderColor' => 'rgb(54, 162, 235)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'CO2 (ppm)',
                    'data' => $co2,
                    'borderColor' => 'rgb(255, 206, 86)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Noise (dB)',
                    'data' => $noise,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ]
            ]
        ]);
    }
}