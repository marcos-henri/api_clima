<?php

namespace App\EventListener;

use App\Exception\CityNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Internal server error occurred.';

        if($exception instanceof CityNotFoundException) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $message = $exception->getMessage();
        } elseif($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } else {
            $code = $exception->getCode();
            $statusCode = ($code >= 300 && $code <= 600) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $exception->getMessage();
        }

        $response = new JsonResponse([
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message
        ], $statusCode);

        $event->setResponse($response);
    }
}