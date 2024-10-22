-- Adminer 4.8.1 MySQL 9.0.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `client`;
CREATE TABLE `client` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `client` (`id`, `name`) VALUES
(1,	'Boulanger'),
(2,	'Fnac'),
(3,	'Darty'),
(4,	'Amazon'),
(5,	'Cdiscount');

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product` (`id`, `name`, `price`, `description`) VALUES
(1,	'iPhone 15 Pro Max',	1299.99,	'Le dernier modèle d\'Apple avec une puce A17, écran OLED 6,7 pouces, triple caméra 48MP, et une autonomie améliorée.'),
(2,	'Samsung Galaxy S23 Ultra',	1199.99,	'Téléphone phare de Samsung avec un écran AMOLED 6,8 pouces, processeur Exynos 2200, et un zoom optique 10x.'),
(3,	'Google Pixel 8 Pro',	1099.99,	'La meilleure expérience Android par Google avec un appareil photo incroyable et des mises à jour rapides.'),
(4,	'OnePlus 12 Pro',	999.99,	'Un téléphone haut de gamme avec écran 120Hz, Snapdragon 8 Gen 3, et une charge rapide de 100W.'),
(5,	'Xiaomi Mi 13 Pro',	899.99,	'Téléphone avec capteur Leica, Snapdragon 8 Gen 2, et un écran AMOLED de 6,73 pouces.'),
(6,	'Sony Xperia 1 V',	999.99,	'Smartphone avec un écran 4K HDR OLED de 6,5 pouces et des capacités photo avancées.'),
(7,	'Oppo Find X6 Pro',	949.99,	'Téléphone avec un design élégant, un processeur puissant, et une technologie de charge ultra-rapide.'),
(8,	'Huawei Mate 50 Pro',	1099.99,	'Sans Google mais avec des capacités impressionnantes, un appareil photo performant et un design premium.'),
(9,	'Asus ROG Phone 7',	999.99,	'Le meilleur smartphone pour gamers avec un processeur Snapdragon 8+ Gen 1, un écran AMOLED 165Hz, et des fonctionnalités gaming uniques.'),
(10,	'Vivo X90 Pro',	999.99,	'Téléphone axé sur la photographie avec un capteur d\'appareil photo de qualité professionnelle et une puissante batterie.'),
(11,	'Honor Magic5 Pro',	1099.99,	'Téléphone avec un écran OLED incurvé, une caméra de 50MP et une grande batterie de 5100mAh.');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  KEY `IDX_8D93D64919EB6921` (`client_id`),
  CONSTRAINT `FK_8D93D64919EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `client_id`) VALUES
(1,	'admin@bilemoapi.com',	'[\"ROLE_USER\"]',	'$2y$13$IoOmqPJgC5t2GWdgNCKo5O6VYYim4.f5Tpo3nhW6/dwvsZUWiI7YW',	NULL);

-- 2024-10-21 12:46:11
