<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class APIClientController extends AbstractController
{

    /*
    Récupère la liste de tous les clients

    - URI : /api/clients
    - Méthode HTTP : "Verbe" GET

    */

    #[Route('/api/clients', name: 'clients', methods: ['GET'])]
    public function getAllClients(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clientList = $clientRepository->findAll();
        
        $jsonClientList = $serializer->serialize($clientList, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }
	
    
    /*

    Récupère les détails d'un seul client

    - URI : /api/clients/{id}
    - Méthode HTTP : "Verbe" GET

    */

    #[Route('/api/clients/{id}', name: 'detailClient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse {
        $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }
}
