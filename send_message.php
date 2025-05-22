<?php
session_start();
require_once "connexion.inc.php"; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUserId = $_SESSION['user_id'];
$friendId = $_POST['friend_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (!$friendId || $message === '') {
    die("Invalid data.");
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

// Insert the message
$query = "
    INSERT INTO chat_messages (sender_id, receiver_id, message_text) 
    VALUES (:sender, :receiver, :message)
";
$stmt = $cnx->prepare($query);
$stmt->execute([
    'sender' => $currentUserId,
    'receiver' => $friendId,
    'message' => $message
]);

header("Location: html/chat.php?friend_id=$friendId");
exit;