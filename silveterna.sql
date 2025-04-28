-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 28 avr. 2025 à 16:11
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `silveterna`
--

-- --------------------------------------------------------

--
-- Structure de la table `agenda`
--

CREATE TABLE `agenda` (
  `id_agenda` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `type_evenement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `agenda`
--

INSERT INTO `agenda` (`id_agenda`, `titre`, `description`, `date`, `heure_debut`, `heure_fin`, `lieu`, `type_evenement`) VALUES
(1, 'Réunion', 'Réunion de projet', '2023-10-03', '09:00:00', '10:00:00', 'Salle de réunion', 'Professionnel'),
(2, 'Anniversaire', 'Anniversaire de Marie', '2023-10-04', '19:00:00', '22:00:00', 'Maison de Marie', 'Personnel'),
(3, 'Conférence', 'Conférence sur l\'IA', '2023-10-05', '14:00:00', '16:00:00', 'Centre de conférence', 'Professionnel'),
(4, 'Voyage', 'Voyage à Paris', '2023-10-06', '08:00:00', '18:00:00', 'Paris', 'Personnel');

-- --------------------------------------------------------

--
-- Structure de la table `a_un_agenda`
--

CREATE TABLE `a_un_agenda` (
  `id_user` int(11) NOT NULL,
  `id_agenda` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `a_un_agenda`
--

INSERT INTO `a_un_agenda` (`id_user`, `id_agenda`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `calendrier`
--

CREATE TABLE `calendrier` (
  `id_loisir` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure_deb` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `nombre_max_participants` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `calendrier`
--

INSERT INTO `calendrier` (`id_loisir`, `titre`, `description`, `lieu`, `date`, `heure_deb`, `heure_fin`, `nombre_max_participants`) VALUES
(1, 'Randonnée', 'Randonnée dans les montagnes', 'Montagne', '2023-10-01', '09:00:00', '12:00:00', 20),
(2, 'Cinéma', 'Soirée cinéma', 'Cinéma du centre', '2023-10-02', '19:00:00', '21:00:00', 50),
(3, 'Concert', 'Concert de rock', 'Salle de concert', '2023-10-03', '20:00:00', '22:00:00', 100),
(4, 'Exposition', 'Exposition d\'art', 'Galerie d\'art', '2023-10-04', '14:00:00', '18:00:00', 30);

-- --------------------------------------------------------

--
-- Structure de la table `creer`
--

CREATE TABLE `creer` (
  `id_user` int(11) NOT NULL,
  `id_loisir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `creer`
--

INSERT INTO `creer` (`id_user`, `id_loisir`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `envoyer_mess`
--

CREATE TABLE `envoyer_mess` (
  `id_user` int(11) NOT NULL,
  `id_message` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `envoyer_mess`
--

INSERT INTO `envoyer_mess` (`id_user`, `id_message`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `etre_ami`
--

CREATE TABLE `etre_ami` (
  `id_ami1` int(11) NOT NULL,
  `id_ami2` int(11) NOT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etre_ami`
--

INSERT INTO `etre_ami` (`id_ami1`, `id_ami2`, `statut`, `date_creation`) VALUES
(1, 2, 'amis', '2023-03-01 12:00:00'),
(2, 3, 'amis', '2023-03-02 13:00:00'),
(3, 4, 'amis', '2023-03-03 14:00:00'),
(4, 1, 'amis', '2023-03-04 15:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `login`
--

CREATE TABLE `login` (
  `id_login` int(11) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `login`
--

INSERT INTO `login` (`id_login`, `password`) VALUES
(1, 'password1'),
(2, 'password2'),
(3, 'password3'),
(4, 'password4');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id_message` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `heure_envoi` datetime DEFAULT NULL,
  `date_envoi` date DEFAULT NULL,
  `compteur_msg_nonlu` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id_message`, `contenu`, `heure_envoi`, `date_envoi`, `compteur_msg_nonlu`) VALUES
(1, 'Bonjour !', '2023-04-01 08:00:00', '2023-04-01', 0),
(2, 'Salut !', '2023-04-01 09:00:00', '2023-04-01', 1),
(3, 'Comment ça va ?', '2023-04-01 10:00:00', '2023-04-01', 0),
(4, 'Bien et toi ?', '2023-04-01 11:00:00', '2023-04-01', 1);

-- --------------------------------------------------------

--
-- Structure de la table `participer`
--

CREATE TABLE `participer` (
  `id_user` int(11) NOT NULL,
  `id_loisir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participer`
--

INSERT INTO `participer` (`id_user`, `id_loisir`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 3),
(3, 4),
(4, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `posseder`
--

CREATE TABLE `posseder` (
  `id_user` int(11) NOT NULL,
  `id_statut` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `posseder`
--

INSERT INTO `posseder` (`id_user`, `id_statut`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `se_connecter`
--

CREATE TABLE `se_connecter` (
  `id_user` int(11) NOT NULL,
  `id_login` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `se_connecter`
--

INSERT INTO `se_connecter` (`id_user`, `id_login`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `statut`
--

CREATE TABLE `statut` (
  `id_statut` int(11) NOT NULL,
  `activite` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statut`
--

INSERT INTO `statut` (`id_statut`, `activite`) VALUES
(1, 'En ligne'),
(2, 'Hors ligne'),
(3, 'Occupé'),
(4, 'En vacances');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `dateNaissance` date DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `sexe` char(1) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `avatar`, `nom`, `prenom`, `login`, `email`, `pass`, `dateNaissance`, `telephone`, `statut`, `sexe`, `date_create`) VALUES
(1, 'avatar1.jpg', 'Dupont', 'Jean', 'jdupont', 'jean.dupont@example.com', 'password1', '1990-01-01', '0612345678', 'actif', 'M', '2023-01-01 10:00:00'),
(2, 'avatar2.jpg', 'Martin', 'Marie', 'mmartin', 'marie.martin@example.com', 'password2', '1992-02-02', '0687654321', 'inactif', 'F', '2023-02-01 11:00:00'),
(3, 'avatar3.jpg', 'Durand', 'Pierre', 'pdurand', 'pierre.durand@example.com', 'password3', '1985-03-03', '0654321789', 'actif', 'M', '2023-03-01 12:00:00'),
(4, 'avatar4.jpg', 'Lefebvre', 'Sophie', 'slefebvre', 'sophie.lefebvre@example.com', 'password4', '1995-04-04', '0698765432', 'inactif', 'F', '2023-04-01 13:00:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id_agenda`);

--
-- Index pour la table `a_un_agenda`
--
ALTER TABLE `a_un_agenda`
  ADD PRIMARY KEY (`id_user`,`id_agenda`),
  ADD KEY `id_agenda` (`id_agenda`);

--
-- Index pour la table `calendrier`
--
ALTER TABLE `calendrier`
  ADD PRIMARY KEY (`id_loisir`);

--
-- Index pour la table `creer`
--
ALTER TABLE `creer`
  ADD PRIMARY KEY (`id_user`,`id_loisir`),
  ADD KEY `id_loisir` (`id_loisir`);

--
-- Index pour la table `envoyer_mess`
--
ALTER TABLE `envoyer_mess`
  ADD PRIMARY KEY (`id_user`,`id_message`),
  ADD KEY `id_message` (`id_message`);

--
-- Index pour la table `etre_ami`
--
ALTER TABLE `etre_ami`
  ADD PRIMARY KEY (`id_ami1`,`id_ami2`),
  ADD KEY `id_ami2` (`id_ami2`);

--
-- Index pour la table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id_login`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_message`);

--
-- Index pour la table `participer`
--
ALTER TABLE `participer`
  ADD PRIMARY KEY (`id_user`,`id_loisir`),
  ADD KEY `id_loisir` (`id_loisir`);

--
-- Index pour la table `posseder`
--
ALTER TABLE `posseder`
  ADD PRIMARY KEY (`id_user`,`id_statut`),
  ADD KEY `id_statut` (`id_statut`);

--
-- Index pour la table `se_connecter`
--
ALTER TABLE `se_connecter`
  ADD PRIMARY KEY (`id_user`,`id_login`),
  ADD KEY `id_login` (`id_login`);

--
-- Index pour la table `statut`
--
ALTER TABLE `statut`
  ADD PRIMARY KEY (`id_statut`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id_agenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `calendrier`
--
ALTER TABLE `calendrier`
  MODIFY `id_loisir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `login`
--
ALTER TABLE `login`
  MODIFY `id_login` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `statut`
--
ALTER TABLE `statut`
  MODIFY `id_statut` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `a_un_agenda`
--
ALTER TABLE `a_un_agenda`
  ADD CONSTRAINT `a_un_agenda_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `a_un_agenda_ibfk_2` FOREIGN KEY (`id_agenda`) REFERENCES `agenda` (`id_agenda`);

--
-- Contraintes pour la table `creer`
--
ALTER TABLE `creer`
  ADD CONSTRAINT `creer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `creer_ibfk_2` FOREIGN KEY (`id_loisir`) REFERENCES `calendrier` (`id_loisir`);

--
-- Contraintes pour la table `envoyer_mess`
--
ALTER TABLE `envoyer_mess`
  ADD CONSTRAINT `envoyer_mess_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `envoyer_mess_ibfk_2` FOREIGN KEY (`id_message`) REFERENCES `messages` (`id_message`);

--
-- Contraintes pour la table `etre_ami`
--
ALTER TABLE `etre_ami`
  ADD CONSTRAINT `etre_ami_ibfk_1` FOREIGN KEY (`id_ami1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `etre_ami_ibfk_2` FOREIGN KEY (`id_ami2`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `participer`
--
ALTER TABLE `participer`
  ADD CONSTRAINT `participer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `participer_ibfk_2` FOREIGN KEY (`id_loisir`) REFERENCES `calendrier` (`id_loisir`);

--
-- Contraintes pour la table `posseder`
--
ALTER TABLE `posseder`
  ADD CONSTRAINT `posseder_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posseder_ibfk_2` FOREIGN KEY (`id_statut`) REFERENCES `statut` (`id_statut`);

--
-- Contraintes pour la table `se_connecter`
--
ALTER TABLE `se_connecter`
  ADD CONSTRAINT `se_connecter_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `se_connecter_ibfk_2` FOREIGN KEY (`id_login`) REFERENCES `login` (`id_login`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
