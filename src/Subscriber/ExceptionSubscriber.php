<?php

namespace App\Subscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{

    private bool $dev;

    const STATUS_CODE = 'getStatusCode';

    public function __construct(
        string $env
    ) {
        $this->dev = $env === 'dev';
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();
        $message = $throwable->getMessage();
        if (!method_exists($throwable, self::STATUS_CODE)) {
            $json = [
                'message' => 'Internal Server Error',
                'code' => 500
            ];
            if ($this->dev) {
                $json += [
                    'stackTrace' => [
                        'message' => $message,
                        'file' => $throwable->getFile(),
                        'line' => $throwable->getLine(),
                    ]
                ];
            }

            $event->setResponse(
                new JsonResponse($json, 500)
            );
            return;
        }

        $getStatusCode = self::STATUS_CODE;
        $code = $throwable->$getStatusCode();

        $content = [
            'errors' => [
                'auth' => [
                    'error' => $message,
                ]
            ],
            'code' => $code
        ];

        $res = new JsonResponse($content, $code);
        $event->setResponse($res);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
