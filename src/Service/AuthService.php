<?php

namespace App\Service;

use stdClass;
use App\Entity\Client;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthService
{
    private JWTTokenManagerInterface $jwt_manager;
    private ClientService $client_service;
    private ApiService $api_service;

    const INVALID_USERNAME = 'There are no client with this username.';
    const INVALID_CREDENTIALS = 'Invalid credentials. Make sure your password is correct.';

    const INVALID_TOKEN = 'This action needs a valid token!';

    const AUTH_UID = '_auth';

    public function __construct(
        JWTTokenManagerInterface $jwt_manager,
        ClientService $client_service,
        ApiService $api_service
    ) {
        $this->jwt_manager = $jwt_manager;
        $this->client_service = $client_service;
        $this->api_service = $api_service;
    }

    /**
     * Logins a user by it's credentials & return a JWT
     * 
     * @param Request       The incoming http request
     * @param FormInterface The login form
     * 
     * @return JsonResponse Response including either an error or a JWT
     */
    public function login(Request $request, FormInterface $form_interface): JsonResponse
    {
        $form = $this->api_service->form($form_interface, (array) $request->getContent());

        if (!$form->valid) {
            return $form->response ?? new JsonResponse();
        }

        $jwt = $this->checkCredentials((object) $form->content, $form_interface);

        $valid = $this->api_service->form($form_interface, (array) $request->getContent());

        if (!$valid->valid) {
            return $valid->response;
        }

        $res = [
            'message' => 'Authentication success !',
            'code' => 200,
            'token' => $jwt
        ];

        return new JsonResponse($res);
    }

    /**
     * Checks a client's credentials
     * 
     * @param Client $client   The client to check the credentials from
     * @param string $password The plain password given by user
     * 
     * @throws Exception\AccessDeniedHttpException If password is not valid
     * 
     * @return void
     */
    private function checkCredentials(stdClass $content, FormInterface $form): string|null
    {
        $client = $this->exists($content);
        if (!$client) {
            $this->api_service->generateError('username', self::INVALID_USERNAME, $form);
            return null;
        }

        $valid = $this->client_service->isPasswordValid($client, $content->password);
        if (!$valid) {
            $this->api_service->generateError('password', self::INVALID_CREDENTIALS, $form);
            return null;
        }

        return $this->generateJWT($client);
    }

    /**
     * Checks if a user exists with a given username
     * 
     * @param string $username The user to check
     * 
     * @throws Exception\NotFoundHttpException If user does not exist
     * 
     * @return Client
     */
    private function exists(stdClass $content): Client|null
    {
        $client = $this->client_service->getClientFromUsername(
            $content->username
        );

        return $client;
    }

    /**
     * Generates a JsonWebToken
     * 
     * @param Client $client The client who requested the JWT
     * 
     * @return string $jwt The generated JWT
     */
    public function generateJWT(Client $client): string
    {
        return $this->jwt_manager->create($client);
    }

    /**
     * Validates a JsonWebToken
     * 
     * @param string $token     The token to validate
     * @param bool   ?$throwing Should an error be thrown ?
     * 
     * @throws Exception\UnauthorizedHttpException If token is not valid & throwing is set to true
     * 
     * @return array|bool ParsedClient|false
     */
    public function validateToken(string $token, bool $throwing = true): array|bool
    {
        try {
            $client = $this->jwt_manager->parse(substr($token, 7));
            return $client;
        } catch (JWTDecodeFailureException $exception) {
            if ($throwing) {
                throw new Exception\UnauthorizedHttpException('Bearer', self::INVALID_TOKEN);
            }
            return false;
        }
    }

    /**
     * Generates a new JsonWebToken from a request's payload
     * 
     * @param array $payload The request's payload
     * 
     * @return string|bool JWT|false
     */
    public function generateJWTFromPayload(array $payload): string|bool
    {
        $client = $this->client_service->getClientFromUsername($payload['username']);
        if ($client instanceof Client) {
            return $this->generateJWT($client);
        }
        return false;
    }

    public function getClientFromJWT(string $token): array
    {
        $parsed = $this->validateToken($token);
        return [];
    }
}
