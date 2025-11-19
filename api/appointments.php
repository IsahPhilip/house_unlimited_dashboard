<?php
// api/appointments.php - Returns appointments for current user (Client/Agent/Admin)
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
            a.id, a.appointment_date, a.appointment_time, a.status, a.notes, a.created_at,
            p.title AS property_title, p.location AS property_location, 
            pi.image_path AS property_image,
            u.name AS counterparty_name, u.phone AS counterparty_phone
        FROM appointments a
        JOIN properties p ON a.property_id = p.id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.id = (
            SELECT MIN(id) FROM property_images WHERE property_id = p.id
        )
        JOIN users u ON p.agent_id = u.id
        WHERE a.client_id = ?
    ";
} else {
    // Agent or Admin sees appointments for their properties
    $sql = "
        SELECT 
            a.id, a.appointment_date, a.appointment_time, a.status, a.notes, a.created_at,
            p.title AS property_title, p.location AS property_location,
            pi.image_path AS property_image,
            u.name AS counterparty_name, u.phone AS counterparty_phone
        FROM appointments a
        JOIN properties p ON a.property_id = p.id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.id = (
            SELECT MIN(id) FROM property_images WHERE property_id = p.id
        )
        JOIN users u ON a.client_id = u.id
        WHERE p.agent_id = ?
    ";
}

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
    $row['property_image'] = $row['property_image'] ?? 'default.jpg';
    $appointments[] = $row;
}

echo json_encode($appointments);
?>