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
    const USER_DELETE_SUCCESS = 'The user "%s" has been successfully deleted !';
    const USER_EDIT_SUCCESS = 'User "%s" with id #%s has been successfully updated !';

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

        $user = $this->exists($id);
        $this->checkOwner($user, $client);

        return new JsonResponse(['user' => $user]);
    }

    /**
     * Returns a user if it exists & throws an error if it doesn't
     * 
     * @param int  $id    The user id 
     * @param bool $array Should the method return an entity or an array
     * 
     * @throws NotFoundHttpException If there are no users with this id
     * 
     * @return User|array|null
     */
    private function exists(int $id, bool $array = true): User|array|null
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
     * Verifies that the requested User belongs to the requesting Client
     * 
     * @param User|array  $user_infos The user or user infos array
     * @param Client      $client     The client requesting the user
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

    /**
     * Saves a user to database
     * 
     * @param FormInterface The user form
     * @param array         $content The request content
     * 
     * @return User
     */
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

    public function edit(Request $request, FormInterface $form_interface, int $id): JsonResponse
    {
        $content = (array) $request->getContent();
        $form = $this->api_service->form($form_interface, $content);

        if (!$form->valid) {
            return $form->response ?? new JsonResponse();
        }

        $user = $this->update($form_interface, $content, $id);

        $valid = $this->api_service->form($form_interface, $content);
        if (!$valid->valid) {
            return $valid->response;
        }


        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(
            [
                'message' => sprintf(self::USER_EDIT_SUCCESS, $user->getName(), $user->getId()),
                'code' => 200
            ],
            200
        );
    }

    private function update(FormInterface $form, array $content, int $id): User
    {
        $user = $this->exists($id, false);
        $client = $this->client_service->getClientFromUsername(
            $content[$this->auth_service::AUTH_UID]
        );
        $this->checkOwner($user, $client);

        $updated_user = $this->updateProperties($user, $form->getData());

        return $updated_user;
    }

    private function updateProperties(User $user, User $new_data): User
    {
        foreach (User::PROPERTIES as $property) {
            $property_name = ucfirst($property);
            $setter = sprintf('set%s', $property_name);
            $getter = sprintf('get%s', $property_name);
            $value = $new_data->$getter();

            if (!$value || $value === $user->$getter()) {
                $user->$setter($new_data->$getter());
            }
        }

        return $user;
    }

    /**
     * Deletes a user from database
     * 
     * @param Request $request The controller request
     * @param int     $id      The user id
     * 
     * @return JsonResponse The json containing either an error or a success message
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $user = $this->exists($id, false);
        $client = $this->client_service->getClientFromUsername(
            $request->getContent()[$this->auth_service::AUTH_UID]
        );
        $this->checkOwner($user, $client);

        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse([
            'message' => sprintf(self::USER_DELETE_SUCCESS, $user->getName())
        ]);
    }
}
