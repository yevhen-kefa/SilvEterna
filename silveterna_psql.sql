BEGIN;

CREATE TABLE agenda (
  id_agenda SERIAL PRIMARY KEY,
  titre VARCHAR(255),
  description TEXT,
  date DATE,
  heure_debut TIME,
  heure_fin TIME,
  lieu VARCHAR(255),
  type_evenement VARCHAR(50)
);

INSERT INTO agenda (id_agenda, titre, description, date, heure_debut, heure_fin, lieu, type_evenement) VALUES
(1, 'Réunion', 'Réunion de projet', '2023-10-03', '09:00:00', '10:00:00', 'Salle de réunion', 'Professionnel'),
(2, 'Anniversaire', 'Anniversaire de Marie', '2023-10-04', '19:00:00', '22:00:00', 'Maison de Marie', 'Personnel'),
(3, 'Conférence', 'Conférence sur IA', '2023-10-05', '14:00:00', '16:00:00', 'Centre de conférence', 'Professionnel'),
(4, 'Voyage', 'Voyage à Paris', '2023-10-06', '08:00:00', '18:00:00', 'Paris', 'Personnel');

CREATE TABLE a_un_agenda (
  id_user INTEGER NOT NULL,
  id_agenda INTEGER NOT NULL
);

INSERT INTO a_un_agenda (id_user, id_agenda) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

CREATE TABLE calendrier (
  id_loisir SERIAL PRIMARY KEY,
  titre VARCHAR(255),
  description TEXT,
  lieu VARCHAR(255),
  date DATE,
  heure_deb TIME,
  heure_fin TIME,
  nombre_max_participants INTEGER
);

INSERT INTO calendrier (id_loisir, titre, description, lieu, date, heure_deb, heure_fin, nombre_max_participants) VALUES
(1, 'Randonnée', 'Randonnée dans les montagnes', 'Montagne', '2023-10-01', '09:00:00', '12:00:00', 20),
(2, 'Cinéma', 'Soirée cinéma', 'Cinéma du centre', '2023-10-02', '19:00:00', '21:00:00', 50),
(3, 'Concert', 'Concert de rock', 'Salle de concert', '2023-10-03', '20:00:00', '22:00:00', 100),
(4, 'Exposition', 'Exposition d''art', 'Galerie d''art', '2023-10-04', '14:00:00', '18:00:00', 30);

CREATE TABLE creer (
  id_user INTEGER NOT NULL,
  id_loisir INTEGER NOT NULL
);

INSERT INTO creer (id_user, id_loisir) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

CREATE TABLE envoyer_mess (
  id_user INTEGER NOT NULL,
  id_message INTEGER NOT NULL
);

INSERT INTO envoyer_mess (id_user, id_message) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

CREATE TABLE etre_ami (
  id_ami1 INTEGER NOT NULL,
  id_ami2 INTEGER NOT NULL,
  statut VARCHAR(50),
  date_creation TIMESTAMP
);

INSERT INTO etre_ami (id_ami1, id_ami2, statut, date_creation) VALUES
(1, 2, 'amis', '2023-03-01 12:00:00'),
(2, 3, 'amis', '2023-03-02 13:00:00'),
(3, 4, 'amis', '2023-03-03 14:00:00'),
(4, 1, 'amis', '2023-03-04 15:00:00');

CREATE TABLE login (
  id_login SERIAL PRIMARY KEY,
  password VARCHAR(255)
);

INSERT INTO login (id_login, password) VALUES
(1, 'password1'),
(2, 'password2'),
(3, 'password3'),
(4, 'password4');

COMMIT;


-- Таблиця messages
CREATE TABLE messages (
  id_message SERIAL PRIMARY KEY,
  contenu TEXT,
  heure_envoi TIMESTAMP,
  date_envoi DATE,
  compteur_msg_nonlu INTEGER
);

INSERT INTO messages (id_message, contenu, heure_envoi, date_envoi, compteur_msg_nonlu) VALUES
(1, 'Bonjour !', '2023-04-01 08:00:00', '2023-04-01', 0),
(2, 'Salut !', '2023-04-01 09:00:00', '2023-04-01', 1),
(3, 'Comment ça va ?', '2023-04-01 10:00:00', '2023-04-01', 0),
(4, 'Bien et toi ?', '2023-04-01 11:00:00', '2023-04-01', 1);

-- Таблиця participer
CREATE TABLE participer (
  id_user INTEGER NOT NULL,
  id_loisir INTEGER NOT NULL
);

INSERT INTO participer (id_user, id_loisir) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 3),
(3, 4),
(4, 3),
(4, 4);

-- Таблиця posseder
CREATE TABLE posseder (
  id_user INTEGER NOT NULL,
  id_statut INTEGER NOT NULL
);

INSERT INTO posseder (id_user, id_statut) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- Таблиця se_connecter
CREATE TABLE se_connecter (
  id_user INTEGER NOT NULL,
  id_login INTEGER NOT NULL
);

INSERT INTO se_connecter (id_user, id_login) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- Таблиця statut
CREATE TABLE statut (
  id_statut SERIAL PRIMARY KEY,
  activite VARCHAR(255)
);

INSERT INTO statut (id_statut, activite) VALUES
(1, 'En ligne'),
(2, 'Hors ligne'),
(3, 'Occupé'),
(4, 'En vacances');

-- Таблиця users
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  avatar VARCHAR(255),
  nom VARCHAR(255),
  prenom VARCHAR(255),
  login VARCHAR(255),
  email VARCHAR(255),
  pass VARCHAR(255),
  dateNaissance DATE,
  telephone VARCHAR(20),
  statut VARCHAR(50),
  sexe CHAR(1),
  date_create TIMESTAMP
);

INSERT INTO users (id, avatar, nom, prenom, login, email, pass, dateNaissance, telephone, statut, sexe, date_create) VALUES
(1, 'avatar1.jpg', 'Dupont', 'Jean', 'jdupont', 'jean.dupont@example.com', 'password1', '1990-01-01', '0612345678', 'actif', 'M', '2023-01-01 10:00:00'),
(2, 'avatar2.jpg', 'Martin', 'Marie', 'mmartin', 'marie.martin@example.com', 'password2', '1992-02-02', '0687654321', 'inactif', 'F', '2023-02-01 11:00:00'),
(3, 'avatar3.jpg', 'Durand', 'Pierre', 'pdurand', 'pierre.durand@example.com', 'password3', '1985-03-03', '0654321789', 'actif', 'M', '2023-03-01 12:00:00'),
(4, 'avatar4.jpg', 'Lefebvre', 'Sophie', 'slefebvre', 'sophie.lefebvre@example.com', 'password4', '1995-04-04', '0698765432', 'inactif', 'F', '2023-04-01 13:00:00');


-- PRIMARY KEYS
ALTER TABLE agenda ADD PRIMARY KEY (id_agenda);
ALTER TABLE a_un_agenda ADD PRIMARY KEY (id_user, id_agenda);
ALTER TABLE calendrier ADD PRIMARY KEY (id_loisir);
ALTER TABLE creer ADD PRIMARY KEY (id_user, id_loisir);
ALTER TABLE envoyer_mess ADD PRIMARY KEY (id_user, id_message);
ALTER TABLE etre_ami ADD PRIMARY KEY (id_ami1, id_ami2);
ALTER TABLE login ADD PRIMARY KEY (id_login);
ALTER TABLE messages ADD PRIMARY KEY (id_message);
ALTER TABLE participer ADD PRIMARY KEY (id_user, id_loisir);
ALTER TABLE posseder ADD PRIMARY KEY (id_user, id_statut);
ALTER TABLE se_connecter ADD PRIMARY KEY (id_user, id_login);
ALTER TABLE statut ADD PRIMARY KEY (id_statut);
ALTER TABLE users ADD PRIMARY KEY (id);

-- INDEXES
CREATE UNIQUE INDEX unique_login ON users(login);
CREATE UNIQUE INDEX unique_email ON users(email);

-- FOREIGN KEYS
ALTER TABLE a_un_agenda
  ADD CONSTRAINT fk_a_un_agenda_user FOREIGN KEY (id_user) REFERENCES users(id),
  ADD CONSTRAINT fk_a_un_agenda_agenda FOREIGN KEY (id_agenda) REFERENCES agenda(id_agenda);

ALTER TABLE creer
  ADD CONSTRAINT fk_creer_user FOREIGN KEY (id_user) REFERENCES users(id),
  ADD CONSTRAINT fk_creer_loisir FOREIGN KEY (id_loisir) REFERENCES calendrier(id_loisir);

ALTER TABLE envoyer_mess
  ADD CONSTRAINT fk_envoyer_user FOREIGN KEY (id_user) REFERENCES users(id),
  ADD CONSTRAINT fk_envoyer_message FOREIGN KEY (id_message) REFERENCES messages(id_message);

ALTER TABLE etre_ami
  ADD CONSTRAINT fk_ami1 FOREIGN KEY (id_ami1) REFERENCES users(id),
  ADD CONSTRAINT fk_ami2 FOREIGN KEY (id_ami2) REFERENCES users(id);

ALTER TABLE participer
  ADD CONSTRAINT fk_participer_user FOREIGN KEY (id_user) REFERENCES users(id),
  ADD CONSTRAINT fk_participer_loisir FOREIGN KEY (id_loisir) REFERENCES calendrier(id_loisir);

ALTER TABLE posseder
  ADD CONSTRAINT fk_posseder_user FOREIGN KEY (id_user) REFERENCES users(id),
  ADD CONSTRAINT fk_posseder_statut FOREIGN KEY (id_statut) REFERENCES statut(id_statut);

ALTER TABLE se_connecter
  ADD CONSTRAINT fk_connecter_user FOREIGN KEY (id_user) REFERENCES users(id),
  ADD CONSTRAINT fk_connecter_login FOREIGN KEY (id_login) REFERENCES login(id_login);

--