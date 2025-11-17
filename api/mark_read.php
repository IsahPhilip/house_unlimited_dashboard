<?php
// api/mark_read.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$from_user = intval($input['from_user'] ?? 0);

if ($from_user <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $db->prepare("UPDATE messages SET read_status = 1 WHERE to_user = ? AND from_user = ? AND read_status = 0");
$stmt->bind_param('ii', $user_id, $from_user);
$stmt->execute();

echo json_encode(['success' => true, 'marked' => $stmt->affected_rows]);
?>