<?php
// Fichier: check_admin_setup.php
// Ce script vérifie et corrige les problèmes d'accès administrateur

// Inclure la configuration
require_once 'silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour afficher les messages
function showMessage($message, $type = 'info') {
    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; ";
    if ($type == 'success') echo "background-color: #d4edda; color: #155724;";
    else if ($type == 'error') echo "background-color: #f8d7da; color: #721c24;";
    else echo "background-color: #cce5ff; color: #004085;";
    echo "'>" . htmlspecialchars($message) . "</div>";
}

// Établir la connexion à la base de données
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

// 1. Vérifier si la table admin_users existe, sinon la créer
echo "<h2>Vérification de la table admin_users</h2>";
$checkTableQuery = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'admin_users'
)";
$checkTableResult = pg_query($conn, $checkTableQuery);
$tableExists = pg_fetch_result($checkTableResult, 0, 0);

if ($tableExists !== 't') {
    $createTableQuery = "CREATE TABLE admin_users (
        id_user INTEGER PRIMARY KEY,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_user) REFERENCES users(id)
    )";
    
    $createResult = pg_query($conn, $createTableQuery);
    if (!$createResult) {
        showMessage("Erreur lors de la création de la table admin_users: " . pg_last_error($conn), 'error');
    } else {
        showMessage("Table admin_users créée avec succès.", 'success');
    }
} else {
    showMessage("La table admin_users existe déjà.");
}

// 2. Vérifier si l'utilisateur courant est connecté
echo "<h2>Vérification de la session utilisateur</h2>";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    showMessage("Utilisateur connecté avec ID: " . $userId);
    
    // Récupérer les détails de l'utilisateur
    $userQuery = "SELECT id, login, nom, prenom FROM users WHERE id = $1";
    $userResult = pg_query_params($conn, $userQuery, array($userId));
    
    if ($userResult && pg_num_rows($userResult) > 0) {
        $user = pg_fetch_assoc($userResult);
        showMessage("Utilisateur identifié: " . htmlspecialchars($user['prenom']) . " " . htmlspecialchars($user['nom']) . " (login: " . htmlspecialchars($user['login']) . ")");
    } else {
        showMessage("Impossible de récupérer les informations de l'utilisateur.", 'error');
    }
    
    // 3. Vérifier si l'utilisateur est admin
    echo "<h2>Vérification des droits administrateur</h2>";
    $checkAdminQuery = "SELECT id_user FROM admin_users WHERE id_user = $1";
    $checkAdminResult = pg_query_params($conn, $checkAdminQuery, array($userId));
    
    if ($checkAdminResult && pg_num_rows($checkAdminResult) > 0) {
        showMessage("Cet utilisateur est déjà administrateur.", 'success');
        // S'assurer que la session est correctement initialisée
        $_SESSION['is_admin'] = true;
        showMessage("Variable de session 'is_admin' initialisée à 'true'.", 'success');
    } else {
        showMessage("Cet utilisateur n'est pas administrateur.", 'error');
        
        // Formulaire pour ajouter cet utilisateur comme admin si nécessaire
        echo "<form method='POST'>";
        echo "<input type='hidden' name='make_admin' value='1'>";
        echo "<input type='hidden' name='user_id' value='" . $userId . "'>";
        echo "<button type='submit' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;'>Ajouter comme administrateur</button>";
        echo "</form>";
    }
} else {
    showMessage("Aucun utilisateur n'est connecté. Veuillez vous connecter avant d'exécuter ce script.", 'error');
}

// 4. Si l'utilisateur soumet le formulaire pour devenir admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['make_admin'])) {
    $makeAdminUserId = $_POST['user_id'];
    
    // Ajouter l'utilisateur comme administrateur
    $addAdminQuery = "INSERT INTO admin_users (id_user) VALUES ($1)";
    $addAdminResult = pg_query_params($conn, $addAdminQuery, array($makeAdminUserId));
    
    if ($addAdminResult) {
        $_SESSION['is_admin'] = true;
        showMessage("L'utilisateur a été ajouté comme administrateur avec succès. La variable de session 'is_admin' est maintenant définie.", 'success');
        echo "<script>setTimeout(function(){ window.location.href = 'admin.php'; }, 3000);</script>";
        showMessage("Redirection vers admin.php dans 3 secondes...");
    } else {
        showMessage("Erreur lors de l'ajout de l'administrateur: " . pg_last_error($conn), 'error');
    }
}

// 5. Afficher les informations de débogage sur la session
echo "<h2>Informations de débogage de session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 6. Afficher un lien vers les pages admin
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='admin.php'>Page d'administration</a></li>";
echo "<li><a href='login.php'>Page de connexion</a></li>";
echo "</ul>";

// Fermer la connexion à la base de données
if (isset($conn)) pg_close($conn);
?>