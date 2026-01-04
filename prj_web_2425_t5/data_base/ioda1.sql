-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : lun. 21 avr. 2025 à 09:07
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ioda1`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `admin_messages`
--

INSERT INTO `admin_messages` (`id`, `sender_id`, `receiver_id`, `subject`, `message`, `is_read`, `created_at`) VALUES
(4, 1, 11, 'support client ', 't.as deja repondu a le client ', 1, '2025-04-13 16:21:07'),
(5, 11, 1, 'hoy ', 'oui je vais lui responde ', 1, '2025-04-13 16:22:29');

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `commentaire` text NOT NULL,
  `note` int DEFAULT NULL,
  `date_post` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `user_id`, `commentaire`, `note`, `date_post`) VALUES
(1, 2, 'Super service!', 5, '2025-02-25 22:00:00'),
(3, 2, 'Super expérience, tout était parfait !', 5, '2025-03-01 17:43:00'),
(4, 4, 'Très bon accueil, je recommande.', 4, '2025-03-01 17:43:00'),
(5, 15, 'Service impeccable, mais un peu long à l’arrivée.', 4, '2025-03-01 17:43:00'),
(6, 16, 'Très déçu, le service ne correspond pas à mes attentes.', 2, '2025-03-01 17:43:00'),
(7, 17, 'Excellent rapport qualité-prix, je reviendrai.', 5, '2025-03-01 17:43:00'),
(8, 18, 'Très bon moment passé, tout était parfait.', 5, '2025-03-01 17:43:00'),
(9, 19, 'Service correct, mais rien d’exceptionnel.', 3, '2025-03-01 17:43:00'),
(10, 20, 'Très bon accueil, mais le délai était un peu long.', 4, '2025-03-01 17:43:00'),
(11, 21, 'Expérience incroyable, je recommande vivement !', 5, '2025-03-01 17:43:00'),
(12, 22, 'Très déçu, le service était lent et peu aimable.', 2, '2025-03-01 17:43:00'),
(13, 23, 'Superbe expérience, tout était parfait.', 5, '2025-03-01 17:43:00'),
(14, 24, 'Service correct, mais manque de professionnalisme.', 3, '2025-03-01 17:43:00'),
(15, 25, 'Très bon moment, je reviendrai avec plaisir.', 4, '2025-03-01 17:43:00'),
(16, 26, 'Service impeccable, je recommande vivement.', 5, '2025-03-01 17:43:00'),
(17, 27, 'Très déçu, le service ne correspond pas à mes attentes.', 2, '2025-03-01 17:43:00'),
(18, 28, 'Super expérience, tout était parfait !', 5, '2025-03-01 17:43:00'),
(19, 29, 'Très bon accueil, je recommande.', 4, '2025-03-01 17:43:00'),
(20, 30, 'Service correct, mais rien d’exceptionnel.', 3, '2025-03-01 17:43:00'),
(21, 31, 'Très bon moment passé, tout était parfait.', 5, '2025-03-01 17:43:00'),
(22, 32, 'Service impeccable, mais un peu long à l’arrivée.', 4, '2025-03-01 17:43:00');

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

CREATE TABLE `commentaires` (
  `id` int NOT NULL,
  `destination_id` int NOT NULL,
  `nom_utilisateur` varchar(100) NOT NULL,
  `commentaire` text NOT NULL,
  `note` tinyint NOT NULL,
  `date_commentaire` datetime NOT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `contact_support`
--

CREATE TABLE `contact_support` (
  `user_id` int DEFAULT NULL,
  `message` text NOT NULL,
  `statut` enum('en attente','répondu') DEFAULT 'en attente',
  `date_post` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `prenom` varchar(50) DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contact_support`
--

INSERT INTO `contact_support` (`user_id`, `message`, `statut`, `date_post`, `prenom`, `nom`, `email`, `telephone`, `id`) VALUES
(11, 'Question sur une offre - le prix ?', 'en attente', '2025-04-13 13:12:31', NULL, NULL, 'admin@email.com', NULL, 5),
(1, 'Problème de réservation - j\'ai une problème de reservation', 'en attente', '2025-04-13 16:21:54', NULL, NULL, 'jade@gmail.com', NULL, 6);

-- --------------------------------------------------------

--
-- Structure de la table `contenus`
--

CREATE TABLE `contenus` (
  `id` int NOT NULL,
  `type` enum('texte','image','video','publicite') NOT NULL,
  `position` varchar(100) NOT NULL,
  `sous_position` varchar(50) DEFAULT 'default',
  `titre` varchar(255) DEFAULT NULL,
  `contenu` text,
  `url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `ordre` int DEFAULT '0',
  `est_actif` tinyint(1) DEFAULT '1',
  `style` text,
  `classes_css` varchar(255) DEFAULT NULL,
  `html_id` varchar(50) DEFAULT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `couleur_fond` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `destinations`
--

CREATE TABLE `destinations` (
  `id` int NOT NULL,
  `pays` varchar(100) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `images` varchar(255) DEFAULT NULL,
  `type_activites` enum('camping','auberge','randonnée','plage','...') NOT NULL,
  `activite_jeune` varchar(255) DEFAULT NULL,
  `budget_moyen` decimal(10,2) DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  `conseils` text,
  `endroits_visiter` text,
  `langue` varchar(100) DEFAULT NULL,
  `monnaie` varchar(50) DEFAULT NULL,
  `transport_commun` text,
  `peuples_culture` text,
  `info_pays` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `destinations`
--

INSERT INTO `destinations` (`id`, `pays`, `ville`, `images`, `type_activites`, `activite_jeune`, `budget_moyen`, `date_publication`, `conseils`, `endroits_visiter`, `langue`, `monnaie`, `transport_commun`, `peuples_culture`, `info_pays`) VALUES
(1, 'Espagne', 'Barcelone', 'img/barcelone.jpg', 'plage', 'Visiter la Sagrada Familia, découvrir le parc Güell, profiter de la plage de Barceloneta.', 300.00, '2025-04-01', 'Venez tôt pour éviter la foule à la Sagrada Familia.', 'Plage de Barceloneta, Parc Güell, Sagrada Familia', 'Espagnol, Catalan', 'Euro', 'Métro, tram', 'Culture catalane, festivals de rue, architecture moderniste', 'L\'Espagne est un pays diversifié avec des régions autonomes comme la Catalogne. Connue pour ses siestes, sa vie nocturne animée et sa cuisine méditerranéenne (tapas, paella). Climat méditerranéen chaud en été, doux en hiver. Superficie: 505 990 km². Capitale: Madrid.'),
(2, 'France', 'Alpes', 'img/alpes.jpg', 'randonnée', 'Randonnée au sommet du Mont Blanc, ski dans les stations de ski locales.', 450.00, '2025-04-02', 'N\'oubliez pas vos équipements de montagne, même pour les randonnées simples.', 'Mont Blanc, Chamonix, Stations de ski', 'Français', 'Euro', 'Voiture, téléphérique', 'Culture alpine, folklore, sports d\'hiver', 'La France est le pays le plus visité au monde, célèbre pour sa gastronomie (fromages, vins), son art et son histoire. Les Alpes françaises offrent certains des plus beaux paysages montagneux d\'Europe. Climat alpin avec des hivers froids et neigeux. Superficie: 643 801 km². Capitale: Paris.'),
(3, 'Hongrie', 'Budapest', 'img/budapest.jpg', 'plage', 'Se détendre dans les bains thermaux, explorer les ruines de la ville antique.', 200.00, '2025-04-03', 'Visitez les bains Széchenyi pour une expérience relaxante.', 'Bains Széchenyi, Parlement, Rue Váci', 'Hongrois', 'Forint', 'Métro, tram', 'Culture hongroise, bains thermaux, patrimoine historique', 'La Hongrie est un pays d\'Europe centrale sans accès à la mer, connu pour ses sources thermales (plus de 1 500 sources), son vin Tokaji et sa cuisine riche (goulasch). Budapest est divisée par le Danube en Buda (collines) et Pest (plaine). Climat continental. Superficie: 93 028 km². Capitale: Budapest.'),
(4, 'Bulgarie', 'Sofia', 'img/bulgarie.jpg', 'randonnée', 'Randonnée dans les montagnes Rila, visite du monastère de Rila.', 250.00, '2025-04-04', 'Vérifiez les prévisions météorologiques avant les randonnées de montagne.', 'Monastère de Rila, Montagne de Vitosha, Ville de Sofia', 'Bulgare', 'Lev', 'Bus, taxi', 'Culture bulgare, traditions orthodoxes, folklore', 'La Bulgarie est un pays des Balkans avec un riche patrimoine historique (ancien empire bulgare) et des paysages variés (montagnes, mer Noire). Connue pour son yoghourt, son huile de rose et ses monastères orthodoxes. Climat continental tempéré. Superficie: 110 994 km². Capitale: Sofia.'),
(5, 'France', 'Bretagne', 'img/bretagne.jpg', 'plage', 'Faire du surf à la plage de la Torche, visiter les phares bretons.', 180.00, '2025-04-05', 'Les conditions de surf sont meilleures en été.', 'Plage de la Torche, Phare de la Vieille', 'Français, Breton', 'Euro', 'Voiture, vélo', 'Culture bretonne, musique celtique, folklore maritime', 'La Bretagne est une région culturellement distincte avec sa propre langue (breton) et traditions. Connue pour ses crêpes, son cidre et ses légendes arthuriennes. Côtes sauvages et mégalithes (Carnac). Climat océanique pluvieux. Superficie de la région: 27 208 km². Chef-lieu: Rennes.'),
(6, 'Portugal', 'Lisbonne', 'img/lisbonne.jpg', 'plage', 'Visiter la tour de Belém, découvrir la vieille ville de l\'Alfama.', 220.00, '2025-04-06', 'Ne manquez pas les pasteis de nata dans le quartier de Belém.', 'Tour de Belém, Quartier de l\'Alfama, Musée Calouste Gulbenkian', 'Portugais', 'Euro', 'Tram, bus', 'Culture portugaise, musique fado, architecture manuéline', 'Le Portugal est le pays le plus à l\'ouest de l\'Europe continentale, connu pour son histoire maritime (grandes découvertes), son vin de Porto et ses plages atlantiques. Lisbonne est construite sur sept collines. Climat méditerranéen avec influence atlantique. Superficie: 92 090 km². Capitale: Lisbonne.'),
(7, 'France', 'Vosges', 'img/vosges.jpg', 'randonnée', 'Randonnée à la cascade du Nideck, découverte de la faune locale.', 180.00, '2025-04-07', 'Apportez de bonnes chaussures de randonnée pour les sentiers rocailleux.', 'Cascade du Nideck, Forêt des Vosges', 'Français', 'Euro', 'Voiture, vélo', 'Culture alsacienne, traditions, faune et flore des montagnes', 'Les Vosges sont une région montagneuse de l\'est de la France, connue pour ses forêts, ses lacs et son patrimoine alsacien. Spécialités: tarte flambée, munster. Climat semi-continental avec des hivers froids. Superficie du massif: environ 6 000 km². Ville principale: Épinal.'),
(8, 'Pays-Bas', 'Amsterdam', 'img/amsterdam.jpg', 'plage', 'Se promener en vélo le long des canaux, visiter le musée Van Gogh.', 250.00, '2025-04-08', 'Louez un vélo pour explorer la ville à votre rythme.', 'Musée Van Gogh, Quartier des canaux, Musée d\'Anne Frank', 'Néerlandais', 'Euro', 'Vélo, tram', 'Culture néerlandais, art, histoire des canaux', 'Les Pays-Bas sont un pays plat avec un quart de son territoire sous le niveau de la mer, connu pour ses moulins à vent, ses tulipes et son système de digues. Amsterdam compte plus de 100 km de canaux. Climat océanique tempéré. Superficie: 41 543 km². Capitale: Amsterdam (capitale constitutionnelle: La Haye).'),
(9, 'Belgique', 'Bruxelles', 'img/bruxelles.jpg', 'plage', 'Découvrir l\'Atomium, déguster des frites belges et de la bière artisanale.', 300.00, '2025-04-09', 'Goûtez les différentes bières belges locales dans les tavernes traditionnelles.', 'Atomium, Grand-Place, Manneken Pis', 'Néerlandais, Français, Allemand', 'Euro', 'Tram, bus', 'Culture belge, art nouveau, tradition des bières', 'La Belgique est un pays fédéral multilingue au cœur de l\'Europe, connu pour son chocolat, ses gaufres et ses 1 500 types de bière. Bruxelles est le siège de l\'UE et de l\'OTAN. Climat maritime tempéré. Superficie: 30 528 km². Capitale: Bruxelles.'),
(10, 'Espagne', 'Costa Brava', 'img/costa.jpg', 'plage', 'Se détendre sur les plages et visiter les villages de pêcheurs.', 350.00, '2025-04-10', 'Apportez des lunettes de plongée pour explorer les fonds marins.', 'Plage de Lloret de Mar, Cala Montjoi', 'Espagnol, Catalan', 'Euro', 'Voiture, vélo', 'Culture catalane, festivals, cuisine méditerranéenne', 'La Costa Brava (\"côte sauvage\") est une région côtière de Catalogne avec des criques rocheuses et des villages méditerranéens. Salvador Dalí y a vécu (théâtre-musée Dalí à Figueres). Climat méditerranéen chaud. Longueur de la côte: environ 200 km. Ville principale: Gérone.'),
(11, 'Portugal', 'Porto', 'img/porto.jpg', 'plage', 'Découvrir les caves à vin de Porto, se promener dans le quartier Ribeira.', 230.00, '2025-04-11', 'Dégustez le vin de Porto directement dans les caves.', 'Pont Dom-Luís, Quartier Ribeira, Caves de Porto', 'Portugais', 'Euro', 'Vélo, bus', 'Culture portugaise, vin, gastronomie locale', 'Porto est la deuxième ville du Portugal, connue pour son vin muté (porto) produit dans la vallée du Douro. Architecture typique avec maisons colorées. Climat océanique avec influence méditerranéenne. Ville classée au patrimoine mondial de l\'UNESCO. Population: environ 240 000 habitants.'),
(12, 'Croatie', 'Dubrovnik', 'img/croatie.jpg', 'plage', 'Se détendre sur les plages et visiter la vieille ville protégée par des remparts.', 300.00, '2025-04-12', 'Assurez-vous de visiter les remparts en fin de journée pour de belles vues sur la mer.', 'Vieille ville de Dubrovnik, Plages de Copacabana', 'Croate', 'Kuna croate', 'Bus, ferry', 'Culture croate, sites historiques, nature méditerranéenne', 'La Croatie est un pays d\'Europe du Sud-Est avec une côte adriatique très découpée (plus de 1 000 îles). Dubrovnik, \"la perle de l\'Adriatique\", est une cité médiévale fortifiée. Climat méditerranéen sur la côte. Superficie: 56 594 km². Capitale: Zagreb.'),
(13, 'Allemagne', 'Berlin', 'img/berlin.jpg', 'randonnée', 'Visiter le mur de Berlin, découvrir les musées sur l\'histoire de la ville.', 250.00, '2025-04-13', 'Prenez un guide pour mieux comprendre l\'histoire du mur de Berlin.', 'Mur de Berlin, Porte de Brandebourg', 'Allemand', 'Euro', 'Métro, bus', 'Culture allemande, histoire moderne, art contemporain', 'L\'Allemagne est le pays le plus peuplé de l\'UE, connu pour son histoire complexe, ses châteaux (Neuschwanstein) et sa bière (Oktoberfest). Berlin est une ville dynamique avec une scène artistique alternative. Climat tempéré. Superficie: 357 022 km². Capitale: Berlin.'),
(14, 'République Tchèque', 'Prague', 'img/prague.jpg', 'plage', 'Explorer le château de Prague, se promener sur le pont Charles.', 220.00, '2025-04-14', 'Ne manquez pas la bière tchèque dans l\'un des pubs traditionnels.', 'Château de Prague, Pont Charles', 'Tchèque', 'Couronne tchèque', 'Tram, bus', 'Culture tchèque, architecture médiévale, gastronomie', 'La République Tchèque est un pays d\'Europe centrale sans accès à la mer, connu pour ses châteaux (plus de 2 000), sa bière (Pilsner Urquell) et son cristal de Bohême. Prague a échappé aux destructions de la WWII. Climat continental. Superficie: 78 871 km². Capitale: Prague.'),
(15, 'Italie', 'Naples', 'img/naples.jpg', 'plage', 'Visiter Pompéi, déguster une pizza napolitaine authentique.', 270.00, '2025-04-15', 'Assurez-vous de goûter la vraie pizza à la napolitaine, une spécialité locale.', 'Pompéi, Vésuve, Plages de Posillipo', 'Italien', 'Euro', 'Voiture, métro', 'Culture italienne, histoire romaine, gastronomie', 'L\'Italie est un pays en forme de botte avec un riche patrimoine culturel (Renaissance) et culinaire (pasta, gelato). Naples est le berceau de la pizza, près des ruines de Pompéi et du Vésuve. Climat méditerranéen. Superficie: 301 340 km². Capitale: Rome.'),
(16, 'Grèce', 'Athènes', 'img/athenes.jpg', 'randonnée', 'Visiter l\'Acropole, se détendre sur les plages d\'Athènes.', 280.00, '2025-04-16', 'N\'oubliez pas de visiter le Parthénon au coucher du soleil pour une expérience magique.', 'Acropole, Parthénon, Plages d\'Athènes', 'Grec', 'Euro', 'Métro, bus', 'Culture grecque, histoire antique, cuisine méditerranéenne', 'La Grèce est le berceau de la démocratie et de la philosophie occidentale, avec des milliers d\'îles et des sites antiques. Athènes combine antiquité et vie urbaine moderne. Climat méditerranéen chaud. Superficie: 131 957 km². Capitale: Athènes.'),
(17, 'France', 'Ardèche', 'img/ardeche.jpg', 'randonnée', 'Faire du kayak sur l\'Ardèche, découvrir les grottes et les formations rocheuses.', 250.00, '2025-04-17', 'Réservez votre excursion en kayak à l\'avance pendant la haute saison.', 'Gorges de l\'Ardèche, Pont d\'Arc', 'Français', 'Euro', 'Voiture, canoë', 'Culture locale, nature, sport en plein air', 'L\'Ardèche est un département du sud-est de la France connu pour ses gorges spectaculaires et ses grottes préhistoriques (Chauvet). Produit des vins Côtes-du-Rhône. Climat méditerranéen dans le sud. Superficie: 5 529 km². Chef-lieu: Privas.'),
(18, 'Pays-Bas', 'Rotterdam', 'img/rotterdam.jpg', 'plage', 'Explorer l\'architecture moderne, visiter le marché couvert Markthal.', 240.00, '2025-04-18', 'Prenez un bateau pour une visite des ports historiques.', 'Markthal, Pont Erasmus, Quartier Delfshaven', 'Néerlandais', 'Euro', 'Tram, métro', 'Culture urbaine, design, histoire maritime', 'Rotterdam est le plus grand port d\'Europe, reconstruit après la WWII avec une architecture avant-gardiste (Maisons Cubes). Connu pour son art street (Kunsthal). Climat océanique. Deuxième ville des Pays-Bas. Population: environ 650 000 habitants.'),
(19, 'Italie', 'Rome', 'img/rome.jpg', 'randonnée', 'Visiter le Colisée, explorer le Forum romain, lancer une pièce dans la Fontaine de Trevi.', 350.00, '2025-04-19', 'Évitez les files d\'attente en réservant vos billets à l\'avance.', 'Colisée, Vatican, Fontaine de Trevi', 'Italien', 'Euro', 'Métro, bus', 'Culture romaine, art baroque, histoire antique', 'Rome, \"la Ville Éternelle\", fut le centre de l\'Empire romain et abrite le Vatican, centre de l\'Église catholique. Combine antiquité (Panthéon) et style baroque (Fontaine de Trevi). Climat méditerranéen. Superficie de la ville: 1 285 km². Capitale de l\'Italie.'),
(20, 'Autriche', 'Vienne', 'img/vienne.jpg', 'plage', 'Visiter le palais de Schönbrunn, assister à un concert de musique classique.', 320.00, '2025-04-20', 'Goûtez le fameux Sachertorte dans un café viennois.', 'Palais de Schönbrunn, Opéra national, Quartier des musées', 'Allemand', 'Euro', 'Métro, tram', 'Culture impériale, musique classique, art', 'L\'Autriche est un pays alpin au cœur de l\'Europe, ancien centre de l\'Empire austro-hongrois. Vienne est associée aux compositeurs (Mozart, Strauss) et à l\'architecture impériale (Hofburg). Climat continental. Superficie: 83 879 km². Capitale: Vienne.');

-- --------------------------------------------------------

--
-- Structure de la table `destination_images`
--

CREATE TABLE `destination_images` (
  `id` int NOT NULL,
  `destination_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `destination_images`
--

INSERT INTO `destination_images` (`id`, `destination_id`, `image_path`) VALUES
(1, 11, '67fb94124adf8_IMG_2937.AVIF');

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `offre_id` int NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `favoris`
--

INSERT INTO `favoris` (`id`, `user_id`, `offre_id`, `date_ajout`) VALUES
(6, 11, 3, '2025-04-11 12:36:38'),
(7, 11, 1, '2025-04-11 13:03:19'),
(10, 1, 1, '2025-04-12 11:01:50'),
(11, 11, 11, '2025-04-14 13:30:14'),
(15, 1, 3, '2025-04-16 14:16:27'),
(22, 1, 17, '2025-04-16 14:30:21');

-- --------------------------------------------------------

--
-- Structure de la table `forum`
--

CREATE TABLE `forum` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_post` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `forum`
--

INSERT INTO `forum` (`id`, `user_id`, `titre`, `message`, `date_post`) VALUES
(2, 1, 'Super offres de voyage', 'love this site ', '2025-04-11 08:06:28'),
(3, 1, 'Présentation', 'Salut à tous, je suis nouveau sur le forum !', '2025-04-11 08:18:16'),
(4, 2, 'Mise à jour du site', 'Quelqu’un a testé la dernière version ? Des avis ?', '2025-04-11 08:18:16'),
(5, 3, 'Apprendre PHP', 'Auriez-vous des bons tutos pour débuter en PHP ?', '2025-04-11 08:18:16'),
(6, 4, 'Ambiance générale', 'Franchement, j’adore la communauté ici !', '2025-04-11 08:18:16'),
(7, 5, 'Erreur 500', 'Je rencontre une erreur 500 sur mon site. Des idées ?', '2025-04-11 08:18:16'),
(8, 6, 'Choix de base de données', 'Vous conseillez MySQL ou PostgreSQL pour un projet perso ?', '2025-04-11 08:18:16'),
(9, 7, 'Débutant total', 'Je commence en dev web. Par où commencer selon vous ?', '2025-04-11 08:18:16'),
(10, 8, 'Partage de ressource', 'Voici un tuto super utile pour les formulaires HTML.', '2025-04-11 08:18:16'),
(11, 9, 'Merci !', 'Un grand merci à ceux qui prennent le temps de répondre.', '2025-04-11 08:18:16'),
(12, 10, 'Vos projets', 'Est-ce qu’il y a un endroit où on peut montrer nos projets ?', '2025-04-11 08:18:16');

-- --------------------------------------------------------

--
-- Structure de la table `groupes`
--

CREATE TABLE `groupes` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `createur_id` int NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupes`
--

INSERT INTO `groupes` (`id`, `nom`, `createur_id`, `date_creation`) VALUES
(1, 'Groupe des Aventuriers', 11, '2025-04-11 16:05:55');

-- --------------------------------------------------------

--
-- Structure de la table `groupes_utilisateurs`
--

CREATE TABLE `groupes_utilisateurs` (
  `id` int NOT NULL,
  `groupe_id` int NOT NULL,
  `user_id` int NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupes_utilisateurs`
--

INSERT INTO `groupes_utilisateurs` (`id`, `groupe_id`, `user_id`, `date_ajout`) VALUES
(1, 1, 2, '2025-04-11 16:07:41'),
(2, 1, 1, '2025-04-11 16:07:41'),
(3, 1, 11, '2025-04-11 16:07:41');

-- --------------------------------------------------------

--
-- Structure de la table `logements`
--

CREATE TABLE `logements` (
  `id` int NOT NULL,
  `destination_id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `type_logement` enum('auberge','camping','airbnb','hôtel','colocation') NOT NULL,
  `etoiles` int DEFAULT NULL,
  `capacite_max` int NOT NULL,
  `all_inclusive` tinyint(1) DEFAULT '0',
  `demi_pension` tinyint(1) DEFAULT '0',
  `petit_dejeuner` tinyint(1) DEFAULT '0',
  `wifi` tinyint(1) DEFAULT '1',
  `prix_nuit` decimal(10,2) NOT NULL,
  `activite_jeune` varchar(255) DEFAULT 'Non spécifié',
  `description` text,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_maj` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `equipements` text
) ;

--
-- Déchargement des données de la table `logements`
--

INSERT INTO `logements` (`id`, `destination_id`, `nom`, `type_logement`, `etoiles`, `capacite_max`, `all_inclusive`, `demi_pension`, `petit_dejeuner`, `wifi`, `prix_nuit`, `activite_jeune`, `description`, `date_creation`, `date_maj`, `equipements`) VALUES
(1, 1, 'Van Aventura', 'airbnb', NULL, 4, 0, 0, 0, 1, 39.99, 'Road trip libre', 'Van tout équipé pour explorer la côte espagnole. Lit double, kitchenette et toit ouvrant.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(2, 2, 'Refuge des Alpes', 'auberge', 2, 6, 0, 1, 1, 1, 29.99, 'Feu de camp', 'Refuge convivial pour sportifs avec vue imprenable sur le Mont Blanc. Salle commune avec cheminée.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(3, 3, 'Auberge Budapest Fun', 'auberge', 3, 10, 0, 0, 1, 1, 24.99, 'Sorties nocturnes', 'Auberge jeune à 10 min des bains Széchenyi. Terrasse sur le toit et bar communautaire.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(4, 4, 'Auberge Ski Chill', 'auberge', 2, 8, 0, 1, 1, 1, 34.99, 'Soirées raclette', 'Chalet chaleureux au pied des montagnes Rila. Espace détente avec sauna finlandais.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(5, 5, 'Camping Bretagne', 'camping', NULL, 6, 0, 0, 1, 1, 19.99, 'Plage et surf', 'Camping nature à 200m de la plage de la Torche. Location de planches et cours de surf disponibles.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(6, 6, 'Airbnb Lisbonne Center', 'airbnb', NULL, 3, 0, 0, 1, 1, 44.99, 'Soirées rooftop', 'Studio moderne au cœur du quartier de l\'Alfama. Terrasse avec vue sur le château Saint-Georges.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(7, 7, 'Cabane Vosges', 'airbnb', NULL, 4, 0, 1, 1, 1, 39.99, 'Randos forêt', 'Cabane en bois perchée avec vue sur la forêt vosgienne. Accès direct aux sentiers de randonnée.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(8, 8, 'Auberge Amsterdam Chill', 'auberge', 2, 8, 0, 0, 1, 1, 22.99, 'Sorties vélo', 'Auberge festive à 5 min de la gare centrale. Location de vélos et visites guidées incluses.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(9, 9, 'Auberge Bruxelles Jeunesse', 'auberge', 2, 6, 0, 0, 1, 1, 21.99, 'Activités gratuites', 'Logement jeune à deux pas de la Grand-Place. Soirées bières belges et dégustations organisées.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(10, 10, 'Camping Costa Brava', 'camping', NULL, 5, 0, 0, 1, 1, 18.99, 'Beach volley', 'Camping les pieds dans l\'eau avec accès privé à une crique. Terrains de beach-volley et animations.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(11, 11, 'Airbnb Porto Cool', 'airbnb', NULL, 2, 0, 0, 1, 1, 27.99, 'Bars et street art', 'Petit studio dans le quartier artistique de Ribeira. Décoration locale et vue sur le Douro.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(12, 12, 'Camping Croatie Fun', 'camping', NULL, 5, 0, 0, 1, 1, 17.99, 'Canoë', 'Camping ombragé à 15 min de la vieille ville. Location de canoës et excursions en mer organisées.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(13, 13, 'Airbnb Berlin Style', 'airbnb', NULL, 3, 0, 0, 1, 1, 33.99, 'Clubbing techno', 'Loft industriel dans le quartier branché de Kreuzberg. Guide des meilleurs clubs techno fourni.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(14, 14, 'Hôtel Budget Prague', 'hôtel', 3, 2, 0, 0, 1, 1, 36.99, 'Visites historiques', 'Petit hôtel charmant près du pont Charles. Visites guidées du château incluses dans le séjour.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(15, 15, 'Auberge Naples Chill', 'auberge', 2, 6, 0, 1, 1, 1, 26.99, 'Soirées pizza', 'Auberge avec terrasse panoramique sur le Vésuve. Cours de cuisine napolitaine organisés.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(16, 16, 'Airbnb Athènes Roof', 'airbnb', NULL, 3, 0, 0, 1, 1, 38.99, 'Soirées rooftop', 'Appartement avec terrasse toit offrant une vue imprenable sur l\'Acropole. Décoration cycladique.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(17, 17, 'Camping Ardèche Nature', 'camping', NULL, 6, 0, 1, 1, 1, 16.99, 'Kayak et escalade', 'Camping sauvage au bord de l\'Ardèche. Location de kayaks et parcours d\'escalade à proximité.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(18, 18, 'Auberge Rotterdam Urban', 'auberge', 2, 6, 0, 0, 1, 1, 28.99, 'Street art tours', 'Auberge design située dans un ancien entrepôt reconverti. Visites guidées du street art incluses.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(19, 19, 'Hôtel Rome Antique', 'hôtel', 3, 4, 0, 1, 1, 1, 42.99, 'Visites guidées', 'Hôtel de charme à 10 min du Colisée. Petit-déjeuner italien servi dans le jardin intérieur.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL),
(20, 20, 'Auberge Vienne Music', 'auberge', 2, 8, 0, 0, 1, 1, 31.99, 'Concerts classiques', 'Auberge musicale proche de l\'Opéra. Soirées concerts et dégustation de Sachertorte organisées.', '2025-04-10 09:46:58', '2025-04-10 09:46:58', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `logement_images`
--

CREATE TABLE `logement_images` (
  `id` int NOT NULL,
  `logement_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `ordre` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `logement_images`
--

INSERT INTO `logement_images` (`id`, `logement_id`, `image_url`, `ordre`) VALUES
(1, 1, 'img/van1.jpg', 1),
(2, 1, 'img/van2.jpg', 2),
(3, 1, 'img/van3.jpg', 3),
(4, 2, 'img/refuge1.jpg', 1),
(5, 2, 'img/refuge2.jpg', 2),
(6, 2, 'img/refuge3.jpg', 3),
(7, 3, 'img/budapest1.jpg', 1),
(8, 3, 'img/budapest2.jpg', 2),
(9, 3, 'img/budapest3.jpg', 3),
(10, 4, 'img/sofia1.jpg', 1),
(11, 4, 'img/sofia2.jpg', 2),
(12, 4, 'img/sofia3.jpg', 3),
(13, 5, 'img/bretagne1.jpg', 1),
(14, 5, 'img/bretagne2.jpg', 2),
(15, 5, 'img/bretagne3.jpg', 3),
(16, 6, 'img/lisbonne1.jpg', 1),
(17, 6, 'img/lisbonne2.jpg', 2),
(18, 6, 'img/lisbonne3.jpg', 3),
(19, 7, 'img/vosges1.jpg', 1),
(20, 7, 'img/vosges2.jpg', 2),
(21, 7, 'img/vosges3.jpg', 3),
(22, 8, 'img/amsterdam1.jpg', 1),
(23, 8, 'img/amsterdam2.jpg', 2),
(24, 8, 'img/amsterdam3.jpg', 3),
(25, 9, 'img/bruxelles1.jpg', 1),
(26, 9, 'img/bruxelles2.jpg', 2),
(27, 9, 'img/bruxelles3.jpg', 3),
(28, 10, 'img/costa1.jpg', 1),
(29, 10, 'img/costa2.jpg', 2),
(30, 10, 'img/costa3.jpg', 3),
(31, 11, 'img/porto1.jpg', 1),
(32, 11, 'img/porto2.jpg', 2),
(33, 11, 'img/porto3.jpg', 3),
(34, 12, 'img/dubrovnik1.jpg', 1),
(35, 12, 'img/dubrovnik2.jpg', 2),
(36, 12, 'img/dubrovnik3.jpg', 3),
(37, 13, 'img/berlin1.jpg', 1),
(38, 13, 'img/berlin2.jpg', 2),
(39, 13, 'img/berlin3.jpg', 3),
(40, 14, 'img/prague1.jpg', 1),
(41, 14, 'img/prague2.jpg', 2),
(42, 14, 'img/prague3.jpg', 3),
(43, 15, 'img/naples1.jpg', 1),
(44, 15, 'img/naples2.jpg', 2),
(45, 15, 'img/naples3.jpg', 3),
(46, 16, 'img/athenes1.jpg', 1),
(47, 16, 'img/athenes2.jpg', 2),
(48, 16, 'img/athenes3.jpg', 3),
(49, 17, 'img/ardeche1.jpg', 1),
(50, 17, 'img/ardeche2.jpg', 2),
(51, 17, 'img/ardeche3.jpg', 3),
(52, 18, 'img/rotterdam1.jpg', 1),
(53, 18, 'img/rotterdam2.jpg', 2),
(54, 18, 'img/rotterdam3.jpg', 3),
(55, 19, 'img/rome1.jpg', 1),
(56, 19, 'img/rome2.jpg', 2),
(57, 19, 'img/rome3.jpg', 3),
(58, 20, 'img/vienne1.jpg', 1),
(59, 20, 'img/vienne2.jpg', 2),
(60, 20, 'img/vienne3.jpg', 3);

-- --------------------------------------------------------

--
-- Structure de la table `offres`
--

CREATE TABLE `offres` (
  `id` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `destination_id` int NOT NULL,
  `logement_id` int NOT NULL,
  `type_transport` enum('avion','bus','train','covoiturage','voiture') NOT NULL,
  `duree_sejour` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `prix_groupe` decimal(10,2) DEFAULT NULL,
  `date_depart` date NOT NULL,
  `date_retour` date NOT NULL,
  `disponibilite` int NOT NULL DEFAULT '0',
  `images` varchar(255) DEFAULT NULL,
  `ancien_prix` decimal(10,2) DEFAULT NULL,
  `transport_aller_id` int DEFAULT NULL,
  `transport_retour_id` int DEFAULT NULL,
  `avantages` text,
  `groupe` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `offres`
--

INSERT INTO `offres` (`id`, `titre`, `description`, `destination_id`, `logement_id`, `type_transport`, `duree_sejour`, `prix`, `prix_groupe`, `date_depart`, `date_retour`, `disponibilite`, `images`, `ancien_prix`, `transport_aller_id`, `transport_retour_id`, `avantages`, `groupe`) VALUES
(1, 'Barcelone Street Culture', 'Expérience street art avec artiste local + soirée rooftop exclusif. Auberge design dans le quartier gothique avec terrasse collaboratrice.', 1, 1, 'covoiturage', 5, 349.99, 909.97, '2025-06-15', '2025-06-20', 4, 'img/barcelone_street.jpg', NULL, 29, 30, 'Pass culturel 20€, atelier graffiti inclus', NULL),
(2, 'Alpes Xtreme Challenge', 'Via ferrata + rafting + VTT descente. Refuge connecté avec spa et espace coworking montagnard.', 2, 2, 'train', 6, 429.99, 1117.97, '2025-07-10', '2025-07-16', -2, 'img/alpes_xtreme.jpg', NULL, 32, 33, 'Pack photo action, soirée fondue party', NULL),
(3, 'Budapest Spa Festival', '3 jours d accès illimité aux bains + pool parties. Pod hotel avec vue sur le Danube.', 3, 3, 'bus', 4, 239.99, 623.97, '2025-08-20', '2025-08-24', 1, 'img/budapest_spa.jpg', NULL, 35, 36, 'Goodies de bain offerts', NULL),
(4, 'Borovets Freestyle Week', 'Ski park + soirées igloo bar. Chalet gaming avec simulateur de ski et espace stream.', 4, 4, 'avion', 7, 499.99, 1299.97, '2025-01-05', '2025-01-12', 10, 'img/borovets_freestyle.jpg', NULL, 38, 39, 'Forfait remontées illimitées', NULL),
(5, 'Bretagne Zero Waste Surf', 'Surf écoresponsable + cleanwalks. Eco-camping avec ateliers upcycling et concerts acoustiques.', 5, 5, 'bus', 5, 219.99, 571.97, '2025-06-01', '2025-06-06', 15, 'img/bretagne_eco.jpg', NULL, 41, 42, 'Kit éco-surfeur offert', NULL),
(6, 'Lisbonne Digital Nomad', 'Coliving avec espace coworking + ateliers création de contenu. Tours photo secrets et sunset sessions.', 6, 6, 'avion', 7, 379.99, 987.97, '2025-05-01', '2025-05-08', 8, 'img/lisbonne_digital.jpg', NULL, 44, 45, 'Pass transports 1 semaine inclus', NULL),
(7, 'Vosges Bushcraft Camp', 'Stage survie douce + nuit en cabane sylvestre. Initiation trappeur et cuisine sauvage.', 7, 7, 'voiture', 4, 189.99, 493.97, '2025-09-15', '2025-09-19', 8, 'img/vosges_bushcraft.jpg', NULL, 47, 48, 'Couteau multi-fonctions offert', NULL),
(8, 'Amsterdam Underground', 'Visite des squats artistiques + atelier sérigraphie. Vélo customisé et carte des lieux secrets.', 8, 8, 'train', 5, 289.99, 753.97, '2025-08-10', '2025-08-15', 7, 'img/amsterdam_underground.jpg', NULL, 50, 51, 'Pass musées alternatifs', NULL),
(9, 'Bruxelles Comic Explosion', 'Parcours BD réalité augmentée + atelier création. Logement thématique et soirée jeux géante.', 9, 9, 'bus', 3, 169.99, 441.97, '2025-06-20', '2025-06-23', 17, 'img/bruxelles_comic.jpg', NULL, 53, 54, 'Exemplaire collector BD rare', NULL),
(10, 'Costa Brava Sail Week', 'Croisière en catamaran + beach parties exclusives. Cabines partagées style backpacker luxe.', 10, 10, 'bus', 6, 599.99, 1559.97, '2025-07-01', '2025-07-07', 5, 'img/costa_sail.jpg', NULL, 56, 57, 'Pack snorkeling premium', NULL),
(11, 'Porto Food Raiders', 'Tour des eateries clandestins + atelier pastel de nata. Auberge avec rooftop gastronomique.', 11, 11, 'avion', 4, 199.99, 519.97, '2025-05-12', '2025-05-16', 11, 'img/porto_food.jpg', NULL, 1, 2, 'Carnet de recettes secret', NULL),
(12, 'King s Landing Experience', 'Tour des lieux de tournage + atelier combat médiéval. Camping plage avec soirées thématiques.', 12, 12, 'bus', 5, 259.99, 675.97, '2025-07-05', '2025-07-10', 9, 'img/dubrovnik_got.jpg', NULL, 4, 5, 'Costume de personnage inclus', NULL),
(13, 'Berlin Techno Safari', 'Pass 3 clubs légendaires + atelier production musicale. Appart partagé style loft industriel.', 13, 13, 'train', 4, 279.99, 727.97, '2025-08-01', '2025-08-05', 6, 'img/berlin_techno.jpg', NULL, 7, 8, 'Playlist exclusive par DJ local', NULL),
(14, 'Prague Time Travel', 'Escape game grandeur nature dans la vieille ville + banquet médiéval. Auberge style château.', 14, 14, 'avion', 5, 229.99, 597.97, '2025-04-20', '2025-04-25', 11, 'img/prague_time.jpg', NULL, 10, 11, 'Costume d époque prêté', NULL),
(15, 'Naples Underground Secrets', 'Visite des tunnels clandestins + atelier pizza avec chef ancien. Auberge dans un palais restauré.', 15, 15, 'avion', 4, 179.99, 467.97, '2025-06-10', '2025-06-14', 14, 'img/naples_underground.jpg', NULL, 13, 14, 'Certificat de pizzaïolo', NULL),
(16, 'Olympian Games Revival', 'Parcours mythologique interactif + olympiades antiques. Hôtel avec piscine à colonnes grecques.', 16, 16, 'avion', 5, 269.99, 701.97, '2025-10-01', '2025-10-06', 8, 'img/athenes_mytho.jpg', NULL, 16, 17, 'Toge de cérémonie offerte', NULL),
(17, 'Ardèche Survivor', '48h en autonomie complète + parcours du combattant naturel. Bivouac sous les étoiles avec guide expert.', 17, 17, 'voiture', 3, 159.99, 415.97, '2025-05-20', '2025-05-23', 10, 'img/ardeche_survivor.jpg', NULL, 19, 20, 'Kit de survie premium', NULL),
(18, 'Rotterdam Future City', 'Visite des projets urbains futuristes + atelier de design. Cube house avec espace créatif partagé.', 18, 18, 'train', 4, 239.99, 623.97, '2025-09-01', '2025-09-05', 7, 'img/rotterdam_future.jpg', NULL, 21, 22, 'Maquette 3D de la ville', NULL),
(19, 'Gladiator Academy', 'Stage de combat antique au Colisée + banquet romain. Auberge style caserne militaire.', 19, 19, 'avion', 5, 299.99, 779.97, '2025-11-01', '2025-11-06', 6, 'img/rome_gladiator.jpg', NULL, 23, 24, 'Armure photo offerte', NULL),
(20, 'Vienna Music Hackers', 'Atelier sound design + visite des lieux secrets de Mozart. Auberge avec pianos partagés et studio d enregistrement.', 20, 20, 'avion', 4, 259.99, 675.97, '2025-12-10', '2025-12-14', 5, 'img/vienne_music.jpg', NULL, 26, 27, 'Clé USB de samples exclusifs', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` int NOT NULL,
  `reservation_id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `participants`
--

INSERT INTO `participants` (`id`, `reservation_id`, `nom`, `prenom`, `date_naissance`) VALUES
(1, 8, 'Dupont', 'Lucas', '1995-06-20'),
(2, 8, 'joao', 'nunes', '2025-02-15'),
(3, 8, 'antonio', 'costa', '2025-12-25'),
(4, 9, 'Admin', 'Alex', '1985-04-15'),
(5, 10, 'Admin', 'Alex', '1985-04-15'),
(6, 14, 'Marx', 'Jade', '2025-04-23');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `offre_id` int NOT NULL,
  `logement_id` int NOT NULL,
  `transport_aller_id` int NOT NULL,
  `transport_retour_id` int NOT NULL,
  `nbr_personnes` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `prix_total` decimal(10,2) GENERATED ALWAYS AS ((`prix_unitaire` * `nbr_personnes`)) STORED,
  `statut` enum('en attente','confirmé','annulé') DEFAULT 'en attente',
  `methode_paiement` enum('carte bancaire','paypal','virement','aucun') DEFAULT 'aucun',
  `date_paiement` datetime DEFAULT NULL,
  `date_reservation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_depart` date NOT NULL,
  `date_retour` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `offre_id`, `logement_id`, `transport_aller_id`, `transport_retour_id`, `nbr_personnes`, `prix_unitaire`, `statut`, `methode_paiement`, `date_paiement`, `date_reservation`, `date_depart`, `date_retour`) VALUES
(6, 1, 3, 3, 35, 36, 3, 623.97, 'confirmé', 'carte bancaire', NULL, '2025-04-10 13:50:53', '2025-08-20', '2025-08-24'),
(7, 1, 1, 1, 29, 30, 4, 244.99, 'confirmé', 'carte bancaire', NULL, '2025-04-10 13:56:36', '2025-06-15', '2025-06-20'),
(8, 2, 2, 2, 32, 33, 3, 300.99, 'confirmé', 'carte bancaire', NULL, '2025-04-11 06:52:45', '2025-07-10', '2025-07-16'),
(9, 11, 6, 6, 44, 45, 1, 379.99, 'confirmé', 'carte bancaire', NULL, '2025-04-11 16:02:29', '2025-05-01', '2025-05-08'),
(10, 11, 11, 11, 1, 2, 1, 199.99, 'confirmé', 'carte bancaire', NULL, '2025-04-13 16:15:09', '2025-05-12', '2025-05-16'),
(14, 1, 2, 2, 32, 33, 1, 429.99, 'confirmé', 'carte bancaire', NULL, '2025-04-13 16:18:03', '2025-07-10', '2025-07-16'),
(15, 10, 1, 1, 21, 22, 2, 150.00, 'confirmé', 'carte bancaire', NULL, '2023-01-10 09:30:00', '2023-02-01', '2023-02-05'),
(16, 10, 2, 2, 23, 24, 1, 200.00, 'confirmé', 'paypal', NULL, '2023-03-12 11:45:00', '2023-04-10', '2023-04-15'),
(17, 10, 3, 3, 25, 26, 3, 180.00, 'confirmé', 'virement', NULL, '2023-05-20 07:15:00', '2023-06-01', '2023-06-08'),
(18, 10, 4, 4, 27, 28, 2, 210.00, 'confirmé', 'carte bancaire', NULL, '2023-06-25 12:20:00', '2023-07-05', '2023-07-10'),
(19, 10, 5, 5, 29, 30, 4, 120.00, 'confirmé', 'carte bancaire', NULL, '2023-08-01 09:30:00', '2023-08-15', '2023-08-22'),
(20, 10, 6, 6, 31, 32, 1, 300.00, 'annulé', 'paypal', NULL, '2023-09-15 14:00:00', '2023-10-01', '2023-10-07'),
(21, 10, 7, 7, 33, 34, 2, 250.00, 'confirmé', 'carte bancaire', NULL, '2023-11-01 12:40:00', '2023-11-20', '2023-11-25'),
(22, 10, 8, 8, 35, 36, 3, 270.00, 'confirmé', 'paypal', NULL, '2023-12-12 09:00:00', '2023-12-24', '2023-12-30'),
(23, 10, 9, 9, 37, 38, 1, 199.99, 'confirmé', 'carte bancaire', NULL, '2024-01-05 08:00:00', '2024-01-10', '2024-01-15'),
(24, 10, 10, 10, 39, 40, 2, 180.00, 'confirmé', 'carte bancaire', NULL, '2024-02-18 07:00:00', '2024-03-01', '2024-03-05'),
(25, 10, 11, 11, 41, 42, 1, 310.00, 'confirmé', 'virement', NULL, '2024-03-15 14:30:00', '2024-04-10', '2024-04-15'),
(26, 10, 12, 12, 43, 44, 2, 160.00, 'confirmé', 'paypal', NULL, '2024-04-20 15:45:00', '2024-05-05', '2024-05-12'),
(27, 10, 13, 13, 45, 46, 3, 280.00, 'confirmé', 'carte bancaire', NULL, '2024-06-10 12:10:00', '2024-06-25', '2024-07-01'),
(28, 10, 14, 14, 47, 48, 2, 150.00, 'confirmé', 'paypal', NULL, '2024-07-01 08:00:00', '2024-07-15', '2024-07-22'),
(29, 10, 15, 15, 49, 50, 1, 350.00, 'confirmé', 'virement', NULL, '2024-08-03 07:30:00', '2024-08-20', '2024-08-26'),
(30, 10, 16, 16, 21, 22, 2, 175.00, 'confirmé', 'carte bancaire', NULL, '2024-09-09 11:00:00', '2024-09-20', '2024-09-25'),
(31, 10, 17, 17, 23, 24, 1, 300.00, 'annulé', 'aucun', NULL, '2024-10-10 10:00:00', '2024-10-22', '2024-10-30'),
(32, 10, 18, 18, 25, 26, 2, 265.00, 'confirmé', 'carte bancaire', NULL, '2024-11-11 15:20:00', '2024-11-20', '2024-11-27'),
(33, 10, 19, 19, 27, 28, 4, 190.00, 'confirmé', 'paypal', NULL, '2024-12-12 10:15:00', '2024-12-24', '2024-12-31'),
(34, 10, 20, 20, 29, 30, 1, 320.00, 'confirmé', 'virement', NULL, '2025-01-01 09:30:00', '2025-01-15', '2025-01-20'),
(35, 10, 1, 2, 31, 32, 2, 250.00, 'confirmé', 'carte bancaire', NULL, '2025-02-14 14:00:00', '2025-02-25', '2025-03-02'),
(36, 10, 3, 4, 33, 34, 3, 280.00, 'confirmé', 'paypal', NULL, '2025-03-01 10:30:00', '2025-03-10', '2025-03-15'),
(37, 10, 5, 6, 35, 36, 2, 225.00, 'confirmé', 'virement', NULL, '2025-03-15 11:45:00', '2025-03-25', '2025-04-01'),
(38, 10, 7, 8, 37, 38, 1, 190.00, 'confirmé', 'carte bancaire', NULL, '2025-04-01 12:15:00', '2025-04-10', '2025-04-13'),
(39, 10, 9, 10, 39, 40, 2, 310.00, 'confirmé', 'paypal', NULL, '2025-04-12 15:00:00', '2025-04-20', '2025-04-25');

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `classes_css` varchar(255) DEFAULT NULL,
  `toujours_afficher` tinyint(1) DEFAULT '0',
  `est_actif` tinyint(1) DEFAULT '1',
  `ordre` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `sections`
--

INSERT INTO `sections` (`id`, `nom`, `slug`, `titre`, `classes_css`, `toujours_afficher`, `est_actif`, `ordre`) VALUES
(1, 'Qui sommes-nous', 'about', NULL, NULL, 0, 1, 0),
(2, 'Offres', 'offers', NULL, NULL, 0, 1, 0),
(3, 'Best-sellers', 'bestsellers', NULL, NULL, 0, 1, 0),
(4, 'Destinations Futures', 'future-destinations', NULL, NULL, 0, 1, 0),
(5, 'Pourquoi Nous', 'why-us', NULL, NULL, 0, 1, 0),
(6, 'Paiement Flexible', 'flexible-payment', NULL, NULL, 0, 1, 0),
(7, 'Contact', 'contact', NULL, NULL, 0, 1, 0),
(8, 'Témoignages', 'testimonials', NULL, NULL, 0, 1, 0),
(9, 'Newsletter', 'newsletter', NULL, NULL, 0, 1, 0),
(10, 'Publicité', 'publicit--', NULL, NULL, 0, 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `support_reponses`
--

CREATE TABLE `support_reponses` (
  `id` int NOT NULL,
  `support_id` int NOT NULL,
  `message` text NOT NULL,
  `sender` enum('admin','user') NOT NULL,
  `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transports`
--

CREATE TABLE `transports` (
  `id` int NOT NULL,
  `type_transport` enum('avion','bus','train','covoiturage','voiture','ferry') NOT NULL,
  `origine` varchar(255) NOT NULL,
  `destination_id` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `depart` datetime NOT NULL,
  `arrivee` datetime NOT NULL,
  `duree` varchar(50) NOT NULL,
  `compagnie` varchar(255) DEFAULT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `type_trajet` enum('aller','retour','unique') NOT NULL,
  `classe` enum('economique','premium','affaires','premiere') DEFAULT 'economique',
  `bagage_inclus` tinyint(1) DEFAULT '0',
  `details` text,
  `offre_id` int DEFAULT NULL,
  `heure_depart` time DEFAULT NULL,
  `aeroport_gare` varchar(100) DEFAULT NULL
) ;

--
-- Déchargement des données de la table `transports`
--

INSERT INTO `transports` (`id`, `type_transport`, `origine`, `destination_id`, `prix`, `depart`, `arrivee`, `duree`, `compagnie`, `numero`, `type_trajet`, `classe`, `bagage_inclus`, `details`, `offre_id`, `heure_depart`, `aeroport_gare`) VALUES
(1, 'avion', 'Paris Orly', 11, 79.99, '2025-06-15 08:00:00', '2025-06-15 10:15:00', '2h15', 'Ryanair', 'FR1234', 'aller', 'economique', 0, 'Vol direct - Bagage payant', NULL, '07:00:00', 'Aéroport de Barcelone-El Prat (BCN)'),
(2, 'avion', 'Porto', 11, 79.99, '2025-06-22 18:30:00', '2025-06-22 20:45:00', '2h15', 'Ryanair', 'FR1235', 'retour', 'economique', 0, 'Vol direct - Bagage payant', NULL, '10:15:00', 'Aéroport de Barcelone-El Prat (BCN)'),
(3, 'train', 'Lisbonne', 11, 19.00, '2025-06-16 09:00:00', '2025-06-16 12:30:00', '3h30', 'CP', 'IC123', 'unique', 'economique', 1, 'Train panoramique le long du fleuve Douro', NULL, '15:30:00', 'Aéroport d’Amsterdam-Schiphol (AMS)'),
(4, 'avion', 'Paris CDG', 12, 129.00, '2025-06-15 11:30:00', '2025-06-15 14:15:00', '2h45', 'Croatia Airlines', 'OU456', 'aller', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '07:00:00', 'Aéroport d’Amsterdam-Schiphol (AMS)'),
(5, 'avion', 'Dubrovnik', 12, 129.00, '2025-06-22 15:45:00', '2025-06-22 18:30:00', '2h45', 'Croatia Airlines', 'OU457', 'retour', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '10:15:00', 'Aéroport de Rome Fiumicino (FCO)'),
(6, 'ferry', 'Split', 12, 35.00, '2025-06-16 08:00:00', '2025-06-16 12:30:00', '4h30', 'Jadrolinija', 'JD789', 'unique', 'economique', 0, 'Trajet maritime avec vue sur les îles', NULL, '15:30:00', 'Aéroport de Rome Fiumicino (FCO)'),
(7, 'avion', 'Paris Beauvais', 13, 59.99, '2025-06-15 06:45:00', '2025-06-15 08:30:00', '1h45', 'easyJet', 'U2456', 'aller', 'economique', 0, 'Vol low-cost - Bagage cabine payant', NULL, '07:00:00', 'Gare du Nord (TGV/Thalys)'),
(8, 'avion', 'Berlin', 13, 59.99, '2025-06-22 19:15:00', '2025-06-22 21:00:00', '1h45', 'easyJet', 'U2457', 'retour', 'economique', 0, 'Vol low-cost - Bagage cabine payant', NULL, '10:15:00', 'Gare du Nord (TGV/Thalys)'),
(9, 'bus', 'Prague', 13, 29.00, '2025-06-16 07:30:00', '2025-06-16 12:00:00', '4h30', 'FlixBus', 'FLB456', 'unique', 'economique', 1, 'Trajet confortable avec WiFi', NULL, '15:30:00', 'Aéroport Berlin-Brandenburg (BER)'),
(10, 'avion', 'Paris Orly', 14, 89.00, '2025-06-15 09:15:00', '2025-06-15 11:00:00', '1h45', 'Czech Airlines', 'OK123', 'aller', 'economique', 1, 'Vol direct - Bagage 20kg inclus', NULL, '07:00:00', 'Aéroport Berlin-Brandenburg (BER)'),
(11, 'avion', 'Prague', 14, 89.00, '2025-06-22 16:30:00', '2025-06-22 18:15:00', '1h45', 'Czech Airlines', 'OK124', 'retour', 'economique', 1, 'Vol direct - Bagage 20kg inclus', NULL, '10:15:00', 'Aéroport Humberto Delgado de Lisbonne (LIS)'),
(12, 'train', 'Vienne', 14, 25.00, '2025-06-16 08:00:00', '2025-06-16 11:30:00', '3h30', 'ČD', 'EC456', 'unique', 'economique', 0, 'Train direct avec paysages pittoresques', NULL, '15:30:00', 'Aéroport Humberto Delgado de Lisbonne (LIS)'),
(13, 'avion', 'Paris CDG', 15, 69.99, '2025-06-15 10:00:00', '2025-06-15 12:15:00', '2h15', 'Volotea', 'V71234', 'aller', 'economique', 0, 'Vol direct - Bagage payant', NULL, '07:00:00', 'Aéroport Václav Havel Prague (PRG)'),
(14, 'avion', 'Naples', 15, 69.99, '2025-06-22 17:45:00', '2025-06-22 20:00:00', '2h15', 'Volotea', 'V71235', 'retour', 'economique', 0, 'Vol direct - Bagage payant', NULL, '10:15:00', 'Aéroport Václav Havel Prague (PRG)'),
(15, 'train', 'Rome', 15, 15.00, '2025-06-16 07:30:00', '2025-06-16 09:15:00', '1h45', 'Trenitalia', 'FR123', 'unique', 'economique', 1, 'Train rapide Frecciarossa', NULL, '15:30:00', 'Aéroport international de Vienne-Schwechat (VIE)'),
(16, 'avion', 'Paris Orly', 16, 119.00, '2025-06-15 07:30:00', '2025-06-15 11:45:00', '4h15', 'Aegean Airlines', 'A3456', 'aller', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '07:00:00', 'Aéroport international de Vienne-Schwechat (VIE)'),
(17, 'avion', 'Athènes', 16, 119.00, '2025-06-22 18:00:00', '2025-06-22 22:15:00', '4h15', 'Aegean Airlines', 'A3457', 'retour', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '10:15:00', 'Aéroport international de Budapest-Ferenc Liszt (BUD)'),
(18, 'ferry', 'Santorini', 16, 45.00, '2025-06-16 08:30:00', '2025-06-16 14:00:00', '5h30', 'Blue Star Ferries', 'BS789', 'unique', 'economique', 0, 'Trajet maritime avec vue sur les îles', NULL, '15:30:00', 'Aéroport international de Budapest-Ferenc Liszt (BUD)'),
(19, 'train', 'Paris Gare de Lyon', 17, 45.00, '2025-06-15 06:15:00', '2025-06-15 10:30:00', '4h15', 'TER', 'TER456', 'unique', 'economique', 0, 'Changement à Valence', NULL, '07:00:00', 'Aéroport de Londres Heathrow (LHR)'),
(20, 'covoiturage', 'Lyon', 17, 20.00, '2025-06-16 09:00:00', '2025-06-16 12:30:00', '3h30', 'BlaBlaCar', 'BL789012', 'unique', 'economique', 0, 'Trajet direct vers Vallon-Pont-d’Arc', NULL, '10:15:00', 'Aéroport de Londres Heathrow (LHR)'),
(21, 'train', 'Paris Nord', 18, 55.00, '2025-06-15 08:00:00', '2025-06-15 11:30:00', '3h30', 'Thalys', 'THY5678', 'unique', 'economique', 1, 'Changement à Bruxelles', NULL, '15:30:00', 'Aéroport Adolfo Suárez Madrid-Barajas (MAD)'),
(22, 'bus', 'Amsterdam', 18, 12.00, '2025-06-16 10:00:00', '2025-06-16 11:15:00', '1h15', 'FlixBus', 'FLB789', 'unique', 'economique', 0, 'Trajet rapide entre les deux villes', NULL, '07:00:00', 'Aéroport Adolfo Suárez Madrid-Barajas (MAD)'),
(23, 'avion', 'Paris CDG', 19, 89.00, '2025-06-15 09:45:00', '2025-06-15 12:00:00', '2h15', 'Air France', 'AF1234', 'aller', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '10:15:00', 'Aéroport international d’Athènes Elefthérios-Venizélos (ATH)'),
(24, 'avion', 'Rome', 19, 89.00, '2025-06-22 19:30:00', '2025-06-22 21:45:00', '2h15', 'Air France', 'AF1235', 'retour', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '15:30:00', 'Aéroport international d’Athènes Elefthérios-Venizélos (ATH)'),
(25, 'train', 'Florence', 19, 22.00, '2025-06-16 07:00:00', '2025-06-16 09:30:00', '2h30', 'Trenitalia', 'FR456', 'unique', 'economique', 1, 'Train Frecciarossa avec WiFi', NULL, '07:00:00', 'Aéroport de Dubrovnik (DBV)'),
(26, 'avion', 'Paris Orly', 20, 99.00, '2025-06-15 08:30:00', '2025-06-15 10:45:00', '2h15', 'Austrian Airlines', 'OS123', 'aller', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '10:15:00', 'Aéroport de Dubrovnik (DBV)'),
(27, 'avion', 'Vienne', 20, 99.00, '2025-06-22 17:15:00', '2025-06-22 19:30:00', '2h15', 'Austrian Airlines', 'OS124', 'retour', 'economique', 1, 'Vol direct - Bagage 23kg inclus', NULL, '15:30:00', 'Aéroport Francisco Sá-Carneiro de Porto (OPO)'),
(28, 'train', 'Bratislava', 20, 12.00, '2025-06-16 08:00:00', '2025-06-16 09:30:00', '1h30', 'ÖBB', 'RJ789', 'unique', 'economique', 0, 'Train régional rapide', NULL, '07:00:00', 'Aéroport Francisco Sá-Carneiro de Porto (OPO)'),
(29, 'avion', 'Paris CDG', 1, 89.99, '2025-06-15 07:30:00', '2025-06-15 09:15:00', '1h45', 'Vueling', 'VY1234', 'aller', 'economique', 1, 'Vol direct - Bagage cabine inclus (10kg)', NULL, '10:15:00', 'Gare de Nice-Ville (TGV)'),
(30, 'avion', 'Barcelone', 1, 89.99, '2025-06-22 18:45:00', '2025-06-22 20:30:00', '1h45', 'Vueling', 'VY1235', 'retour', 'economique', 1, 'Vol direct - Bagage cabine inclus', NULL, '15:30:00', 'Gare de Nice-Ville (TGV)'),
(31, 'train', 'Gérone', 1, 12.00, '2025-06-16 08:00:00', '2025-06-16 09:15:00', '1h15', 'Renfe', 'R123', 'unique', 'economique', 0, 'Trajet régional rapide', NULL, '07:00:00', 'Aéroport de Séville-San Pablo (SVQ)'),
(32, 'train', 'Paris Gare de Lyon', 2, 59.00, '2025-06-15 06:30:00', '2025-06-15 10:45:00', '4h15', 'TER', 'TER 84521', 'aller', 'economique', 0, 'Changement à Lyon Part-Dieu', NULL, '10:15:00', 'Aéroport de Séville-San Pablo (SVQ)'),
(33, 'train', 'Chamonix', 2, 59.00, '2025-06-22 16:00:00', '2025-06-22 20:15:00', '4h15', 'TER', 'TER 84522', 'retour', 'economique', 0, 'Changement à Lyon Part-Dieu', NULL, '15:30:00', 'Aéroport Franz-Josef Strauss de Munich (MUC)'),
(34, 'bus', 'Annecy', 2, 8.00, '2025-06-16 09:30:00', '2025-06-16 11:00:00', '1h30', 'Ouibus', 'OUI456', 'unique', 'economique', 0, 'Trajet direct vers Chamonix', NULL, '07:00:00', 'Aéroport Franz-Josef Strauss de Munich (MUC)'),
(35, 'avion', 'Paris Orly', 3, 120.00, '2025-06-15 10:15:00', '2025-06-15 12:30:00', '2h15', 'Wizz Air', 'W6 2345', 'aller', 'economique', 0, 'Vol low-cost - Bagage payant', NULL, '10:15:00', 'Gare de Bruxelles-Midi'),
(36, 'avion', 'Budapest', 3, 120.00, '2025-06-22 19:00:00', '2025-06-22 21:15:00', '2h15', 'Wizz Air', 'W6 2346', 'retour', 'economique', 0, 'Vol low-cost - Bagage payant', NULL, '15:30:00', 'Gare de Bruxelles-Midi'),
(37, 'bus', 'Vienne', 3, 25.00, '2025-06-16 07:00:00', '2025-06-16 10:30:00', '3h30', 'FlixBus', 'FLB123', 'unique', 'economique', 1, 'Trajet confortable avec WiFi', NULL, '07:00:00', 'Aéroport de Stockholm Arlanda (ARN)'),
(38, 'avion', 'Paris CDG', 4, 135.00, '2025-06-15 13:45:00', '2025-06-15 17:30:00', '3h45', 'Air France', 'AF 678', 'aller', 'economique', 1, 'Escale à Vienne - Bagage 23kg inclus', NULL, '10:15:00', 'Aéroport de Stockholm Arlanda (ARN)'),
(39, 'avion', 'Sofia', 4, 135.00, '2025-06-22 14:15:00', '2025-06-22 18:00:00', '3h45', 'Air France', 'AF 679', 'retour', 'economique', 1, 'Escale à Vienne - Bagage 23kg inclus', NULL, '15:30:00', 'Aéroport de Milan Malpensa (MXP)'),
(40, 'train', 'Plovdiv', 4, 10.00, '2025-06-16 08:30:00', '2025-06-16 10:45:00', '2h15', 'BDZ', 'BDZ456', 'unique', 'economique', 0, 'Trajet local économique', NULL, '07:00:00', 'Aéroport de Milan Malpensa (MXP)'),
(41, 'train', 'Paris Montparnasse', 5, 45.00, '2025-06-15 07:20:00', '2025-06-15 10:05:00', '2h45', 'TGV', 'TGV 8765', 'aller', 'economique', 1, 'Direct jusqu\'à Quimper', NULL, '10:15:00', NULL),
(42, 'train', 'Quimper', 5, 45.00, '2025-06-22 17:30:00', '2025-06-22 20:15:00', '2h45', 'TGV', 'TGV 8766', 'retour', 'economique', 1, 'Direct jusqu\'à Paris', NULL, '15:30:00', NULL),
(43, 'bus', 'Brest', 5, 5.00, '2025-06-16 09:00:00', '2025-06-16 10:30:00', '1h30', 'BreizhGo', 'BG789', 'unique', 'economique', 0, 'Trajet local vers la côte', NULL, '07:00:00', NULL),
(44, 'avion', 'Paris Beauvais', 6, 49.99, '2025-06-15 06:00:00', '2025-06-15 08:15:00', '2h15', 'Ryanair', 'FR 3456', 'aller', 'economique', 0, 'Bagage cabine payant (40x20x25cm)', NULL, '10:15:00', NULL),
(45, 'avion', 'Lisbonne', 6, 49.99, '2025-06-22 21:00:00', '2025-06-22 23:15:00', '2h15', 'Ryanair', 'FR 3457', 'retour', 'economique', 0, 'Bagage cabine payant', NULL, '15:30:00', NULL),
(46, 'train', 'Porto', 6, 15.00, '2025-06-16 08:00:00', '2025-06-16 11:30:00', '3h30', 'CP', 'IC456', 'unique', 'economique', 1, 'Trajet panoramique le long de la côte', NULL, '07:00:00', NULL),
(47, 'train', 'Paris Est', 7, 32.00, '2025-06-15 08:05:00', '2025-06-15 11:20:00', '3h15', 'TER', 'TER 4567', 'aller', 'economique', 0, 'Changement à Nancy', NULL, '10:15:00', NULL),
(48, 'train', 'Épinal', 7, 32.00, '2025-06-22 16:40:00', '2025-06-22 19:55:00', '3h15', 'TER', 'TER 4568', 'retour', 'economique', 0, 'Changement à Nancy', NULL, '15:30:00', NULL),
(49, 'bus', 'Strasbourg', 7, 7.00, '2025-06-16 10:00:00', '2025-06-16 12:30:00', '2h30', 'Fluo', 'FLU123', 'unique', 'economique', 0, 'Trajet vers Gérardmer', NULL, '07:00:00', NULL),
(50, 'train', 'Paris Nord', 8, 35.00, '2025-06-15 07:25:00', '2025-06-15 10:45:00', '3h20', 'Thalys', 'THY 9876', 'aller', 'economique', 1, 'Direct - WiFi gratuit', NULL, '10:15:00', NULL),
(51, 'train', 'Amsterdam', 8, 35.00, '2025-06-22 18:30:00', '2025-06-22 21:50:00', '3h20', 'Thalys', 'THY 9877', 'retour', 'economique', 1, 'Direct - WiFi gratuit', NULL, '15:30:00', NULL),
(52, 'bus', 'Rotterdam', 8, 10.00, '2025-06-16 09:00:00', '2025-06-16 10:15:00', '1h15', 'FlixBus', 'FLB456', 'unique', 'economique', 0, 'Trajet rapide entre villes', NULL, '07:00:00', NULL),
(53, 'train', 'Paris Nord', 9, 25.00, '2025-06-15 08:15:00', '2025-06-15 09:45:00', '1h30', 'Thalys', 'THY 1234', 'aller', 'economique', 1, 'Trajet le plus rapide', NULL, '10:15:00', NULL),
(54, 'train', 'Bruxelles', 9, 25.00, '2025-06-22 19:00:00', '2025-06-22 20:30:00', '1h30', 'Thalys', 'THY 1235', 'retour', 'economique', 1, 'Trajet le plus rapide', NULL, '15:30:00', NULL),
(55, 'bus', 'Anvers', 9, 8.00, '2025-06-16 10:30:00', '2025-06-16 11:45:00', '1h15', 'De Lijn', 'DL789', 'unique', 'economique', 0, 'Trajet local en Flandre', NULL, '07:00:00', NULL),
(56, 'avion', 'Paris Orly', 10, 65.00, '2025-06-15 09:30:00', '2025-06-15 11:15:00', '1h45', 'Transavia', 'TO 6789', 'aller', 'economique', 0, 'Vol direct - Bagage payant', NULL, '10:15:00', NULL),
(57, 'avion', 'Gérone', 10, 65.00, '2025-06-22 17:30:00', '2025-06-22 19:15:00', '1h45', 'Transavia', 'TO 6790', 'retour', 'economique', 0, 'Vol direct - Bagage payant', NULL, '15:30:00', NULL),
(58, 'bus', 'Barcelone', 10, 18.00, '2025-06-16 08:00:00', '2025-06-16 10:30:00', '2h30', 'Sagalés', 'SG 789', 'unique', 'economique', 1, 'Arrêt à Gérone', NULL, '07:00:00', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `gender` enum('M','F','Autre') NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `access_level` enum('user','admin') DEFAULT 'user',
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('actif','suspendu','désactivé') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `gender`, `date_naissance`, `adresse`, `telephone`, `profile_picture`, `access_level`, `date_inscription`, `derniere_connexion`, `statut`) VALUES
(1, 'Marx', 'Jade', 'jade@gmail.com', '12345', 'F', '2025-04-23', NULL, NULL, NULL, 'admin', '2025-04-10 12:27:57', '2025-04-10 12:27:57', 'actif'),
(2, 'Dupont', 'Lucas', 'lucas.dupont@email.com', '12345', 'M', '1995-06-20', '10 rue des Lilas, Paris', '0612345678', 'lucas.jpg', 'user', '2023-01-15 09:30:00', '2023-04-01 12:15:00', 'actif'),
(3, 'Martin', 'Sophie', 'sophie.martin@email.com', '12345', 'F', '1998-11-02', '24 avenue Foch, Lyon', '0623456789', 'sophie.png', 'user', '2023-02-10 08:00:00', '2023-04-02 10:00:00', 'actif'),
(4, 'Durand', 'Mehdi', 'mehdi.durand@email.com', '12345', 'M', '2000-03-18', '5 rue du Marché, Lille', '0645678901', 'mehdi.jpg', 'user', '2023-03-05 07:45:00', '2023-04-03 14:20:00', 'actif'),
(5, 'Bernard', 'Julie', 'julie.bernard@email.com', '12345', 'F', '1992-07-14', '5 impasse des Cerisiers, Marseille', '0634567890', 'julie.jpeg', 'user', '2023-01-20 12:25:00', '2023-04-01 07:00:00', 'actif'),
(6, 'Petit', 'Thomas', 'thomas.petit@email.com', '12345', 'M', '1994-09-30', '12 boulevard Haussmann, Paris', '0654321098', 'thomas.jpg', 'user', '2023-02-28 16:00:00', '2023-04-05 16:15:00', 'actif'),
(7, 'Leroy', 'Claire', 'claire.leroy@email.com', '12345', 'F', '1997-01-05', '8 rue Victor Hugo, Lille', '0678901234', 'claire.png', 'user', '2023-03-15 13:50:00', '2023-04-02 09:30:00', 'actif'),
(8, 'Roux', 'Antoine', 'antoine.roux@email.com', '12345', 'M', '1993-12-01', '15 chemin des Vignes, Toulouse', '0665432198', 'antoine.jpeg', 'user', '2023-01-10 06:40:00', '2023-04-06 06:00:00', 'actif'),
(9, 'Fontaine', 'Emma', 'emma.fontaine@email.com', '12345', 'F', '1999-08-22', '17 rue Pasteur, Bordeaux', '0688888888', 'emma.jpg', 'user', '2023-03-22 18:00:00', '2023-04-04 15:45:00', 'actif'),
(10, 'Moreau', 'Jules', 'jules.moreau@email.com', '12345', 'M', '1996-05-10', '11 place Bellecour, Lyon', '0678991234', 'jules.jpg', 'user', '2023-02-01 11:20:00', '2023-04-05 08:10:00', 'actif'),
(11, 'Admin', 'Alex', 'admin@email.com', '12345', 'Autre', '1985-04-15', '1 place de l’Hôtel de Ville, Paris', '0677777777', 'admin.jpg', 'admin', '2023-01-01 07:00:00', '2023-04-07 07:30:00', 'actif'),
(12, 'Costa', 'Waldyr', 'costa@gmail.com', '$2y$10$ksdVnHPEGdDRgxrnwnW6zOBIXHygf6plBVB/oSM.Ebjv/UM6Nd7pa', 'M', '1999-02-12', 'rue bruxelles 201', '07895623147', NULL, 'admin', '2025-04-13 12:20:12', '2025-04-13 12:20:12', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `vols`
--

CREATE TABLE `vols` (
  `id` int NOT NULL,
  `origine` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `depart` date NOT NULL,
  `heure_depart` time NOT NULL,
  `heure_arrivee` time NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `type` enum('aller','retour') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vols`
--

INSERT INTO `vols` (`id`, `origine`, `destination`, `depart`, `heure_depart`, `heure_arrivee`, `prix`, `type`) VALUES
(1, 'Bruxelles', 'Barcelone', '2025-06-10', '08:00:00', '10:00:00', 120.00, 'aller'),
(2, 'Bruxelles', 'Alpes', '2025-12-20', '10:30:00', '12:00:00', 150.00, 'aller'),
(3, 'Bruxelles', 'Budapest', '2025-05-15', '07:45:00', '09:30:00', 95.00, 'aller'),
(4, 'Bruxelles', 'Sofia', '2025-06-05', '06:30:00', '09:15:00', 105.00, 'aller'),
(5, 'Bruxelles', 'Bretagne', '2025-07-01', '09:00:00', '10:30:00', 80.00, 'aller'),
(6, 'Bruxelles', 'Lisbonne', '2025-06-18', '11:00:00', '13:30:00', 130.00, 'aller'),
(7, 'Bruxelles', 'Vosges', '2025-08-01', '13:00:00', '14:30:00', 75.00, 'aller'),
(8, 'Bruxelles', 'Amsterdam', '2025-06-15', '07:00:00', '08:45:00', 110.00, 'aller'),
(9, 'Bruxelles', 'Costa Brava', '2025-06-12', '08:30:00', '11:15:00', 140.00, 'aller'),
(10, 'Bruxelles', 'Porto', '2025-06-08', '10:00:00', '12:30:00', 125.00, 'aller'),
(11, 'Bruxelles', 'Dubrovnik', '2025-06-20', '09:15:00', '11:45:00', 160.00, 'aller'),
(12, 'Bruxelles', 'Berlin', '2025-05-25', '07:30:00', '09:00:00', 100.00, 'aller'),
(13, 'Bruxelles', 'Prague', '2025-06-03', '06:00:00', '08:00:00', 110.00, 'aller'),
(14, 'Bruxelles', 'Naples', '2025-06-14', '11:00:00', '13:00:00', 135.00, 'aller'),
(15, 'Bruxelles', 'Athènes', '2025-06-25', '08:00:00', '11:45:00', 175.00, 'aller'),
(16, 'Bruxelles', 'Ardèche', '2025-07-10', '14:00:00', '15:30:00', 70.00, 'aller'),
(17, 'Bruxelles', 'Rotterdam', '2025-06-01', '09:30:00', '11:00:00', 105.00, 'aller'),
(18, 'Bruxelles', 'Rome', '2025-05-28', '10:45:00', '13:15:00', 150.00, 'aller'),
(19, 'Bruxelles', 'Vienne', '2025-06-19', '07:00:00', '09:30:00', 145.00, 'aller'),
(20, 'Barcelone', 'Bruxelles', '2025-06-17', '10:30:00', '12:30:00', 120.00, 'retour'),
(21, 'Alpes', 'Bruxelles', '2025-12-27', '13:00:00', '14:30:00', 150.00, 'retour'),
(22, 'Budapest', 'Bruxelles', '2025-05-22', '10:00:00', '11:45:00', 95.00, 'retour'),
(23, 'Sofia', 'Bruxelles', '2025-06-12', '09:45:00', '12:30:00', 105.00, 'retour'),
(24, 'Bretagne', 'Bruxelles', '2025-07-08', '11:00:00', '12:30:00', 80.00, 'retour'),
(25, 'Lisbonne', 'Bruxelles', '2025-06-25', '14:30:00', '17:00:00', 130.00, 'retour'),
(26, 'Vosges', 'Bruxelles', '2025-08-08', '15:00:00', '16:30:00', 75.00, 'retour'),
(27, 'Amsterdam', 'Bruxelles', '2025-06-22', '09:00:00', '10:45:00', 110.00, 'retour'),
(28, 'Costa Brava', 'Bruxelles', '2025-06-19', '11:30:00', '14:15:00', 140.00, 'retour'),
(29, 'Porto', 'Bruxelles', '2025-06-15', '13:00:00', '15:30:00', 125.00, 'retour'),
(30, 'Dubrovnik', 'Bruxelles', '2025-06-27', '12:00:00', '14:30:00', 160.00, 'retour'),
(31, 'Berlin', 'Bruxelles', '2025-06-01', '10:00:00', '11:30:00', 100.00, 'retour'),
(32, 'Prague', 'Bruxelles', '2025-06-10', '09:00:00', '11:00:00', 110.00, 'retour'),
(33, 'Naples', 'Bruxelles', '2025-06-21', '13:30:00', '15:30:00', 135.00, 'retour'),
(34, 'Athènes', 'Bruxelles', '2025-07-02', '12:00:00', '15:45:00', 175.00, 'retour'),
(35, 'Ardèche', 'Bruxelles', '2025-07-17', '16:00:00', '17:30:00', 70.00, 'retour'),
(36, 'Rotterdam', 'Bruxelles', '2025-06-08', '11:30:00', '13:00:00', 105.00, 'retour'),
(37, 'Rome', 'Bruxelles', '2025-06-04', '13:30:00', '16:00:00', 150.00, 'retour'),
(38, 'Vienne', 'Bruxelles', '2025-06-26', '10:00:00', '12:30:00', 145.00, 'retour');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Index pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Index pour la table `contact_support`
--
ALTER TABLE `contact_support`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `contenus`
--
ALTER TABLE `contenus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `position` (`position`);

--
-- Index pour la table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `destination_images`
--
ALTER TABLE `destination_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`user_id`,`offre_id`),
  ADD KEY `offre_id` (`offre_id`);

--
-- Index pour la table `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `groupes`
--
ALTER TABLE `groupes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `createur_id` (`createur_id`);

--
-- Index pour la table `groupes_utilisateurs`
--
ALTER TABLE `groupes_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `logements`
--
ALTER TABLE `logements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Index pour la table `logement_images`
--
ALTER TABLE `logement_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logement_id` (`logement_id`);

--
-- Index pour la table `offres`
--
ALTER TABLE `offres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `logement_id` (`logement_id`),
  ADD KEY `transport_aller_id` (`transport_aller_id`),
  ADD KEY `transport_retour_id` (`transport_retour_id`);

--
-- Index pour la table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `offre_id` (`offre_id`),
  ADD KEY `logement_id` (`logement_id`),
  ADD KEY `transport_aller_id` (`transport_aller_id`),
  ADD KEY `transport_retour_id` (`transport_retour_id`);

--
-- Index pour la table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `support_reponses`
--
ALTER TABLE `support_reponses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_id` (`support_id`);

--
-- Index pour la table `transports`
--
ALTER TABLE `transports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `depart` (`depart`),
  ADD KEY `type_transport` (`type_transport`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `vols`
--
ALTER TABLE `vols`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contact_support`
--
ALTER TABLE `contact_support`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `contenus`
--
ALTER TABLE `contenus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `destination_images`
--
ALTER TABLE `destination_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `forum`
--
ALTER TABLE `forum`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `groupes`
--
ALTER TABLE `groupes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `groupes_utilisateurs`
--
ALTER TABLE `groupes_utilisateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `logements`
--
ALTER TABLE `logements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `logement_images`
--
ALTER TABLE `logement_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT pour la table `offres`
--
ALTER TABLE `offres`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `support_reponses`
--
ALTER TABLE `support_reponses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transports`
--
ALTER TABLE `transports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `vols`
--
ALTER TABLE `vols`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD CONSTRAINT `admin_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contenus`
--
ALTER TABLE `contenus`
  ADD CONSTRAINT `contenus_ibfk_1` FOREIGN KEY (`position`) REFERENCES `sections` (`slug`);

--
-- Contraintes pour la table `destination_images`
--
ALTER TABLE `destination_images`
  ADD CONSTRAINT `destination_images_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum`
--
ALTER TABLE `forum`
  ADD CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupes`
--
ALTER TABLE `groupes`
  ADD CONSTRAINT `groupes_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `groupes_utilisateurs`
--
ALTER TABLE `groupes_utilisateurs`
  ADD CONSTRAINT `groupes_utilisateurs_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes` (`id`),
  ADD CONSTRAINT `groupes_utilisateurs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `logements`
--
ALTER TABLE `logements`
  ADD CONSTRAINT `logements_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `logement_images`
--
ALTER TABLE `logement_images`
  ADD CONSTRAINT `logement_images_ibfk_1` FOREIGN KEY (`logement_id`) REFERENCES `logements` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offres`
--
ALTER TABLE `offres`
  ADD CONSTRAINT `offres_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`),
  ADD CONSTRAINT `offres_ibfk_2` FOREIGN KEY (`logement_id`) REFERENCES `logements` (`id`),
  ADD CONSTRAINT `offres_ibfk_3` FOREIGN KEY (`transport_aller_id`) REFERENCES `transports` (`id`),
  ADD CONSTRAINT `offres_ibfk_4` FOREIGN KEY (`transport_retour_id`) REFERENCES `transports` (`id`);

--
-- Contraintes pour la table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`logement_id`) REFERENCES `logements` (`id`),
  ADD CONSTRAINT `reservations_ibfk_4` FOREIGN KEY (`transport_aller_id`) REFERENCES `transports` (`id`),
  ADD CONSTRAINT `reservations_ibfk_5` FOREIGN KEY (`transport_retour_id`) REFERENCES `transports` (`id`);

--
-- Contraintes pour la table `support_reponses`
--
ALTER TABLE `support_reponses`
  ADD CONSTRAINT `support_reponses_ibfk_1` FOREIGN KEY (`support_id`) REFERENCES `contact_support` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transports`
--
ALTER TABLE `transports`
  ADD CONSTRAINT `transports_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
