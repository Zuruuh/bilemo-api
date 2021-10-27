<?php

namespace App\Service;

use stdClass;
use App\Entity\Client;
use App\Repository\ClientRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthService
{
    private ClientRepository $client_repo;
    private UserPasswordHasherInterface $hasher;
    private ClientService $client_service;

    const INVALID_USERNAME = "There are no clients with this username.";
    const INVALID_REQ = "Invalid Request, please specify your username and your password.";
    const INVALID_CREDENTIALS = "Invalid credentials. Make sure your password is correct.";

    public function __construct(
        ClientRepository $client_repo,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jwt_manager,
        ClientService $client_service,
    ) {
        $this->hasher = $hasher;
        $this->client_repo = $client_repo;
        $this->jwt_manager = $jwt_manager;
        $this->client_service = $client_service;
    }

    public function login(stdClass $json): JsonResponse
    {
        $username = $this->getProperty($json, 'username');
        $password = $this->getProperty($json, 'password');

        $client = $this->exists($username);
        $this->checkCredentials($client, $password);

        $res = [
            "message" => "Authentification success !",
            "code" => 200,
            "token" => $this->generateJWT($client)
        ];

        return new JsonResponse($res);
    }

    private function exists(string $username): Client
    {
        $client = $this->client_repo->findOneBy(['username' => $username]);
        if (!$client) {
            throw new NotFoundHttpException(self::INVALID_USERNAME);
        }

        return $client;
    }

    public function decode(Request $request): stdClass
    {
        $data = $request->getContent();

        return json_decode($data) ?? (object) [];
    }

    private function getProperty(stdClass $json, string $property): mixed
    {
        if (!isset($json->$property)) {
            throw new BadRequestHttpException(self::INVALID_REQ);
        }

        return $json->$property;
    }

    private function checkCredentials(Client $client, string $password): void
    {
        $valid = $this->hasher->isPasswordValid($client, $password);
        if (!$valid) {
            throw new UnauthorizedHttpException('Bearer', self::INVALID_CREDENTIALS);
        }
    }

    public function generateJWT(Client $client): string
    {
        return $this->jwt_manager->create($client);
    }

    public function validateToken(string $token, bool $throwing): array|bool
    {
        try {
            $client = $this->jwt_manager->parse(substr($token, 7));
            return $client;
        } catch (JWTDecodeFailureException $exception) {
            if ($throwing) {
                throw $exception;
            }
            return false;
        }
    }

    public function generateJWTFromPayload(array $payload): string|bool
    {
        $client = $this->client_service->getClientFromUsername($payload["username"]);
        if ($client instanceof Client) {
            return $this->generateJWT($client);
        }
        return false;
    }
}
