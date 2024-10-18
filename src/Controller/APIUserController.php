<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class APIUserController extends AbstractController
{

    /*

    Récupère la liste de tous les utilisateurs

    - URI : /api/users
    - Méthode HTTP : "Verbe" GET
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"
    - Pagination défauts : Limite de 10 par page
    - Modifier la pagination : URI + ?page=X&limit=X (X etant un chiffre à choisir)

    */

    #[Route('/api/users', name: 'users', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour consulter les utilisateurs')]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // Identifiant unique pour le cache basé sur la pagination
        $idCache = "getAllUsers-" . $page . "-" . $limit;

        // Mise en cache de la liste des utilisateurs
        $jsonUserList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
            echo ("L'élément n'est pas encore en cache !\n");

            // Tag pour invalider le cache en cas de mise à jour des utilisateurs
            $item->tag('usersCache');

            $userList = $userRepository->findAllWithPagination($page, $limit);
            return $serializer->serialize($userList, 'json', ['groups' => 'getUsers']);
        });

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }



    /*

    Récupère les détails d'un seul utilisateur

    - URI : /api/users/{id}
    - Méthode HTTP : "Verbe" GET
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    */

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour consulter les détails d\'un utilisateur')]
    public function getDetailUser(User $user, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        
        $idCache = "getDetailUser-" . $user->getId();

        
        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($user, $serializer) {
            echo ("L'utilisateur n'est pas encore en cache !\n");

            
            $item->tag('usersCache');

            return $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        });

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }



    /*

    Supprime l'utilisateur d'un client

    - URI : /api/clients/{clientId}/users/{id}
    - Méthode HTTP : "Verbe" DELETE
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    */

    #[Route('/api/clients/{clientId}/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour supprimer un utilisateur')]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /*

    Crée un utilisateur pour un client

    - URI : /api/clients/{clientId}/users
    - Méthode HTTP : "Verbe" POST
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    */

    #[Route('/api/clients/{clientId}/users', name: "createUser", methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour créer un utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ClientRepository $clientRepository,
        int $clientId,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');


        $errors = $validator->validate($user);


        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }


        $client = $clientRepository->find($clientId);
        $user->setClient($client);


        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);


        $user->setRoles(['ROLE_USER']);


        $em->persist($user);
        $em->flush();


        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);


        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
