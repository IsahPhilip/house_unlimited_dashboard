<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');


$user_id = $_SESSION['user_id'];
$role = $_SESSION['user']['role'];

try {
    // For admin/agent, fetch all activities. For client, fetch only their own.
    if ($role === 'admin' || $role === 'agent') {
        $stmt = $db->prepare("SELECT al.action, al.timestamp, u.name as user_name FROM activity_log al JOIN users u ON al.user_id = u.id ORDER BY al.timestamp DESC LIMIT 10");
    } else {
        $stmt = $db->prepare("SELECT action, timestamp FROM activity_log WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
        $stmt->bind_param('i', $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $activities = [];

    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'action' => htmlspecialchars($row['action']),
            'timestamp' => (new DateTime($row['timestamp']))->format('M d, Y h:i A')
        ];
    }

    echo json_encode($activities);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>