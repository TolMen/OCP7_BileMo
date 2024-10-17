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

class APIUserController extends AbstractController
{

    /*
    Récupère la liste de tous les utilisateurs
    - URI : /api/users
    - Méthode HTTP : "Verbe" GET
    */
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /*
    Récupère les détails d'un seul utilisateur
    - URI : /api/users/{id}
    - Méthode HTTP : "Verbe" GET
    */
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /*
    Supprime l'utilisateur d'un client
    - URI : /api/clients/{clientId}/users/{id}
    - Méthode HTTP : "Verbe" DELETE
    */
    #[Route('/api/clients/{clientId}/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
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
    */
    #[Route('/api/clients/{clientId}/users', name:"createUser", methods: ['POST'])]
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
        // Désérialise la requête en un objet User
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // On vérifie les erreurs de validation automatiquement avec les contraintes définies dans l'entité
        $errors = $validator->validate($user);

        // Retourne les erreurs de validation si présentes
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        // Associe le client à l'utilisateur
        $client = $clientRepository->find($clientId);
        $user->setClient($client);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);
        
        // Persist l'utilisateur et enregistre dans la base de données
        $em->persist($user);
        $em->flush();

        // Retourne l'utilisateur créé en JSON
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        
        // Génération de l'URL pour accéder au détail de l'utilisateur
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
