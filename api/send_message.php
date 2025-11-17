<?php
// api/send_message.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$to_user = intval($input['to_user'] ?? 0);
$message = trim($input['message'] ?? '');
$property_id = !empty($input['property_id']) ? intval($input['property_id']) : null;

if ($to_user <= 0 || empty($message) || strlen($message) > 2000) {
    echo json_encode(['success' => false, 'error' => 'Invalid message']);
    exit;
}

$user_id = $_SESSION['user']['id'];

// Prevent self-messaging
if ($to_user == $user_id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $db->prepare("INSERT INTO messages (from_user, to_user, message, property_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iisi', $user_id, $to_user, $message, $property_id);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>