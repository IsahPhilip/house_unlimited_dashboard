<?php
// api/admin_activity.php - System Activity Feed
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$sql = "SELECT al.*, u.name as user_name
        FROM activity_log al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.timestamp DESC
        LIMIT 50";

$result = $db->query($sql);
$logs = [];

while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'action' => $row['action'],
        'user_name' => $row['user_name'] ?? 'System',
        'timestamp' => $row['timestamp'],
        'ip' => $row['ip_address']
    ];
}

echo json_encode($logs);
?>