<?php

namespace App\Subscriber;

use App\Controller\Api\ProtectedRoute;
use App\Service\AuthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthSubscriber implements EventSubscriberInterface
{
    private AuthService $auth_service;

    public function __construct(
        AuthService $auth_service
    ) {
        $this->auth_service = $auth_service;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof ProtectedRoute) {
            $authorization = $event->getRequest()->headers->all("authorization");

            if (
                !isset($authorization[0]) ||
                (bool) !$this->auth_service->validateToken($authorization[0], true)
            ) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response) {
            return;
        }

        $content = json_decode($response->getContent(), true);
        if (!$content) {
            return;
        }

        $authorization = $event->getRequest()->headers->all("authorization");
        if (
            !isset($authorization[0]) ||
            !($payload = $this->auth_service->validateToken($authorization[0], false))
        ) {
            return;
        }
        $jwt = $this->auth_service->generateJWTFromPayload($payload);
        if (!$jwt) {
            return;
        }
        $content += ["token" => $jwt];

        $response->setContent(json_encode($content));
        return $response;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE   => 'onKernelResponse'
        ];
    }
}