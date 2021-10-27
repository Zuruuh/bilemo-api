<?php

namespace App\Subscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{

    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();
        $message = $throwable->getMessage();
        // @php-ignore
        $code = $throwable->getStatusCode();

        $content = [
            "message" => $message,
            "code" => $code
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
