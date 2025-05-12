<?php
// config.php - Fichier de configuration pour la connexion à la base de données

// Informations de connexion à la base de données PostgreSQL
$config = [
    'host' => 'localhost',
    'dbname' => 'silveterna',
    'user' => 'postgres',
    'password' => 'pass',
];

// Fonction pour établir la connexion à la base de données
function connectDB() {
    global $config;
    
    $dsn = "host={$config['host']} dbname={$config['dbname']} user={$config['user']} password={$config['password']}";
    
    $conn = pg_connect($dsn);
    
    if (!$conn) {
        die("Erreur de connexion à la base de données: " . pg_last_error());
    }
    
    return $conn;
}
?>