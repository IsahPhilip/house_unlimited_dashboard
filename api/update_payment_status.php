<?php
// api/update_payment_status.php - FINAL + DYNAMIC LUXURY LOGS + ZERO HARDCODING
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

// Only admin can update payment status
if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$payment_id = intval($input['payment_id'] ?? 0);
$new_status = trim($input['status'] ?? '');

if ($payment_id <= 0 || empty($new_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment ID and status are required']);
    exit;
}

// Validate allowed statuses
$allowed_statuses = ['pending', 'completed', 'failed', 'refunded', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Fetch full payment details BEFORE update for rich logging
$stmt = $db->prepare("
    SELECT p.*, 
           u.name AS client_name, 
           u.email,
           pr.title AS property_title,
           pr.location AS property_location
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ?
");
$stmt->bind_param('i', $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();
$stmt->close();

if (!$payment) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Payment not found']);
    exit;
}

// Build dynamic components
$client_name = $payment['client_name'] ?? 'Client';
$amount = '₦' . number_format($payment['amount']);
$ref = $payment['payment_ref'] ?? 'Unknown';

$property = $payment['property_title']
    ? $payment['property_title'] . ' in ' . ucwords($payment['property_location'] ?? '')
    : 'Booking/Reservation Fee';

$old_status = ucfirst($payment['status']);
$new_status_label = ucfirst($new_status);

// DYNAMIC, PROFESSIONAL LOG MESSAGE — NO HARDCODING EVER AGAIN
$log_message = match ($new_status) {
    'completed' => "Approved payment of $amount from $client_name for $property (Ref: $ref)",
    'refunded'  => "Processed REFUND of $amount to $client_name – $property (Ref: $ref)",
    'failed'    => "Marked payment FAILED – $amount from $client_name for $property (Ref: $ref)",
    'cancelled' => "Cancelled payment of $amount from $client_name – $property (Ref: $ref)",
    'pending'   => "Set payment status to PENDING – $amount from $client_name for $property (Ref: $ref)",
    default     => "Updated payment status from $old_status to $new_status_label – $amount by $client_name (Ref: $ref)"
};

// Update the payment status
$stmt = $db->prepare("UPDATE payments SET status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('si', $new_status, $payment_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    // FINAL DYNAMIC LOG — PURE DOMINANCE
    log_activity($log_message);

    echo json_encode([
        'success' => true,
        'message' => 'Payment status updated successfully',
        'new_status' => $new_status_label
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
}

$stmt->close();
?>