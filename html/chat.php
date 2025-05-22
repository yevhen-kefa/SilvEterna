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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat avec <?= htmlspecialchars($friend['prenom'] . ' ' . $friend['nom']) ?></title>
    <link rel="stylesheet" href="chat-style.css">
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #e6f7ef;
    margin: 0;
    padding: 20px;
    min-height: 100vh;
}

.chat-container {
    max-width: 800px;
    margin: 0 auto;
    background-color: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

h1 {
    text-align: center;
    color: #333;
    margin: 0;
    padding: 20px;
    background-color: #9cd3b2;
    font-size: 1.5rem;
    font-weight: bold;
    border-bottom: 1px solid #86c7a1;
}

.chat-box {
    height: 400px;
    overflow-y: auto;
    padding: 20px;
    background-color: #f8fdf9;
    border-bottom: 1px solid #e0e0e0;
}

.chat-box::-webkit-scrollbar {
    width: 8px;
}

.chat-box::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.chat-box::-webkit-scrollbar-thumb {
    background: #9cd3b2;
    border-radius: 10px;
}

.chat-box::-webkit-scrollbar-thumb:hover {
    background: #86c7a1;
}

.message {
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
}

.message.me {
    justify-content: flex-end;
}

.message.other {
    justify-content: flex-start;
}

.message .text {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
    position: relative;
}

.message.me .text {
    background-color: #9cd3b2;
    color: #000;
    border-bottom-right-radius: 5px;
    margin-left: auto;
}

.message.other .text {
    background-color: #ffffff;
    color: #333;
    border: 1px solid #e0e0e0;
    border-bottom-left-radius: 5px;
    margin-right: auto;
}

.message-form {
    padding: 20px;
    background-color: #ffffff;
    display: flex;
    gap: 10px;
    align-items: center;
}

.message-form input[type="text"] {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #ccc;
    border-radius: 25px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease;
}

.message-form input[type="text"]:focus {
    border-color: #9cd3b2;
    box-shadow: 0 0 0 2px rgba(156, 211, 178, 0.2);
}

.message-form button {
    padding: 12px 24px;
    background-color: #9cd3b2;
    border: none;
    border-radius: 25px;
    font-weight: bold;
    color: #000;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 14px;
}

.message-form button:hover {
    background-color: #86c7a1;
}

.message-form button:active {
    transform: translateY(1px);
}

/* Animation pour les nouveaux messages */
.message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Style responsive */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }
    
    .chat-container {
        border-radius: 10px;
    }
    
    h1 {
        font-size: 1.3rem;
        padding: 15px;
    }
    
    .chat-box {
        height: 300px;
        padding: 15px;
    }
    
    .message .text {
        max-width: 85%;
        font-size: 13px;
        padding: 10px 14px;
    }
    
    .message-form {
        padding: 15px;
    }
    
    .message-form input[type="text"] {
        padding: 10px 14px;
        font-size: 13px;
    }
    
    .message-form button {
        padding: 10px 20px;
        font-size: 13px;
    }
}
</style>
<body>
    <div class="chat-container">
        <h1>Chat avec <?= htmlspecialchars($friend['prenom'] . ' ' . $friend['nom']) ?></h1>
        
        <div class="chat-box">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender_id'] == $currentUserId ? 'me' : 'other' ?>">
                    <span class="text"><?= htmlspecialchars($msg['message_text']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <form class="message-form" action="../send_message.php" method="POST">
            <input type="hidden" name="friend_id" value="<?= $friendId ?>">
            <input type="text" name="message" placeholder="Tapez votre message..." required>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</body>
</html>