
# API Bile Mo 📱

> **Ce projet a été réalisé dans le cadre de mon apprentissage pour le parcours d'OpenClassrooms (Développeur d'application PHP/Symfony).**  
> --> *Version : [English](README.md)* 📖 <br>
> --> *Documentation : [API Doc](https://github.com/TolMen/DocumentationAPIBileMo)* 📃

## 📖 Description

**API Bile Mo** est une API permettant à une entreprise spécialisée dans la vente de téléphones mobiles haut de gamme d'offrir un accès à son catalogue à travers une interface B2B (Business to Business). <br>
L'accès à l'API est réservé aux clients référencés qui doivent s'authentifier via un jeton JWT. <br>
Les données sont exposées en format JSON, respectant les niveaux 1, 2 et 3 du modèle Richardson. <br>
Les réponses sont mises en cache pour améliorer les performances des requêtes.

## 🚀 Fonctionnalités

- **Authentification avec JWT** : Accès sécurisé via un jeton JWT pour valider l'identité des utilisateurs.
- **Gestion du catalogue** : Accès au catalogue de produits de l'entreprise avec des informations détaillées sur les mobiles.
- **Cache des réponses** : Les réponses sont mises en cache pour optimiser les performances.
- **Architecture REST** : Conformité avec le modèle Richardson à trois niveaux.
- **Sécurisation des échanges** : Sécurisation des communications avec les clients via l'utilisation de JWT.

## 🚧 Installation

### Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- **PHP** (version 8.0 ou supérieure)
- **Symfony** (version 7 ou supérieure)
- **Composer**
- **Base de données MySQL**

### Étapes d'installation

1. **Cloner le dépôt**  
   Utilisez Git pour cloner le projet :  
   ```sh
   git clone https://github.com/TolMen/OCBileMo.git
   ```
2. **Installer les dépendances**  
   Exécutez la commande suivante pour installer les librairies nécessaires :  
   ```sh
   symfony console composer install
   ```

3. **Créer la base de données**  
   Modifiez le fichier `.env` pour configurer votre base de données :  
   ```sh
   DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
   ```
   Ensuite, exécutez les commandes suivantes :  
   ```sh
   symfony console doctrine:database:create
   php bin/console doctrine:schema:update --force
   ```

4. **Configurer JWT**  
   Installez le package JWT et générez les clés secrètes :  
   ```sh
   composer require lexik/jwt-authentication-bundle
   php bin/console lexik:jwt:generate-keypair
   ```
   Vérifiez que les chemins des fichiers de clés privée et publique sont correctement définis dans votre fichier `.env` :  
   ```sh
   JWT_PASSPHRASE=VotrePhraseSecrète
   ```

---

Merci d'explorer ce projet.  
N'hésitez pas à l'explorer, le modifier et l'améliorer ! ✨  

**Pour toute question ou collaboration, n'hésitez pas à me contacter ! 📩**

[TolMen](https://github.com/TolMen) - [LinkedIn](https://www.linkedin.com/in/jessyfrachisse/)
