<?php
// Démarrer une session si elle n'a pas déjà été démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Temps d'inactivité en secondes (2 minutes = 120 secondes))
$inactiveLimit = 120;

// Vérifier la dernière activité
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactiveLimit) {
    // Session ajournée
    session_unset();     
    session_destroy();   
    header("Location: /login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
?>
