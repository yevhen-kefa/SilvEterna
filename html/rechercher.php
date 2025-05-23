<?php
require_once '../silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$isAdmin = $_SESSION['is_admin'] ?? false;

// Établir la connexion
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

// Fonction pour formater les dates
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date('Y-m-d', $timestamp);
}

// Traitement de la modification d'un utilisateur
$successMessage = '';
$errorMessage = '';

// Ajout d'un ami
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_friend'])) {
    $ami1 = $_SESSION['user_id']; // L'utilisateur actuel
    $ami2 = $_POST['ami_id']; // L'ami ciblé
    
    // Vérifier si l'amitié existe déjà
    $checkFriendshipQuery = "SELECT * FROM amis WHERE 
                            (ami_1 = $1 AND ami_2 = $2) OR 
                            (ami_1 = $2 AND ami_2 = $1)";
    $checkFriendshipResult = pg_query_params($conn, $checkFriendshipQuery, array($ami1, $ami2));
    
    if (pg_num_rows($checkFriendshipResult) > 0) {
        $errorMessage = "Cette relation d'amitié existe déjà.";
    } else {
        // Ajouter la nouvelle relation d'amitié
        $addFriendQuery = "INSERT INTO amis (ami_1, ami_2) 
                          VALUES ($1, $2)";
        $addFriendResult = pg_query_params($conn, $addFriendQuery, array($ami1, $ami2));
        
        if ($addFriendResult) {
            $successMessage = "L'ami a été ajouté avec succès.";
        } else {
            $errorMessage = "Erreur lors de l'ajout de l'ami : " . pg_last_error($conn);
        }
    }
}

// Traitement de la modification d'un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $userId = $_POST['user_id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $dateNaissance = $_POST['dateNaissance'];
    $telephone = $_POST['telephone'];
    $statut = $_POST['statut'];
    $sexe = $_POST['sexe'];
    
    // Vérifier si le login est déjà utilisé par un autre utilisateur
    $checkLoginQuery = "SELECT id FROM users WHERE login = $1 AND id != $2";
    $checkLoginResult = pg_query_params($conn, $checkLoginQuery, array($login, $userId));
    
    if (pg_num_rows($checkLoginResult) > 0) {
        $errorMessage = "Ce login est déjà utilisé par un autre utilisateur.";
    } else {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $checkEmailQuery = "SELECT id FROM users WHERE email = $1 AND id != $2";
        $checkEmailResult = pg_query_params($conn, $checkEmailQuery, array($email, $userId));
        
        if (pg_num_rows($checkEmailResult) > 0) {
            $errorMessage = "Cet email est déjà utilisé par un autre utilisateur.";
        } else {
            // Mise à jour des informations de l'utilisateur
            $updateQuery = "UPDATE users SET 
                nom = $1, 
                prenom = $2, 
                login = $3, 
                email = $4, 
                dateNaissance = $5, 
                telephone = $6, 
                statut = $7, 
                sexe = $8 
                WHERE id = $9";
            
            $updateResult = pg_query_params($conn, $updateQuery, array(
                $nom, $prenom, $login, $email, $dateNaissance, $telephone, $statut, $sexe, $userId
            ));
            
            if ($updateResult) {
                // Traitement de l'image de profil si téléchargée
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                    $allowed = array('jpg', 'jpeg', 'png', 'gif');
                    $filename = $_FILES['avatar']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($ext), $allowed)) {
                        // Dossier d'avatars
                        $avatar_dir = 'avatars/';
                        if (!file_exists($avatar_dir)) {
                            mkdir($avatar_dir, 0777, true);
                        }
                        
                        // Création d'un nom unique pour l'image
                        $new_avatar_name = uniqid('avatar_') . '.' . $ext;
                        $avatar_path = $avatar_dir . $new_avatar_name;
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                            // Mettre à jour le chemin de l'avatar dans la base de données
                            $updateAvatarQuery = "UPDATE users SET avatar = $1 WHERE id = $2";
                            pg_query_params($conn, $updateAvatarQuery, array($new_avatar_name, $userId));
                        } else {
                            $errorMessage = "Erreur lors du téléchargement de l'image.";
                        }
                    } else {
                        $errorMessage = "Le format du fichier n'est pas autorisé. Utilisez JPG, JPEG, PNG ou GIF.";
                    }
                }
                
                if (empty($errorMessage)) {
                    $successMessage = "Les informations de l'utilisateur ont été mises à jour avec succès.";
                }
            } else {
                $errorMessage = "Erreur lors de la mise à jour : " . pg_last_error($conn);
            }
        }
    }
}

// Suppression d'un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    
    // Vérifier et supprimer les relations dans les tables liées
    $tables = array(
        'a_un_agenda' => 'id_user',
        'creer' => 'id_user',
        'envoyer_mess' => 'id_user',
        'amis' => array('ami_1', 'ami_2'),
        'participer' => 'id_user',
        'posseder' => 'id_user',
        'se_connecter' => 'id_user'
    );
    
    pg_query($conn, "BEGIN");
    
    try {
        foreach ($tables as $table => $columns) {
            if (is_array($columns)) {
                foreach ($columns as $column) {
                    $deleteRelationQuery = "DELETE FROM $table WHERE $column = $1";
                    pg_query_params($conn, $deleteRelationQuery, array($userId));
                }
            } else {
                $deleteRelationQuery = "DELETE FROM $table WHERE $columns = $1";
                pg_query_params($conn, $deleteRelationQuery, array($userId));
            }
        }
        
        // Supprimer l'utilisateur
        $deleteUserQuery = "DELETE FROM users WHERE id = $1";
        $deleteResult = pg_query_params($conn, $deleteUserQuery, array($userId));
        
        if ($deleteResult) {
            pg_query($conn, "COMMIT");
            $successMessage = "L'utilisateur a été supprimé avec succès.";
        } else {
            throw new Exception(pg_last_error($conn));
        }
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");
        $errorMessage = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Création d'un nouvel utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT); // Hachage du mot de passe
    $dateNaissance = $_POST['dateNaissance'];
    $telephone = $_POST['telephone'];
    $statut = $_POST['statut'];
    $sexe = $_POST['sexe'];
    
    // Vérifier si le login est déjà utilisé
    $checkLoginQuery = "SELECT id FROM users WHERE login = $1";
    $checkLoginResult = pg_query_params($conn, $checkLoginQuery, array($login));
    
    if (pg_num_rows($checkLoginResult) > 0) {
        $errorMessage = "Ce login est déjà utilisé.";
    } else {
        // Vérifier si l'email est déjà utilisé
        $checkEmailQuery = "SELECT id FROM users WHERE email = $1";
        $checkEmailResult = pg_query_params($conn, $checkEmailQuery, array($email));
        
        if (pg_num_rows($checkEmailResult) > 0) {
            $errorMessage = "Cet email est déjà utilisé.";
        } else {
            // Insertion du nouvel utilisateur
            $createQuery = "INSERT INTO users (nom, prenom, login, email, pass, dateNaissance, telephone, statut, sexe, date_create) 
                            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW()) RETURNING id";
            
            $createResult = pg_query_params($conn, $createQuery, array(
                $nom, $prenom, $login, $email, $pass, $dateNaissance, $telephone, $statut, $sexe
            ));
            
            if ($createResult) {
                $newUser = pg_fetch_assoc($createResult);
                $userId = $newUser['id'];
                
                // Traitement de l'image de profil si téléchargée
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                    $allowed = array('jpg', 'jpeg', 'png', 'gif');
                    $filename = $_FILES['avatar']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($ext), $allowed)) {
                        // Dossier d'avatars
                        $avatar_dir = 'avatars/';
                        if (!file_exists($avatar_dir)) {
                            mkdir($avatar_dir, 0777, true);
                        }
                        
                        // Création d'un nom unique pour l'image
                        $new_avatar_name = uniqid('avatar_') . '.' . $ext;
                        $avatar_path = $avatar_dir . $new_avatar_name;
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                            // Mettre à jour le chemin de l'avatar dans la base de données
                            $updateAvatarQuery = "UPDATE users SET avatar = $1 WHERE id = $2";
                            pg_query_params($conn, $updateAvatarQuery, array($new_avatar_name, $userId));
                        }
                    }
                }
                
                $successMessage = "Le nouvel utilisateur a été créé avec succès.";
            } else {
                $errorMessage = "Erreur lors de la création : " . pg_last_error($conn);
            }
        }
    }
}

// Récupérer tous les utilisateurs avec recherche
$currentUserId = $_SESSION['user_id']; // ID de l'utilisateur actuel
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($searchTerm)) {
    // Requête avec filtre de recherche (nom, prénom ou login)
    $queryUsers = "SELECT * FROM users WHERE id != $1 AND 
                  (LOWER(nom) LIKE LOWER($2) OR 
                   LOWER(prenom) LIKE LOWER($2) OR 
                   LOWER(login) LIKE LOWER($2)) 
                   ORDER BY nom, prenom";
    $resultUsers = pg_query_params($conn, $queryUsers, array($currentUserId, '%'.$searchTerm.'%'));
} else {
    // Requête sans filtre
    $queryUsers = "SELECT * FROM users WHERE id != $1 ORDER BY nom, prenom";
    $resultUsers = pg_query_params($conn, $queryUsers, array($currentUserId));
}

// Récupérer les relations d'amitié de l'utilisateur actuel
$queryFriends = "SELECT * FROM amis WHERE ami_1 = $1 OR ami_2 = $1";
$resultFriends = pg_query_params($conn, $queryFriends, array($currentUserId));

// Créer un tableau d'IDs d'amis pour vérification rapide
$friendIds = array();
if ($resultFriends && pg_num_rows($resultFriends) > 0) {
    while ($friendship = pg_fetch_assoc($resultFriends)) {
        if ($friendship['ami_1'] == $currentUserId) {
            $friendIds[] = $friendship['ami_2'];
        } else {
            $friendIds[] = $friendship['ami_1'];
        }
    }
}

if ($resultUsers === false) {
    $errorMessage = "Erreur SQL : " . pg_last_error($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - SilvEterna</title>
    <style>
        @import url("html/assets/styles/sidebar.css");

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 200px;
            background-color: #c7e5d6;
            padding: 20px;
            box-sizing: border-box;
        }

        .logo {
            font-family: 'Cursive', sans-serif;
            font-size: 24px;
            color: #b52b2b;
            margin-bottom: 40px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            margin-bottom: 20px;
        }

        .sidebar a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }

        .content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }

        h1, h2 {
            color: #333;
        }

        .admin-panel {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        tr:hover {
            background-color: #f5f5f5;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
            text-align: center;
        }

        .modal-content h3 {
            margin-top: 0;
            color: #333;
        }

        .modal-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .confirm-btn {
            background-color: #4CAF50;
            color: white;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
        }

        .friend-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            margin-left: 10px;
        }

        .friend-active {
            background-color: #4CAF50;
        }

        form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }

        .add-user-btn {
            background-color: #2196F3;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        /* Styles pour la barre de recherche */
        .search-container {
            margin: 20px 0;
        }
        
        .search-form {
            display: flex;
            align-items: center;
            max-width: 600px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
        }
        
        .search-btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-size: 14px;
        }
        
        .clear-search {
            margin-left: 10px;
            color: #f44336;
            text-decoration: none;
            font-size: 14px;
        }
        
        .admin-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="profil.php"> <img class="logo" src="../img/silverternalogo.png" style="height: 15%; width: auto;"></a>
            <nav>
                <ul>
                    <li><a href="rechercher.php">Rechercher</a></li>
                    <li><a href="../Agenda.php">Calendrier</a></li>
                    <li><a href="jeux.php">Jeux</a></li>
                    <li><a href="option.php">Option</a></li>
                    <?php if ($isAdmin) : ?>
                    <li><a href="../Agenda_globale.php">Calendrier_globale</a></li>
                    <li><a href="../admin.php">Page admin utilisateur</a></li>
                    <li><a href="../admin_loisir.php">Page admin loisir</a></li>
                    <?php endif; ?>
                    <li><a href="../deconnexion.php">Deconnexion</a></li>
                </ul>
            </nav>
        </aside>

    <div class="content">
        <div class="admin-panel">
            <h1>Rechercher utilisateurs</h1>
            
            <?php if (!empty($successMessage)): ?>
                <div class="message success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="message error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="admin-actions">
                
                <!-- Barre de recherche -->
                <div class="search-container">
                    <form action="" method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Rechercher par nom, prénom ou login..." 
                               value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>" class="search-input">
                        <button type="submit" class="search-btn">Rechercher</button>
                        <?php if (!empty($searchTerm)): ?>
                            <a href="?clear" class="clear-search">Effacer</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <h2>Liste des utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Login</th>
                        <th>Date de naissance</th>
                        <th>Statut</th>
                        <th>Sexe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultUsers && pg_num_rows($resultUsers) > 0): ?>
                        <?php while ($user = pg_fetch_assoc($resultUsers)): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($user['avatar']) ? '../avatars/' . htmlspecialchars($user['avatar']) : '/api/placeholder/50/50'; ?>" 
                                        alt="Avatar" width="50" height="50" style="border-radius: 50%;">
                                </td>
                                <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($user['login']); ?></td>
                                <td><?php echo !empty($user['datenaissance']) ? formatDate($user['datenaissance']) : ''; ?></td>
                                <td><?php echo htmlspecialchars($user['statut']); ?></td>
                                <td><?php echo htmlspecialchars($user['sexe']); ?></td>
                                <td>
                                    <?php if (in_array($user['id'], $friendIds)): ?>
                                        <span class="friend-status friend-active">Ami</span>
                                    <?php else: ?>
                                        <button class="action-btn edit-btn" onclick="openAddFriendModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?>')">Ajouter en ami</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php elseif (!empty($searchTerm)): ?>
                        <tr>
                            <td colspan="8">Aucun utilisateur trouvé pour la recherche "<?php echo htmlspecialchars($searchTerm); ?>"</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Aucun utilisateur trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal d'ajout d'ami -->
    <div id="addFriendModal" class="modal">
        <div class="modal-content">
            <h3>Ajouter un ami</h3>
            <p id="friendConfirmText">Voulez-vous ajouter cette personne comme ami ?</p>
            <div class="modal-actions">
                <form method="POST" action="">
                    <input type="hidden" id="ami_id" name="ami_id" value="">
                    <input type="hidden" name="add_friend" value="1">
                    <button type="submit" class="modal-btn confirm-btn">Confirmer</button>
                    <button type="button" class="modal-btn cancel-btn" onclick="closeAddFriendModal()">Annuler</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Références aux modals
        var addFriendModal = document.getElementById("addFriendModal");
        
        // Fonction pour ouvrir le modal d'ajout d'ami
        function openAddFriendModal(userId, userName) {
            document.getElementById("ami_id").value = userId;
            document.getElementById("friendConfirmText").innerText = "Voulez-vous ajouter " + userName + " comme ami ?";
            addFriendModal.style.display = "block";
        }
        
        // Fonction pour fermer le modal d'ajout d'ami
        function closeAddFriendModal() {
            addFriendModal.style.display = "none";
        }
        
        // Fermer le modal quand on clique en dehors
        window.onclick = function(event) {
            if (event.target == addFriendModal) {
                addFriendModal.style.display = "none";
            }
        }
    </script>
</body>
</html>