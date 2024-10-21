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
        $idCache = "getAllClients";

        $jsonClientList = $cache->get($idCache, function (ItemInterface $item) use ($clientRepository, $serializer) {
            $item->tag('clientsCache');
            $item->expiresAfter(120);
            echo ("Les clients ne sont pas encore en cache !\n");

            $clientList = $clientRepository->findAll();

            // Ajout des liens pour chaque client
            $clientsWithLinks = array_map(function ($client) use ($serializer) {
                $clientArray = json_decode($serializer->serialize($client, 'json', SerializationContext::create()->setGroups(['getClients'])), true);

                // Ajout des liens pour les utilisateurs
                $clientArray['Lien du client'] = $client->getLinks();
                $clientArray['users'] = array_map(function ($user) use ($serializer) {
                    $userArray = json_decode($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['getUsers'])), true);
                    $userArray['Lien de l\'utilisateur'] = $user->getLinks(); // Ajout du lien pour l'utilisateur
                    return $userArray;
                }, $client->getUsers()->toArray());

                return $clientArray;
            }, $clientList);

            return $serializer->serialize($clientsWithLinks, 'json', SerializationContext::create()->setGroups(['getClients']));
        });

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

        $idCache = "getDetailClient-" . $client->getID();

        $jsonClient = $cache->get($idCache, function (ItemInterface $item) use ($client, $serializer) {

            $item->tag('getDetailClient');
            $item->expiresAfter(120);
            echo ("Le client n'est pas encore en cache !\n");

            $context = SerializationContext::create()->setGroups(['getClients']);

            return $serializer->serialize($client, 'json', $context);
        });



        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }
}
