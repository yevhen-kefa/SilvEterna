<?php
session_start();
// Include database connection file
require_once "../silveterna_config.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}



$isAdmin = $_SESSION['is_admin'] ?? false;
$userId = $_SESSION['user_id'];

// Establish database connection
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

// Function to format dates
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date('Y-m-d', $timestamp);
}

// Process user profile update
$successMessage = '';
$errorMessage = '';

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
    
    // Check if login is already used by another user
    $checkLoginQuery = "SELECT id FROM users WHERE login = $1 AND id != $2";
    $checkLoginResult = pg_query_params($conn, $checkLoginQuery, array($login, $userId));
    
    if (pg_num_rows($checkLoginResult) > 0) {
        $errorMessage = "Ce login est déjà utilisé par un autre utilisateur.";
    } else {
        // Check if email is already used by another user
        $checkEmailQuery = "SELECT id FROM users WHERE email = $1 AND id != $2";
        $checkEmailResult = pg_query_params($conn, $checkEmailQuery, array($email, $userId));
        
        if (pg_num_rows($checkEmailResult) > 0) {
            $errorMessage = "Cet email est déjà utilisé par un autre utilisateur.";
        } else {
            // Update user information
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
                // Process avatar upload if provided
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                    $allowed = array('jpg', 'jpeg', 'png', 'gif');
                    $filename = $_FILES['avatar']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($ext), $allowed)) {
                        // Avatar directory
                        $avatar_dir = '../avatars/';
                        if (!file_exists($avatar_dir)) {
                            mkdir($avatar_dir, 0777, true);
                        }
                        
                        // Create unique name for image
                        $new_avatar_name = uniqid('avatar_') . '.' . $ext;
                        $avatar_path = $avatar_dir . $new_avatar_name;
                        
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                            // Update avatar path in database
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

// Get current user data
$userQuery = "SELECT * FROM users WHERE id = $1";
$userResult = pg_query_params($conn, $userQuery, array($userId));

if ($userResult === false) {
    $errorMessage = "Erreur SQL : " . pg_last_error($conn);
} else {
    $userData = pg_fetch_assoc($userResult);
}

// Fonction pour écrire dans un fichier de log
function writeLog($message, $level = 'INFO') {
    $logDir = '../logs';
    
    // Créer le répertoire de logs s'il n'existe pas
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // Écrire dans le fichier de log
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}

// Exemple d'utilisation pour déboguer le problème de connexion
writeLog("Tentative d'inclusion du fichier de connexion", 'DEBUG');
require_once "../connexion.inc.php";
writeLog("Fichier de connexion inclus", 'DEBUG');

writeLog("Vérification de la fonction connectDB()", 'DEBUG');
if (!function_exists('connectDB')) {
    writeLog("La fonction connectDB() n'existe pas!", 'ERROR');
    // Vous pourriez afficher une erreur ou implémenter temporairement la fonction ici
} else {
    writeLog("La fonction connectDB() existe", 'DEBUG');
    $conn = connectDB();
    
    if (!$conn) {
        writeLog("Échec de la connexion à la base de données", 'ERROR');
    } else {
        writeLog("Connexion à la base de données réussie", 'INFO');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options - SilvEterna</title>
    <style>
        @import url("html/assets/styles/sidebar.css");

        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .main-content h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-container {
            max-width: 600px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .main-content label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .main-content input,
        .main-content select,
        .main-content textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px 0;
            border: 1px solid #ccc;
        }

        .submit-btn {
            padding: 10px 15px;
            background-color: #d9138f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #b8107a;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            margin-right: 5px;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            background-color: #f9f9f9;
        }

        .tab.active {
            background-color: #d9138f;
            color: white;
            border-color: #d9138f;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
        <a href="profil.php"> <img class="logo" src="../img/silverternalogo.png" style="height: 25%; width: auto;"></a>
            <nav>
                <ul>
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
        <div class="main-content">
            <h2>OPTIONS</h2>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="tabs">
                <div class="tab active" data-tab="profile">Profil</div>
                <div class="tab" data-tab="notifications">Notifications</div>
                <div class="tab" data-tab="privacy">Confidentialité</div>
            </div>
            
            <div id="profile" class="tab-content active">
                <div class="form-container">
                    <h3>Modifier mon profil</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo $userData['id']; ?>">
                        <input type="hidden" name="update_user" value="1">
                        
                        <div class="form-group">
                            <label for="avatar">Avatar:</label>
                            <?php if (!empty($userData['avatar'])): ?>
                                <img class="avatar-preview" src="../avatars/<?php echo htmlspecialchars($userData['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <img class="avatar-preview" src="img/default-avatar.png" alt="Avatar par défaut">
                            <?php endif; ?>
                            <input type="file" id="avatar" name="avatar" accept="image/*">
                        </div>
                        
                        <div class="form-group">
                            <label for="nom">Nom:</label>
                            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($userData['nom'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom:</label>
                            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($userData['prenom'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="login">Login:</label>
                            <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($userData['login'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="dateNaissance">Date de naissance:</label>
                            <input type="date" id="dateNaissance" name="dateNaissance" value="<?php echo formatDate($userData['datenaissance'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone:</label>
                            <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($userData['telephone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="statut">Statut:</label>
                            <select id="statut" name="statut">
                                <option value="actif" <?php echo (($userData['statut'] ?? '') == 'actif') ? 'selected' : ''; ?>>Actif</option>
                                <option value="inactif" <?php echo (($userData['statut'] ?? '') == 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="sexe">Sexe:</label>
                            <select id="sexe" name="sexe">
                                <option value="M" <?php echo (($userData['sexe'] ?? '') == 'M') ? 'selected' : ''; ?>>Masculin</option>
                                <option value="F" <?php echo (($userData['sexe'] ?? '') == 'F') ? 'selected' : ''; ?>>Féminin</option>
                                <option value="A" <?php echo (($userData['sexe'] ?? '') == 'A') ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="submit-btn">Mettre à jour</button>
                    </form>
                </div>
            </div>
            
            <div id="notifications" class="tab-content">
                <div class="form-container">
                    <h3>Paramètres de notifications</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="notif_email" value="1" <?php echo (($userData['notif_email'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                Recevoir des notifications par email
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="notif_app" value="1" <?php echo (($userData['notif_app'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                Recevoir des notifications dans l'application
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="notif_events" value="1" <?php echo (($userData['notif_events'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                Notifications d'événements
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="notif_messages" value="1" <?php echo (($userData['notif_messages'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                Notifications de messages
                            </label>
                        </div>
                        
                        <button type="submit" name="update_notifications" class="submit-btn">Enregistrer</button>
                    </form>
                </div>
            </div>
            
            <div id="privacy" class="tab-content">
                <div class="form-container">
                    <h3>Paramètres de confidentialité</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="profile_visibility">Visibilité du profil:</label>
                            <select id="profile_visibility" name="profile_visibility">
                                <option value="public" <?php echo (($userData['profile_visibility'] ?? '') == 'public') ? 'selected' : ''; ?>>Public</option>
                                <option value="friends" <?php echo (($userData['profile_visibility'] ?? '') == 'friends') ? 'selected' : ''; ?>>Amis uniquement</option>
                                <option value="private" <?php echo (($userData['profile_visibility'] ?? '') == 'private') ? 'selected' : ''; ?>>Privé</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="show_online_status" value="1" <?php echo (($userData['show_online_status'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                Afficher mon statut en ligne
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="show_age" value="1" <?php echo (($userData['show_age'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                Afficher mon âge
                            </label>
                        </div>
                        
                        <button type="submit" name="update_privacy" class="submit-btn">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>