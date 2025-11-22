<?php
// api/update_appointment.php - FINAL + DYNAMIC LUXURY LOGS + ZERO HARDCODING
require '../inc/config.php';
require '../inc/auth.php';
require '../inc/send_email.php';

header('Content-Type: application/json');

if (!in_array($_SESSION['user']['role'], ['agent', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = intval($data['id'] ?? 0);
$action         = $data['action'] ?? ''; // 'accept', 'reschedule', 'reject'
$new_date       = $data['date'] ?? '';
$new_time       = $data['time'] ?? '';
$note           = trim($data['note'] ?? '');

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit;
}

// Fetch appointment with FULL real data for RICH LOGGING
$stmt = $db->prepare("
    SELECT a.*, 
           p.title AS property_title, 
           p.location AS property_location,
           u.name AS client_name, 
           u.email AS client_email
    FROM appointments a
    LEFT JOIN properties p ON a.property_id = p.id
    LEFT JOIN users u ON a.user_id = u.id  -- assuming user_id = client
    WHERE a.id = ?
");
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appt) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit;
}

if ($appt['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'This appointment has already been processed']);
    exit;
}

// Build property string
$property_full = $appt['property_title'] 
    ? $appt['property_title'] . " in " . ucwords(str_replace(['-', '_'], ' ', $appt['property_location']))
    : "Property Viewing";

// Original date/time for logging
$original_date = date('l, j F Y \a\t g:i A', strtotime($appt['viewing_date'] . ' ' . $appt['viewing_time']));

$updateFields = [];
$params = [];
$types = 'i';

$log_message = "";  // We'll build this dynamically

switch ($action) {
    case 'accept':
        $updateFields[] = "status = 'confirmed'";
        $agent_note = "Appointment confirmed for " . date('l, j F Y \a\t g:i A', strtotime($appt['viewing_date'] . ' ' . $appt['viewing_time']));
        $updateFields[] = "agent_notes = ?";
        $params[] = $note ? "$note\n\n$agent_note" : $agent_note;
        $types .= 's';

        $log_message = "Confirmed viewing appointment for {$appt['client_name']} – $property_full on " . date('j M Y \a\t g:i A', strtotime($appt['viewing_date'] . ' ' . $appt['viewing_time']));

        $emailSubject = "Your Viewing Appointment is CONFIRMED!";
        $emailBody = "
            <h2>Great News, {$appt['client_name']}! Your appointment is confirmed.</h2>
            <p><strong>Property:</strong> $property_full</p>
            <p><strong>Date & Time:</strong> " . date('l, jS F Y \a\t g:i A', strtotime($appt['viewing_date'] . ' ' . $appt['viewing_time'])) . "</p>
            <p>We look forward to showing you this masterpiece!</p>
            <p>Warm regards,<br><strong>{$_SESSION['user']['name']}</strong><br>House Unlimited Agent</p>
        ";
        break;

    case 'reschedule':
        if (empty($new_date) || empty($new_time)) {
            echo json_encode(['success' => false, 'message' => 'New date and time required']);
            exit;
        }

        $new_datetime = "$new_date $new_time";
        $formatted_new = date('l, j F Y \a\t g:i A', strtotime($new_datetime));

        $updateFields[] = "viewing_date = ?";
        $updateFields[] = "viewing_time = ?";
        $updateFields[] = "status = 'rescheduled'";
        $updateFields[] = "agent_notes = ?";
        $params[] = $new_date;
        $params[] = $new_time;
        $params[] = $note ?: "Rescheduled from $original_date to $formatted_new";
        $types .= 'sss';

        $log_message = "Rescheduled viewing for {$appt['client_name']} – $property_full from $original_date → $formatted_new";

        $emailSubject = "Your Appointment Has Been Rescheduled";
        $emailBody = "
            <h2>Hi {$appt['client_name']},</h2>
            <p>Your viewing for <strong>$property_full</strong> has been rescheduled.</p>
            <p><strong>New Date & Time:</strong> $formatted_new</p>
            <p><strong>Reason/Note:</strong> " . ($note ?: "Agent requested a better time") . "</p>
            <p>Looking forward to hosting you!</p>
            <p>Best,<br><strong>{$_SESSION['user']['name']}</strong></p>
        ";
        break;

    case 'reject':
        $updateFields[] = "status = 'rejected'";
        $updateFields[] = "agent_notes = ?";
        $params[] = $note ?: "Appointment declined by agent.";
        $types .= 's';

        $log_message = "Rejected viewing request from {$appt['client_name']} for $property_full (originally $original_date)";

        $emailSubject = "Update: Your Appointment Request";
        $emailBody = "
            <h2>Hi {$appt['client_name']},</h2>
            <p>We're sorry — we're unable to accommodate your requested time for <strong>$property_full</strong>.</p>
            <p><strong>Reason:</strong> " . ($note ?: "No slots available at requested time") . "</p>
            <p>Please book another time that works better. We're excited to show you the property!</p>
            <p>Warm regards,<br><strong>{$_SESSION['user']['name']}</strong></p>
        ";
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

$updateFields[] = "updated_at = NOW()";
$sql = "UPDATE appointments SET " . implode(', ', $updateFields) . " WHERE id = ?";
$params[] = $appointment_id;
$types .= 'i';

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // SEND EMAIL
    if (!empty($appt['client_email'])) {
        send_email($appt['client_email'], $emailSubject, $emailBody);
    }

    // DYNAMIC, RICH, PROFESSIONAL LOG — NO HARDCODING
    log_activity($log_message);

    echo json_encode([
        'success' => true,
        'message' => ucfirst($action === 'reschedule' ? 'rescheduled' : $action) . ' successfully',
        'new_status' => $action === 'accept' ? 'confirmed' : ($action === 'reschedule' ? 'rescheduled' : 'rejected')
    ]);
} else {
    error_log("Appointment update failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Update failed. Please try again.']);
}

$stmt->close();
?>