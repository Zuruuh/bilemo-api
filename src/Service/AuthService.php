<?php

namespace App\Service;

use stdClass;
use App\Entity\Client;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

class AuthService
{
    private JWTTokenManagerInterface $jwt_manager;
    private ClientService $client_service;

    const INVALID_USERNAME = 'There are no clients with this username.';
    const INVALID_REQ = 'Invalid Request, please specify your username and your password.';
    const INVALID_CREDENTIALS = 'Invalid credentials. Make sure your password is correct.';

    const INVALID_TOKEN = 'This action needs a valid token!';

    public function __construct(
        JWTTokenManagerInterface $jwt_manager,
        ClientService $client_service,
    ) {
        $this->jwt_manager = $jwt_manager;
        $this->client_service = $client_service;
    }

    /**
     * Logins a user by it's credentials & return a JWT
     * 
     * @param stdClass $json<$username: string, $password: string>
     * 
     * @return JsonResponse Response including either an error or a JWT
     */
    public function login(stdClass $json): JsonResponse
    {
        $username = $this->getProperty($json, 'username');
        $password = $this->getProperty($json, 'password');

        $client = $this->exists($username);
        $this->checkCredentials($client, $password);

        $res = [
            'message' => 'Authentification success !',
            'code' => 200,
            'token' => $this->generateJWT($client)
        ];

        return new JsonResponse($res);
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
    private function exists(string $username): Client
    {
        $client = $this->client_service->getClientFromUsername($username);
        if (!$client) {
            throw new Exception\NotFoundHttpException(self::INVALID_USERNAME);
        }

        return $client;
    }

    /**
     * Gets a property from an object
     * 
     * @param stdClass $json      The object to get the property from
     * @param string   $property  The property to get
     * @param bool     ?$throwing Is the property nullable ?
     * 
     * @throws Exception\BadRequestHttpException If property does not exist & throwing is set to true
     * 
     * @return mixed Property if it exists
     */
    private function getProperty(stdClass $json, string $property, bool $throwing = true): mixed
    {
        if (!isset($json->$property)) {
            if ($throwing) {
                throw new Exception\BadRequestHttpException(self::INVALID_REQ);
            }
            return null;
        }

        return $json->$property;
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
    private function checkCredentials(Client $client, string $password): void
    {
        $valid = $this->client_service->isPasswordValid($client, $password);
        if (!$valid) {
            throw new Exception\AccessDeniedHttpException(self::INVALID_CREDENTIALS);
        }
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
}
