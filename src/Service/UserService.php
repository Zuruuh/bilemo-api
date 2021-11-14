<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class UserService
{
    private UserRepository         $user_repo;
    private AuthService            $auth_service;
    private ApiService             $api_service;
    private ClientService          $client_service;
    private EntityManagerInterface $em;
    private RouterInterface        $router;
    private TagAwareCacheInterface $cache;

    public const USER_DOES_NOT_EXIST = 'There are no user with the id %s';
    public const USER_CREATE_SUCCESS = 'User "%s" with id #%s has been successfully created !';
    public const NOT_YOUR_USER = 'You are not the owner of this user';
    public const USER_EDIT_SUCCESS = 'User "%s" with id #%s has been successfully updated !';

    public function __construct(
        UserRepository $user_repo,
        AuthService $auth_service,
        ApiService $api_service,
        ClientService $client_service,
        EntityManagerInterface $em,
        RouterInterface $router,
        TagAwareCacheInterface $cache,
    ) {
        $this->user_repo = $user_repo;
        $this->auth_service = $auth_service;
        $this->api_service = $api_service;
        $this->client_service = $client_service;
        $this->em = $em;
        $this->router = $router;
        $this->cache = $cache;
    }

    /**
     * Returns paginated users in a json object.
     * 
     * @param Request $request The controller request
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getOwnPaginatedUsers(Request $request): JsonResponse
    {
        $username = $request->getContent()[AuthService::AUTH_UID];
        $client = $this->client_service->getClientFromUsername($username);

        $total = $this->user_repo->count(['client' => $client]);
        $cursor = $request->query->getInt('cursor');
        $cursor = min($cursor, $total);

        $users = $this->cache->get("users-$username-$cursor", function (ItemInterface $item) use ($client, $cursor, $username) {
            $item->expiresAfter(60 * 60);
            $item->tag('user-' . $username);

            $usersList = $this->user_repo->findByCursor($cursor, ['client' => $client->getId()]);

            $entity_cursor = $cursor;
            $usersArray = [];
            foreach ($usersList as $user) {
                ++$entity_cursor;
                $entity = (array) $user;
                $entity['_links'] = $this->generateLinks($user['id']);
                $entity['cursor'] = $entity_cursor;

                $usersArray[] = $entity;
            }
            return $usersArray;
        });


        return new JsonResponse(
            ['users' => $users],
            empty([$users]) ? 404 : 200 // 302: Found ?
        );
    }

    /**
     * Returns a specific user.
     * 
     * @param Request $request The controller request
     * @param int     $id      The requsted user's id
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getOne(Request $request, int $id): JsonResponse
    {
        $username = $request->getContent()[AuthService::AUTH_UID];
        $client = $this->client_service->getClientFromUsername($username);
        $user = $this->cache->get("user-$username-$id", function (ItemInterface $item) use ($client, $id, $username) {
            $item->expiresAfter(60 * 60);
            $item->tag('user-' . $username);

            $user = $this->exists($id);
            $this->checkOwner($user, $client);
            $user['_links'] = $this->generateLinks($user['id']);

            return $user;
        });

        return new JsonResponse(['user' => $user]);
    }

    /**
     * Returns a user if it exists & throws an error if it doesn't.
     * 
     * @param int  $id    The user id
     * @param bool $array Should the method return an entity or an array
     * 
     * @throws NotFoundHttpException If there are no users with this id
     * 
     * @return User|array|null
     */
    public function exists(int $id, bool $array = true): User|array|null
    {
        $user = $array ?
            $this->user_repo->findOneByWithArray(['id' => $id]) :
            $this->user_repo->findOneBy(['id' => $id]);
        if (!$user) {
            throw new NotFoundHttpException(sprintf(self::USER_DOES_NOT_EXIST, $id));
        }

        return $array ? (array) $user[0] : $user;
    }

    /**
     * Verifies that the requested User belongs to the requesting Client.
     * 
     * @param User|array $user_infos The user or user infos array
     * @param Client     $client     The client requesting the user
     * 
     * @throws AccessDeniedHttpException If user does not belong to client
     * 
     * @return void
     */
    private function checkOwner(mixed $user_infos, Client $client): void
    {
        $user = $user_infos;
        if (!($user instanceof User)) {
            $user = $this->user_repo->find($user_infos['id']);
        }

        if ($user->getClient()->getId() !== $client->getId()) {
            throw new AccessDeniedHttpException(self::NOT_YOUR_USER);
        }
    }

    /**
     *  Creates a user & returns the operation status.
     * 
     * @param Request       $request        The controller request
     * @param FormInterface $form_interface The user form
     * 
     * @return JsonResponse The http response containing the operation status
     */
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
        $user['_links'] = $this->generateLinks($user['id']);

        $this->cache->invalidateTags(['user-' . $request->getContent()[AuthService::AUTH_UID]]);

        return new JsonResponse(
            [
                'message' => sprintf(self::USER_CREATE_SUCCESS, $user['name'], $user['id']),
                'user' => $user,
                'code' => 201
            ],
            201
        );
    }

    /**
     * Saves a user to database.
     * 
     * @param FormInterface The user form
     * @param array         $content The request content
     * 
     * @return array
     */
    private function save(FormInterface $form, array $content): array
    {
        $user = $form->getData();
        $user->setClient(
            $this->client_service->getClientFromUsername($content[$this->auth_service::AUTH_UID])
        );
        $this->em->persist($user);
        $this->em->flush();

        return (array) $this->user_repo->findOneByWithArray(['id' => $user->getId()])[0];
    }

    /**
     *  Edits a user & returns the operation status.
     * 
     * @param Request       $request        The controller request
     * @param FormInterface $form_interface The user form
     * @param int           $id             The updated user's id
     * 
     * @return JsonResponse The http response containing the operation status
     */
    public function edit(Request $request, FormInterface $form_interface, int $id): JsonResponse
    {
        $content = (array) $request->getContent();
        $form = $this->api_service->form($form_interface, $content, true);

        if (!$form->valid) {
            return $form->response ?? new JsonResponse();
        }

        $user = $this->update($content, $id);

        $valid = $this->api_service->form($form_interface, $content);
        if (!$valid->valid) {
            return $valid->response;
        }

        $this->em->persist($user);
        $this->em->flush();

        $user_id = $user->getId();
        $user_as_array = $this->user_repo->findOneByWithArray(['id' => $user_id])[0];
        $user_as_array['_links'] = $this->generateLinks($user_id);

        $this->cache->invalidateTags(['user-' . $request->getContent()[AuthService::AUTH_UID]]);

        return new JsonResponse(
            [
                'message' => sprintf(self::USER_EDIT_SUCCESS, $user->getName(), $user_id),
                'user' => $user_as_array,
                'code' => 200
            ],
            200
        );
    }

    /**
     * Updates a user in database.
     * 
     * @param FormInterface $form    The user form
     * @param array         $content The submitted content
     * @param int           $id      The updated user's id
     * 
     * @return User
     */
    private function update(array $content, int $id): User
    {
        $user = $this->exists($id, false);
        $client = $this->client_service->getClientFromUsername(
            $content[$this->auth_service::AUTH_UID]
        );
        $this->checkOwner($user, $client);

        return $user;
    }

    /**
     * Deletes a user from database.
     * 
     * @param Request $request The controller request
     * @param int     $id      The user id
     * 
     * @return Response The empty response
     */
    public function delete(Request $request, int $id): Response
    {
        $user = $this->exists($id, false);
        $client = $this->client_service->getClientFromUsername(
            $request->getContent()[$this->auth_service::AUTH_UID]
        );
        $this->checkOwner($user, $client);

        $this->em->remove($user);
        $this->em->flush();

        $this->cache->invalidateTags(['user-' . $client->getUserIdentifier()]);

        return new Response('', 204);
    }

    /**
     * Generate Hateoas links for users.
     * 
     * @param int $id The user id
     * 
     * @return array The generated links
     */
    private function generateLinks(int $id): array
    {
        $param = ['id' => $id];

        $get_link = $this->router->generate('app_api_user_getone', $param);
        $edit_link = $this->router->generate('app_api_user_edit', $param);
        $delete_link = $this->router->generate('app_api_user_delete', $param);

        return [
            'get' => [
                'methods' => [
                    'GET',
                ],
                'route' => $get_link
            ],
            'edit' => [
                'methods' => [
                    'PUT',
                    'PATCH'
                ],
                'route' => $edit_link
            ],
            'delete' => [
                'methods' => [
                    'DELETE'
                ],
                'route' => $delete_link
            ]
        ];
    }
}
