<?php
require_once 'silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Vérifier si l'ID de l'utilisateur est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID utilisateur non valide']);
    exit;
}

$userId = (int)$_GET['id'];

// Établir la connexion
$conn = connectDB();
if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupérer les informations de l'utilisateur
$query = "SELECT id, avatar, nom, prenom, login, email, dateNaissance, telephone, statut, sexe FROM users WHERE id = $1";
$result = pg_query_params($conn, $query, array($userId));

if ($result && pg_num_rows($result) > 0) {
    $user = pg_fetch_assoc($result);
    
    // Formatage de la date de naissance pour l'input date HTML
    if (!empty($user['datenaissance'])) {
        $timestamp = strtotime($user['datenaissance']);
        $user['dateNaissance'] = date('Y-m-d', $timestamp);
    } else {
        $user['dateNaissance'] = '';
    }
    
    // Envoyer les données en JSON
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Utilisateur non trouvé']);
}

// Libérer le résultat et fermer la connexion
if ($result) pg_free_result($result);
pg_close($conn);
?>