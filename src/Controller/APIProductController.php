<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class APIProductController extends AbstractController
{

    /*

    Récupère la liste de tous les produits avec pagination et mise en cache

    - URI : /api/products
    - Méthode HTTP : "Verbe" GET
    - Authentification : Accès libre
    - Pagination défauts : Limite de 10 par page
    - Modifier la pagination : URI + ?page=X&limit=X (X etant un chiffre à choisir)
    
    */

    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        // Récupère la page et la limite à partir des paramètres de la requête, avec des valeurs par défaut
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // Génère un identifiant de cache basé sur la page et la limite
        $idCache = "getAllProducts-" . $page . "-" . $limit;

        // Récupère la liste des produits depuis le cache ou effectue une requête si non disponible
        $jsonProductList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $serializer) {
            // Marque l'élément de cache avec une étiquette et définit sa durée de vie
            $item->tag('productsCache');
            $item->expiresAfter(3600);

            // Récupère la liste paginée des produits
            $productList = $productRepository->findAllWithPagination($page, $limit);

            // Ajoute des liens pour chaque produit et les transforme en tableau
            $productsWithLinks = array_map(function ($product) use ($serializer) {
                $productArray = json_decode($serializer->serialize($product, 'json', SerializationContext::create()->setGroups(['getProducts'])), true);
                $productArray['link'] = $product->getLinks();
                return $productArray;
            }, $productList);

            // Sérialise le tableau des produits avec liens en JSON pour le retour
            return $serializer->serialize($productsWithLinks, 'json', SerializationContext::create()->setGroups(['getProducts']));
        });

        // Retourne la réponse JSON avec le code HTTP 200 (OK)
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /*

    Récupère les détails d'un seul produit

    - URI : /api/products/{id}
    - Méthode HTTP : "Verbe" GET
    - Authentification : Accès libre

    */

    #[Route('/api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function getDetailProduct(Product $product, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        // Génère un identifiant de cache basé sur l'identifiant du produit
        $idCache = "getDetailProduct-" . $product->getId();

        // Récupère les détails du produit depuis le cache ou effectue une requête si non disponible
        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($product, $serializer) {
            // Marque l'élément de cache avec une étiquette et définit sa durée de vie
            $item->tag('productsCache');
            $item->expiresAfter(3600);
            
            // Crée un contexte de sérialisation pour le produit
            $context = SerializationContext::create()->setGroups(['getProducts']);

            // Sérialise le produit en JSON pour le retour
            return $serializer->serialize($product, 'json', $context);
        });

        // Retourne la réponse JSON avec le code HTTP 200 (OK)
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
