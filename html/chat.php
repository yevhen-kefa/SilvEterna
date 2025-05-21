<?php
session_start();
require_once "../connexion.inc.php"; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUserId = $_SESSION['user_id'];
$friendId = $_GET['friend_id'] ?? null;

if (!$friendId) {
    die("No friend selected.");
}

// Check if the users are friends
$query = "
    SELECT 1 
    FROM amis 
    WHERE (ami_1 = :user1 AND ami_2 = :user2) 
       OR (ami_1 = :user2 AND ami_2 = :user1)
";
$stmt = $cnx->prepare($query);
$stmt->execute(['user1' => $currentUserId, 'user2' => $friendId]);
if ($stmt->rowCount() === 0) {
    die("You are not friends with this user.");
}

// Fetch messages
$query = "
    SELECT * 
    FROM chat_messages 
    WHERE (sender_id = :user1 AND receiver_id = :user2) 
       OR (sender_id = :user2 AND receiver_id = :user1)
    ORDER BY timestamp ASC
";
$stmt = $cnx->prepare($query);
$stmt->execute(['user1' => $currentUserId, 'user2' => $friendId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get friend's name
$query = "SELECT prenom, nom FROM users WHERE id = :id";
$stmt = $cnx->prepare($query);
$stmt->execute(['id' => $friendId]);
$friend = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with <?= htmlspecialchars($friend['prenom'] . ' ' . $friend['nom']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; }
        .chat-box { border: 1px solid #ccc; padding: 10px; height: 300px; overflow-y: scroll; margin-bottom: 10px; }
        .message { margin-bottom: 10px; }
        .message.me { text-align: right; }
        .message .text { display: inline-block; padding: 5px 10px; border-radius: 5px; }
        .message.me .text { background-color: #d1ffd1; }
        .message.other .text { background-color: #f1f1f1; }
    </style>
</head>
<body>
    <h1>Chat with <?= htmlspecialchars($friend['prenom'] . ' ' . $friend['nom']) ?></h1>
    <div class="chat-box">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= $msg['sender_id'] == $currentUserId ? 'me' : 'other' ?>">
                <span class="text"><?= htmlspecialchars($msg['message_text']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <form action="../send_message.php" method="POST">
        <input type="hidden" name="friend_id" value="<?= $friendId ?>">
        <input type="text" name="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
</body>
</html>