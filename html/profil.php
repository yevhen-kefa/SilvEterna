<?php

session_start();
require_once "../connexion.inc.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Chargement des données de l'utilisateur
$stmt = $cnx->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Calcul de l'âge
$birthDate = new DateTime($user['datenaissance']);
$today = new DateTime();
$age = $today->diff($birthDate)->y;

// Format de la date d'enregistrement
$dateCreated = (new DateTime($user['date_create']))->format('d/m/Y');
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SilvEterna</title>
    <link rel="stylesheet" href="assets/styles/styles_index.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h1 class="logo">SilvEterna</h1>
            <nav>
                <ul>
                    <li><a href="calendar.html">Calendrier</a></li>
                    <li><a href="jeux.html">Jeux</a></li>
                    <li><a href="option.html">Option</a></li>
                    <li><a href="../deconnexion.php">Deconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="profile">
            <div class="profile-header">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Flower_garden_on_Tet_2021.jpg/640px-Flower_garden_on_Tet_2021.jpg" alt="fleurs" class="header-img">
                <div class="avatar-container">
                    <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fi.pinimg.com%2F736x%2Fb5%2F78%2Fcf%2Fb578cf8c3e4b937ee94c56e59930690c.jpg&f=1&nofb=1&ipt=18feba40ed4470561cdfbfdf8a801f6ac76269e14e8ef0cef664440e845d1d08" alt="Kasane Teto" class="avatar">
                </div>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
                <p><strong>Age:</strong> <?= $age ?> ans</p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Membre depuis:</strong> <?= $dateCreated ?></p>
            </div>
        </main>

        <aside class="friends">
            <h3>Vos Amis:</h3>
            <ul class="friends-list">
                <li>
                  <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRRzhLIs9SIoNtBwHQy0sYDa8-51bJA4rHuqw&s" alt="Jean" class="friend-avatar">
                  <div class="friend-details">
                    <span class="friend-name">Jean</span>
                    <button class="friend-btn">Envoyer un message</button>
                  </div>

                  <img src="Frieren" alt="Frieren" class="friend-avatar">
                  <div class="friend-details" style="display: flexbox; flex-direction: row;">
                    <span class="friend-name">Frieren</span>
                    <button class="friend-btn">Envoyer un message</button>
                  </div>
                </li>
              </ul>            
              <div class="chat-box">Chat</div>
        </aside>
    </div>
</body>
</html>