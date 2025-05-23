<?php
require_once 'silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
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
            // Préparer la requête de mise à jour
            if (!empty($_POST['pass'])) {
                // Si un nouveau mot de passe est fourni, le hacher et l'inclure dans la mise à jour
                $hashedPassword = password_hash($_POST['pass'], PASSWORD_DEFAULT);
                $updateQuery = "UPDATE users SET 
                    nom = $1, 
                    prenom = $2, 
                    login = $3, 
                    email = $4, 
                    dateNaissance = $5, 
                    telephone = $6, 
                    statut = $7, 
                    sexe = $8,
                    pass = $9
                    WHERE id = $10";
                
                $updateResult = pg_query_params($conn, $updateQuery, array(
                    $nom, $prenom, $login, $email, $dateNaissance, $telephone, $statut, $sexe, $hashedPassword, $userId
                ));
            } else {
                // Si aucun mot de passe n'est fourni, ne pas le modifier
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
            }
            
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

// Création d'un nouvel utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $dateNaissance = $_POST['dateNaissance'];
    $telephone = $_POST['telephone'];
    $statut = $_POST['statut'];
    $sexe = $_POST['sexe'];
    
    // Vérifier qu'un mot de passe a été fourni
    if (empty($_POST['pass'])) {
        $errorMessage = "Le mot de passe est obligatoire pour créer un utilisateur.";
    } else {
        $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT); // Hachage du mot de passe
        
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
                    
                    $successMessage = "Le nouvel utilisateur a été créé avec succès avec le mot de passe défini.";
                } else {
                    $errorMessage = "Erreur lors de la création : " . pg_last_error($conn);
                }
            }
        }
    }
}

// Récupérer tous les utilisateurs
$queryUsers = "SELECT * FROM users ORDER BY nom, prenom";
$resultUsers = pg_query($conn, $queryUsers);

if ($resultUsers === false) {
    $errorMessage = "Erreur SQL : " . pg_last_error($conn);
}

function generateTemporaryPassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Traitement de la réinitialisation de mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $userId = $_POST['user_id'];
    
    // Générer un nouveau mot de passe temporaire
    $newPassword = generateTemporaryPassword();
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe dans la base de données
    $updatePasswordQuery = "UPDATE users SET pass = $1 WHERE id = $2";
    $updateResult = pg_query_params($conn, $updatePasswordQuery, array($hashedPassword, $userId));
    
    if ($updateResult) {
        $successMessage = "Mot de passe réinitialisé avec succès. Nouveau mot de passe temporaire : <strong>" . $newPassword . "</strong><br>L'utilisateur devra le changer lors de sa prochaine connexion.";
    } else {
        $errorMessage = "Erreur lors de la réinitialisation du mot de passe : " . pg_last_error($conn);
    }
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
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
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
            margin-bottom: 20px;
        }

        .password-toggle {
    position: relative;
}

.password-toggle input {
    padding-right: 40px;
}

.password-toggle button {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    font-size: 14px;
}
    </style>
</head>
<body>
   <aside class="sidebar">
        <a href="html/profil.php"> <img class="logo" src="img/silverternalogo.png" style="height: 15%; width: auto;"></a>
        <nav>
            <ul>
                <li><a href="html/rechercher.php">Rechercher</a></li>
                <li><a href="Agenda.php">Calendrier</a></li>
                <li><a href="html/jeux.php">Jeux</a></li>
                <li><a href="html/option.php">Option</a></li>
                <?php if ($isAdmin) : ?>
                <li><a href="Agenda_globale.php">Calendrier_globale</a></li>
                <li><a href="admin.php">Page admin utilisateur</a></li>
                <li><a href="admin_loisir.php">Page admin loisir</a></li>
                <?php endif; ?>
                <li><a href="deconnexion.php">Deconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <div class="content">
        <div class="admin-panel">
            <h1>Panel d'administration</h1>
            
            <?php if (!empty($successMessage)): ?>
                <div class="message success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="message error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <button class="add-user-btn" id="addUserBtn">Ajouter un nouvel utilisateur</button>
            
            <h2>Liste des utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Login</th>
                        <th>Email</th>
                        <th>Date de naissance</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Sexe</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultUsers && pg_num_rows($resultUsers) > 0): ?>
                        <?php while ($user = pg_fetch_assoc($resultUsers)): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($user['avatar']) ? 'avatars/' . htmlspecialchars($user['avatar']) : '/api/placeholder/50/50'; ?>" 
                                        alt="Avatar" width="50" height="50" style="border-radius: 50%;">
                                </td>
                                <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($user['login']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo !empty($user['datenaissance']) ? formatDate($user['datenaissance']) : ''; ?></td>
                                <td><?php echo htmlspecialchars($user['telephone']); ?></td>
                                <td><?php echo htmlspecialchars($user['statut']); ?></td>
                                <td><?php echo htmlspecialchars($user['sexe']); ?></td>
                                <td><?php echo htmlspecialchars($user['is_admin']); ?></td>
                                <td>
                                <button class="action-btn edit-btn" onclick="editUser(<?php echo $user['id']; ?>)">Modifier</button>
                                <button class="action-btn" style="background-color: #ff9800;" onclick="resetPassword(<?php echo $user['id']; ?>)">Réinitialiser MDP</button>
                                <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $user['id']; ?>)">Supprimer</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">Aucun utilisateur trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour modifier un utilisateur -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Modifier l'utilisateur</h2>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="update_user" value="1">
                
                <div class="form-group">
                    <label for="edit_avatar">Avatar:</label>
                    <img id="avatarPreview" class="avatar-preview" src="/api/placeholder/100/100" alt="Avatar prévisualisation">
                    <input type="file" id="edit_avatar" name="avatar" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="edit_nom">Nom:</label>
                    <input type="text" id="edit_nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_prenom">Prénom:</label>
                    <input type="text" id="edit_prenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_login">Login:</label>
                    <input type="text" id="edit_login" name="login" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_dateNaissance">Date de naissance:</label>
                    <input type="date" id="edit_dateNaissance" name="dateNaissance">
                </div>
                
                <div class="form-group">
                    <label for="edit_telephone">Téléphone:</label>
                    <input type="tel" id="edit_telephone" name="telephone">
                </div>
                
                <div class="form-group">
                    <label for="edit_statut">Statut:</label>
                    <select id="edit_statut" name="statut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_sexe">Sexe:</label>
                    <select id="edit_sexe" name="sexe">
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                        <option value="A">Autre</option>
                    </select>
                </div>
                <div class="form-group">
            <label for="edit_pass">Nouveau mot de passe (laisser vide pour ne pas modifier):</label>
            <input type="password" id="edit_pass" name="pass" placeholder="Laisser vide pour conserver l'ancien mot de passe">
            </div>
                <button type="submit" class="submit-btn">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- Modal pour ajouter un utilisateur -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ajouter un nouvel utilisateur</h2>
            <form id="addForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="create_user" value="1">
                
                <div class="form-group">
                    <label for="add_avatar">Avatar:</label>
                    <img id="newAvatarPreview" class="avatar-preview" src="/api/placeholder/100/100" alt="Avatar prévisualisation">
                    <input type="file" id="add_avatar" name="avatar" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="add_nom">Nom:</label>
                    <input type="text" id="add_nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="add_prenom">Prénom:</label>
                    <input type="text" id="add_prenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="add_login">Login:</label>
                    <input type="text" id="add_login" name="login" required>
                </div>
                
                <div class="form-group">
                    <label for="add_email">Email:</label>
                    <input type="email" id="add_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="add_pass">Mot de passe:</label>
                    <input type="password" id="add_pass" name="pass" required>
                </div>
                
                <div class="form-group">
                    <label for="add_dateNaissance">Date de naissance:</label>
                    <input type="date" id="add_dateNaissance" name="dateNaissance">
                </div>
                
                <div class="form-group">
                    <label for="add_telephone">Téléphone:</label>
                    <input type="tel" id="add_telephone" name="telephone">
                </div>
                
                <div class="form-group">
                    <label for="add_statut">Statut:</label>
                    <select id="add_statut" name="statut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="add_sexe">Sexe:</label>
                    <select id="add_sexe" name="sexe">
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                        <option value="A">Autre</option>
                    </select>
                    </div>

                    <div class="form-group">
                    <label for="add_pass">Mot de passe:</label>
                    <input type="password" id="add_pass" name="pass" required placeholder="Mot de passe pour le nouvel utilisateur">
                    </div>
                
                <button type="submit" class="submit-btn">Créer l'utilisateur</button>
            </form>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close">&times;</span>
            <h2>Confirmation de suppression</h2>
            <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
            <form id="deleteForm" method="POST">
                <input type="hidden" name="user_id" id="delete_user_id">
                <input type="hidden" name="delete_user" value="1">
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <button type="button" class="action-btn" style="background-color: #ccc;" onclick="closeDeleteModal()">Annuler</button>
                    <button type="submit" class="action-btn delete-btn">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variables pour les modals
        var editModal = document.getElementById("editModal");
        var addModal = document.getElementById("addModal");
        var deleteModal = document.getElementById("deleteModal");
        
        // Boutons pour ouvrir les modals
        var addUserBtn = document.getElementById("addUserBtn");
        
        // Boutons pour fermer les modals
        var closeButtons = document.getElementsByClassName("close");
        
        // Afficher le modal d'ajout d'utilisateur
        addUserBtn.onclick = function() {
            addModal.style.display = "block";
        }
        
        // Fermer les modals quand on clique sur le X
        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].onclick = function() {
                editModal.style.display = "none";
                addModal.style.display = "none";
                deleteModal.style.display = "none";
            }
        }
        
        // Fermer les modals quand on clique en dehors
        window.onclick = function(event) {
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
        }
        
        // Fonction pour éditer un utilisateur
        function editUser(userId) {
            // Réinitialiser le formulaire
            document.getElementById("editForm").reset();
            
            // Faire une requête AJAX pour récupérer les données de l'utilisateur
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_user.php?id=' + userId, true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    var user = JSON.parse(this.responseText);
                    
                    // Remplir le formulaire avec les données de l'utilisateur
                    document.getElementById("edit_user_id").value = user.id;
                    document.getElementById("edit_nom").value = user.nom;
                    document.getElementById("edit_prenom").value = user.prenom;
                    document.getElementById("edit_login").value = user.login;
                    document.getElementById("edit_email").value = user.email;
                    document.getElementById("edit_dateNaissance").value = user.dateNaissance;
                    document.getElementById("edit_telephone").value = user.telephone;
                    document.getElementById("edit_statut").value = user.statut;
                    document.getElementById("edit_sexe").value = user.sexe;
                    
                    // Afficher l'avatar s'il existe
                    var avatarPreview = document.getElementById("avatarPreview");
                    if (user.avatar) {
                        avatarPreview.src = 'avatars/' + user.avatar;
                    } else {
                        avatarPreview.src = '/api/placeholder/100/100';
                    }
                    
                    // Afficher le modal
                    editModal.style.display = "block";
                }
            };
            
            xhr.send();
        }
        // Ajoutez ce code à la fin de votre section <script>

// Fonction pour confirmer la suppression d'un utilisateur
function confirmDelete(userId) {
    // Définir l'ID de l'utilisateur à supprimer
    document.getElementById("delete_user_id").value = userId;
    
    // Afficher le modal de confirmation
    deleteModal.style.display = "block";
}

// Fonction pour fermer le modal de confirmation de suppression
function closeDeleteModal() {
    deleteModal.style.display = "none";
}

// Prévisualisation de l'avatar lors du téléchargement (pour le formulaire d'édition)
document.getElementById('edit_avatar').addEventListener('change', function(event) {
    var file = event.target.files[0];
    var reader = new FileReader();
    
    reader.onload = function(e) {
        document.getElementById('avatarPreview').src = e.target.result;
    };
    
    if (file) {
        reader.readAsDataURL(file);
    }
});

// Prévisualisation de l'avatar lors du téléchargement (pour le formulaire d'ajout)
document.getElementById('add_avatar').addEventListener('change', function(event) {
    var file = event.target.files[0];
    var reader = new FileReader();
    
    reader.onload = function(e) {
        document.getElementById('newAvatarPreview').src = e.target.result;
    };
    
    if (file) {
        reader.readAsDataURL(file);
    }
});

function resetPassword(userId) {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        var userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        var resetInput = document.createElement('input');
        resetInput.type = 'hidden';
        resetInput.name = 'reset_password';
        resetInput.value = '1';
        
        form.appendChild(userIdInput);
        form.appendChild(resetInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function togglePassword(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'Masquer';
    } else {
        input.type = 'password';
        button.textContent = 'Afficher';
    }
}

// Ajouter des boutons pour afficher/masquer les mots de passe si souhaité
document.addEventListener('DOMContentLoaded', function() {
    // Pour le formulaire d'ajout
    const addPassField = document.getElementById('add_pass');
    if (addPassField) {
        addPassField.parentElement.classList.add('password-toggle');
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.textContent = 'Afficher';
        toggleBtn.onclick = () => togglePassword('add_pass', toggleBtn.id);
        toggleBtn.id = 'toggle_add_pass';
        addPassField.parentElement.appendChild(toggleBtn);
    }
    
    // Pour le formulaire de modification
    const editPassField = document.getElementById('edit_pass');
    if (editPassField) {
        editPassField.parentElement.classList.add('password-toggle');
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.textContent = 'Afficher';
        toggleBtn.onclick = () => togglePassword('edit_pass', toggleBtn.id);
        toggleBtn.id = 'toggle_edit_pass';
        editPassField.parentElement.appendChild(toggleBtn);
    }
});
</script>