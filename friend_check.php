<?php
function areFriends($user1, $user2, $cnx) {
    $query = "
        SELECT * FROM amis 
        WHERE (ami_1 = :user1 AND ami_2 = :user2) 
           OR (ami_1 = :user2 AND ami_2 = :user1)
    ";
    $stmt = $cnx->prepare($query);
    $stmt->execute(['user1' => $user1, 'user2' => $user2]);
    return $stmt->rowCount() > 0;
}