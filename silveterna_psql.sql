--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: a_un_agenda; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.a_un_agenda (
    id_user integer NOT NULL,
    id_agenda integer NOT NULL
);


ALTER TABLE public.a_un_agenda OWNER TO postgres;

--
-- Name: agenda; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.agenda (
    id_agenda integer NOT NULL,
    titre character varying(255),
    description text,
    date date,
    heure_debut time without time zone,
    heure_fin time without time zone,
    lieu character varying(255),
    type_evenement character varying(50),
    id_user integer
);


ALTER TABLE public.agenda OWNER TO postgres;

--
-- Name: agenda_id_agenda_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.agenda_id_agenda_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agenda_id_agenda_seq OWNER TO postgres;

--
-- Name: agenda_id_agenda_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.agenda_id_agenda_seq OWNED BY public.agenda.id_agenda;


--
-- Name: amis; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.amis (
    id integer NOT NULL,
    ami_1 integer NOT NULL,
    ami_2 integer NOT NULL,
    CONSTRAINT check_amis_differents CHECK ((ami_1 <> ami_2))
);


ALTER TABLE public.amis OWNER TO postgres;

--
-- Name: amis_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.amis_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.amis_id_seq OWNER TO postgres;

--
-- Name: amis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.amis_id_seq OWNED BY public.amis.id;


--
-- Name: calendrier; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendrier (
    id_loisir integer NOT NULL,
    titre character varying(255),
    description text,
    lieu character varying(255),
    date date,
    heure_deb time without time zone,
    heure_fin time without time zone,
    nombre_max_participants integer
);


ALTER TABLE public.calendrier OWNER TO postgres;

--
-- Name: calendrier_id_loisir_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.calendrier_id_loisir_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calendrier_id_loisir_seq OWNER TO postgres;

--
-- Name: calendrier_id_loisir_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.calendrier_id_loisir_seq OWNED BY public.calendrier.id_loisir;


--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chat_messages (
    id integer NOT NULL,
    sender_id integer NOT NULL,
    receiver_id integer NOT NULL,
    message_text text NOT NULL,
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_read boolean DEFAULT false
);


ALTER TABLE public.chat_messages OWNER TO postgres;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chat_messages_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chat_messages_id_seq OWNER TO postgres;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chat_messages_id_seq OWNED BY public.chat_messages.id;


--
-- Name: creer; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.creer (
    id_user integer NOT NULL,
    id_loisir integer NOT NULL
);


ALTER TABLE public.creer OWNER TO postgres;

--
-- Name: envoyer_mess; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.envoyer_mess (
    id_user integer NOT NULL,
    id_message integer NOT NULL
);


ALTER TABLE public.envoyer_mess OWNER TO postgres;

--
-- Name: login; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.login (
    id_login integer NOT NULL,
    password character varying(255)
);


ALTER TABLE public.login OWNER TO postgres;

--
-- Name: login_id_login_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.login_id_login_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.login_id_login_seq OWNER TO postgres;

--
-- Name: login_id_login_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.login_id_login_seq OWNED BY public.login.id_login;


--
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id_message integer NOT NULL,
    contenu text,
    heure_envoi timestamp without time zone,
    date_envoi date,
    compteur_msg_nonlu integer
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- Name: messages_id_message_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.messages_id_message_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.messages_id_message_seq OWNER TO postgres;

--
-- Name: messages_id_message_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.messages_id_message_seq OWNED BY public.messages.id_message;


--
-- Name: participer; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.participer (
    id_user integer NOT NULL,
    id_loisir integer NOT NULL
);


ALTER TABLE public.participer OWNER TO postgres;

--
-- Name: posseder; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.posseder (
    id_user integer NOT NULL,
    id_statut integer NOT NULL
);


ALTER TABLE public.posseder OWNER TO postgres;

--
-- Name: se_connecter; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.se_connecter (
    id_user integer NOT NULL,
    id_login integer NOT NULL
);


ALTER TABLE public.se_connecter OWNER TO postgres;

--
-- Name: statut; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.statut (
    id_statut integer NOT NULL,
    activite character varying(255)
);


ALTER TABLE public.statut OWNER TO postgres;

--
-- Name: statut_id_statut_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.statut_id_statut_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.statut_id_statut_seq OWNER TO postgres;

--
-- Name: statut_id_statut_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.statut_id_statut_seq OWNED BY public.statut.id_statut;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    avatar character varying(255),
    nom character varying(255),
    prenom character varying(255),
    login character varying(255),
    email character varying(255),
    pass character varying(255),
    datenaissance date,
    telephone character varying(20),
    statut character varying(50),
    sexe character(1),
    date_create timestamp without time zone,
    is_admin boolean DEFAULT false
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: agenda id_agenda; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agenda ALTER COLUMN id_agenda SET DEFAULT nextval('public.agenda_id_agenda_seq'::regclass);


--
-- Name: amis id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.amis ALTER COLUMN id SET DEFAULT nextval('public.amis_id_seq'::regclass);


--
-- Name: calendrier id_loisir; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendrier ALTER COLUMN id_loisir SET DEFAULT nextval('public.calendrier_id_loisir_seq'::regclass);


--
-- Name: chat_messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages ALTER COLUMN id SET DEFAULT nextval('public.chat_messages_id_seq'::regclass);


--
-- Name: login id_login; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.login ALTER COLUMN id_login SET DEFAULT nextval('public.login_id_login_seq'::regclass);


--
-- Name: messages id_message; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages ALTER COLUMN id_message SET DEFAULT nextval('public.messages_id_message_seq'::regclass);


--
-- Name: statut id_statut; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statut ALTER COLUMN id_statut SET DEFAULT nextval('public.statut_id_statut_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: a_un_agenda; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.a_un_agenda (id_user, id_agenda) FROM stdin;
\.


--
-- Data for Name: agenda; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.agenda (id_agenda, titre, description, date, heure_debut, heure_fin, lieu, type_evenement, id_user) FROM stdin;
2	Anniversaire	Anniversaire de Marie	2023-10-04	19:00:00	22:00:00	Maison de Marie	Personnel	\N
4	Voyage	Voyage Ã  Paris	2023-10-06	08:00:00	18:00:00	Paris	Personnel	\N
5	tetsd	hbdhxgdzi	2025-05-01	11:02:00	04:05:00	chedte	Rendez-vous	\N
\.


--
-- Data for Name: amis; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.amis (id, ami_1, ami_2) FROM stdin;
1	5	7
\.


--
-- Data for Name: calendrier; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendrier (id_loisir, titre, description, lieu, date, heure_deb, heure_fin, nombre_max_participants) FROM stdin;
1	RandonnÃ©e	RandonnÃ©e dans les montagnes	Montagne	2023-10-01	09:00:00	12:00:00	20
2	CinÃ©ma	SoirÃ©e cinÃ©ma	CinÃ©ma du centre	2023-10-02	19:00:00	21:00:00	50
3	Concert	Concert de rock	Salle de concert	2023-10-03	20:00:00	22:00:00	100
4	Exposition	Exposition d'art	Galerie d'art	2023-10-04	14:00:00	18:00:00	30
\.


--
-- Data for Name: chat_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chat_messages (id, sender_id, receiver_id, message_text, "timestamp", is_read) FROM stdin;
\.


--
-- Data for Name: creer; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.creer (id_user, id_loisir) FROM stdin;
\.


--
-- Data for Name: envoyer_mess; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.envoyer_mess (id_user, id_message) FROM stdin;
\.


--
-- Data for Name: login; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.login (id_login, password) FROM stdin;
1	password1
2	password2
3	password3
4	password4
\.


--
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id_message, contenu, heure_envoi, date_envoi, compteur_msg_nonlu) FROM stdin;
1	Bonjour !	2023-04-01 08:00:00	2023-04-01	0
2	Salut !	2023-04-01 09:00:00	2023-04-01	1
3	Comment Ã§a va ?	2023-04-01 10:00:00	2023-04-01	0
4	Bien et toi ?	2023-04-01 11:00:00	2023-04-01	1
\.


--
-- Data for Name: participer; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.participer (id_user, id_loisir) FROM stdin;
\.


--
-- Data for Name: posseder; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.posseder (id_user, id_statut) FROM stdin;
\.


--
-- Data for Name: se_connecter; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.se_connecter (id_user, id_login) FROM stdin;
\.


--
-- Data for Name: statut; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.statut (id_statut, activite) FROM stdin;
1	En ligne
2	Hors ligne
3	OccupÃ©
4	En vacances
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, avatar, nom, prenom, login, email, pass, datenaissance, telephone, statut, sexe, date_create, is_admin) FROM stdin;
6	avatar_682a01a1117c3.jpeg	kasane	Teto	tetoUTAU	contact@teto.xyz	$2y$10$7WhV4.QMusKFgQwyq65ohOtSinj4lBgH9ds35Z61D61tgU9Fiilcu	2111-02-11	545454654534	actif	A	2025-05-18 17:49:53.026396	f
7	avatar_682a029fd10a4.jpg	Chartier 	Pierre	x2007	HeadofTrading@Volet.Hybrides	$2y$10$IAXBUhxODJMxvz1nW18MSOVEYPFA7VFhFNSjfc9B61DEO0vL/cgQS	2001-09-11	2007	actif	M	2025-05-18 17:53:29.01731	f
8	avatar_682a0ab830ba5.jpeg	Nigga	Goku	nigga	contact@nigga.xyz	$2y$10$RlWxkK84.eSS8WIOXJrTfun8A.QURG8H8nYTDUGqhUtfotZOtAMlG	2006-02-11	0755555555	actif	M	2025-05-18 18:28:40.194375	f
5	avatar_682a016f51829.jpg	Tayoken	Garde	tayoken	contact@tayoken.xyz	$2y$10$DaJK9sXHP3./Snb29XDpH.dqFIu.Yjmsnam017LvHUn0BhzpqCg6i	2006-11-02	07 66 36 26 67	actif	M	2025-05-12 15:51:25.056695	t
\.


--
-- Name: agenda_id_agenda_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.agenda_id_agenda_seq', 3, true);


--
-- Name: amis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.amis_id_seq', 1, true);


--
-- Name: calendrier_id_loisir_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.calendrier_id_loisir_seq', 1, false);


--
-- Name: chat_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chat_messages_id_seq', 1, false);


--
-- Name: login_id_login_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.login_id_login_seq', 1, false);


--
-- Name: messages_id_message_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.messages_id_message_seq', 1, true);


--
-- Name: statut_id_statut_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.statut_id_statut_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 8, true);


--
-- Name: a_un_agenda a_un_agenda_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.a_un_agenda
    ADD CONSTRAINT a_un_agenda_pkey PRIMARY KEY (id_user, id_agenda);


--
-- Name: agenda agenda_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agenda
    ADD CONSTRAINT agenda_pkey PRIMARY KEY (id_agenda);


--
-- Name: amis amis_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.amis
    ADD CONSTRAINT amis_pkey PRIMARY KEY (id);


--
-- Name: calendrier calendrier_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendrier
    ADD CONSTRAINT calendrier_pkey PRIMARY KEY (id_loisir);


--
-- Name: chat_messages chat_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_pkey PRIMARY KEY (id);


--
-- Name: creer creer_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.creer
    ADD CONSTRAINT creer_pkey PRIMARY KEY (id_user, id_loisir);


--
-- Name: envoyer_mess envoyer_mess_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.envoyer_mess
    ADD CONSTRAINT envoyer_mess_pkey PRIMARY KEY (id_user, id_message);


--
-- Name: login login_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.login
    ADD CONSTRAINT login_pkey PRIMARY KEY (id_login);


--
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id_message);


--
-- Name: participer participer_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participer
    ADD CONSTRAINT participer_pkey PRIMARY KEY (id_user, id_loisir);


--
-- Name: posseder posseder_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posseder
    ADD CONSTRAINT posseder_pkey PRIMARY KEY (id_user, id_statut);


--
-- Name: se_connecter se_connecter_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.se_connecter
    ADD CONSTRAINT se_connecter_pkey PRIMARY KEY (id_user, id_login);


--
-- Name: statut statut_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statut
    ADD CONSTRAINT statut_pkey PRIMARY KEY (id_statut);


--
-- Name: amis uc_amis; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.amis
    ADD CONSTRAINT uc_amis UNIQUE (ami_1, ami_2);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: idx_chat_messages_receiver; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_chat_messages_receiver ON public.chat_messages USING btree (receiver_id);


--
-- Name: idx_chat_messages_sender; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_chat_messages_sender ON public.chat_messages USING btree (sender_id);


--
-- Name: unique_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX unique_email ON public.users USING btree (email);


--
-- Name: unique_login; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX unique_login ON public.users USING btree (login);


--
-- Name: chat_messages chat_messages_receiver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_receiver_id_fkey FOREIGN KEY (receiver_id) REFERENCES public.users(id);


--
-- Name: chat_messages chat_messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id);


--
-- Name: a_un_agenda fk_a_un_agenda_agenda; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.a_un_agenda
    ADD CONSTRAINT fk_a_un_agenda_agenda FOREIGN KEY (id_agenda) REFERENCES public.agenda(id_agenda);


--
-- Name: a_un_agenda fk_a_un_agenda_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.a_un_agenda
    ADD CONSTRAINT fk_a_un_agenda_user FOREIGN KEY (id_user) REFERENCES public.users(id);


--
-- Name: amis fk_ami_1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.amis
    ADD CONSTRAINT fk_ami_1 FOREIGN KEY (ami_1) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: amis fk_ami_2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.amis
    ADD CONSTRAINT fk_ami_2 FOREIGN KEY (ami_2) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: se_connecter fk_connecter_login; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.se_connecter
    ADD CONSTRAINT fk_connecter_login FOREIGN KEY (id_login) REFERENCES public.login(id_login);


--
-- Name: se_connecter fk_connecter_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.se_connecter
    ADD CONSTRAINT fk_connecter_user FOREIGN KEY (id_user) REFERENCES public.users(id);


--
-- Name: creer fk_creer_loisir; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.creer
    ADD CONSTRAINT fk_creer_loisir FOREIGN KEY (id_loisir) REFERENCES public.calendrier(id_loisir);


--
-- Name: creer fk_creer_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.creer
    ADD CONSTRAINT fk_creer_user FOREIGN KEY (id_user) REFERENCES public.users(id);


--
-- Name: envoyer_mess fk_envoyer_message; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.envoyer_mess
    ADD CONSTRAINT fk_envoyer_message FOREIGN KEY (id_message) REFERENCES public.messages(id_message);


--
-- Name: envoyer_mess fk_envoyer_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.envoyer_mess
    ADD CONSTRAINT fk_envoyer_user FOREIGN KEY (id_user) REFERENCES public.users(id);


--
-- Name: participer fk_participer_loisir; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participer
    ADD CONSTRAINT fk_participer_loisir FOREIGN KEY (id_loisir) REFERENCES public.calendrier(id_loisir);


--
-- Name: participer fk_participer_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.participer
    ADD CONSTRAINT fk_participer_user FOREIGN KEY (id_user) REFERENCES public.users(id);


--
-- Name: posseder fk_posseder_statut; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posseder
    ADD CONSTRAINT fk_posseder_statut FOREIGN KEY (id_statut) REFERENCES public.statut(id_statut);


--
-- Name: posseder fk_posseder_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.posseder
    ADD CONSTRAINT fk_posseder_user FOREIGN KEY (id_user) REFERENCES public.users(id);


--
-- Name: agenda fk_user; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agenda
    ADD CONSTRAINT fk_user FOREIGN KEY (id_user) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

