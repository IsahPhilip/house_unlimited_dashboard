<?php
// api/update_payment_status.php - Update payment status
require '../inc/config.php';
require '../inc/auth.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$payment_id = $input['payment_id'] ?? 0;
$status = $input['status'] ?? '';

if (empty($payment_id) || empty($status)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Payment ID and status are required']);
    exit;
}

// Update payment status
$stmt = $db->prepare("UPDATE payments SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $payment_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    log_activity("Marked transaction as REFUNDED – Client changed mind");
    echo json_encode(['success' => true, 'message' => 'Payment status updated']);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
}
?>