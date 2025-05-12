<?php
session_start();
session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session

// Redirige vers la page de connexion ou la page d'accueil
header("Location: login.php");
exit;
?>