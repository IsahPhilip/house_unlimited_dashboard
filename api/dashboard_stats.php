<?php
// api/dashboard_stats.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

$stats = [];

if ($role === 'client') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE client_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['appointments'] = $stmt->get_result()->fetch_row()[0];
} else {
    $stmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE agent_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['properties'] = $stmt->get_result()->fetch_row()[0];
}

echo json_encode($stats);
?>