<?php
// api/send_message.php - FINAL & BULLETPROOF
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$to_user     = intval($input['to_user'] ?? 0);
$message     = trim($input['message'] ?? '');
$property_id = !empty($input['property_id']) ? intval($input['property_id']) : null;

if ($to_user <= 0 || empty($message) || strlen($message) > 5000) {
    echo json_encode(['success' => false, 'error' => 'Invalid message']);
    exit;
}

$user_id = $_SESSION['user']['id'];
if ($to_user == $user_id) {
    echo json_encode(['success' => false, 'error' => 'Cannot message yourself']);
    exit;
}

// Security: Verify recipient exists
$check = $db->query("SELECT id FROM users WHERE id = $to_user")->num_rows;
if ($check === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$stmt = $db->prepare("INSERT INTO messages (sender_id, recipient_id, message, property_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iisi', $user_id, $to_user, $message, $property_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message_id' => $db->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send']);
}
?>