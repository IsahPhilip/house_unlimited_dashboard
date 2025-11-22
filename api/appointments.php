<?php
// api/appointments.php — FINAL + DYNAMIC LUXURY LOGS + ZERO HARDCODING
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $property_id   = (int)($data['property_id'] ?? 0);
    $viewing_date  = $data['viewing_date'] ?? '';
    $viewing_time  = $data['viewing_time'] ?? '';
    $message       = trim($data['message'] ?? '');
    $user_id       = $_SESSION['user']['id'];

    if (empty($property_id) || empty($viewing_date) || empty($viewing_time)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Fetch property + agent details for RICH LOGGING
    $stmt = $db->prepare("
        SELECT p.title, p.location, p.agent_id, u.name AS client_name
        FROM properties p
        LEFT JOIN users u ON u.id = ?
        WHERE p.id = ?
    ");
    $stmt->bind_param('ii', $user_id, $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $stmt->close();

    if (!$property) {
        echo json_encode(['success' => false, 'message' => 'Property not found']);
        exit;
    }

    $agent_id = $property['agent_id'];
    $client_name = $property['client_name'] ?? 'Client';

    // Format the viewing date/time beautifully
    $date_obj = new DateTime($viewing_date . ' ' . $viewing_time);
    $formatted_date = $date_obj->format('l, F j, Y');
    $formatted_time = $date_obj->format('g:i A');

    // Insert appointment
    $stmt = $db->prepare("
        INSERT INTO appointments 
        (property_id, user_id, agent_id, viewing_date, viewing_time, message) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiisss', $property_id, $user_id, $agent_id, $viewing_date, $viewing_time, $message);

    if ($stmt->execute()) {
        $appointment_id = $stmt->insert_id;

        // DYNAMIC, RICH, PROFESSIONAL LOG — NO HARDCODING
        $property_full = $property['title'] . " in " . ucwords(str_replace(['-', '_'], ' ', $property['location']));
        
        log_activity(
            "Scheduled viewing appointment: $client_name → $property_full on $formatted_date at $formatted_time"
        );

        echo json_encode([
            'success' => true,
            'message' => 'Appointment created successfully',
            'appointment_id' => $appointment_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create appointment']);
    }
    exit;
}

// === GET REQUEST: Fetch appointments (unchanged logic, just cleaner) ===
$user_id = $_SESSION['user']['id'];
$role    = $_SESSION['user']['role'];
$status_filter = $_GET['status'] ?? '';

// Base query
if ($role === 'client') {
    $sql = "
        SELECT 
            a.id, a.viewing_date AS appointment_date, a.viewing_time AS appointment_time,
            a.status, a.message AS notes, a.created_at,
            p.id AS property_id, p.title AS property_title, p.location AS property_location,
            pi.image_path AS property_image,
            u.name AS counterparty_name, u.phone AS counterparty_phone
        FROM appointments a
        JOIN properties p ON a.property_id = p.id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND (pi.is_featured = 1 OR pi.id IS NOT NULL)
        LEFT JOIN users u ON a.agent_id = u.id
        WHERE a.user_id = ?
    ";
} else {
    $sql = "
        SELECT 
            a.id, a.viewing_date AS appointment_date, a.viewing_time AS appointment_time,
            a.status, a.message AS notes, a.created_at,
            p.id AS property_id, p.title AS property_title, p.location AS property_location,
            pi.image_path AS property_image,
            u.name AS counterparty_name, u.phone AS counterparty_phone
        FROM appointments a
        JOIN properties p ON a.property_id = p.id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND (pi.is_featured = 1 OR pi.id IS NOT NULL)
        LEFT JOIN users u ON a.user_id = u.id
        WHERE p.agent_id = ?
    ";
}

// Add status filter
if ($status_filter && in_array($status_filter, ['pending','confirmed','completed','cancelled'])) {
    $sql .= " AND a.status = ?";
    $stmt = $db->prepare($sql);
    if ($role === 'client') {
        $stmt->bind_param('is', $user_id, $status_filter);
    } else {
        $stmt->bind_param('is', $user_id, $status_filter);
    }
} else {
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$appointments = [];

while ($row = $result->fetch_assoc()) {
    $row['property_image'] = !empty($row['property_image']) ? $row['property_image'] : 'default.jpg';
    $row['appointment_date'] = date('M j, Y', strtotime($row['appointment_date']));
    $row['appointment_time'] = date('g:i A', strtotime($row['appointment_time']));
    $appointments[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $appointments
]);
?>