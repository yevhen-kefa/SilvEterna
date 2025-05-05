<?php
$host = "localhost";
$dbname = "silveterna"; 
$user = "postgres";
$pass = "pass"; 
$schema = "";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $cnx = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo("<p>Connexion réussie !</p>");  // Додано повідомлення про успішне підключення
} catch (PDOException $e) {
    echo "<p>ERREUR : La connexion a échouée</p>";
    echo "<p>Message d'erreur : " . $e->getMessage() . "</p>";  // Додано виведення повідомлення про помилку
}
?>
