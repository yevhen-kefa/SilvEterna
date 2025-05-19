<?php
require_once 'silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ce script est destiné à être exécuté une seule fois pour configurer un administrateur
// Ensuite, il devrait être supprimé ou protégé pour éviter l'ajout non autorisé d'administrateurs

// Vérifications de sécurité - décommentez pour exécuter une fois puis recommentez
// exit("Ce script est désactivé pour des raisons de sécurité. Pour l'utiliser, modifiez le code source.");

// Établir la connexion
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

// Vérifier si la table 'admin_users' existe, sinon la créer
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
        die("Erreur lors de la création de la table admin_users: " . pg_last_error($conn));
    }
    
    echo "Table admin_users créée avec succès.<br>";
}

// Ajouter un administrateur par son login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $login = $_POST['login'];
    
    // Vérifier si l'utilisateur existe
    $checkUserQuery = "SELECT id FROM users WHERE login = $1";
    $checkUserResult = pg_query_params($conn, $checkUserQuery, array($login));
    
    if (pg_num_rows($checkUserResult) > 0) {
        $userId = pg_fetch_result($checkUserResult, 0, 0);
        
        // Vérifier si l'utilisateur est déjà administrateur
        $checkAdminQuery = "SELECT id_user FROM admin_users WHERE id_user = $1";
        $checkAdminResult = pg_query_params($conn, $checkAdminQuery, array($userId));
        
        if (pg_num_rows($checkAdminResult) > 0) {
            echo "L'utilisateur " . htmlspecialchars($login) . " est déjà administrateur.";
        } else {
            // Ajouter l'utilisateur comme administrateur
            $addAdminQuery = "INSERT INTO admin_users (id_user) VALUES ($1)";
            $addAdminResult = pg_query_params($conn, $addAdminQuery, array($userId));
            
            if ($addAdminResult) {
                echo "L'utilisateur " . htmlspecialchars($login) . " a été ajouté comme administrateur avec succès.";
            } else {
                echo "Erreur lors de l'ajout de l'administrateur: " . pg_last_error($conn);
            }
        }
    } else {
        echo "Utilisateur non trouvé.";
    }
}

// Afficher tous les administrateurs
$listAdminsQuery = "SELECT u.id, u.login, u.nom, u.prenom, a.date_creation
                   FROM admin_users a
                   JOIN users u ON a.id_user = u.id
                   ORDER BY u.nom, u.prenom";
$listAdminsResult = pg_query($conn, $listAdminsQuery);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration des administrateurs - SilvEterna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        form {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .warning {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Configuration des administrateurs SilvEterna</h1>
    
    <div class="warning">
        <strong>Attention :</strong> Ce script est destiné à être utilisé uniquement par le développeur pour 
        la configuration initiale des administrateurs. Une fois configuré, ce script devrait être supprimé 
        ou protégé contre les accès non autorisés.
    </div>
    
    <form method="POST" action="">
        <h2>Ajouter un administrateur</h2>
        <div>
            <label for="login">Login de l'utilisateur :</label>
            <input type="text" id="login" name="login" required>
        </div>
        <button type="submit">Ajouter comme administrateur</button>
    </form>
    
    <h2>Liste des administrateurs</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Date d'ajout</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($listAdminsResult && pg_num_rows($listAdminsResult) > 0): ?>
                <?php while ($admin = pg_fetch_assoc($listAdminsResult)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['id']); ?></td>
                        <td><?php echo htmlspecialchars($admin['login']); ?></td>
                        <td><?php echo htmlspecialchars($admin['nom']); ?></td>
                        <td><?php echo htmlspecialchars($admin['prenom']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($admin['date_creation'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Aucun administrateur configuré.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <strong>Note :</strong> Pour activer les droits d'administration lors de la connexion, vous devez 
        modifier le script de login pour vérifier si l'utilisateur est dans la table admin_users.
    </p>
</body>
</html>

<?php
// Libérer les résultats
if (isset($checkTableResult)) pg_free_result($checkTableResult);
if (isset($checkUserResult)) pg_free_result($checkUserResult);
if (isset($checkAdminResult)) pg_free_result($checkAdminResult);
if (isset($listAdminsResult)) pg_free_result($listAdminsResult);

// Fermer la connexion à la base de données
pg_close($conn);
?>