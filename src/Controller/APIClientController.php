<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
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
            echo ("L'élement n'est pas encore en cache !\n");
            $item->tag('clientsCache');
            $clientList = $clientRepository->findAll();
            return $serializer->serialize($clientList, 'json', ['groups' => 'getClients']);
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
            echo ("Le produit n'est pas encore en cache !\n");

            return $serializer->serialize($client, 'json', ['groups' => 'getClients']);
        });



        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }
}
