<?php

require_once "connexion.inc.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['em'] ?? '';
    $pass = $_POST['pass'] ?? '';

    $stmt = $cnx->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['pass'])) {
        session_start(); 
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: html/profil.php"); 
        exit;
    } else {
        $error = "Identifiant ou mot de passe incorrect.";
    }
}

// sudo -u nom_username -i  psql -d nom_db  - connexion au postgresql

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SilvEterna</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <div class="illustration">
            <img src="img/log_img.svg" alt="Illustration de personnes âgées">
        </div>
        <div class="login-form">
            <h1>SilvEterna</h1>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="post" action="">
                <label for="em">Identifiant :</label>
                <input type="email" id="em" name="em" placeholder="vous@exemple.com" required>

                <label for="pass">Mot de Passe :</label>
                <input type="password" id="pass" name="pass" placeholder="Entrez votre mot de passe" required>

                <button type="submit">Connexion</button>
            </form>
            <p>Vous n'avez pas de compte ? Contactez l'Administrateur</p>
        </div>
    </div>
</body>
</html>
