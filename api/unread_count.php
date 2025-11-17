<?php
// api/unread_count.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$stmt = $db->prepare("SELECT COUNT(*) as count FROM messages WHERE to_user = ? AND read_status = 0");
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

echo json_encode(['count' => $row['count']]);
?>