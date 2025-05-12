<?php
$host = "localhost";
$dbname = "silveterna"; 
$user = "postgres";
$pass = "2606"; 
$schema = "";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $cnx = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo "<p>ERREUR : La connexion a échouée</p>";
    echo "<p>Message d'erreur : " . $e->getMessage() . "</p>"; 
}
?>
