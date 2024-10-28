<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
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
    - Pagination défaut : Limite de 10 par page
    - Modifier la pagination : URI + ?page=X&limit=X (X étant un chiffre à choisir)

    */

    #[Route('/api/users', name: 'users', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour consulter les utilisateurs')]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        // Récupération de la page et de la limite à partir des paramètres de requête
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // Identifiant de cache unique basé sur la pagination
        $idCache = "getAllUsers-" . $page . "-" . $limit;

        // Mise en cache de la liste des utilisateurs
        $jsonUserList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
            // Tag pour invalider le cache si nécessaire et durée de vie
            $item->tag('usersCache');
            $item->expiresAfter(3600);

            // Récupération des utilisateurs avec pagination
            $userList = $userRepository->findAllWithPagination($page, $limit);

            // Ajout des liens à chaque utilisateur
            $usersWithLinks = array_map(function ($user) use ($serializer) {
                $userArray = json_decode($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['getUsers'])), true);
                $userArray['link'] = $user->getLinks();
                return $userArray;
            }, $userList);

            // Sérialisation du tableau en JSON avant de le retourner
            return $serializer->serialize($usersWithLinks, 'json', SerializationContext::create()->setGroups(['getUsers']));
        });

        // Retourne la liste des utilisateurs sous forme de réponse JSON
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
        // Identifiant de cache pour les détails de l'utilisateur
        $idCache = "getDetailUser-" . $user->getId();

        // Mise en cache des détails de l'utilisateur
        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($user, $serializer) {
            $item->tag('usersCache');
            $item->expiresAfter(3600);

            // Création d'un contexte de sérialisation pour l'utilisateur
            $context = SerializationContext::create()->setGroups(["getUsers"]);

            return $serializer->serialize($user, 'json', $context);
        });

        // Retourne les détails de l'utilisateur sous forme de réponse JSON
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }


    /*

    Supprime l'utilisateur

    - URI : /api/users/{id}
    - Méthode HTTP : "Verbe" DELETE
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    */

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour supprimer un utilisateur')]
    public function deleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        // Invalidation du cache pour les utilisateurs
        $cache->invalidateTags(["usersCache"]);

        // Suppression de l'utilisateur
        $em->remove($user);
        $em->flush();

        // Retourne une réponse vide avec le code HTTP 204 No Content
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /*

    Crée un utilisateur pour un client

    - URI : /api/users
    - Méthode HTTP : "Verbe" POST
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    - Exemple de body :
    {
        "email": "nouvel.utilisateur@example.com",
        "password": "motdepasse123",
        "clientId": X
    }

    */

    #[Route('/api/users', name: "createUser", methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour créer un utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ClientRepository $clientRepository,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        // Invalidation du cache pour les utilisateurs
        $cache->invalidateTags(["usersCache"]);

        // Désérialisation du contenu de la requête dans un objet User
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Validation de l'utilisateur
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true
            );
        }

        // Récupération du client lié à l'utilisateur
        $content = $request->toArray();
        $clientId = $content['clientId'] ?? null;

        if (!$clientId || !($client = $clientRepository->find($clientId))) {
            return new JsonResponse(['message' => 'Client not found'], Response::HTTP_BAD_REQUEST);
        }

        // Association de l'utilisateur avec le client
        $user->setClient($client);

        // Hashage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        // Attribution du rôle utilisateur
        $user->setRoles(['ROLE_USER']);

        // Persistance de l'utilisateur en base de données
        $em->persist($user);
        $em->flush();

        // Sérialisation de l'utilisateur pour la réponse
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        // Génération de l'URL du nouvel utilisateur
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }


    /*

    Met à jour un utilisateur

    - URI : /api/users/{id}
    - Méthode HTTP : "Verbe" PUT
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    - Exemple de body :
    {
        "email": "utilisateur.MODIFIER@example.com",
        "password": "motdepasseMODIFIER",
    }

    */

    #[Route('/api/users/{id}', name: "updateUser", methods: ['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour modifier un utilisateur')]
    public function updateUser(
        Request $request,
        SerializerInterface $serializer,
        User $currentUser,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // Désérialisation partielle (évite de désérialiser des relations sensibles)
        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Mise à jour de l'email si modifié
        $currentUser->setEmail($newUser->getEmail());

        // Vérification si un nouveau mot de passe est fourni
        if ($newUser->getPassword()) {
            // Hashage du nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($currentUser, $newUser->getPassword());
            $currentUser->setPassword($hashedPassword);
        }

        // Validation des modifications
        $errors = $validator->validate($currentUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        // Sauvegarde des modifications
        $em->persist($currentUser);
        $em->flush();

        // Invalidation du cache
        $cache->invalidateTags(["usersCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
