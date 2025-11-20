<?php
// api/appointments.php — FINAL FIXED VERSION (Works with your actual DB schema)
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$role    = $_SESSION['user']['role'];
$status_filter = $_GET['status'] ?? '';

// Build query based on role
if ($role === 'client') {
    $sql = "
        SELECT 
            a.id,
            a.viewing_date AS appointment_date,
            a.viewing_time AS appointment_time,
            a.status,
            a.message AS notes,
            a.created_at,
            p.title AS property_title,
            p.location AS property_location,
            pi.image_path AS property_image,
            u.name AS counterparty_name,
            u.phone AS counterparty_phone
        FROM appointments a
        JOIN properties p ON a.property_id = p.id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_featured = 1
        LEFT JOIN users u ON a.agent_id = u.id
        WHERE a.user_id = ?
    ";
} else {
    // Agent or Admin
    $sql = "
        SELECT 
            a.id,
            a.viewing_date AS appointment_date,
            a.viewing_time AS appointment_time,
            a.status,
            a.message AS notes,
            a.created_at,
            p.title AS property_title,
            p.location AS property_location,
            pi.image_path AS property_image,
            u.name AS counterparty_name,
            u.phone AS counterparty_phone
        FROM appointments a
        JOIN properties p ON a.property_id = p.id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_featured = 1
        LEFT JOIN users u ON a.user_id = u.id
        WHERE p.agent_id = ?
    ";
}

// Add status filter if valid
if ($status_filter && in_array($status_filter, ['pending','confirmed','completed','cancelled'])) {
    $sql .= " AND a.status = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('is', $user_id, $status_filter);
} else {
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$appointments = [];

while ($row = $result->fetch_assoc()) {
    // Fallback image
    $row['property_image'] = !empty($row['property_image']) 
        ? $row['property_image'] 
        : 'default.jpg';

    // Format date & time nicely
    $row['appointment_date'] = date('M j, Y', strtotime($row['appointment_date']));
    $row['appointment_time'] = date('g:i A', strtotime($row['appointment_time']));

    $appointments[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $appointments
]);
?>