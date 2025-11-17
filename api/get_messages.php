<?php
// api/get_messages.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$with_user = intval($_GET['with'] ?? 0);
$user_id = $_SESSION['user']['id'];

if ($with_user <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT m.*, 
               u1.name AS from_name,
               u2.name AS to_name
        FROM messages m
        LEFT JOIN users u1 ON m.from_user = u1.id
        LEFT JOIN users u2 ON m.to_user = u2.id
        WHERE (m.from_user = ? AND m.to_user = ?) 
           OR (m.from_user = ? AND m.to_user = ?)
        ORDER BY m.created_at ASC";

$stmt = $db->prepare($sql);
$stmt->bind_param('iiii', $user_id, $with_user, $with_user, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'from_user' => $row['from_user'],
        'to_user' => $row['to_user'],
        'message' => htmlspecialchars($row['message']),
        'created_at' => $row['created_at'],
        'property_id' => $row['property_id']
    ];
}

echo json_encode($messages);
?>