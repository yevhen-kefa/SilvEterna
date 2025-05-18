<?php

// Connexion à la base de données avec pg_connect()
require_once 'silveterna_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$config_file = 'silveterna_config.php';
if (!file_exists($config_file)) {
    die("Error: Configuration file '$config_file' not found!");
}

// Utilisation de la fonction connectDB() définie dans silveterna_config.php
$conn = connectDB();

if (!$conn) {
    die("La connexion à la base de données a échoué : " . pg_last_error());
}

// Initialisation des variables de date
$mois = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));

// Vérifier si le mois est valide
if ($mois < 1 || $mois > 12) {
    $mois = date('m');
}

// Obtenir le premier jour du mois
$premier_jour = mktime(0, 0, 0, $mois, 1, $annee);
$nom_mois = strftime('%B', $premier_jour);
$jours_dans_mois = date('t', $premier_jour);
$jour_semaine_debut = date('w', $premier_jour);
if ($jour_semaine_debut == 0) $jour_semaine_debut = 7; // Convertir dimanche de 0 à 7

// Calculer le mois précédent et suivant
$mois_precedent = $mois - 1;
$annee_precedent = $annee;
if ($mois_precedent < 1) {
    $mois_precedent = 12;
    $annee_precedent--;
}

$mois_suivant = $mois + 1;
$annee_suivant = $annee;
if ($mois_suivant > 12) {
    $mois_suivant = 1;
    $annee_suivant++;
}

// Récupérer les événements du mois en cours
$sql = "SELECT id_agenda, titre, description, date, heure_debut, heure_fin, lieu, type_evenement 
        FROM agenda 
        WHERE EXTRACT(MONTH FROM date) = $mois 
        AND EXTRACT(YEAR FROM date) = $annee";
$result = pg_query($conn, $sql);

if (!$result) {
    die("Erreur lors de la requête SQL : " . pg_last_error($conn));
}

$evenements = [];
while ($row = pg_fetch_assoc($result)) {
    $jour = date('j', strtotime($row['date']));
    if (!isset($evenements[$jour])) {
        $evenements[$jour] = [];
    }
    $evenements[$jour][] = $row;
}

// Traitement du formulaire d'ajout d'événement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_evenement') {
    $titre = pg_escape_string($conn, $_POST['titre']);
    $description = pg_escape_string($conn, $_POST['description']);
    $date = pg_escape_string($conn, $_POST['date']);
    $heure_debut = pg_escape_string($conn, $_POST['heure_debut']);
    $heure_fin = pg_escape_string($conn, $_POST['heure_fin']);
    $lieu = pg_escape_string($conn, $_POST['lieu']);
    $type_evenement = pg_escape_string($conn, $_POST['type_evenement']);
    
    // Récupérer le dernier ID utilisé dans la table agenda
    $sql_last_id = "SELECT MAX(id_agenda) as max_id FROM agenda";
    $result_last_id = pg_query($conn, $sql_last_id);
    
    if (!$result_last_id) {
        $erreur = "Erreur lors de la récupération du dernier ID: " . pg_last_error($conn);
    } else {
        $row = pg_fetch_assoc($result_last_id);
        $next_id = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;
        
        // Insertion avec l'ID spécifié
        $sql = "INSERT INTO agenda (id_agenda, titre, description, date, heure_debut, heure_fin, lieu, type_evenement) 
                VALUES ($next_id, '$titre', '$description', '$date', '$heure_debut', '$heure_fin', '$lieu', '$type_evenement')";
        
        $result = pg_query($conn, $sql);
        
        if ($result) {
            $message = "Événement ajouté avec succès avec l'ID: $next_id!";
            // Rediriger vers la même page pour éviter la resoumission du formulaire
            header("Location: Agenda.php?mois=$mois&annee=$annee&message=" . urlencode($message));
            exit;
        } else {
            $erreur = "Erreur lors de l'ajout de l'événement: " . pg_last_error($conn);
        }
    }
}

// Tableau des noms des jours de la semaine
$jours_semaine = ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'];

// Tableau des noms des mois
setlocale(LC_TIME, 'fr_FR.UTF-8');
$noms_mois = [
    1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
];
$isAdmin = $_SESSION['is_admin'] ?? false;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - SilvEterna</title>
    <style>
        @import url("html/assets/styles/sidebar.css");

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
        }

       
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .main-content h2 {
            color: #333;
            text-align: center;
        }

        .month-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .month-nav a {
            text-decoration: none;
            color: #d9138f;
            padding: 5px 10px;
            border: 1px solid #d9138f;
            border-radius: 4px;
        }

        .month-nav a:hover {
            background-color: #d9138f;
            color: white;
        }

        .calendar {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 20px;
            width: 80%;
            max-width: 800px;
        }

        .calendar-grid div {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
            min-height: 80px;
            position: relative;
        }

        .calendar-grid div:hover {
            background-color: #f0f0f0;
        }

        .calendar-grid div.selected {
            background-color: #ffb3c6;
            color: white;
            border: 2px solid #d9138f;
        }

        .day-header {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #d9138f;
            cursor: default;
            min-height: auto !important;
        }

        .day-number {
            position: absolute;
            top: 5px;
            left: 5px;
            font-weight: bold;
        }

        .event-indicator {
            display: block;
            margin-top: 25px;
            font-size: 0.8em;
            color: #d9138f;
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .event-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            min-width: 400px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .event-popup h3 {
            margin-top: 0;
            color: #d9138f;
            text-align: center;
        }

        .event-popup label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .event-popup input, .event-popup textarea, .event-popup select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
        }

        .event-popup textarea {
            height: 100px;
            resize: vertical;
        }

        .event-popup .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .event-popup .button {
            background-color: #d9138f;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            width: 48%;
        }

        .event-popup .button-cancel {
            background-color: #ccc;
        }

        .event-details {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            min-width: 400px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .event-details h3 {
            margin-top: 0;
            color: #d9138f;
            text-align: center;
        }

        .event-details p {
            margin: 10px 0;
        }

        .event-details .button {
            background-color: #d9138f;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }

        .previous-month, .next-month {
            color: #999;
        }

        .message {
            background-color: #a8e6cf;
            color: #333;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .error {
            background-color: #ffb3c6;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h1 class="logo"><a href="profil.php">SilvEterna</a></h1>
        <nav>
            <ul>
                <li><a href="Agenda.php">Calendrier</a></li>
                <li><a href="html/jeux.php">Jeux</a></li>
                <li><a href="html/option.php">Option</a></li>
                <?php if ($isAdmin) : ?>
                <li><a href="admin.php">Page admin</a></li>
                <?php endif; ?>
                <li><a href="deconnexion.php">Deconnexion</a></li>
            </ul>
        </nav>
    </aside>
    <div class="main-content">
        <?php if (isset($_GET['message'])): ?>
            <div class="message">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($erreur)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($erreur); ?>
            </div>
        <?php endif; ?>
        
        <div class="month-nav">
            <a href="?mois=<?php echo $mois_precedent; ?>&annee=<?php echo $annee_precedent; ?>">&lt; Mois précédent</a>
            <h2><?php echo $noms_mois[$mois] . ' ' . $annee; ?></h2>
            <a href="?mois=<?php echo $mois_suivant; ?>&annee=<?php echo $annee_suivant; ?>">Mois suivant &gt;</a>
        </div>
        
        <div class="calendar">
            <div class="calendar-grid">
                <?php foreach ($jours_semaine as $jour): ?>
                    <div class="day-header"><?php echo $jour; ?></div>
                <?php endforeach; ?>
                
                <?php
                // Jours du mois précédent
                $jour_semaine_debut_index = $jour_semaine_debut - 1;
                if ($jour_semaine_debut_index > 0) {
                    $jours_mois_precedent = date('t', mktime(0, 0, 0, $mois - 1, 1, $annee));
                    for ($i = $jour_semaine_debut_index - 1; $i >= 0; $i--) {
                        echo '<div class="previous-month"><span class="day-number">' . ($jours_mois_precedent - $i) . '</span></div>';
                    }
                }
                
                // Jours du mois actuel
                for ($jour = 1; $jour <= $jours_dans_mois; $jour++) {
                    $class = '';
                    echo '<div class="' . $class . '" data-date="' . sprintf('%04d-%02d-%02d', $annee, $mois, $jour) . '">
                            <span class="day-number">' . $jour . '</span>';
                    
                    // Afficher les événements pour ce jour
                    if (isset($evenements[$jour])) {
                        foreach ($evenements[$jour] as $event) {
                            echo '<div class="event-indicator" data-id="' . $event['id_agenda'] . '">' . 
                                 htmlspecialchars($event['titre']) . 
                                 '</div>';
                        }
                    }
                    
                    echo '</div>';
                }
                
                // Jours du mois suivant
                $jours_restants = 42 - ($jour_semaine_debut_index + $jours_dans_mois);
                for ($i = 1; $i <= $jours_restants; $i++) {
                    echo '<div class="next-month"><span class="day-number">' . $i . '</span></div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Liste des événements -->
        <div class="events-list">
            <h3>Liste des Événements</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #d9138f; color: white;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Titre</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Heure</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Lieu</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Récupérer tous les événements
                    $sql_all = "SELECT id_agenda, titre, date, heure_debut, heure_fin, lieu, type_evenement 
                                FROM agenda 
                                ORDER BY date DESC, heure_debut ASC
                                LIMIT 10";
                    $result_all = pg_query($conn, $sql_all);
                    
                    if (!$result_all) {
                        echo "<tr><td colspan='5'>Erreur lors de la récupération des événements</td></tr>";
                    } else {
                        if (pg_num_rows($result_all) == 0) {
                            echo "<tr><td colspan='5' style='text-align: center; padding: 10px;'>Aucun événement enregistré</td></tr>";
                        } else {
                            while ($row = pg_fetch_assoc($result_all)) {
                                echo "<tr style='border-bottom: 1px solid #ddd;'>";
                                echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($row['titre']) . "</td>";
                                echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($row['date'])) . "</td>";
                                echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($row['heure_debut']) . " - " . htmlspecialchars($row['heure_fin']) . "</td>";
                                echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($row['lieu']) . "</td>";
                                echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($row['type_evenement']) . "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div id="overlay"></div>
        
        <div class="event-popup" id="event-popup">
            <h3>Nouvel Événement</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="ajouter_evenement">
                
                <label for="titre">Titre :</label>
                <input type="text" id="titre" name="titre" required>
                
                <label for="description">Description :</label>
                <textarea id="description" name="description"></textarea>
                
                <label for="date">Date :</label>
                <input type="date" id="date" name="date" required>
                
                <label for="heure_debut">Heure de début :</label>
                <input type="time" id="heure_debut" name="heure_debut" required>
                
                <label for="heure_fin">Heure de fin :</label>
                <input type="time" id="heure_fin" name="heure_fin" required>
                
                <label for="lieu">Lieu :</label>
                <input type="text" id="lieu" name="lieu">
                
                <label for="type_evenement">Type d'événement :</label>
                <select id="type_evenement" name="type_evenement">
                    <option value="Rendez-vous">Rendez-vous</option>
                    <option value="Anniversaire">Anniversaire</option>
                    <option value="Réunion">Réunion</option>
                    <option value="Autre">Autre</option>
                </select>
                
                <div class="button-group">
                    <button type="submit" class="button">Enregistrer</button>
                    <button type="button" class="button button-cancel" onclick="closePopup()">Annuler</button>
                </div>
            </form>
        </div>
        
        <div class="event-details" id="event-details">
            <h3 id="event-title"></h3>
            <p><strong>Date :</strong> <span id="event-date"></span></p>
            <p><strong>Horaire :</strong> <span id="event-time"></span></p>
            <p><strong>Lieu :</strong> <span id="event-location"></span></p>
            <p><strong>Type :</strong> <span id="event-type"></span></p>
            <p><strong>Description :</strong></p>
            <p id="event-description"></p>
            <button class="button" onclick="closeEventDetails()">Fermer</button>
        </div>
    </div>

    <script>
        // Afficher le popup quand on clique sur un jour
        document.querySelectorAll('.calendar-grid div:not(.day-header):not(.previous-month):not(.next-month)').forEach(day => {
            day.addEventListener('click', function(e) {
                // Ne pas ouvrir le popup si on a cliqué sur un indicateur d'événement
                if (!e.target.classList.contains('event-indicator')) {
                    const selectedDate = this.getAttribute('data-date');
                    document.getElementById('date').value = selectedDate;
                    document.getElementById('event-popup').style.display = 'block';
                    document.getElementById('overlay').style.display = 'block';
                }
            });
        });
        
        // Afficher les détails d'un événement quand on clique sur un indicateur
        document.querySelectorAll('.event-indicator').forEach(indicator => {
            indicator.addEventListener('click', function(e) {
                e.stopPropagation();
                const eventId = this.getAttribute('data-id');
                // Ici, vous devriez charger les détails de l'événement via AJAX
                // Pour l'exemple, nous allons simplement utiliser des données fictives
                fetch('get_event.php?id=' + eventId)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('event-title').textContent = data.titre;
                        document.getElementById('event-date').textContent = data.date;
                        document.getElementById('event-time').textContent = data.heure_debut + ' - ' + data.heure_fin;
                        document.getElementById('event-location').textContent = data.lieu;
                        document.getElementById('event-type').textContent = data.type_evenement;
                        document.getElementById('event-description').textContent = data.description;
                        document.getElementById('event-details').style.display = 'block';
                        document.getElementById('overlay').style.display = 'block';
                    })
                    .catch(error => console.error('Erreur:', error));
            });
        });
        
        // Fermer le popup quand on clique sur le bouton Annuler
        function closePopup() {
            document.getElementById('event-popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        // Fermer les détails de l'événement
        function closeEventDetails() {
            document.getElementById('event-details').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        // Fermer les popups si on clique sur l'overlay
        document.getElementById('overlay').addEventListener('click', function() {
            closePopup();
            closeEventDetails();
        });
    </script>
</body>
</html>

<?php
// N'oubliez pas de fermer la connexion à la fin du script
pg_close($conn);
?>