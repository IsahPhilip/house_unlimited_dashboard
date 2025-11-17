<?php
// api/admin_stats.php - Admin Dashboard Stats
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'users':
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $row = $stmt->fetch_assoc();
        echo json_encode(['count' => (int)$row['count']]);
        break;

    case 'properties':
        $stmt = $db->query("SELECT COUNT(*) as count FROM properties WHERE status = 'active'");
        $row = $stmt->fetch_assoc();
        echo json_encode(['count' => (int)$row['count']]);
        break;

    case 'revenue':
        $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE status = 'success'");
        $row = $stmt->fetch_assoc();
        echo json_encode(['total' => (float)$row['total']]);
        break;

    case 'appointments_today':
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM appointments WHERE DATE(viewing_date) = ?");
        $stmt->bind_param('s', $today);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        echo json_encode(['count' => (int)$row['count']]);
        break;

    default:
        echo json_encode(['error' => 'Invalid stat type']);
}
?>