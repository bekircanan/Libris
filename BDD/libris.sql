-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 20 jan. 2025 à 07:45
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `libris`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnement`
--

DROP TABLE IF EXISTS `abonnement`;
CREATE TABLE IF NOT EXISTS `abonnement` (
  `id_abonnement` int NOT NULL AUTO_INCREMENT,
  `prix` double NOT NULL,
  `nom_abonnement` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_abonnement`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `abonnement`
--

INSERT INTO `abonnement` (`id_abonnement`, `prix`, `nom_abonnement`) VALUES
(1, 0, 'Pass Jeune'),
(3, 0, 'Pass Culture'),
(4, 18, 'Pass Lib');

-- --------------------------------------------------------

--
-- Structure de la table `achat_ebook`
--

DROP TABLE IF EXISTS `achat_ebook`;
CREATE TABLE IF NOT EXISTS `achat_ebook` (
  `id_achat` int NOT NULL AUTO_INCREMENT,
  `id_util` int NOT NULL,
  `id_ebook` int NOT NULL,
  `date_achat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `regle` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_achat`,`id_util`),
  KEY `id_util` (`id_util`),
  KEY `idEbook` (`id_ebook`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `auteur`
--

DROP TABLE IF EXISTS `auteur`;
CREATE TABLE IF NOT EXISTS `auteur` (
  `id_auteur` int NOT NULL AUTO_INCREMENT,
  `nom_auteur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom_auteur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_naissance_util` date NOT NULL,
  `biographie` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `img_tete` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_auteur`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `auteur`
--

INSERT INTO `auteur` (`id_auteur`, `nom_auteur`, `prenom_auteur`, `date_naissance_util`, `biographie`, `img_tete`) VALUES
(1, 'Perbal', 'Sonia', '1980-05-15', 'Sonia Perbal est une auteure française connue pour ses livres de développement personnel. Elle aborde des thématiques autour de la psychologie et du bien-être.', 'https://i.imgur.com/DkG3C9L.jpg'),
(2, 'Brichant', 'Christophe', '1975-03-30', 'Christophe Brichant est un écrivain français spécialisé dans les thrillers et les romans policiers. Son style captivant tient le lecteur en haleine jusqu’à la dernière page.', 'https://i.imgur.com/OPu7zV1.jpg'),
(3, 'Koné', 'Océane', '1992-08-22', 'Océane Koné est une auteure émergente dans le domaine de la littérature jeunesse. Elle écrit des histoires captivantes pour les adolescents, abordant des sujets de société importants.', 'https://i.imgur.com/FO5dy4Y.jpg'),
(4, 'Joulié', 'Jessica', '1988-11-12', 'Jessica Joulié est une autrice française qui écrit des romans romantiques et des récits inspirants. Son écriture sensible et douce touche de nombreux lecteurs.', 'https://i.imgur.com/fXOZf7h.jpg'),
(14, 'Dupont', 'Marie', '0000-00-00', '', ''),
(15, 'Nguyen', 'Thierry', '0000-00-00', '', ''),
(16, 'Lemoine', 'Sophie', '0000-00-00', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `id_util` int NOT NULL,
  `id_livre` int NOT NULL,
  `note_avis` int NOT NULL,
  `comment_avis` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_avis` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avis`),
  KEY `id_livre` (`id_livre`),
  KEY `id_util` (`id_util`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id_avis`, `id_util`, `id_livre`, `note_avis`, `comment_avis`, `date_avis`) VALUES
(11, 11, 2, 5, 'Un livre passionnant, plein de révélations sur la maternité.', '2024-11-01 11:00:00'),
(12, 11, 2, 4, 'Très intéressant, mais quelques passages trop longs.', '2024-11-02 13:30:00'),
(13, 11, 3, 3, 'L\'amitié selon Aristote, une lecture complexe mais enrichissante.', '2024-11-03 08:15:00'),
(14, 11, 4, 5, 'Un excellent ouvrage, très bien écrit et captivant.', '2024-11-04 16:45:00'),
(15, 11, 2, 2, 'Pas vraiment ce à quoi je m\'attendais, assez décevant.', '2024-11-05 09:00:00'),
(16, 11, 4, 4, 'Bon livre, mais quelques éléments étaient prévisibles.', '2024-11-06 12:30:00'),
(17, 11, 4, 5, 'Un vrai chef-d\'œuvre, à lire absolument !', '2024-11-07 10:00:00'),
(18, 11, 3, 3, 'Une lecture agréable mais trop légère à mon goût.', '2024-11-08 15:20:00'),
(19, 11, 3, 4, 'Une histoire bien écrite, mais quelques longueurs.', '2024-11-09 07:40:00'),
(20, 11, 2, 5, 'Un livre incroyable, avec des personnages très bien développés.', '2024-11-10 17:00:00'),
(21, 12, 2, 2, 'etst', '2025-01-13 18:47:24'),
(22, 12, 2, 3, 'test2', '2025-01-13 18:50:06');

-- --------------------------------------------------------

--
-- Structure de la table `a_ecrit`
--

DROP TABLE IF EXISTS `a_ecrit`;
CREATE TABLE IF NOT EXISTS `a_ecrit` (
  `id_auteur` int NOT NULL,
  `id_livre` int NOT NULL,
  `date_parution` date NOT NULL,
  PRIMARY KEY (`id_auteur`,`id_livre`),
  KEY `id_livre` (`id_livre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `a_ecrit`
--

INSERT INTO `a_ecrit` (`id_auteur`, `id_livre`, `date_parution`) VALUES
(1, 4, '2024-09-25'),
(2, 4, '2024-09-25'),
(3, 2, '2024-10-01'),
(4, 3, '2024-11-17'),
(14, 146, '2023-05-10'),
(15, 147, '2020-11-15'),
(16, 148, '2018-03-20');

-- --------------------------------------------------------

--
-- Structure de la table `bibliotecaire`
--

DROP TABLE IF EXISTS `bibliotecaire`;
CREATE TABLE IF NOT EXISTS `bibliotecaire` (
  `id_util` int NOT NULL,
  PRIMARY KEY (`id_util`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `bibliotecaire`
--

INSERT INTO `bibliotecaire` (`id_util`) VALUES
(12);

-- --------------------------------------------------------

--
-- Structure de la table `ebook`
--

DROP TABLE IF EXISTS `ebook`;
CREATE TABLE IF NOT EXISTS `ebook` (
  `id_ebook` int NOT NULL AUTO_INCREMENT,
  `id_livre` int NOT NULL,
  `lien_PDF` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prix` int NOT NULL,
  PRIMARY KEY (`id_ebook`,`id_livre`),
  KEY `id_livre` (`id_livre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ebook`
--

INSERT INTO `ebook` (`id_ebook`, `id_livre`, `lien_PDF`, `prix`) VALUES
(1, 2, 'https://example.com/devenir-mere.pdf', 15),
(2, 3, 'https://example.com/je-tavais-dit.pdf', 12),
(3, 4, 'https://example.com/amitie-aristote.pdf', 18);

-- --------------------------------------------------------

--
-- Structure de la table `edition`
--

DROP TABLE IF EXISTS `edition`;
CREATE TABLE IF NOT EXISTS `edition` (
  `id_edition` int NOT NULL AUTO_INCREMENT,
  `nom_edition` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_edition`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `edition`
--

INSERT INTO `edition` (`id_edition`, `nom_edition`) VALUES
(1, 'Gallimard'),
(2, 'Hachette'),
(3, 'Flammarion'),
(4, 'Albin Michel'),
(5, 'Actes Sud'),
(6, 'Seuil'),
(7, 'Folio'),
(8, 'Bayard Jeunesse'),
(9, 'Éditions de Minuit');

-- --------------------------------------------------------

--
-- Structure de la table `emprunter`
--

DROP TABLE IF EXISTS `emprunter`;
CREATE TABLE IF NOT EXISTS `emprunter` (
  `id_exemplaire` int NOT NULL,
  `id_util` int NOT NULL,
  `date_debut_emprunt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_fin_emprunt` date DEFAULT NULL,
  PRIMARY KEY (`id_exemplaire`,`id_util`,`date_debut_emprunt`),
  KEY `id_util` (`id_util`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `emprunter`
--

INSERT INTO `emprunter` (`id_exemplaire`, `id_util`, `date_debut_emprunt`, `date_fin_emprunt`) VALUES
(21, 11, '2025-01-01 17:54:59', NULL),
(22, 11, '2025-01-04 17:54:59', NULL),
(31, 11, '2025-01-04 17:58:11', NULL),
(32, 11, '2025-01-06 21:07:27', NULL),
(33, 11, '2024-12-26 17:58:22', NULL),
(35, 11, '2025-01-01 17:58:48', NULL),
(44, 11, '2025-01-02 17:59:11', NULL),
(45, 11, '2024-12-27 17:59:53', NULL),
(46, 11, '2024-12-19 17:59:53', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `est_abonne`
--

DROP TABLE IF EXISTS `est_abonne`;
CREATE TABLE IF NOT EXISTS `est_abonne` (
  `id_abonnement` int NOT NULL,
  `id_util` int NOT NULL,
  `date_abonnement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_abonnement`,`id_util`),
  UNIQUE KEY `abo` (`id_util`),
  KEY `id_util` (`id_util`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `est_abonne`
--

INSERT INTO `est_abonne` (`id_abonnement`, `id_util`, `date_abonnement`) VALUES
(1, 11, '2025-01-10 12:21:56');

-- --------------------------------------------------------

--
-- Structure de la table `exemplaire`
--

DROP TABLE IF EXISTS `exemplaire`;
CREATE TABLE IF NOT EXISTS `exemplaire` (
  `id_exemplaire` int NOT NULL AUTO_INCREMENT,
  `num_isbn` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_exemplaire`),
  KEY `num_isbn` (`num_isbn`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `exemplaire`
--

INSERT INTO `exemplaire` (`id_exemplaire`, `num_isbn`) VALUES
(23, '978-2-01-000210-8'),
(24, '978-2-01-000210-8'),
(35, '978-2-02-000320-8'),
(36, '978-2-02-000320-8'),
(21, '978-2-07-000270-8'),
(22, '978-2-07-000270-8'),
(41, '978-2-07-000470-8'),
(42, '978-2-07-000470-8'),
(25, '978-2-08-000280-8'),
(26, '978-2-08-000280-8'),
(31, '978-2-226-00360-8'),
(32, '978-2-226-00360-8'),
(45, '978-2-7073-0430-8'),
(46, '978-2-7073-0430-8'),
(33, '978-2-7427-0370-8'),
(34, '978-2-7427-0370-8'),
(43, '978-2-7470-0400-8'),
(44, '978-2-7470-0400-8');

-- --------------------------------------------------------

--
-- Structure de la table `genre`
--

DROP TABLE IF EXISTS `genre`;
CREATE TABLE IF NOT EXISTS `genre` (
  `id_genre` int NOT NULL AUTO_INCREMENT,
  `nom_genre` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_genre`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `genre`
--

INSERT INTO `genre` (`id_genre`, `nom_genre`) VALUES
(1, 'Science-fiction'),
(2, 'Fantastique'),
(3, 'Romance'),
(4, 'Thriller'),
(5, 'Policier'),
(6, 'Historique'),
(7, 'Biographie'),
(8, 'Poésie'),
(9, 'Aventure'),
(10, 'Humour'),
(11, 'Non-fiction'),
(12, 'Guide pratique'),
(13, 'Philosophie'),
(19, 'Mystère'),
(20, 'Histoire'),
(21, 'Science');

-- --------------------------------------------------------

--
-- Structure de la table `isbn`
--

DROP TABLE IF EXISTS `isbn`;
CREATE TABLE IF NOT EXISTS `isbn` (
  `num_isbn` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_livre` int NOT NULL,
  `id_langue` int NOT NULL,
  `id_edition` int NOT NULL,
  `nb_pages` int NOT NULL,
  PRIMARY KEY (`num_isbn`),
  KEY `id_livre` (`id_livre`,`id_langue`,`id_edition`),
  KEY `isbn_ibfk_2` (`id_langue`),
  KEY `isbn_ibfk_3` (`id_edition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `isbn`
--

INSERT INTO `isbn` (`num_isbn`, `id_livre`, `id_langue`, `id_edition`, `nb_pages`) VALUES
('\n978-0-98-765432-0', 147, 1, 6, 245),
('\n978-1-12-233445-0', 148, 1, 3, 198),
('978-1-23-456789-0', 146, 1, 1, 320),
('978-2-01-000210-8', 2, 1, 2, 480),
('978-2-02-000320-8', 3, 1, 6, 250),
('978-2-07-000270-8', 2, 1, 1, 500),
('978-2-07-000470-8', 4, 1, 7, 410),
('978-2-08-000280-8', 2, 1, 3, 520),
('978-2-226-00360-8', 3, 1, 4, 230),
('978-2-7073-0430-8', 4, 1, 9, 430),
('978-2-7427-0370-8', 3, 1, 5, 210),
('978-2-7470-0400-8', 4, 1, 8, 420);

-- --------------------------------------------------------

--
-- Structure de la table `langue`
--

DROP TABLE IF EXISTS `langue`;
CREATE TABLE IF NOT EXISTS `langue` (
  `id_langue` int NOT NULL AUTO_INCREMENT,
  `nom_langue` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_langue`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `langue`
--

INSERT INTO `langue` (`id_langue`, `nom_langue`) VALUES
(1, 'Français'),
(2, 'Anglais'),
(3, 'Espagnol'),
(4, 'Allemand'),
(5, 'Italien'),
(6, 'Chinois'),
(7, 'Japonais'),
(8, 'Russe'),
(9, 'Arabe'),
(10, 'Portugais');

-- --------------------------------------------------------

--
-- Structure de la table `livre`
--

DROP TABLE IF EXISTS `livre`;
CREATE TABLE IF NOT EXISTS `livre` (
  `id_livre` int NOT NULL AUTO_INCREMENT,
  `cote_livre` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `titre_livre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type_litteraire` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `img_couverture` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `resume` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_livre`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livre`
--

INSERT INTO `livre` (`id_livre`, `cote_livre`, `titre_livre`, `type_litteraire`, `img_couverture`, `resume`) VALUES
(2, 'LIV001', 'Devenir mère une maternité sans langue de bois', 'Essai', 'https://i.imgur.com/IWHmNgD.jpeg', 'Un guide sans tabou sur la maternité, abordant tous les aspects physiques, émotionnels et psychologiques de devenir mère.'),
(3, 'LIV002', '“Je t’avais dit de ne pas faire Koh-Lanta”', 'Essai', 'https://i.imgur.com/ttvGT0K.jpeg', 'Un récit humoristique et poignant sur l’aventure Koh-Lanta et les péripéties vécues par les participants.'),
(4, 'LIV003', 'L’amitié selon Aristote', 'Philosophie', 'https://i.imgur.com/b6JkX6k.jpeg', 'Une réflexion sur le concept d’amitié selon Aristote, dans le cadre de sa philosophie éthique.'),
(146, 'A123', 'Les Étoiles du Destin', ' Roman ', './img/img_couv/Les_Étoiles_du_Destin.png', 'Un récit captivant sur les luttes et triomphes d\'une jeune héroïne dans un monde fantastique.'),
(147, 'B456', 'Le Mystère de l\'Orient', ' Nouvelle ', './img/img_couv/Le_Mystère_de_l\'Orient.png', 'Une plongée intrigante dans les secrets d\'une civilisation ancienne.'),
(148, 'C789', 'Voyage au Centre du Temps', ' Essai ', './img/img_couv/Voyage_au_Centre_du_Temps.png', 'Une exploration des notions de temps et d\'espace à travers des anecdotes historiques.');

-- --------------------------------------------------------

--
-- Structure de la table `livre_genre`
--

DROP TABLE IF EXISTS `livre_genre`;
CREATE TABLE IF NOT EXISTS `livre_genre` (
  `id_genre` int NOT NULL,
  `id_livre` int NOT NULL,
  PRIMARY KEY (`id_genre`,`id_livre`),
  KEY `id_livre` (`id_livre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livre_genre`
--

INSERT INTO `livre_genre` (`id_genre`, `id_livre`) VALUES
(1, 2),
(2, 3),
(3, 4),
(2, 146),
(9, 146),
(19, 147),
(20, 147),
(13, 148),
(21, 148);

-- --------------------------------------------------------

--
-- Structure de la table `livre_public`
--

DROP TABLE IF EXISTS `livre_public`;
CREATE TABLE IF NOT EXISTS `livre_public` (
  `id_public` int NOT NULL,
  `id_livre` int NOT NULL,
  PRIMARY KEY (`id_public`,`id_livre`),
  KEY `id_livre` (`id_livre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livre_public`
--

INSERT INTO `livre_public` (`id_public`, `id_livre`) VALUES
(1, 2),
(3, 3),
(5, 4),
(2, 146),
(3, 146),
(3, 147),
(25, 148);

-- --------------------------------------------------------

--
-- Structure de la table `public_cible`
--

DROP TABLE IF EXISTS `public_cible`;
CREATE TABLE IF NOT EXISTS `public_cible` (
  `id_public` int NOT NULL AUTO_INCREMENT,
  `type_public` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_public`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `public_cible`
--

INSERT INTO `public_cible` (`id_public`, `type_public`) VALUES
(1, 'Enfants'),
(2, 'Adolescents'),
(3, 'Adultes'),
(4, 'Seniors'),
(5, 'Étudiants'),
(6, 'Professionnels'),
(7, 'Lecteurs passionnés'),
(8, 'Amateurs de sciences'),
(9, 'Amateurs de philosophie'),
(10, 'Familles'),
(25, 'Tous');

-- --------------------------------------------------------

--
-- Structure de la table `reserver`
--

DROP TABLE IF EXISTS `reserver`;
CREATE TABLE IF NOT EXISTS `reserver` (
  `num_isbn` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_util` int NOT NULL,
  `date_reservation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`num_isbn`,`id_util`),
  KEY `num_isbn` (`num_isbn`,`id_util`),
  KEY `reserver_ibfk_2` (`id_util`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reserver`
--

INSERT INTO `reserver` (`num_isbn`, `id_util`, `date_reservation`) VALUES
('978-2-01-000210-8', 11, '2025-01-04 18:06:46'),
('978-2-02-000320-8', 12, '2025-01-13 19:38:43'),
('978-2-08-000280-8', 12, '2025-01-13 18:40:17'),
('978-2-7470-0400-8', 12, '2025-01-13 19:12:09');

-- --------------------------------------------------------

--
-- Structure de la table `token`
--

DROP TABLE IF EXISTS `token`;
CREATE TABLE IF NOT EXISTS `token` (
  `id_token` int NOT NULL AUTO_INCREMENT,
  `nom_token` varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expire` datetime NOT NULL,
  `id_util` int NOT NULL,
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `uni_token` (`nom_token`),
  KEY `fk_token` (`id_util`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_util` int NOT NULL AUTO_INCREMENT,
  `prenom_util` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nom_util` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `adresse_util` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tel_util` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pseudo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mdp` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `img_profil` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_naissance` date NOT NULL,
  PRIMARY KEY (`id_util`),
  UNIQUE KEY `pseudo` (`pseudo`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_util`, `prenom_util`, `nom_util`, `adresse_util`, `tel_util`, `pseudo`, `mdp`, `img_profil`, `email`, `date_naissance`) VALUES
(11, 'isi', 'ra', '27 chemin de la planchette', '0974837672', 'isira', '$2y$10$Wu45JADg382URcWjt8sFdO4/pWsyZtpaZAEePhVX8Ae5LIKmL354u', 'test', 'isira213213@gmail.com', '2005-03-08'),
(12, 'Amadis', 'Senelet', '328 Rue Francis de Pressenssé', '0624512396', 'ama', '$2y$10$33sfKpQxB/qcQkwHWHi0iuBpro2Ol9Q1t.O1oNOzoc4bxXkvDsfEK', 'test', 'amadis.artemis@gmail.com', '1999-09-21');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `achat_ebook`
--
ALTER TABLE `achat_ebook`
  ADD CONSTRAINT `achat_ebook_ibfk_3` FOREIGN KEY (`id_ebook`) REFERENCES `ebook` (`id_ebook`) ON DELETE CASCADE,
  ADD CONSTRAINT `achat_ebook_ibfk_4` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`);

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`id_livre`) REFERENCES `livre` (`id_livre`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE CASCADE;

--
-- Contraintes pour la table `a_ecrit`
--
ALTER TABLE `a_ecrit`
  ADD CONSTRAINT `a_ecrit_ibfk_1` FOREIGN KEY (`id_livre`) REFERENCES `livre` (`id_livre`) ON DELETE CASCADE,
  ADD CONSTRAINT `a_ecrit_ibfk_2` FOREIGN KEY (`id_auteur`) REFERENCES `auteur` (`id_auteur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bibliotecaire`
--
ALTER TABLE `bibliotecaire`
  ADD CONSTRAINT `bibliotecaire_ibfk_1` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ebook`
--
ALTER TABLE `ebook`
  ADD CONSTRAINT `ebook_ibfk_1` FOREIGN KEY (`id_livre`) REFERENCES `livre` (`id_livre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `emprunter`
--
ALTER TABLE `emprunter`
  ADD CONSTRAINT `emprunter_ibfk_1` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `emprunter_ibfk_2` FOREIGN KEY (`id_exemplaire`) REFERENCES `exemplaire` (`id_exemplaire`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `est_abonne`
--
ALTER TABLE `est_abonne`
  ADD CONSTRAINT `est_abonne_ibfk_1` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE CASCADE,
  ADD CONSTRAINT `est_abonne_ibfk_2` FOREIGN KEY (`id_abonnement`) REFERENCES `abonnement` (`id_abonnement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `exemplaire`
--
ALTER TABLE `exemplaire`
  ADD CONSTRAINT `exemplaire_ibfk_1` FOREIGN KEY (`num_isbn`) REFERENCES `isbn` (`num_isbn`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `isbn`
--
ALTER TABLE `isbn`
  ADD CONSTRAINT `isbn_ibfk_1` FOREIGN KEY (`id_livre`) REFERENCES `livre` (`id_livre`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `isbn_ibfk_2` FOREIGN KEY (`id_langue`) REFERENCES `langue` (`id_langue`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `isbn_ibfk_3` FOREIGN KEY (`id_edition`) REFERENCES `edition` (`id_edition`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `livre_genre`
--
ALTER TABLE `livre_genre`
  ADD CONSTRAINT `livre_genre_ibfk_1` FOREIGN KEY (`id_genre`) REFERENCES `genre` (`id_genre`) ON DELETE CASCADE,
  ADD CONSTRAINT `livre_genre_ibfk_2` FOREIGN KEY (`id_livre`) REFERENCES `livre` (`id_livre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `livre_public`
--
ALTER TABLE `livre_public`
  ADD CONSTRAINT `livre_public_ibfk_1` FOREIGN KEY (`id_livre`) REFERENCES `livre` (`id_livre`) ON DELETE CASCADE,
  ADD CONSTRAINT `livre_public_ibfk_2` FOREIGN KEY (`id_public`) REFERENCES `public_cible` (`id_public`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reserver`
--
ALTER TABLE `reserver`
  ADD CONSTRAINT `reserver_ibfk_1` FOREIGN KEY (`num_isbn`) REFERENCES `isbn` (`num_isbn`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reserver_ibfk_2` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `fk_token` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
