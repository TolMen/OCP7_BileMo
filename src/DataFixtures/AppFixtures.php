<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }


    public function load(ObjectManager $manager): void
    {

        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@bilemoapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);
        
        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@bilemoapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);


        
        // Produits
        $products = [
            ['iPhone 15 Pro Max', 1299.99, 'Le dernier modèle d\'Apple avec une puce A17, écran OLED 6,7 pouces, triple caméra 48MP, et une autonomie améliorée.'],
            ['Samsung Galaxy S23 Ultra', 1199.99, 'Téléphone phare de Samsung avec un écran AMOLED 6,8 pouces, processeur Exynos 2200, et un zoom optique 10x.'],
            ['Google Pixel 8 Pro', 1099.99, 'La meilleure expérience Android par Google avec un appareil photo incroyable et des mises à jour rapides.'],
            ['OnePlus 12 Pro', 999.99, 'Un téléphone haut de gamme avec écran 120Hz, Snapdragon 8 Gen 3, et une charge rapide de 100W.'],
            ['Xiaomi Mi 13 Pro', 899.99, 'Téléphone avec capteur Leica, Snapdragon 8 Gen 2, et un écran AMOLED de 6,73 pouces.'],
            ['Sony Xperia 1 V', 999.99, 'Smartphone avec un écran 4K HDR OLED de 6,5 pouces et des capacités photo avancées.'],
            ['Oppo Find X6 Pro', 949.99, 'Téléphone avec un design élégant, un processeur puissant, et une technologie de charge ultra-rapide.'],
            ['Huawei Mate 50 Pro', 1099.99, 'Sans Google mais avec des capacités impressionnantes, un appareil photo performant et un design premium.'],
            ['Asus ROG Phone 7', 999.99, 'Le meilleur smartphone pour gamers avec un processeur Snapdragon 8+ Gen 1, un écran AMOLED 165Hz, et des fonctionnalités gaming uniques.'],
            ['Vivo X90 Pro', 999.99, 'Téléphone axé sur la photographie avec un capteur d\'appareil photo de qualité professionnelle et une puissante batterie.'],
            ['Honor Magic5 Pro', 1099.99, 'Téléphone avec un écran OLED incurvé, une caméra de 50MP et une grande batterie de 5100mAh.']
        ];

        foreach ($products as [$name, $price, $description]) {
            $product = new Product();
            $product->setName($name);
            $product->setPrice($price);
            $product->setDescription($description);

            $manager->persist($product);
        }

        // Clients
        $clients = [
            'Boulanger',
            'Fnac',
            'Darty',
            'Amazon',
            'Cdiscount'
        ];

        foreach ($clients as $clientName) {
            $client = new Client();
            $client->setName($clientName);

            $manager->persist($client);
        }

        // Sauvegarder toutes les données
        $manager->flush();
    }
}
