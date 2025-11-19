<?php
// api/unread_count.php - 100% WORKING WITH CURRENT DB SCHEMA
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

// Use the UPDATED column names: recipient_id + is_read
$user_id = $_SESSION['user']['id'];

$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM messages 
    WHERE recipient_id = ? AND is_read = 0
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => (int)$row['count']]);
?>