<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class APIClientController extends AbstractController
{

    /*
    Récupère la liste de tous les clients

    - URI : /api/clients
    - Méthode HTTP : "Verbe" GET
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    */

    #[Route('/api/clients', name: 'clients', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour consulter les clients')]
    public function getAllClients(ClientRepository $clientRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        // Identifiant de cache pour la liste des clients
        $idCache = "getAllClients";

        // Récupération de la liste des clients depuis le cache ou requête si non disponible
        $jsonClientList = $cache->get($idCache, function (ItemInterface $item) use ($clientRepository, $serializer) {
            // Marque l'élément de cache avec une étiquette et définit sa durée de vie
            $item->tag('clientsCache');
            $item->expiresAfter(240);

            // echo ("Les clients ne sont pas encore en cache !\n");

            // Récupère tous les clients
            $clientList = $clientRepository->findAll();

            // Ajoute des liens et des utilisateurs pour chaque client
            $clientsWithLinks = array_map(function ($client) use ($serializer) {
                $clientArray = json_decode($serializer->serialize($client, 'json', SerializationContext::create()->setGroups(['getClients'])), true);

                // Ajoute le lien du client
                $clientArray['linkClient'] = $client->getLinks();
                // Récupère et ajoute les informations des utilisateurs associés
                $clientArray['users'] = array_map(function ($user) use ($serializer) {
                    $userArray = json_decode($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['getUsers'])), true);
                    $userArray['linkUser'] = $user->getLinks(); // Ajoute le lien pour l'utilisateur
                    return $userArray;
                }, $client->getUsers()->toArray());

                return $clientArray;
            }, $clientList);

            // Sérialise la liste des clients avec liens en JSON
            return $serializer->serialize($clientsWithLinks, 'json', SerializationContext::create()->setGroups(['getClients']));
        });

        // Retourne la réponse JSON avec le code HTTP 200 (OK)
        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }


    /*

    Récupère les détails d'un seul client

    - URI : /api/clients/{id}
    - Méthode HTTP : "Verbe" GET
    - Authentification : JWT requise
    - Header Key : Value --> "Content-Type : application/json" AND "Authorization : bearer TOKEN"

    */

    #[Route('/api/clients/{id}', name: 'detailClient', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits pour consulter les clients')]
    public function getDetailClient(Client $client, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        // Identifiant de cache pour les détails du client
        $idCache = "getDetailClient-" . $client->getId();

        // Récupération des détails du client depuis le cache ou requête si non disponible
        $jsonClient = $cache->get($idCache, function (ItemInterface $item) use ($client, $serializer) {
            // Marque l'élément de cache avec une étiquette et définit sa durée de vie
            $item->tag('getDetailClient');
            $item->expiresAfter(240);

            // echo ("Le client n'est pas encore en cache !\n");

            // Crée un contexte de sérialisation pour le client
            $context = SerializationContext::create()->setGroups(['getClients']);

            // Sérialise le client en JSON pour le retour
            return $serializer->serialize($client, 'json', $context);
        });

        // Retourne la réponse JSON avec le code HTTP 200 (OK)
        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }
}
