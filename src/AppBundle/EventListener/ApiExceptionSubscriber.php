<?php

namespace AppBundle\EventListener;

use AppBundle\Api\ApiProblem;
use AppBundle\Api\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (strpos($request->getPathInfo(), '/api') !== 0) {
            return;
        }

        $exception = $event->getException();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if ($statusCode == 500 && $this->debug) {
            return;
        }

        if ($exception instanceof ApiProblemException) {
            $apiProblem = $exception->getApiProblem();
        } else {
            $apiProblem = new ApiProblem($statusCode);
            if ($exception instanceof HttpExceptionInterface) {
                $apiProblem->set('detail', $exception->getMessage());
            }
        }

        $data = $apiProblem->toArray();
        if ($data['type'] != 'about:blank') {
            $data['type'] = 'http://localhost:8000/docs/errors#' . $data['type'];
        }

        $response = new JsonResponse($data, $apiProblem->getStatusCode());
        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }
}
