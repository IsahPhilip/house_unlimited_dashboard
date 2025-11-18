<?php
// api/update_appointment.php
// Allows Agent/Admin to Accept, Reschedule, or Reject an appointment

require '../inc/config.php';
require '../inc/auth.php';
require '../inc/send_email.php';

header('Content-Type: application/json');

// Only agents & admins can update appointments
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

// Fetch appointment + client + property details
$stmt = $db->prepare("
    SELECT a.*, p.title as property_title, u.name as client_name, u.email as client_email
    FROM appointments a
    LEFT JOIN properties p ON a.property_id = p.id
    LEFT JOIN users u ON a.client_id = u.id
    WHERE a.id = ?
");
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$appt = $result->fetch_assoc();
$stmt->close();

if (!$appt) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit;
}

// Prevent double actions
if ($appt['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'This appointment has already been processed']);
    exit;
}

$updateFields = [];
$params = [];
$types = 'i';

switch ($action) {
    case 'accept':
        $updateFields[] = "status = 'confirmed'";
        $updateFields[] = "agent_notes = ?";
        $params[] = "Appointment confirmed. See you on " . date('jS F, Y \a\t g:ia', strtotime($appt['appointment_date'] . ' ' . $appt['appointment_time']));
        $types .= 's';

        $emailSubject = "Your Viewing Appointment is CONFIRMED!";
        $emailBody = "
            <h2>Great News, {$appt['client_name']}! Your appointment is confirmed.</h2>
            <p><strong>Property:</strong> {$appt['property_title']}</p>
            <p><strong>Date & Time:</strong> " . date('l, jS F Y \a\t g:ia', strtotime($appt['appointment_date'] . ' ' . $appt['appointment_time'])) . "</p>
            <p>We look forward to showing you this amazing property!</p>
            <p>Warm regards,<br><strong>{$_SESSION['user']['name']}</strong><br>House Unlimited Agent</p>
        ";
        break;

    case 'reschedule':
        if (empty($new_date) || empty($new_time)) {
            echo json_encode(['success' => false, 'message' => 'New date and time required']);
            exit;
        }
        $updateFields[] = "appointment_date = ?";
        $updateFields[] = "appointment_time = ?";
        $updateFields[] = "status = 'rescheduled'";
        $updateFields[] = "agent_notes = ?";
        $params[] = $new_date;
        $params[] = $new_time;
        $params[] = $note ?: "Appointment rescheduled to " . date('jS F \a\t g:ia', strtotime("$new_date $new_time"));
        $types .= 'sss';

        $emailSubject = "Your Appointment Has Been Rescheduled";
        $emailBody = "
            <h2>Hi {$appt['client_name']},</h2>
            <p>Your viewing appointment for <strong>{$appt['property_title']}</strong> has been rescheduled.</p>
            <p><strong>New Date & Time:</strong> " . date('l, jS F Y \a\t g:ia', strtotime("$new_date $new_time")) . "</p>
            <p><strong>Agent Note:</strong> " . ($note ?: "No additional note") . "</p>
            <p>Let us know if this works for you!</p>
            <p>Best regards,<br><strong>{$_SESSION['user']['name']}</strong></p>
        ";
        break;

    case 'reject':
        $updateFields[] = "status = 'rejected'";
        $updateFields[] = "agent_notes = ?";
        $params[] = $note ?: "Appointment declined by agent.";
        $types .= 's';

        $emailSubject = "Update: Your Appointment Request";
        $emailBody = "
            <h2>Hi {$appt['client_name']},</h2>
            <p>Unfortunately, we are unable to accommodate your requested viewing time for <strong>{$appt['property_title']}</strong>.</p>
            <p><strong>Reason:</strong> " . ($note ?: "No reason provided") . "</p>
            <p>Please feel free to book another slot that works better.</p>
            <p>We're here to help!<br><strong>{$_SESSION['user']['name']}</strong></p>
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
    // Send email notification
    send_email($appt['client_email'], $emailSubject, $emailBody);

    // Log activity
    $log_action = $action === 'accept' ? 'confirmed' : $action . ' appointment';
    $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, appointment_id) VALUES (?, ?, ?)");
    $log_stmt->bind_param('isi', $_SESSION['user']['id'], $log_action, $appointment_id);
    $log_stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => ucfirst($action) . ' successful',
        'new_status' => $action === 'reschedule' ? 'rescheduled' : ($action === 'accept' ? 'confirmed' : 'rejected')
    ]);
} else {
    error_log("Appointment update failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Database error. Try again.']);
}

$stmt->close();
?>