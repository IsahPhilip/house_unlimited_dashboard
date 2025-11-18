<?php
// api/get_messages.php - FIXED & OPTIMIZED
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$with_user = intval($_GET['with'] ?? 0);
$user_id   = $_SESSION['user']['id'];

if ($with_user <= 0) {
    echo json_encode(['messages' => [], 'unread' => 0]);
    exit;
}

// Mark messages from this user as read
$db->query("UPDATE messages SET is_read = 1 WHERE recipient_id = $user_id AND sender_id = $with_user AND is_read = 0");

$sql = "SELECT m.*,
               u1.name AS sender_name,
               u2.name AS recipient_name
        FROM messages m
        LEFT JOIN users u1 ON m.sender_id = u1.id
        LEFT JOIN users u2 ON m.recipient_id = u2.id
        WHERE (m.sender_id = ? AND m.recipient_id = ?) 
           OR (m.sender_id = ? AND m.recipient_id = ?)
        ORDER BY m.created_at ASC";

$stmt = $db->prepare($sql);
$stmt->bind_param('iiii', $user_id, $with_user, $with_user, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id'           => $row['id'],
        'sender_id'    => $row['sender_id'],
        'sender_name'  => $row['sender_name'] ?? 'Unknown',
        'message'      => htmlspecialchars($row['message']),
        'created_at'   => date('c', strtotime($row['created_at'])),
        'is_mine'      => $row['sender_id'] == $user_id,
        'property_id'  => $row['property_id']
    ];
}

echo json_encode([
    'messages' => $messages,
    'unread'   => 0  // Already marked as read above
]);
?>