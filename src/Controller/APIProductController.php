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

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $jsonProductList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $serializer) {

            $item->tag('productsCache');
            $item->expiresAfter(120);
            echo ("Les produits ne sont pas encore en cache !\n");

            $productList = $productRepository->findAllWithPagination($page, $limit);

            // Ajout des liens pour chaque produit
            $productsWithLinks = array_map(function ($product) use ($serializer) {
                $productArray = json_decode($serializer->serialize($product, 'json', SerializationContext::create()->setGroups(['getProducts'])), true);
                $productArray['Link'] = $product->getLinks();
                return $productArray;
            }, $productList);

            // Sérialisation du tableau en JSON avant de le retourner
            return $serializer->serialize($productsWithLinks, 'json', SerializationContext::create()->setGroups(['getProducts']));
          
        });

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

        $idCache = "getDetailProduct-" . $product->getId();

        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($product, $serializer) {

            $item->tag('productsCache');
            $item->expiresAfter(120);
            echo ("Le produit n'est pas encore en cache !\n");

            $context = SerializationContext::create()->setGroups(['getProducts']);

            return $serializer->serialize($product, 'json', $context);
        });

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
