<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    private UserRepository $user_repo;
    private AuthService $auth_service;
    private ApiService $api_service;
    private ClientService $client_service;
    private EntityManagerInterface $em;

    const USER_DOES_NOT_EXIST = 'There are no user with the id %s';
    const USER_CREATE_SUCCESS = 'User "%s" with id #%s has been successfully created !';
    const NOT_YOUR_USER = 'You are not the owner of this user';

    public function __construct(
        UserRepository $user_repo,
        AuthService $auth_service,
        ApiService $api_service,
        ClientService $client_service,
        EntityManagerInterface $em,
    ) {
        $this->user_repo = $user_repo;
        $this->auth_service = $auth_service;
        $this->api_service = $api_service;
        $this->client_service = $client_service;
        $this->em = $em;
    }

    /**
     * Returns paginated users in a json object
     * 
     * @param Request $request The controller request
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getOwnPaginatedUsers(Request $request): JsonResponse
    {
        $client = $this->client_service->getClientFromUsername(
            $request->getContent()[AuthService::AUTH_UID]
        );

        $total = $this->user_repo->count(['client' => $client]);
        $cursor = $request->query->getInt('cursor');
        $cursor = min($cursor, $total);

        $users = $this->user_repo->findByCursor($cursor, ['client' => $client->getId()]);

        $index = $cursor;
        $usersArray = array_map(function ($user) use ($index) {
            ++$index;
            $entity = $user;
            $entity['cursor'] = $index;
            return $entity;
        }, $users);

        return new JsonResponse(
            ['users' => $usersArray],
            empty([$users]) ? 404 : 200 // 302: Found ?
        );
    }

    /**
     * Returns a specific user
     * 
     * @param Request $request The controller request
     * @param int     $id      The requsted user's id
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getOne(Request $request, int $id): JsonResponse
    {
        $client = $this->client_service->getClientFromUsername(
            $request->getContent()[AuthService::AUTH_UID]
        );

        $user = $this->exists($id, $client);
        $this->checkOwner($user, $client);

        return new JsonResponse(['user' => $user]);
    }

    /**
     * Returns a user if it exists & throws an error if it doesn't
     * 
     * @param int $id The user id 
     * 
     * @throws NotFoundHttpException If there are no users with this id
     * 
     * @return User|null
     */
    private function exists(int $id): array
    {
        $user = $this->user_repo->findOneByWithArray(['id' => $id]);
        if (!$user) {
            throw new NotFoundHttpException(sprintf(self::USER_DOES_NOT_EXIST, $id));
        }
        return (array) $user[0];
    }

    /**
     * Verifies that the requested User belongs to the requesting Client
     * 
     * @param array  $user_infos The user array
     * @param Client $client     The client requesting the user
     * 
     * @throws AccessDeniedHttpException If user does not belong to client
     * 
     * @return void
     */
    private function checkOwner(array $user_infos, Client $client): void
    {
        $user = $this->user_repo->find($user_infos['id']);

        if ($user->getClient()->getId() !== $client->getId()) {
            throw new AccessDeniedHttpException(self::NOT_YOUR_USER);
        }
    }

    public function create(Request $request, FormInterface $form_interface): JsonResponse
    {
        $content = (array) $request->getContent();
        $form = $this->api_service->form($form_interface, $content);

        if (!$form->valid) {
            return $form->response ?? new JsonResponse();
        }

        $user = $this->save($form_interface, $content);

        $valid = $this->api_service->form($form_interface, $content);
        if (!$valid->valid) {
            return $valid->response;
        }
        return new JsonResponse(
            [
                'message' => sprintf(self::USER_CREATE_SUCCESS, $user['name'], $user['id']),
                'code' => 201
            ],
            201
        );
    }

    private function save(FormInterface $form, array $content)
    {
        $user = $form->getData();
        $user->setClient(
            $this->client_service->getClientFromUsername($content[$this->auth_service::AUTH_UID])
        );
        $this->em->persist($user);
        $this->em->flush();

        return $this->user_repo->findOneByWithArray(['id' => $user->getId()])[0];
    }
}
