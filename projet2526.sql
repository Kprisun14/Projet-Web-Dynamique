-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : lun. 18 mai 2026 à 12:25
-- Version du serveur : 8.0.44
-- Version de PHP : 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet2526`
--

-- --------------------------------------------------------

--
-- Structure de la table `billets`
--

CREATE TABLE `billets` (
  `id_billet` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_evenement` int NOT NULL,
  `id_participation` int NOT NULL,
  `code_billet` varchar(255) NOT NULL,
  `date_achat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_billet` enum('valide','utilise','annule') DEFAULT 'valide'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `id_evenement` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `date_evenement` datetime NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `prix` decimal(10,2) DEFAULT '0.00',
  `places_disponibles` int DEFAULT '0',
  `id_organisateur` int NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants_evenements`
--

CREATE TABLE `participants_evenements` (
  `id_participation` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_evenement` int NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('inscrit','annule','en_attente') DEFAULT 'inscrit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'x',
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('participant','organisateur','administrateur') DEFAULT 'participant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
(1, 'ESTRUCH--OYARSABAL', 'Victor', 'victor.estruchoyarsabal@edu.ece.fr', '$2y$10$4rCgUsa3L7zvwgIImxA95.K5yzKOwFEmcDLCYNdrTw3xb0sMkp7RO', 'administrateur');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `billets`
--
ALTER TABLE `billets`
  ADD PRIMARY KEY (`id_billet`),
  ADD UNIQUE KEY `code_billet` (`code_billet`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_evenement` (`id_evenement`),
  ADD KEY `id_participation` (`id_participation`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id_evenement`),
  ADD KEY `id_organisateur` (`id_organisateur`);

--
-- Index pour la table `participants_evenements`
--
ALTER TABLE `participants_evenements`
  ADD PRIMARY KEY (`id_participation`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_evenement` (`id_evenement`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `billets`
--
ALTER TABLE `billets`
  MODIFY `id_billet` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id_evenement` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `participants_evenements`
--
ALTER TABLE `participants_evenements`
  MODIFY `id_participation` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `billets`
--
ALTER TABLE `billets`
  ADD CONSTRAINT `billets_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `billets_ibfk_2` FOREIGN KEY (`id_evenement`) REFERENCES `evenements` (`id_evenement`) ON DELETE CASCADE,
  ADD CONSTRAINT `billets_ibfk_3` FOREIGN KEY (`id_participation`) REFERENCES `participants_evenements` (`id_participation`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`id_organisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `participants_evenements`
--
ALTER TABLE `participants_evenements`
  ADD CONSTRAINT `participants_evenements_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_evenements_ibfk_2` FOREIGN KEY (`id_evenement`) REFERENCES `evenements` (`id_evenement`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
