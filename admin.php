<?php

require_once 'silveterna_config.php'; // Faut changer cette partie
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Fonction pour formater les dates
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

// Établir la connexion
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

// Initialiser les variables
$resultUsers = null;
$resultLoisirs = null;

// Récupérer les utilisateurs
$queryUsers = "SELECT * FROM users ORDER BY nom, prenom"; 
$resultUsers = pg_query($conn, $queryUsers);

if ($resultUsers === false) {
    $errorUsers = "Erreur SQL (utilisateurs): " . pg_last_error($conn);
}


$resultLoisirs = null;



?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SilvEterna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
        }

        .sidebar {
            width: 200px;
            background-color: #a8e6cf;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h1 {
            color: #d9138f;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
        }
        
        .logo {
            color: #9b2680;
            font-size: 24px;
            font-weight: bold;
            padding-bottom: 20px;
            border-bottom: 1px solid #88c888;
            margin-bottom: 20px;
        }
        .menu-item {
            color: #333;
            padding: 12px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s ease;
            margin-bottom: 5px;
        }
        
        .menu-item:hover {
            background-color: #88c888;
            color: white;
        }
        
        .content {
            flex: 1;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            gap: 25px;
        }
        
        .column {
            width: 48%;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .search-box {
            margin-bottom: 20px;
            width: 100%;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 25px;
            border: 1px solid #ddd;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #a8e6cf;
            box-shadow: 0 0 5px rgba(168, 230, 207, 0.5);
        }
        
        .search-box .clear-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s ease;
        }
        
        .search-box .clear-btn:hover {
            color: #333;
        }
        
        .list-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #a8e6cf;
            padding-bottom: 10px;
        }
        
        .list-item {
            display: flex;
            margin-bottom: 15px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        
        .list-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profile-pic {
            width: 70px;
            height: 70px;
            object-fit: cover;
        }
        
        .item-info {
            padding: 12px;
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .view-profile {
            background-color: #a3d9a5;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        
        .view-profile:hover {
            background-color: #88c888;
        }
        
        .item-details {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }

        .error-message {
            color: #d32f2f;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">SilvEterna</div>
        <div class="menu-item">Accueil</div>
        <div class="menu-item">Calendrier</div>
        <div class="menu-item">Jeux</div>
        <div class="menu-item">Options</div>
        <div class="menu-item">Page admin</div>
    </div>
    
    <div class="content">
        <div class="column">
            <div class="list-title">Liste des utilisateurs</div>
            
            <?php if (isset($errorUsers)): ?>
                <div class="error-message"><?php echo $errorUsers; ?></div>
            <?php endif; ?>
            
            <div class="search-box">
                <input type="text" id="searchUser" placeholder="Rechercher un utilisateur">
                <span class="clear-btn">×</span>
            </div>
            
            <div id="users-container">
                <?php if ($resultUsers && pg_num_rows($resultUsers) > 0): ?>
                    <?php while ($user = pg_fetch_assoc($resultUsers)): ?>
                        <div class="list-item user-item">
                            <img src="<?php echo !empty($user['avatar']) ? 'avatars/' . htmlspecialchars($user['avatar']) : '/api/placeholder/70/70'; ?>" 
                                alt="Profil de <?php echo htmlspecialchars($user['login']); ?>" class="profile-pic">
                            <div class="item-info">
                                <div class="item-name">
                                    <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                                    <span style="font-size: 12px; color: #666;">(<?php echo htmlspecialchars($user['login']); ?>)</span>
                                </div>
                                <div class="item-details">
                                    <?php if (!empty($user['dateNaissance'])): ?>
                                        Né(e) le <?php echo formatDate($user['dateNaissance']); ?><br>
                                    <?php endif; ?>
                                </div>
                                <button class="view-profile" data-id="<?php echo $user['id']; ?>">Voir profil</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Aucun utilisateur trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="column">
            <div class="list-title">Liste des événements</div>
            
            <?php
            // Récupérer les événements de l'agenda
            $queryAgenda = "SELECT * FROM agenda ORDER BY date, heure_debut";
            $resultAgenda = pg_query($conn, $queryAgenda);
            
            if ($resultAgenda === false):
            ?>
                <div class="error-message">Erreur SQL (agenda): <?php echo pg_last_error($conn); ?></div>
            <?php endif; ?>
            
            <div class="search-box">
                <input type="text" id="searchEvent" placeholder="Rechercher un événement">
                <span class="clear-btn">×</span>
            </div>
            
            <div id="events-container">
                <?php if ($resultAgenda && pg_num_rows($resultAgenda) > 0): ?>
                    <?php while ($event = pg_fetch_assoc($resultAgenda)): ?>
                        <div class="list-item event-item">
                            <img src="/api/placeholder/70/70" alt="<?php echo htmlspecialchars($event['titre']); ?>" class="profile-pic">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($event['titre']); ?></div>
                                <div class="item-details">
                                    <?php echo formatDate($event['date']); ?>, 
                                    <?php echo substr($event['heure_debut'], 0, 5); ?> - <?php echo substr($event['heure_fin'], 0, 5); ?><br>
                                    <?php echo htmlspecialchars($event['lieu']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Aucun événement trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Fonction de recherche pour les utilisateurs
        document.getElementById('searchUser').addEventListener('keyup', function() {
            filterItems(this.value, '.user-item');
        });

        // Fonction de recherche pour les événements
        document.getElementById('searchEvent').addEventListener('keyup', function() {
            filterItems(this.value, '.event-item');
        });

        // Fonction générique pour filtrer les éléments
        function filterItems(searchValue, itemSelector) {
            searchValue = searchValue.toLowerCase();
            const items = document.querySelectorAll(itemSelector);
            
            items.forEach(item => {
                const name = item.querySelector('.item-name').textContent.toLowerCase();
                if (name.includes(searchValue)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Effacer les champs de recherche
        document.querySelectorAll('.clear-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                input.value = '';
                input.dispatchEvent(new Event('keyup'));
                input.focus();
            });
        });
        
        // Redirection pour voir le profil d'un utilisateur
        document.querySelectorAll('.view-profile').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                window.location.href = 'profile.php?id=' + userId;
            });
        });
    </script>
</body>
</html>

<?php
// Libérer les résultats
if ($resultUsers) pg_free_result($resultUsers);
if (isset($resultAgenda) && $resultAgenda) pg_free_result($resultAgenda);

// Fermer la connexion à la base de données
pg_close($conn);
?>