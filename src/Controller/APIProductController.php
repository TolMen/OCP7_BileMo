<?php

namespace App\Controller;


use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class APIProductController extends AbstractController
{

    /*

    Récupère la liste de tous les produits avec pagination et mise en cache

    - URI : /api/products
    - Méthode HTTP : "Verbe" GET
    - Authentification : Accès libre
    
    */

    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productList = $productRepository->findAll();
        
        $jsonProductList = $serializer->serialize($productList, 'json', ['groups' => 'getProducts']);
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }


    /*

    Récupère les détails d'un seul produit

    - URI : /api/products/{id}
    - Méthode HTTP : "Verbe" GET
    - Authentification : Accès libre

    */

    #[Route('/api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function getDetailProduct(Product $product, SerializerInterface $serializer): JsonResponse {
        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'getProducts']);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
