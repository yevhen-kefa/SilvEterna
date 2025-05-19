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
                        <li><a href="../admin.php">Page admin utilisateur</a></li>
                        <li><a href="../admin_loisir.php">Page admin loisirs</a></li>
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
                <img src="data:image/png;base64,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" alt="Jeu de dames">
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
                <img src="data:image/jpeg;base64,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" alt="Solitaire">
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
