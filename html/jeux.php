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
    <title>Jeux - SilvEterna</title>
    <style>
        @import url("assets/styles/sidebar.css");

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
        }
        .container {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .main-content h2 {
            color: #333;
        }

        .game-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.05);
        }

        .game-card img {
            width: 120px;
            height: 120px;
            margin-right: 20px;
            border-radius: 10px;
        }

        .game-card button {
            padding: 10px 20px;
            background-color: #d9138f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .game-info {
            flex-grow: 1;
        }

        .game-info h3 {
            margin: 0;
            color: #444;
        }

        .game-info p {
            color: #666;
            margin: 5px 0 10px 0;
        }

        .iframe-container {
            margin-top: 30px;
            display: none; /* caché au début */
        }

        .iframe-container iframe {
            width: 100%;
            height: 600px;
            border: none;
        }
    </style>
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
                    <li><a href="../Agenda_globale.php">Calendrier_globale</a></li>
                    <li><a href="../admin.php">Page admin utilisateur</a></li>
                    <li><a href="../admin_loisir.php">Page admin loisir</a></li>
                    <?php endif; ?>
                    <li><a href="../deconnexion.php">Deconnexion</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-content">
            <h2>JEUX DISPONIBLES</h2>

            <div class="game-card">
                <img src="https://www.svgrepo.com/show/125004/chess-horse.svg" alt="Jeu d'échecs">
                <div class="game-info">
                    <h3>Jeu d'Échecs</h3>
                    <p>Affrontez un ami ou l'ordinateur dans une partie stratégique classique.</p>
                    <button onclick="window.open('https://lichess.org', '_blank')">Jouer</button>

                </div>
            </div>
                <div class="game-card">
                <img src="https://www.svgrepo.com/show/276229/checkers.svg" alt="Jeu de dames">
                <div class="game-info">
                    <h3>Jeu de Dames</h3>
                    <p>Affrontez un ami ou l'ordinateur dans une partie stratégique classique.</p>
                    <button onclick="window.open('https://playpager.com/dames/', '_blank')">Jouer</button>

                </div>
            </div>

            <div id="iframeJeu" class="iframe-container">
                <iframe src="https://playpager.com/dames/" allowfullscreen></iframe>
            </div>

            <div class="game-card">
                <img src="https://www.svgrepo.com/show/519357/solitairecg.svg" alt="Solitaire">
                <div class="game-info">
                    <h3>Solitaire</h3>
                    <p>Affrontez-vous vous-même dans une partie de solitaire captivante mêlant stratégie et concentration</p>
                    <button onclick="window.open('https://www.jeu-du-solitaire.com/', '_blank')">Jouer</button>

                </div>
            </div>

            <div id="iframeJeu" class="iframe-container">
                <iframe src="https://www.jeu-du-solitaire.com/" allowfullscreen></iframe>
            </div>

            <div id="iframeJeu" class="iframe-container">
                <iframe src="https://lichess.org/embed" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <script>
        function lancerJeu() {
            const iframeDiv = document.getElementById('iframeJeu');
            iframeDiv.style.display = 'block';
            iframeDiv.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
