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

// Établir la connexion
$conn = connectDB();
if (!$conn) {
    die("Erreur de connexion à la base de données");
}
$isAdmin = $_SESSION['is_admin'] ?? false;

// Fonction pour formater les dates
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date('Y-m-d', $timestamp);
}

// Fonction pour formater les heures
function formatTime($time) {
    if (empty($time)) return '';
    return date('H:i', strtotime($time));
}

// Traitement des messages
$successMessage = '';
$errorMessage = '';

// Traitement de la suppression d'un événement
if (isset($_POST['delete_event']) && isset($_POST['event_id'])) {
    $eventId = (int)$_POST['event_id'];
    
    // Suppression de l'événement de la table agenda
    $deleteQuery = "DELETE FROM agenda WHERE id_agenda = $eventId";
    $deleteResult = pg_query($conn, $deleteQuery);
    
    if ($deleteResult) {
        $successMessage = "L'événement a été supprimé avec succès.";
    } else {
        $errorMessage = "Erreur lors de la suppression de l'événement : " . pg_last_error($conn);
    }
}

// Traitement de l'ajout d'un événement de type Loisir
if (isset($_POST['add_loisir'])) {
    $titre = pg_escape_string($conn, $_POST['titre']);
    $description = pg_escape_string($conn, $_POST['description']);
    $date = pg_escape_string($conn, $_POST['date']);
    $heure_debut = pg_escape_string($conn, $_POST['heure_debut']);
    $heure_fin = pg_escape_string($conn, $_POST['heure_fin']);
    $lieu = pg_escape_string($conn, $_POST['lieu']);
    
    $insertQuery = "INSERT INTO agenda (titre, description, date, heure_debut, heure_fin, lieu, type_evenement) 
                  VALUES ('$titre', '$description', '$date', '$heure_debut', '$heure_fin', '$lieu', 'Loisir')";
    $insertResult = pg_query($conn, $insertQuery);
    
    if ($insertResult) {
        $successMessage = "Le loisir a été ajouté avec succès.";
    } else {
        $errorMessage = "Erreur lors de l'ajout du loisir : " . pg_last_error($conn);
    }
}

// Récupération de tous les événements depuis la table agenda
// Option 1: Pour afficher uniquement les loisirs (comportement original)
// $eventsQuery = "SELECT id_agenda, titre, description, date, heure_debut, heure_fin, lieu, type_evenement
//               FROM agenda
//               WHERE type_evenement = 'Loisir'
//               ORDER BY date DESC";

// Option 2: Pour afficher tous les types d'événements
$eventsQuery = "SELECT id_agenda, titre, description, date, heure_debut, heure_fin, lieu, type_evenement
              FROM agenda
              ORDER BY date DESC";

$eventsResult = pg_query($conn, $eventsQuery);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements - SilvEterna</title>
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
        
        h3 {
            color: #555;
            margin-top: 20px;
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

        .action-btn, .submit-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 14px;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        
        .add-form {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="profil.php"> <img class="logo" src="img/silverternalogo.png" style="height: 15%; width: auto;"></a>
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
            <h1>Gestion des événements</h1>
            
            <?php if (!empty($successMessage)): ?>
                <div class="message success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="message error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <h2>Ajouter un nouvel événement de type Loisir</h2>
            <div class="add-form">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="titre">Titre:</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="heure_debut">Heure de début:</label>
                        <input type="time" id="heure_debut" name="heure_debut" required>
                    </div>
                    <div class="form-group">
                        <label for="heure_fin">Heure de fin:</label>
                        <input type="time" id="heure_fin" name="heure_fin" required>
                    </div>
                    <div class="form-group">
                        <label for="lieu">Lieu:</label>
                        <input type="text" id="lieu" name="lieu" required>
                    </div>
                    <button type="submit" name="add_loisir" class="submit-btn">Ajouter l'événement</button>
                </form>
            </div>
            
            <h2>Liste des événements</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Horaires</th>
                        <th>Lieu</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($eventsResult && pg_num_rows($eventsResult) > 0): ?>
                        <?php while ($event = pg_fetch_assoc($eventsResult)): ?>
                            <tr>
                                <td><?php echo $event['id_agenda']; ?></td>
                                <td><?php echo htmlspecialchars($event['titre']); ?></td>
                                <td><?php echo htmlspecialchars(substr($event['description'], 0, 50)) . (strlen($event['description']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo formatDate($event['date']); ?></td>
                                <td><?php echo formatTime($event['heure_debut']) . ' - ' . formatTime($event['heure_fin']); ?></td>
                                <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                                <td><?php echo htmlspecialchars($event['type_evenement']); ?></td>
                                <td>
                                    <form method="post" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement?');">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id_agenda']; ?>">
                                        <button type="submit" name="delete_event" class="action-btn delete-btn">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Aucun événement trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>