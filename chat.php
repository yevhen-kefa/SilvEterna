<?php
session_start();
require_once "connexion.inc.php"; // Adjust path if needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUserId = $_SESSION['user_id'];

$friendId = ($currentUserId == 1) ? 2 : 1;

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        // Insert into messages
        $stmt = $cnx->prepare("INSERT INTO messages (contenu, heure_envoi, date_envoi, compteur_msg_nonlu) VALUES (:contenu, NOW(), CURRENT_DATE, 0) RETURNING id_message");
        $stmt->execute(['contenu' => $message]);
        $msgId = $stmt->fetchColumn();

        // Link message to sender
        $stmt = $cnx->prepare("INSERT INTO envoyer_mess (id_user, id_message) VALUES (:id_user, :id_message)");
        $stmt->execute(['id_user' => $currentUserId, 'id_message' => $msgId]);
    }
    header("Location: chat.php");
    exit;
}

// Fetch messages between the two users
$query = "
    SELECT m.*, u.prenom, u.nom, em.id_user as sender_id
    FROM messages m
    JOIN envoyer_mess em ON m.id_message = em.id_message
    JOIN users u ON em.id_user = u.id
    WHERE (em.id_user = :user1 OR em.id_user = :user2)
    ORDER BY m.heure_envoi ASC
";
$stmt = $cnx->prepare($query);
$stmt->execute(['user1' => $currentUserId, 'user2' => $friendId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get friend info
$stmt = $cnx->prepare("SELECT prenom, nom FROM users WHERE id = :id");
$stmt->execute(['id' => $friendId]);
$friend = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chat avec <?= htmlspecialchars($friend['prenom'] . ' ' . $friend['nom']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .chat-container { width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 20px; }
        .chat-box { height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background: #fafafa; margin-bottom: 15px; }
        .msg { margin-bottom: 10px; }
        .msg.me { text-align: right; }
        .msg .sender { font-weight: bold; }
        .msg .time { color: #888; font-size: 0.8em; }
        form { display: flex; gap: 10px; }
        input[type="text"] { flex: 1; padding: 8px; }
        button { padding: 8px 16px; }
    </style>
</head>
<body>
<div class="chat-container">
    <h2>Chat avec <?= htmlspecialchars($friend['prenom'] . ' ' . $friend['nom']) ?></h2>
    <div class="chat-box" id="chat-box">
        <?php foreach ($messages as $msg): ?>
            <div class="msg<?= $msg['sender_id'] == $currentUserId ? ' me' : '' ?>">
                <span class="sender"><?= htmlspecialchars($msg['prenom']) ?>:</span>
                <?= htmlspecialchars($msg['contenu']) ?>
                <span class="time"><?= date('H:i', strtotime($msg['heure_envoi'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post" autocomplete="off">
        <input type="text" name="message" placeholder="Votre message..." required>
        <button type="submit">Envoyer</button>
    </form>
</div>
<script>
    // Auto-scroll chat box to bottom
    var chatBox = document.getElementById('chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>
</body>
</html>