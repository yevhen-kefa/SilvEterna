-- Sélectionner tous les utilisateurs
SELECT * FROM users;

-- Sélectionner les utilisateurs avec leur statut
SELECT u.id, u.nom, u.prenom, s.activite
FROM users u
JOIN posseder p ON u.id = p.id_user
JOIN statut s ON p.id_statut = s.id_statut;

-- Sélectionner les événements du calendrier créés par un utilisateur spécifique (id_user = 1)
SELECT c.titre, c.description, c.lieu, c.date, c.heure_deb, c.heure_fin
FROM calendrier c
JOIN creer cr ON c.id_loisir = cr.id_loisir
WHERE cr.id_user = 1;

-- Sélectionner les messages envoyés par un utilisateur spécifique (id_user = 1)
SELECT m.contenu, m.heure_envoi, m.date_envoi
FROM messages m
JOIN envoyer_mess em ON m.id_message = em.id_message
WHERE em.id_user = 1;

-- Sélectionner les amis d'un utilisateur spécifique (id_user = 1)
SELECT u2.nom, u2.prenom
FROM etre_ami ea
JOIN users u1 ON ea.id_ami1 = u1.id
JOIN users u2 ON ea.id_ami2 = u2.id
WHERE u1.id = 1;

-- Sélectionner les événements de l'agenda d'un utilisateur spécifique (id_user = 1)
SELECT a.titre, a.description, a.date, a.heure_debut, a.heure_fin, a.lieu, a.type_evenement
FROM Agenda a
JOIN a_un_agenda ag ON a.id_agenda = ag.id_agenda
WHERE ag.id_user = 1;

-- Sélectionner les informations de connexion d'un utilisateur spécifique (id_user = 1)
SELECT l.id_login, l.password
FROM login l
JOIN se_connecter sc ON l.id_login = sc.id_login
WHERE sc.id_user = 1;

-- Sélectionner les événements du calendrier auxquels un utilisateur spécifique participe (id_user = 1)
SELECT c.titre, c.description, c.lieu, c.date, c.heure_deb, c.heure_fin
FROM calendrier c
JOIN participer p ON c.id_loisir = p.id_loisir
WHERE p.id_user = 1;
