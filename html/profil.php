<?php
session_start();


require_once "../connexion.inc.php";
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
$isAdmin = $_SESSION['is_admin'] ?? false;
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
        <a href="profil.php"> <img class="logo" src="../img/silverternalogo.png" style="height: 25%; width: auto;"></a>

            <nav>
                <ul>
                    <li><a href="../Agenda.php">Calendrier</a></li>
                    <li><a href="jeux.php">Jeux</a></li>
                    <li><a href="option.php">Option</a></li>
                    <?php if ($isAdmin) : ?>
                    <li><a href="../admin.php">Page admin utilisateur</a></li>
                    <li><a href="../admin_loisir.php">Page admin loisir</a></li>
                    <?php endif; ?>
                    <li><a href="../deconnexion.php">Deconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main class="profile">
            <div class="profile-header">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Flower_garden_on_Tet_2021.jpg/640px-Flower_garden_on_Tet_2021.jpg" alt="fleurs" class="header-img">
                <div class="avatar-container">
                <img src="../avatars/<?= htmlspecialchars($user['avatar'])?>" alt="Avatar" class="avatar">                </div>
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