-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 07 jan. 2025 à 15:37
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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `achat_ebook`
--

INSERT INTO `achat_ebook` (`id_achat`, `id_util`, `id_ebook`, `date_achat`, `regle`) VALUES
(31, 1, 1, '2024-11-01 09:30:00', 1),
(32, 2, 2, '2024-11-02 10:45:00', 1),
(33, 3, 3, '2024-11-03 11:15:00', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `auteur`
--

INSERT INTO `auteur` (`id_auteur`, `nom_auteur`, `prenom_auteur`, `date_naissance_util`, `biographie`, `img_tete`) VALUES
(1, 'Perbal', 'Sonia', '1980-05-15', 'Sonia Perbal est une auteure française connue pour ses livres de développement personnel. Elle aborde des thématiques autour de la psychologie et du bien-être.', 'https://i.imgur.com/DkG3C9L.jpg'),
(2, 'Brichant', 'Christophe', '1975-03-30', 'Christophe Brichant est un écrivain français spécialisé dans les thrillers et les romans policiers. Son style captivant tient le lecteur en haleine jusqu’à la dernière page.', 'https://i.imgur.com/OPu7zV1.jpg'),
(3, 'Koné', 'Océane', '1992-08-22', 'Océane Koné est une auteure émergente dans le domaine de la littérature jeunesse. Elle écrit des histoires captivantes pour les adolescents, abordant des sujets de société importants.', 'https://i.imgur.com/FO5dy4Y.jpg'),
(4, 'Joulié', 'Jessica', '1988-11-12', 'Jessica Joulié est une autrice française qui écrit des romans romantiques et des récits inspirants. Son écriture sensible et douce touche de nombreux lecteurs.', 'https://i.imgur.com/fXOZf7h.jpg');

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id_avis`, `id_util`, `id_livre`, `note_avis`, `comment_avis`, `date_avis`) VALUES
(11, 1, 2, 5, 'Un livre passionnant, plein de révélations sur la maternité.', '2024-11-01 11:00:00'),
(12, 2, 2, 4, 'Très intéressant, mais quelques passages trop longs.', '2024-11-02 13:30:00'),
(13, 3, 3, 3, 'L\'amitié selon Aristote, une lecture complexe mais enrichissante.', '2024-11-03 08:15:00'),
(14, 4, 4, 5, 'Un excellent ouvrage, très bien écrit et captivant.', '2024-11-04 16:45:00'),
(15, 5, 2, 2, 'Pas vraiment ce à quoi je m\'attendais, assez décevant.', '2024-11-05 09:00:00'),
(16, 6, 4, 4, 'Bon livre, mais quelques éléments étaient prévisibles.', '2024-11-06 12:30:00'),
(17, 7, 4, 5, 'Un vrai chef-d\'œuvre, à lire absolument !', '2024-11-07 10:00:00'),
(18, 8, 3, 3, 'Une lecture agréable mais trop légère à mon goût.', '2024-11-08 15:20:00'),
(19, 9, 3, 4, 'Une histoire bien écrite, mais quelques longueurs.', '2024-11-09 07:40:00'),
(20, 10, 2, 5, 'Un livre incroyable, avec des personnages très bien développés.', '2024-11-10 17:00:00');

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
(4, 3, '2024-11-17');

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
(1);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(21, 4, '2025-01-01 17:54:59', NULL),
(22, 7, '2025-01-04 17:54:59', NULL),
(31, 2, '2025-01-04 17:58:11', NULL),
(32, 7, '2025-01-06 21:07:27', NULL),
(33, 5, '2024-12-26 17:58:22', NULL),
(35, 8, '2025-01-01 17:58:48', NULL),
(44, 6, '2025-01-02 17:59:11', NULL),
(45, 3, '2024-12-27 17:59:53', NULL),
(46, 9, '2024-12-19 17:59:53', NULL);

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
(1, 2, '2024-11-01 11:00:00'),
(1, 4, '2024-11-04 16:45:00'),
(1, 5, '2024-11-05 09:00:00'),
(1, 10, '2024-11-10 17:00:00'),
(3, 3, '2024-11-03 08:15:00'),
(3, 6, '2024-11-06 12:30:00'),
(4, 7, '2024-11-07 10:00:00'),
(4, 8, '2024-11-08 15:20:00'),
(4, 9, '2024-11-09 07:40:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(13, 'Philosophie');

-- --------------------------------------------------------

--
-- Structure de la table `isbn`
--

DROP TABLE IF EXISTS `isbn`;
CREATE TABLE IF NOT EXISTS `isbn` (
  `num_isbn` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `id_genre` int NOT NULL,
  `id_public` int NOT NULL,
  PRIMARY KEY (`id_livre`),
  KEY `id_genre` (`id_genre`),
  KEY `id_public` (`id_public`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livre`
--

INSERT INTO `livre` (`id_livre`, `cote_livre`, `titre_livre`, `type_litteraire`, `img_couverture`, `resume`, `id_genre`, `id_public`) VALUES
(2, 'LIV001', 'Devenir mère une maternité sans langue de bois', 'Essai', 'https://i.imgur.com/IWHmNgD.jpeg', 'Un guide sans tabou sur la maternité, abordant tous les aspects physiques, émotionnels et psychologiques de devenir mère.', 12, 3),
(3, 'LIV002', '“Je t’avais dit de ne pas faire Koh-Lanta”', 'Essai', 'https://i.imgur.com/ttvGT0K.jpeg', 'Un récit humoristique et poignant sur l’aventure Koh-Lanta et les péripéties vécues par les participants.', 10, 7),
(4, 'LIV003', 'L’amitié selon Aristote', 'Philosophie', 'https://i.imgur.com/b6JkX6k.jpeg', 'Une réflexion sur le concept d’amitié selon Aristote, dans le cadre de sa philosophie éthique.', 13, 9);

-- --------------------------------------------------------

--
-- Structure de la table `public_cible`
--

DROP TABLE IF EXISTS `public_cible`;
CREATE TABLE IF NOT EXISTS `public_cible` (
  `id_public` int NOT NULL AUTO_INCREMENT,
  `type_public` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_public`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(10, 'Familles');

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
('978-2-01-000210-8', 6, '2025-01-04 18:06:46'),
('978-2-02-000320-8', 3, '2024-12-19 18:08:53'),
('978-2-07-000270-8', 5, '2024-12-31 18:05:41'),
('978-2-07-000470-8', 4, '2025-01-02 18:09:30'),
('978-2-226-00360-8', 10, '2025-01-04 18:07:05'),
('978-2-7073-0430-8', 8, '2025-01-04 18:10:17'),
('978-2-7470-0400-8', 9, '2025-01-04 18:09:45');

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
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_naissance` date NOT NULL,
  PRIMARY KEY (`id_util`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_util`, `prenom_util`, `nom_util`, `adresse_util`, `tel_util`, `pseudo`, `mdp`, `img_profil`, `email`, `date_naissance`) VALUES
(1, 'Alice', 'Dupont', '12 Rue de Paris, Lyon', '0601020304', 'alice_01', 'mdp1234', 'https://i.imgur.com/JJTpG4j.jpg', 'alice.dupont@example.com', '1995-04-25'),
(2, 'Bob', 'Martin', '15 Rue des Lilas, Lyon', '0602030405', 'bob_the_great', 'password5678', 'https://i.imgur.com/qqR8BOu.jpg', 'bob.martin@example.com', '1993-07-12'),
(3, 'Charlie', 'Leclerc', '8 Avenue de la Liberté, Lyon', '0603040506', 'charlie_cool', 'mypassword890', 'https://i.imgur.com/GGJ9Fnv.jpg', 'charlie.leclerc@example.com', '1991-09-30'),
(4, 'David', 'Tremblay', '20 Boulevard du Rhône, Lyon', '0604050607', 'david_tr', 'davidpass321', 'https://i.imgur.com/M7W8hgQ.jpg', 'david.tremblay@example.com', '1990-02-15'),
(5, 'Eva', 'Lemoine', '35 Rue de la République, Lyon', '0605060708', 'eva_23', 'evaPass!@#', 'https://i.imgur.com/2ZCbsUn.jpg', 'eva.lemoine@example.com', '1988-11-03'),
(6, 'François', 'Dubois', '55 Rue de l’École, Lyon', '0606070809', 'françois_98', 'françois2024', 'https://i.imgur.com/o3PY0gV.jpg', 'francois.dubois@example.com', '1992-05-19'),
(7, 'Gabrielle', 'Roche', '50 Place Bellecour, Lyon', '0607080910', 'gabrielle22', 'gabrielle567', 'https://i.imgur.com/0S7wv91.jpg', 'gabrielle.roche@example.com', '1994-08-14'),
(8, 'Hugo', 'Girard', '65 Rue des Alpes, Lyon', '0608091011', 'hugo_g', 'hugo2024', 'https://i.imgur.com/2o9ZnS1.jpg', 'hugo.girard@example.com', '1996-12-20'),
(9, 'Isabelle', 'Nicolas', '5 Rue des Fleurs, Lyon', '0609101112', 'isabelle_n', 'isabelle1234', 'https://i.imgur.com/ek9zK4u.jpg', 'isabelle.nicolas@example.com', '1987-03-22'),
(10, 'Julien', 'Pires', '75 Rue de la Paix, Lyon', '0610111213', 'julien_pires', 'julienpass567', 'https://i.imgur.com/rtgvjLg.jpg', 'julien.pires@example.com', '1990-10-09');

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
-- Contraintes pour la table `livre`
--
ALTER TABLE `livre`
  ADD CONSTRAINT `livre_ibfk_1` FOREIGN KEY (`id_genre`) REFERENCES `genre` (`id_genre`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `livre_ibfk_2` FOREIGN KEY (`id_public`) REFERENCES `public_cible` (`id_public`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `reserver`
--
ALTER TABLE `reserver`
  ADD CONSTRAINT `reserver_ibfk_1` FOREIGN KEY (`num_isbn`) REFERENCES `isbn` (`num_isbn`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reserver_ibfk_2` FOREIGN KEY (`id_util`) REFERENCES `utilisateur` (`id_util`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
