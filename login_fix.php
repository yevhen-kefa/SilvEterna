<?php
// Fichier: login_fix.php
// Ce script vérifie et corrige le problème de connexion administrateur

require_once 'silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction d'affichage des messages
function showMessage($message, $type = 'info') {
    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; ";
    if ($type == 'success') echo "background-color: #d4edda; color: #155724;";
    else if ($type == 'error') echo "background-color: #f8d7da; color: #721c24;";
    else echo "background-color: #cce5ff; color: #004085;";
    echo "'>" . htmlspecialchars($message) . "</div>";
}

// Fonction pour mettre à jour le script de connexion
function updateLoginScript() {
    $login_file = 'login.php';
    if (!file_exists($login_file)) {
        return false;
    }
    
    $content = file_get_contents($login_file);
    if ($content === false) {
        return false;
    }
    
    // Vérifier si le code de vérification admin est déjà présent
    if (strpos($content, '$_SESSION[\'is_admin\']') !== false) {
        return true; // Le code est déjà présent
    }
    
    // Trouver la position où la session utilisateur est initialisée
    $pos = strpos($content, '$_SESSION[\'user_id\']');
    if ($pos === false) {
        return false;
    }
    
    // Trouver la fin de cette ligne
    $end_pos = strpos($content, ';', $pos);
    if ($end_pos === false) {
        return false;
    }
    
    // Insérer le code de vérification admin après la session utilisateur
    $new_code = <<<'CODE'

            // Vérifier si l'utilisateur est administrateur
            $checkAdminQuery = "SELECT id_user FROM admin_users WHERE id_user = $1";
            $checkAdminResult = pg_query_params($conn, $checkAdminQuery, array($user_id));
            $_SESSION['is_admin'] = (pg_num_rows($checkAdminResult) > 0);
            if ($checkAdminResult) pg_free_result($checkAdminResult);
CODE;
    
    $updated_content = substr($content, 0, $end_pos + 1) . $new_code . substr($content, $end_pos + 1);
    
    // Écrire le fichier mis à jour
    return file_put_contents($login_file, $updated_content) !== false;
}

// Connexion à la base de données
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

// Menu principal
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction de l'accès administrateur</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .btn { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; 
               border: none; border-radius: 4px; cursor: pointer; text-decoration: none; margin: 5px; }
        .btn:hover { background-color: #45a049; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Correction de l'accès administrateur</h1>";

// Traitement du formulaire de mise à jour du script de connexion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_login_script'])) {
    if (updateLoginScript()) {
        showMessage("Le script de connexion a été mis à jour avec succès pour vérifier les droits administrateur.", 'success');
    } else {
        showMessage("Impossible de mettre à jour le script de connexion. Veuillez le modifier manuellement.", 'error');
    }
}

// Vérifier si la table admin_users existe
$checkTableQuery = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'admin_users'
)";
$checkTableResult = pg_query($conn, $checkTableQuery);
$tableExists = pg_fetch_result($checkTableResult, 0, 0);

echo "<h2>État actuel du système</h2>";
echo "<ul>";
echo "<li>Table admin_users: " . ($tableExists === 't' ? "Existe" : "N'existe pas") . "</li>";
echo "<li>Session utilisateur: " . (isset($_SESSION['user_id']) ? "ID " . $_SESSION['user_id'] : "Non connecté") . "</li>";
echo "<li>Droits admin dans la session: " . (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true ? "Oui" : "Non") . "</li>";
echo "</ul>";

// Options disponibles
echo "<h2>Options de correction</h2>";

// Option 1: Mettre à jour le script de connexion
echo "<form method='POST'>";
echo "<input type='hidden' name='update_login_script' value='1'>";
echo "<button type='submit' class='btn'>Mettre à jour le script de connexion</button>";
echo "</form>";

// Option 2: Créer la table admin_users
if ($tableExists !== 't') {
    echo "<p><a href='check_admin_setup.php' class='btn'>Créer la table admin_users</a></p>";
}

// Option 3: Ajouter un admin manuellement
echo "<h2>Ajouter un utilisateur comme administrateur</h2>";
echo "<form method='POST' action='check_admin_setup.php'>";
echo "<label for='login'>Login de l'utilisateur:</label>";
echo "<input type='text' id='login' name='login' style='padding: 8px; margin: 10px 0; width: 100%; max-width: 300px;'>";
echo "<br><button type='submit' class='btn'>Ajouter comme administrateur</button>";
echo "</form>";

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='admin_debug.php'>Diagnostic avancé</a></li>";
echo "<li><a href='admin.php'>Page d'administration</a></li>";
echo "<li><a href='login.php'>Page de connexion</a></li>";
echo "</ul>";



// Fermer la connexion
if (isset($conn)) pg_close($conn);

echo "</body></html>";
?>