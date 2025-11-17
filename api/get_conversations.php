<?php
// api/get_conversations.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];

// Get latest message from each conversation
$sql = "
SELECT 
    m1.*,
    u.name AS with_name,
    u.photo,
    p.title AS property_title,
    p.id AS property_id
FROM messages m1
JOIN (
    SELECT 
        LEAST(from_user, to_user) AS user1,
        GREATEST(from_user, to_user) AS user2,
        MAX(created_at) AS max_time
    FROM messages 
    WHERE from_user = ? OR to_user = ?
    GROUP BY user1, user2
) m2 ON (m1.from_user = m2.user1 AND m1.to_user = m2.user2) 
     OR (m1.from_user = m2.user2 AND m1.to_user = m2.user1)
JOIN users u ON u.id = CASE WHEN m1.from_user = ? THEN m1.to_user ELSE m1.from_user END
LEFT JOIN properties p ON m1.property_id = p.id
WHERE m1.created_at = m2.max_time
ORDER BY m1.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $other_user_id = ($row['from_user'] == $user_id) ? $row['to_user'] : $row['from_user'];
    
    // Count unread messages
    $unread_stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE to_user = ? AND from_user = ? AND read_status = 0");
    $unread_stmt->bind_param('ii', $user_id, $other_user_id);
    $unread_stmt->execute();
    $unread_count = $unread_stmt->get_result()->fetch_row()[0];

    $conversations[] = [
        'with_user_id' => $other_user_id,
        'with_name' => $row['with_name'],
        'photo' => $row['photo'] ?? 'default.png',
        'last_message' => strlen($row['message']) > 50 ? substr($row['message'], 0, 47).'...' : $row['message'],
        'last_time' => date('h:i A', strtotime($row['created_at'])),
        'property_id' => $row['property_id'],
        'property_title' => $row['property_title'] ?? null,
        'unread' => $unread_count > 0
    ];
}

echo json_encode($conversations);
?>