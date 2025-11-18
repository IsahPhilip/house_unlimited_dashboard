<?php
// api/dashboard_stats.php
// Returns real-time stats for admin/agent dashboard
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$role    = $_SESSION['user']['role'];

$stats = [];

// 1. Total Properties
if ($role === 'admin') {
    $stats['properties'] = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'active'")->fetch_row()[0];
} else {
    $stats['properties'] = $db->query("SELECT COUNT(*) FROM properties WHERE agent_id = $user_id AND status = 'active'")->fetch_row()[0];
}

// 2. Total Clients (Registered Users with role 'client')
$stats['clients'] = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetch_row()[0];

// 3. Pending Appointments
if ($role === 'admin') {
    $stats['pending_appointments'] = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetch_row()[0];
} else {
    $stats['pending_appointments'] = $db->query("SELECT COUNT(*) FROM appointments a JOIN properties p ON a.property_id = p.id WHERE a.status = 'pending' AND p.agent_id = $user_id")->fetch_row()[0];
}

// 4. Total Revenue (from transactions table)
$revenueResult = $db->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status = 'success'")->fetch_row();
$stats['total_revenue'] = (float)$revenueResult[0];

// 5. Today's Revenue
$todayRevenue = $db->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status = 'success' AND DATE(created_at) = CURDATE()")->fetch_row();
$stats['today_revenue'] = (float)$todayRevenue[0];

// 6. Recent Activity Count (last 7 days)
$stats['recent_activity'] = $db->query("SELECT COUNT(*) FROM activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_row()[0];

// 7. Unread Messages (for current user)
$stats['unread_messages'] = $db->query("SELECT COUNT(*) FROM messages WHERE recipient_id = $user_id AND is_read = 0")->fetch_row()[0];

// 8. Total Agents (admin only)
if ($role === 'admin') {
    $stats['total_agents'] = $db->query("SELECT COUNT(*) FROM users WHERE role = 'agent'")->fetch_row()[0];
}

echo json_encode($stats);
?>