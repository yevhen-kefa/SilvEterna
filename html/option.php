<?php
session_start();


require_once "../connexion.inc.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$isAdmin = $_SESSION['is_admin'] ?? false;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - SilvEterna</title>
    <style>
        @import url("assets/styles/sidebar.css");

        body {
            font-family: Arial, sans-serif;
            display: flex;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .main-content h2 {
            color: #333;
        }

        .main-content form {
            display: flex;
            flex-direction: column;
        }

        .main-content label {
            margin-top: 10px;
        }

        .main-content input,
        .main-content textarea {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .main-content .photo-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .main-content .photo-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .main-content .photo-container button {
            padding: 5px 10px;
            border: none;
            background-color: #d9138f;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .main-content h3 {
            margin-top: 20px;
        }

        .main-content label input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
        <a href="profil.php"> <img class="logo" src="img/silverternalogo.png" style="height: 25%; width: auto;"></a>
            <nav>
                <ul>
                    <li><a href="../Agenda.php">Calendrier</a></li>
                    <li><a href="jeux.php">Jeux</a></li>
                    <li><a href="option.php">Option</a></li>
                    <?php if ($isAdmin) : ?>
                    <li><a href="../admin.php">Page admin utilisateur</a></li>
                    <li><a href="../admin_loisir.php">Page admin loisirs</a></li>
                    <?php endif; ?>
                    <li><a href="../deconnexion.php">Deconnexion</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-content">
            <h2>PROFIL</h2>
            <form>
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom">

                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom">

                <label for="photo">Photo de Profil</label>
                <div class="photo-container">
                    <img src="path_to_profile_photo.jpg" alt="Photo de Profil">
                    <button type="button">Modifier</button>
                </div>

                <label for="loisirs">Loisirs</label>
                <textarea id="loisirs" name="loisirs"></textarea>

                <label for="date_naissance">Date de Naissance</label>
                <input type="date" id="date_naissance" name="date_naissance">

                <h3>Confidentialité</h3>
                <label>
                    <input type="checkbox" name="demandes_amis" checked>
                    Accepter les demandes d'amis
                </label>
                <label>
                    <input type="checkbox" name="messages_inconnus">
                    Autoriser les messages d'inconnus
                </label>
            </form>
        </div>
    </div>
</body>
</html>
