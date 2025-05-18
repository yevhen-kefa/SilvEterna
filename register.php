<?php

require_once "connexion.inc.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $dateInput = trim($_POST['date_naissance'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['pass'] ?? '';

    $dateObj = DateTime::createFromFormat('d/m/Y', $dateInput);
    if (!$dateObj) {
        $message = "Format de date invalide. Utilisez JJ/MM/AAAA.";
    } else {
        $dateNaissance = $dateObj->format('Y-m-d');

        $stmt = $cnx->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $message = "Un utilisateur avec cet email existe déjà.";
        } else {
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);

            $cnx->exec("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))");

            $stmt = $cnx->prepare("
                INSERT INTO users (nom, prenom, dateNaissance, telephone, email, pass, date_create)
                VALUES (:nom, :prenom, :dateNaissance, :telephone, :email, :pass, NOW())
            ");

            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'dateNaissance' => $dateNaissance,
                'telephone' => $telephone,
                'email' => $email,
                'pass' => $hashedPass
            ]);

            header("Location: login.php");
            exit;
        }
    }
}
$isAdmin = $_SESSION['is_admin'] ?? false;
if (!$isAdmin) {
    header("Location: index.php");
    exit;
}
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
        <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
        <form method="post" action="">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" placeholder="Nom" required />

            <label for="prenom">Prenom:</label>
            <input type="text" id="prenom" name="prenom" placeholder="Prenom" required />

            <label for="date-naissance">Date de naissance :</label>
            <input type="text" id="date-naissance" name="date_naissance" placeholder="JJ/MM/AAAA" required />

            <label for="telephone">Téléphone :</label>
            <input type="tel" id="telephone" name="telephone" placeholder="+33600047079" required />

            <label for="courriel">Courriel :</label>
            <input type="email" id="courriel" name="email" placeholder="mail@gmail.com" required />

            <label for="pass">Mot de passe :</label>
            <input type="password" id="pass" name="pass" placeholder="Mot de passe" required />

            <button type="submit">Inscription</button>
        </form>
    </div>
</div>
</body>
</html>
